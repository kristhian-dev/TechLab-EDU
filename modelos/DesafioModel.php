<?php

require_once APP_ROOT . '/nucleo/Database.php';

final class DesafioModel
{
    /**
     * Cadastra um novo desafio e retorna o id gerado.
     */
    public static function criar(array $dados): int
    {
        $pdo = Database::pdo();

        $dificuldade = strtoupper($dados['dificuldade'] ?? 'FACIL');
        if (!in_array($dificuldade, ['FACIL', 'MEDIO', 'DIFICIL'], true)) {
            $dificuldade = 'FACIL';
        }

        $stmt = $pdo->prepare(
            'INSERT INTO desafios (titulo, descricao, objetivo, categoria, dificuldade, pontuacao_maxima, nivel_minimo, ativo)
             VALUES (:titulo, :descricao, :objetivo, :categoria, :dificuldade, :pontuacao_maxima, :nivel_minimo, TRUE)'
        );

        $stmt->execute([
            'titulo' => $dados['titulo'],
            'descricao' => $dados['descricao'],
            'objetivo' => $dados['objetivo'] ?? null,
            'categoria' => $dados['categoria'] ?? null,
            'dificuldade' => $dificuldade,
            'pontuacao_maxima' => max(0, (int) ($dados['pontuacao_maxima'] ?? 0)),
            'nivel_minimo' => max(1, (int) ($dados['nivel_minimo'] ?? 1)),
        ]);

        return (int) $pdo->lastInsertId();
    }
}
