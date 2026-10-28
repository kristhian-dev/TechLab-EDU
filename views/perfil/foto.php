<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../modelos/UsuarioModel.php';

/**
 * Entrega a foto de perfil do USUÁRIO LOGADO.
 *
 * O diretório storage/uploads/ não é exposto via HTTP (bloqueado pelo
 * roteador do techlab.php e por .htaccess no Apache); este endpoint é o
 * único caminho para servir a imagem, e só devolve a foto de quem está
 * autenticado. O "?v=" enviado pelo navegador (modtime do arquivo) atua
 * como invalidador de cache a cada troca de foto.
 */
Auth::exigeLogin();

$usuario = Auth::usuarioLogado();
$foto = null;

try {
    $foto = UsuarioModel::buscarFotoPerfil((int) ($usuario['id'] ?? 0));
} catch (Throwable $e) {
    error_log('Foto de perfil: erro ao consultar o banco: ' . $e->getMessage());
}

// Sem foto, arquivo inexistente ou nome suspeito: cai no ícone padrão.
if (!$foto || basename($foto) !== $foto) {
    Seguranca::redirecionar('/assets/icons-login/user.png');
}

$caminho = APP_ROOT . '/storage/uploads/perfil/' . $foto;
if (!is_file($caminho)) {
    Seguranca::redirecionar('/assets/icons-login/user.png');
}

$extensao = strtolower(pathinfo($caminho, PATHINFO_EXTENSION));
$tipos = [
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'png' => 'image/png',
    'webp' => 'image/webp',
];

if (!isset($tipos[$extensao])) {
    Seguranca::redirecionar('/assets/icons-login/user.png');
}

header('Content-Type: ' . $tipos[$extensao]);
header('Content-Length: ' . (string) filesize($caminho));
header('X-Content-Type-Options: nosniff');
// O "?v=" na URL muda a cada troca (modtime), então um cache curto é seguro.
header('Cache-Control: private, max-age=3600');

readfile($caminho);
