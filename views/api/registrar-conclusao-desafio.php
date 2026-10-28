<?php
declare(strict_types=1);

require_once __DIR__ . '/../../configuracoes/app.php';
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

$usuario = $_SESSION['usuario'] ?? null;
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

$idBuild = isset($input['id_build']) ? (int) $input['id_build'] : null;
$idDesafio = isset($input['id_desafio']) ? (int) $input['id_desafio'] : null;
if (!$idBuild || $idBuild <= 0 || !$idDesafio || $idDesafio <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Id build e id desafio sao obrigatorios.']);
    exit;
}

$pdo = new PDO(
    sprintf(
        'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
        DB_HOST, DB_PORT, DB_NAME
    ),
    DB_USER, DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]
);
$pdo->beginTransaction();

try {
    $stmt = $pdo->prepare(
        'SELECT b.id_build, b.id_aluno, a.matricula, a.pontuacao_atual, dt.pontuacao_maxima, dt.categoria, dt.dificuldade
         FROM builds b
         JOIN alunos a ON a.id_usuario = b.id_aluno
         JOIN desafios dt ON dt.id_desafio = :id_desafio
         WHERE b.id_build = :id_build AND a.id_usuario = :id_usuario'
    );
    $stmt->execute(['id_build' => $idBuild, 'id_usuario' => $idUsuario, 'id_desafio' => $idDesafio]);
    $build = $stmt->fetch();

    if (!$build) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['ok' => false, 'erro' => 'Build nao encontrada ou nao pertence ao usuario.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT id_turma FROM aluno_turmas WHERE id_aluno = :id_aluno AND status = \'ATIVO\' LIMIT 1'
    );
    $stmt->execute(['id_aluno' => $build['id_aluno']]);
    $turma = $stmt->fetch();

    if (!$turma) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'Aluno nao esta vinculado a nenhuma turma ativa.']);
        exit;
    }

    $idTurma = (int) $turma['id_turma'];

    $stmt = $pdo->prepare(
        'SELECT * FROM desafio_turmas WHERE id_desafio = :id_desafio AND id_turma = :id_turma LIMIT 1'
    );
    $stmt->execute(['id_desafio' => $idDesafio, 'id_turma' => $idTurma]);
    $desafioTurma = $stmt->fetch();

    if (!$desafioTurma) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'Desafio nao esta disponivel para esta turma.']);
        exit;
    }

    // NOVO: Validar condições do desafio no servidor usando dados da build
    // Consultar quais componentes estão na build
    $stmt = $pdo->prepare(
        'SELECT id_componente FROM build_componentes WHERE id_build = :id_build'
    );
    $stmt->execute(['id_build' => $idBuild]);
    $componentesBuild = [];
    while ($row = $stmt->fetch()) {
        $componentesBuild[] = $row['id_componente'];
    }

    // Consultar os tipos de componentes presentes na build (JOIN com componentes para obter o tipo)
    $stmt = $pdo->prepare(
        'SELECT c.tipo FROM build_componentes bc JOIN componentes c ON bc.id_componente = c.id_componente WHERE bc.id_build = :id_build'
    );
    $stmt->execute(['id_build' => $idBuild]);
    $componentesBuildTipos = [];
    while ($row = $stmt->fetch()) {
        $componentesBuildTipos[] = $row['tipo'];
    }

    // Consultar as condições de resolução do desafio
    $stmt = $pdo->prepare(
        'SELECT tipo_condicao, tipo_componente, obrigatorio FROM desafio_condicoes_resolucao WHERE id_desafio = :id_desafio ORDER BY ordem'
    );
    $stmt->execute(['id_desafio' => $idDesafio]);
    $condicoesDesafio = $stmt->fetchAll();

    $condicoesOk = true;
    foreach ($condicoesDesafio as $condicao) {
        $tipoCond = $condicao['tipo_condicao'];
        $tipoComp = $condicao['tipo_componente'];
        $obrigatorio = $condicao['obrigatorio'];

        if ($obrigatorio && $tipoCond === 'COMPONENTE_PRESENTE') {
            $temComponente = in_array($tipoComp, $componentesBuildTipos, true);
            if (!$temComponente) {
                $condicoesOk = false;
                break;
            }
        }
    }

    if (!$condicoesOk) {
        $pdo->rollBack();
        http_response_code(400);
        echo json_encode(['ok' => false, 'erro' => 'Condicoes do desafio nao atendidas. Verifique se todos os componentes obrigatorios foram instalados.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'SELECT status, pontuacao_obtida, numero_tentativas FROM aluno_desafios
         WHERE id_aluno = :id_aluno AND id_desafio = :id_desafio AND id_turma = :id_turma'
    );
    $stmt->execute(['id_aluno' => $build['id_aluno'], 'id_desafio' => $idDesafio, 'id_turma' => $idTurma]);
    $alunoDesafio = $stmt->fetch();

    $jáConcluido = false;
    if ($alunoDesafio && $alunoDesafio['status'] === 'CONCLUIDO') {
        $jáConcluido = true;
    }

    // Usar incremento atômico no banco de dados para evitar corrida de condições
    $pontuacaoIncremento = (int) ($build['pontuacao_maxima'] ?? 0);

    if (!$jáConcluido) {
        $stmt = $pdo->prepare(
            'INSERT INTO aluno_desafios (id_aluno, id_desafio, id_turma, status, pontuacao_obtida, numero_tentativas, concluido_em)
             VALUES (:id_aluno, :id_desafio, :id_turma, :status, :pontuacao_obtida, :numero_tentativas, NOW())
             ON DUPLICATE KEY UPDATE
                 status = VALUES(status),
                 pontuacao_obtida = VALUES(pontuacao_obtida),
                 numero_tentativas = numero_tentativas + 1,
                 concluido_em = VALUES(concluido_em)'
        );
        $stmt->execute([
            'id_aluno' => $build['id_aluno'],
            'id_desafio' => $idDesafio,
            'id_turma' => $idTurma,
            'status' => 'CONCLUIDO',
            'pontuacao_obtida' => $pontuacaoIncremento,
            'numero_tentativas' => 1,
        ]);
    } else {
        // Incremento atômico de tentativas para requisições repetidas
        $stmt = $pdo->prepare(
            'UPDATE aluno_desafios SET numero_tentativas = numero_tentativas + 1 WHERE id_aluno = :id_aluno AND id_desafio = :id_desafio AND id_turma = :id_turma'
        );
        $stmt->execute([
            'id_aluno' => $build['id_aluno'],
            'id_desafio' => $idDesafio,
            'id_turma' => $idTurma,
        ]);
    }

    $stmt = $pdo->prepare(
        'UPDATE alunos SET pontuacao_atual = pontuacao_atual + :incremento WHERE id_usuario = :id_usuario'
    );
    $stmt->execute([
        'incremento' => $pontuacaoIncremento,
        'id_usuario' => $build['id_aluno'],
    ]);

    $pdo->commit();

    $pontuacaoTotal = $pontuacaoIncremento;
    if ($alunoDesafio) {
        $pontuacaoTotal = ($alunoDesafio['pontuacao_obtida'] ?? 0) + $pontuacaoIncremento;
    }

    echo json_encode([
        'ok' => true,
        'status' => 'CONCLUIDO',
        'já_concluido' => $jáConcluido,
        'pontuacao_obtida' => $pontuacaoTotal,
        'numero_tentativas' => $tentativasAtual,
        'xp_ganha' => $pontuacaoIncremento,
        'mensagem' => 'Desafio concluído com sucesso!',
    ]);

} catch (Throwable $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao registrar conclusao de desafio: ' . $e->getMessage()]);
}
