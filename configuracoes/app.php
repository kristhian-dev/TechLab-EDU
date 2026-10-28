<?php

define('APP_ROOT', dirname(__DIR__));

$dotenvFile = APP_ROOT . '/.env';
if (is_file($dotenvFile)) {
    $lines = file($dotenvFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) {
            continue;
        }
        [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
        $_ENV[trim($key)] = trim($value, " \n\r\t\v\0\"'");
        putenv(trim($key) . '=' . trim($value, " \n\r\t\v\0\"'"));
    }
}

define('APP_ENV', getenv('APP_ENV') ?: 'development');
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', (int) (getenv('DB_PORT') ?: 3306));
define('DB_NAME', getenv('DB_NAME') ?: 'techlab_edu');
define('DB_USER', getenv('DB_USER') ?: 'techlab_user');
define('DB_PASS', getenv('DB_PASS') ?: 'techlab_pass');

date_default_timezone_set('America/Sao_Paulo');

$sessionDir = APP_ROOT . '/storage/sessions';
if (!is_dir($sessionDir)) {
    mkdir($sessionDir, 0777, true);
}
session_save_path($sessionDir);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
