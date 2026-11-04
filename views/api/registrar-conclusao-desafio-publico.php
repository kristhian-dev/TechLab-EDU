<?php
declare(strict_types=1);

require_once __DIR__ . '/../../configuracoes/app.php';
require_once APP_ROOT . '/nucleo/Auth.php';
require_once APP_ROOT . '/nucleo/Database.php';
require_once APP_ROOT . '/nucleo/Seguranca.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'erro' => 'Metodo nao permitido.']);
    exit;
}

if (!Seguranca::validarTokenCsrf()) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'erro' => 'Token CSRF invalido.']);
    exit;
}

$usuario = Auth::usuarioLogado();
if (!$usuario) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'erro' => 'Usuario nao autenticado.']);
    exit;
}

$idUsuario = (int) ($usuario['id'] ?? 0);
if ($idUsuario <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Id de usuario invalido.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Payload invalido.']);
    exit;
}

$idBuild = isset($input['id_build']) ? (int) $input['id_build'] : 0;
$idDesafio = isset($input['id_desafio']) ? (int) $input['id_desafio'] : 0;
if ($idBuild <= 0 || $idDesafio <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Id build e id desafio sao obrigatorios.']);
    exit;
}

$pdo = null;
try {
    $pdo = Database::pdo();

    if (Auth::perfilDoUsuario($idUsuario) !== 'aluno') {
        http_response_code(403);
        echo json_encode(['ok' => false, 'erro' => 'Apenas alunos podem concluir desafios publicos.']);
        exit;
    }

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        'SELECT b.id_build, b.id_aluno, b.status AS status_build,
                d.id_desafio, d.pontuacao_maxima, d.visibilidade
         FROM builds b
         INNER JOIN desafios d
                 ON d.id_desafio = :id_desafio
                AND d.ativo = TRUE
                AND d.visibilidade = \'PUBLICO\'
         WHERE b.id_build = :id_build
           AND b.id_aluno = :id_aluno
         LIMIT 1'
    );
    $stmt->execute([
        'id_build' => $idBuild,
        'id_aluno' => $idUsuario,
        'id_desafio' => $idDesafio,
    ]);
    $build = $stmt->fetch();

    if (!$build) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['ok' => false, 'erro' => 'Build nao encontrada, nao pertence ao aluno ou desafio publico invalido.']);
        exit;
    }

    if ($build['status_build'] !== 'COMPLETA') {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'A build precisa estar COMPLETA para concluir o desafio.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT c.tipo
         FROM build_componentes bc
         INNER JOIN componentes c ON c.id_componente = bc.id_componente
         WHERE bc.id_build = :id_build'
    );
    $stmt->execute(['id_build' => $idBuild]);
    $componentesBuildTipos = array_map(
        static fn(array $row): string => (string) $row['tipo'],
        $stmt->fetchAll()
    );

    $stmt = $pdo->prepare(
        'SELECT tipo_condicao, tipo_componente, obrigatorio
         FROM desafio_condicoes_resolucao
         WHERE id_desafio = :id_desafio
         ORDER BY ordem'
    );
    $stmt->execute(['id_desafio' => $idDesafio]);
    $condicoesDesafio = $stmt->fetchAll();

    foreach ($condicoesDesafio as $condicao) {
        if ((int) $condicao['obrigatorio'] !== 1) {
            continue;
        }

        if ($condicao['tipo_condicao'] === 'COMPONENTE_PRESENTE') {
            if (in_array((string) $condicao['tipo_componente'], $componentesBuildTipos, true)) {
                continue;
            }

            $pdo->rollBack();
            http_response_code(400);
            echo json_encode(['ok' => false, 'erro' => 'Condicoes do desafio nao atendidas.']);
            exit;
        }

        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'Tipo de condicao obrigatoria nao suportado.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id_diagnostico, id_build, id_desafio, status
         FROM diagnosticos
         WHERE id_diagnostico = :id_diagnostico
           AND id_build = :id_build
           AND id_desafio = :id_desafio
           AND status = \'SUCESSO\'
         ORDER BY data_execucao DESC, id_diagnostico DESC
         LIMIT 1'
    );
    if (isset($input['id_diagnostico']) && (int) $input['id_diagnostico'] > 0) {
        $stmt = $pdo->prepare(
            'SELECT id_diagnostico, id_build, id_desafio, status
             FROM diagnosticos
             WHERE id_diagnostico = :id_diagnostico
               AND id_build = :id_build
               AND id_desafio = :id_desafio
               AND status = \'SUCESSO\'
             LIMIT 1'
        );
        $stmt->execute([
            'id_diagnostico' => (int) $input['id_diagnostico'],
            'id_build' => $idBuild,
            'id_desafio' => $idDesafio,
        ]);
    } else {
        $stmt->execute([
            'id_build' => $idBuild,
            'id_desafio' => $idDesafio,
        ]);
    }
    $diagnostico = $stmt->fetch();

    if (!$diagnostico) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'E necessario um diagnostico SUCESSO desta build para este desafio.']);
        exit;
    }

    $pontuacao = (int) $build['pontuacao_maxima'];
    $stmt = $pdo->prepare(
        'INSERT INTO aluno_desafios_publicos
            (id_aluno, id_desafio, id_build, id_diagnostico, status,
             pontuacao_obtida, numero_tentativas, iniciado_em, concluido_em)
         VALUES
            (:id_aluno, :id_desafio, NULL, NULL, \'EM_ANDAMENTO\',
             0, 0, NOW(), NULL)
         ON DUPLICATE KEY UPDATE
            id_aluno_desafio_publico = LAST_INSERT_ID(id_aluno_desafio_publico)'
    );
    $stmt->execute([
        'id_aluno' => $idUsuario,
        'id_desafio' => $idDesafio,
    ]);

    $idProgresso = (int) $pdo->lastInsertId();
    $stmt = $pdo->prepare(
        'SELECT id_aluno_desafio_publico, status, pontuacao_obtida,
                numero_tentativas, xp_concedido_em
         FROM aluno_desafios_publicos
         WHERE id_aluno_desafio_publico = :id_progresso
         FOR UPDATE'
    );
    $stmt->execute(['id_progresso' => $idProgresso]);
    $progresso = $stmt->fetch();

    if (!$progresso) {
        throw new RuntimeException('Progresso publico nao foi localizado apos o registro.');
    }

    $jaConcluido = $progresso['status'] === 'CONCLUIDO';
    $xpConcedido = false;
    if (!$jaConcluido) {
        $stmt = $pdo->prepare(
            'UPDATE aluno_desafios_publicos
             SET status = \'CONCLUIDO\',
                 pontuacao_obtida = :pontuacao,
                 id_build = COALESCE(id_build, :id_build),
                 id_diagnostico = COALESCE(id_diagnostico, :id_diagnostico),
                 iniciado_em = COALESCE(iniciado_em, NOW()),
                 concluido_em = NOW(),
                 numero_tentativas = numero_tentativas + 1
             WHERE id_aluno_desafio_publico = :id_progresso
               AND status <> \'CONCLUIDO\''
        );
        $stmt->execute([
            'pontuacao' => $pontuacao,
            'id_build' => $idBuild,
            'id_diagnostico' => $diagnostico['id_diagnostico'],
            'id_progresso' => $idProgresso,
        ]);
        if ($stmt->rowCount() === 1) {
            $stmt = $pdo->prepare(
                'UPDATE aluno_desafios_publicos
                 SET xp_concedido_em = NOW()
                 WHERE id_aluno_desafio_publico = :id_progresso
                   AND status = \'CONCLUIDO\'
                   AND xp_concedido_em IS NULL'
            );
            $stmt->execute(['id_progresso' => $idProgresso]);
            $xpConcedido = $stmt->rowCount() === 1;
        }
    }

    if ($xpConcedido) {
        $stmt = $pdo->prepare(
            'UPDATE alunos
             SET pontuacao_atual = pontuacao_atual + :incremento
             WHERE id_usuario = :id_aluno'
        );
        $stmt->execute([
            'incremento' => $pontuacao,
            'id_aluno' => $idUsuario,
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'status' => 'CONCLUIDO',
        'ja_concluido' => $jaConcluido,
        'pontuacao_obtida' => (int) $progresso['pontuacao_obtida'],
        'numero_tentativas' => (int) $progresso['numero_tentativas'],
        'xp_ganha' => $xpConcedido ? $pontuacao : 0,
        'mensagem' => $xpConcedido
            ? 'Desafio publico concluido com sucesso!'
            : 'Desafio publico ja estava concluido.',
    ]);
} catch (Throwable $e) {
    if ($pdo instanceof PDO && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao registrar conclusao de desafio publico: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao registrar conclusao de desafio publico.']);
}
