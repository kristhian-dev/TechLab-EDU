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
$tipo = trim((string) ($input['tipo'] ?? 'montagem'));
$status = in_array($input['status'] ?? '', ['SUCESSO', 'ALERTA', 'FALHA'], true) ? $input['status'] : 'ALERTA';
$descricao = trim((string) ($input['descricao'] ?? ''));
$resultado = trim((string) ($input['resultado'] ?? ''));
$temperatura = isset($input['temperatura_simulada']) ? (float) $input['temperatura_simulada'] : null;
$potencia = isset($input['potencia_total_simulada']) ? (float) $input['potencia_total_simulada'] : null;

if (!$idBuild || $idBuild <= 0) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'erro' => 'Id da build e obrigatorio.']);
    exit;
}

try {
    $pdo = Database::pdo();
    $pdo->beginTransaction();

    // Verifica se a build pertence ao aluno logado
    $stmt = $pdo->prepare('
        SELECT b.id_build, b.id_aluno 
        FROM builds b 
        JOIN alunos a ON a.id_usuario = b.id_aluno 
        WHERE b.id_build = :id_build AND a.id_usuario = :id_usuario
    ');
    $stmt->execute(['id_build' => $idBuild, 'id_usuario' => $idUsuario]);
    $build = $stmt->fetch();
    if (!$build) {
        $pdo->rollBack();
        http_response_code(403);
        echo json_encode(['ok' => false, 'erro' => 'Build nao encontrada ou nao pertence ao usuario.']);
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO diagnosticos (id_build, id_desafio, tipo, status, descricao, resultado, temperatura_simulada, potencia_total_simulada)
         VALUES (:id_build, :id_desafio, :tipo, :status, :descricao, :resultado, :temperatura, :potencia)'
    );
    $stmt->execute([
        'id_build' => $idBuild,
        'id_desafio' => $idDesafio,
        'tipo' => $tipo,
        'status' => $status,
        'descricao' => $descricao,
        'resultado' => $resultado,
        'temperatura' => $temperatura,
        'potencia' => $potencia,
    ]);
    $idDiagnostico = (int) $pdo->lastInsertId();

    $pdo->commit();

    echo json_encode([
        'ok' => true,
        'id_diagnostico' => $idDiagnostico,
        'mensagem' => 'Diagnostico salvo com sucesso.',
    ]);
} catch (Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('Erro ao salvar diagnostico: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['ok' => false, 'erro' => 'Erro ao salvar diagnostico.']);
}
