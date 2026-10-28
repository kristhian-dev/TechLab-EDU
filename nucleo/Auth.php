<?php

require_once __DIR__ . '/Seguranca.php';
require_once APP_ROOT . '/nucleo/Database.php';

final class Auth
{
    public static function usuarioLogado(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function perfilDoUsuario(int $idUsuario): string
    {
        $pdo = Database::pdo();

        $checks = [
            'administradores' => 'administrador',
            'professores' => 'professor',
            'alunos' => 'aluno',
        ];

        foreach ($checks as $tabela => $perfil) {
            $sql = "SELECT 1 FROM {$tabela} WHERE id_usuario = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute(['id' => $idUsuario]);
            if ($stmt->fetch()) {
                return $perfil;
            }
        }

        // Fallback: usa a hierarquia gravada em usuarios.tipo_usuario durante o cadastro.
        $stmt = $pdo->prepare('SELECT tipo_usuario FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $idUsuario]);
        $tipo = strtoupper((string) $stmt->fetchColumn());

        if ($tipo === 'PROFESSOR') {
            return 'professor';
        }

        return 'aluno';
    }

    public static function exigeLogin(): void
    {
        if (!self::usuarioLogado()) {
            Seguranca::flash('warning', 'Faça login para continuar.');
            Seguranca::redirecionar('/views/auth/login.php');
        }
    }

    public static function exigePerfil(string ...$perfis): void
    {
        self::exigeLogin();

        $usuario = self::usuarioLogado();
        $perfilAtual = $usuario['perfil'] ?? 'aluno';

        if (!in_array($perfilAtual, $perfis, true)) {
            Seguranca::flash('danger', 'Você não possui permissão para acessar esta área.');
            Seguranca::redirecionar('/views/auth/login.php');
        }
    }

    public static function redirecionarDashboard(array $usuario): void
    {
        $perfil = $usuario['perfil'] ?? 'aluno';

        $destino = '/views/aluno/dashboard.php';
        if ($perfil === 'professor') {
            $destino = '/views/professor/dashboard.php';
        }

        if ($perfil === 'administrador') {
            $destino = '/views/professor/dashboard.php';
        }

        Seguranca::redirecionar($destino);
    }
}
