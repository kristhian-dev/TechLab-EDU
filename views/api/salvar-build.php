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

$nome = trim((string) ($input['nome'] ?? ''));
$descricao = trim((string) ($input['descricao'] ?? ''));
$componentes = is_array($input['componentes']) ? $input['componentes'] : [];
$status = in_array($input['status'] ?? '', ['EM_MONTAGEM', 'COMPLETA', 'ARQUIVADA'], true) ? $input['status'] : 'EM_MONTAGEM';

if ($nome === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Nome da build e obrigatorio.']);
    exit;
}

try {
    $pdo = Database::pdo();
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('SELECT id_usuario FROM alunos WHERE id_usuario = :id LIMIT 1');
    $stmt->execute(['id' => $idUsuario]);
    $aluno = $stmt->fetch();
    if (!$aluno) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['ok' => false, 'erro' => 'Usuario nao e um aluno cadastrado.']);
        exit;
    }
    $idAluno = (int) $aluno['id_usuario'];

    $stmt = $pdo->prepare(
        'INSERT INTO builds (id_aluno, nome, descricao, status) VALUES (:id_aluno, :nome, :descricao, :status)'
    );
    $stmt->execute([
        'id_aluno' => $idAluno,
        'nome' => $nome,
        'descricao' => $descricao,
        'status' => $status,
    ]);
    $idBuild = (int) $pdo->lastInsertId();

    $ordem = 0;
    foreach ($componentes as $comp) {
        $idComponente = isset($comp['id_componente']) ? (int) $comp['id_componente'] : null;
        $slot = isset($comp['slot_instalacao']) ? (string) $comp['slot_instalacao'] : null;
        if ($idComponente <= 0) continue;

        $stmt = $pdo->prepare(
            'INSERT INTO build_componentes (id_build, id_componente, slot_instalacao, ordem_instalacao)
             VALUES (:id_build, :id_componente, :slot_instalacao, :ordem)'
        );
        $stmt->execute([
            'id_build' => $idBuild,
            'id_componente' => $idComponente,
            'slot_instalacao' => $slot,
            'ordem' => $ordem++,
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'id_build' => $idBuild,
        'mensagem' => 'Build salva com sucesso.',
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao salvar build: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao salvar build.']);
}
