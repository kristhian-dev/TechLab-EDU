<?php

require_once APP_ROOT . '/nucleo/Database.php';

final class UsuarioModel
{
    public static function buscarPorId(int $idUsuario): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $idUsuario]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    public static function buscarPorCredencial(string $valor): ?array
    {
        $pdo = Database::pdo();
        // Placeholders distintos: prepares nativos do MySQL nao reutilizam o mesmo nome.
        $stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = :email OR nome_usuario = :nome_usuario LIMIT 1');
        $stmt->execute([
            'email' => trim($valor),
            'nome_usuario' => trim($valor),
        ]);

        return $stmt->fetch() ?: null;
    }

    public static function emailExiste(string $email): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT 1 FROM usuarios WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);

        return (bool) $stmt->fetch();
    }

    public static function nomeUsuarioExiste(string $nomeUsuario): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT 1 FROM usuarios WHERE nome_usuario = :nome_usuario LIMIT 1');
        $stmt->execute(['nome_usuario' => $nomeUsuario]);

        return (bool) $stmt->fetch();
    }

    public static function matriculaExiste(string $matricula): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT 1 FROM alunos WHERE matricula = :matricula LIMIT 1');
        $stmt->execute(['matricula' => $matricula]);

        return (bool) $stmt->fetch();
    }

    public static function criarUsuario(array $dados): int
    {
        $pdo = Database::pdo();
        $hash = password_hash($dados['senha'], PASSWORD_DEFAULT);
        $tipo = ($dados['tipo'] ?? 'ALUNO') === 'PROFESSOR' ? 'PROFESSOR' : 'ALUNO';

        $stmt = $pdo->prepare('INSERT INTO usuarios (nome, nome_usuario, email, senha_hash, tipo_usuario, ativo) VALUES (:nome, :nome_usuario, :email, :senha_hash, :tipo_usuario, TRUE)');
        $stmt->execute([
            'nome' => $dados['nome'],
            'nome_usuario' => $dados['nome_usuario'],
            'email' => $dados['email'],
            'senha_hash' => $hash,
            'tipo_usuario' => $tipo,
        ]);

        return (int) $pdo->lastInsertId();
    }

    public static function criarPerfilAluno(int $idUsuario, string $matricula): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO alunos (id_usuario, matricula) VALUES (:id_usuario, :matricula)');
        $stmt->execute(['id_usuario' => $idUsuario, 'matricula' => $matricula]);
    }

    public static function criarPerfilProfessor(int $idUsuario): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT INTO professores (id_usuario) VALUES (:id_usuario)');
        $stmt->execute(['id_usuario' => $idUsuario]);
    }

    public static function buscarFotoPerfil(int $idUsuario): ?string
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT foto_perfil FROM usuarios WHERE id_usuario = :id LIMIT 1');
        $stmt->execute(['id' => $idUsuario]);
        $foto = $stmt->fetchColumn();

        return is_string($foto) && $foto !== '' ? $foto : null;
    }

    /**
     * Atualiza apenas a foto do próprio usuário autenticado: o WHERE usa
     * exclusivamente o id vindo da sessão, nunca dados enviados na requisição.
     */
    public static function atualizarFotoPerfil(int $idUsuario, string $nomeArquivo): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE usuarios SET foto_perfil = :foto WHERE id_usuario = :id');
        $stmt->execute([
            'foto' => $nomeArquivo,
            'id' => $idUsuario,
        ]);
    }

    public static function registrarFalhaLogin(int $idUsuario): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_incorretas_login = tentativas_incorretas_login + 1 WHERE id_usuario = :id');
        $stmt->execute(['id' => $idUsuario]);
    }

    public static function resetarTentativasLogin(int $idUsuario): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_incorretas_login = 0, bloqueado_ate = NULL WHERE id_usuario = :id');
        $stmt->execute(['id' => $idUsuario]);
    }

    public static function bloquearConta(int $idUsuario, int $minutos = 15): void
    {
        $pdo = Database::pdo();
        $bloqueadoAte = (new DateTimeImmutable())->add(new DateInterval('PT' . $minutos . 'M')); 
        $stmt = $pdo->prepare('UPDATE usuarios SET tentativas_incorretas_login = :tentativas, bloqueado_ate = :bloqueado_ate WHERE id_usuario = :id');
        $stmt->execute([
            'tentativas' => 5,
            'bloqueado_ate' => $bloqueadoAte->format('Y-m-d H:i:s'),
            'id' => $idUsuario,
        ]);
    }
}
