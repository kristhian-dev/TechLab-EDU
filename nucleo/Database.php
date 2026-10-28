<?php

require_once APP_ROOT . '/configuracoes/app.php';

final class Database
{
    public static function pdo(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            DB_HOST,
            DB_PORT,
            DB_NAME
        );

        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
            PDO::MYSQL_ATTR_MULTI_STATEMENTS => true,
        ];

        try {
            return new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (Throwable $e) {
            throw new RuntimeException('Falha ao conectar ao banco de dados TechLab EDU. Verifique as configurações de conexão.', 0, $e);
        }
    }

    public static function serverPdo(): PDO
    {
        $dsn = sprintf('mysql:host=%s;port=%d;charset=utf8mb4', DB_HOST, DB_PORT);
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4',
        ];

        return new PDO($dsn, DB_USER, DB_PASS, $options);
    }

    public static function initializeSchema(): void
    {
        $schemaPath = APP_ROOT . '/banco/techlab_edu_v2.sql';

        if (!is_file($schemaPath)) {
            return;
        }

        $server = self::serverPdo();
        $server->exec('CREATE DATABASE IF NOT EXISTS ' . DB_NAME . ' CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci');

        $pdo = self::pdo();
        $result = $pdo->query("SHOW TABLES LIKE 'usuarios'");
        if ($result && $result->fetch()) {
            self::garantirCampoFotoPerfil($pdo);
            return;
        }

        $sql = file_get_contents($schemaPath);
        if ($sql === false || trim($sql) === '') {
            return;
        }

        $pdo->exec($sql);
    }

    /**
     * Migração idempotente: garante a coluna de foto de perfil em bancos
     * criados antes do recurso existir. Bancos novos já nascem com a coluna
     * (banco/techlab_edu_v2.sql); esta rotina apenas a adiciona quando falta,
     * preservando todos os dados atuais.
     */
    private static function garantirCampoFotoPerfil(PDO $pdo): void
    {
        $coluna = $pdo->query("SHOW COLUMNS FROM usuarios LIKE 'foto_perfil'");
        if ($coluna && $coluna->fetch()) {
            return;
        }

        $pdo->exec("ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL AFTER senha_hash");
    }
}
