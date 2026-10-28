<?php

require_once __DIR__ . '/configuracoes/app.php';
require_once __DIR__ . '/nucleo/Seguranca.php';
require_once __DIR__ . '/nucleo/Database.php';
require_once __DIR__ . '/nucleo/Auth.php';
require_once __DIR__ . '/modelos/UsuarioModel.php';
require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/DashboardController.php';

try {
    Database::initializeSchema();
} catch (Throwable $e) {
    error_log('Erro ao inicializar o banco: ' . $e->getMessage());
}

$route = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$route = '/' . trim($route, '/');

if ($route === '/' || $route === '/index.php') {
    Seguranca::redirecionar('/views/auth/login.php');
}

if ($route === '/login') {
    Seguranca::redirecionar('/views/auth/login.php');
}

if ($route === '/cadastro') {
    Seguranca::redirecionar('/views/auth/cadastro.php');
}

if ($route === '/logout' || $route === '/logout.php') {
    AuthController::logout();
}

if ($route === '/dashboard') {
    DashboardController::redirectToDashboard();
}

if (str_starts_with($route, '/views/')) {
    $arquivo = __DIR__ . $route;
    if (is_file($arquivo)) {
        require $arquivo;
        exit;
    }
}

Seguranca::redirecionar('/views/auth/login.php');
