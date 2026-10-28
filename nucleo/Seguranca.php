<?php

final class Seguranca
{
    public static function gerarTokenCsrf(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }

        return $_SESSION['csrf_token'];
    }

    public static function validarTokenCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? null;
        return is_string($token) && hash_equals($_SESSION['csrf_token'] ?? '', $token);
    }

    public static function esc(string $valor): string
    {
        return htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
    }

    public static function redirecionar(string $destino): void
    {
        if (!headers_sent()) {
            header('Location: ' . $destino);
        }
        exit;
    }

    public static function flash(string $tipo, string $mensagem): void
    {
        $_SESSION['flash'] = [
            'type' => $tipo,
            'message' => $mensagem,
        ];
    }
}
