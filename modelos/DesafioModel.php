<?php

require_once APP_ROOT . '/nucleo/Database.php';

final class DesafioModel
{
    private const VISIBILIDADES = ['PUBLICO', 'PRIVADO'];

    public static function listarAtivos(): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->query(
            'SELECT id_desafio, titulo, descricao, objetivo, categoria, dificuldade,
                    pontuacao_maxima, nivel_minimo, visibilidade, id_criador, data_criacao
             FROM desafios
             WHERE ativo = TRUE
             ORDER BY data_criacao DESC, id_desafio DESC'
        );

        return $stmt->fetchAll();
    }

    /**
     * Lista desafios ativos com o estado da associação para uma turma.
     */
    public static function listarAtivosParaTurma(int $idTurma): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.id_desafio, d.titulo, d.descricao, d.categoria, d.dificuldade,
                    d.visibilidade,
                    d.pontuacao_maxima, dt.id_turma AS associado_id_turma,
                    dt.ordem, dt.obrigatorio, dt.data_liberacao
             FROM desafios d
             LEFT JOIN desafio_turmas dt
                    ON dt.id_desafio = d.id_desafio AND dt.id_turma = :id_turma
             WHERE d.ativo = TRUE
             ORDER BY (dt.id_turma IS NULL) ASC, dt.ordem ASC,
                      d.data_criacao DESC, d.id_desafio DESC'
        );
        $stmt->execute(['id_turma' => $idTurma]);

        return $stmt->fetchAll();
    }

    /**
     * Associa um desafio ativo a uma turma, respeitando a ordem exclusiva.
     */
    public static function associarATurma(int $idDesafio, int $idTurma, int $ordem, bool $obrigatorio, ?string $dataLiberacao): void
    {
        if ($idDesafio <= 0 || $idTurma <= 0 || $ordem <= 0) {
            throw new InvalidArgumentException('Informe um desafio, uma turma e uma ordem válidos.');
        }

        $pdo = Database::pdo();

        $stmt = $pdo->prepare('SELECT 1 FROM desafios WHERE id_desafio = :desafio AND ativo = TRUE LIMIT 1');
        $stmt->execute(['desafio' => $idDesafio]);
        if (!$stmt->fetch()) {
            throw new InvalidArgumentException('Desafio inválido ou inativo.');
        }

        $stmt = $pdo->prepare('SELECT 1 FROM desafio_turmas WHERE id_desafio = :desafio AND id_turma = :turma LIMIT 1');
        $stmt->execute(['desafio' => $idDesafio, 'turma' => $idTurma]);
        if ($stmt->fetch()) {
            throw new InvalidArgumentException('Este desafio já está associado à turma.');
        }

        $stmt = $pdo->prepare('SELECT 1 FROM desafio_turmas WHERE id_turma = :turma AND ordem = :ordem LIMIT 1');
        $stmt->execute(['turma' => $idTurma, 'ordem' => $ordem]);
        if ($stmt->fetch()) {
            throw new InvalidArgumentException('A ordem informada já está sendo usada nesta turma.');
        }

        $stmt = $pdo->prepare(
            'INSERT INTO desafio_turmas (id_desafio, id_turma, ordem, obrigatorio, data_liberacao)
             VALUES (:desafio, :turma, :ordem, :obrigatorio, :data_liberacao)'
        );
        $stmt->execute([
            'desafio' => $idDesafio,
            'turma' => $idTurma,
            'ordem' => $ordem,
            'obrigatorio' => $obrigatorio ? 1 : 0,
            'data_liberacao' => $dataLiberacao,
        ]);
    }

    /**
     * Remove uma associação sem apagar o desafio original ou seu progresso.
     */
    public static function removerDaTurma(int $idDesafio, int $idTurma): void
    {
        $pdo = Database::pdo();

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM aluno_desafios
             WHERE id_desafio = :desafio AND id_turma = :turma'
        );
        $stmt->execute(['desafio' => $idDesafio, 'turma' => $idTurma]);
        if ((int) $stmt->fetchColumn() > 0) {
            throw new RuntimeException('A associação possui progresso de alunos e não pode ser removida sem apagar esse histórico.');
        }

        $stmt = $pdo->prepare(
            'DELETE FROM desafio_turmas
             WHERE id_desafio = :desafio AND id_turma = :turma'
        );
        $stmt->execute(['desafio' => $idDesafio, 'turma' => $idTurma]);

        if ($stmt->rowCount() === 0) {
            throw new InvalidArgumentException('Associação não encontrada.');
        }
    }

    public static function listarAtivosComProgresso(int $idAluno): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT d.id_desafio,
                    (
                        SELECT dt2.id_turma
                        FROM desafio_turmas dt2
                        INNER JOIN aluno_turmas at2
                                ON at2.id_turma = dt2.id_turma
                               AND at2.id_aluno = :id_aluno_contexto
                               AND at2.status = \'ATIVO\'
                        WHERE dt2.id_desafio = d.id_desafio
                        ORDER BY dt2.id_turma ASC
                        LIMIT 1
                    ) AS id_turma,
                    (
                        SELECT t2.nome
                        FROM desafio_turmas dt2
                        INNER JOIN aluno_turmas at2
                                ON at2.id_turma = dt2.id_turma
                               AND at2.id_aluno = :id_aluno_contexto_nome
                               AND at2.status = \'ATIVO\'
                        INNER JOIN turmas t2 ON t2.id_turma = dt2.id_turma
                        WHERE dt2.id_desafio = d.id_desafio
                        ORDER BY dt2.id_turma ASC
                        LIMIT 1
                    ) AS turma_nome,
                    d.titulo, d.descricao, d.objetivo, d.categoria,
                    d.dificuldade, d.pontuacao_maxima, d.nivel_minimo,
                    d.visibilidade, d.data_criacao,
                    COALESCE(
                        (SELECT ad2.status
                         FROM aluno_desafios ad2
                         WHERE ad2.id_aluno = :id_aluno_status
                           AND ad2.id_desafio = d.id_desafio
                         ORDER BY ad2.id_turma ASC
                         LIMIT 1),
                        \'DISPONIVEL\'
                    ) AS status
             FROM desafios d
             WHERE d.ativo = TRUE
               AND (
                   d.visibilidade = \'PUBLICO\'
                   OR (
                       d.visibilidade = \'PRIVADO\'
                       AND EXISTS (
                           SELECT 1
                           FROM desafio_turmas dt
                           INNER JOIN aluno_turmas at
                                   ON at.id_turma = dt.id_turma
                                  AND at.id_aluno = :id_aluno
                                  AND at.status = \'ATIVO\'
                           WHERE dt.id_desafio = d.id_desafio
                       )
                   )
               )
             ORDER BY d.data_criacao DESC, d.id_desafio DESC'
        );
        $stmt->execute([
            'id_aluno_contexto' => $idAluno,
            'id_aluno_contexto_nome' => $idAluno,
            'id_aluno_status' => $idAluno,
            'id_aluno' => $idAluno,
        ]);

        return $stmt->fetchAll();
    }

    public static function buscarAtivoPorId(int $idDesafio): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id_desafio, titulo, descricao, objetivo, categoria, dificuldade,
                    pontuacao_maxima, nivel_minimo, visibilidade, id_criador, data_criacao
             FROM desafios
             WHERE id_desafio = :id_desafio AND ativo = TRUE
             LIMIT 1'
        );
        $stmt->execute(['id_desafio' => $idDesafio]);

        $desafio = $stmt->fetch();
        return $desafio ?: null;
    }

    public static function buscarVisivelParaAluno(int $idDesafio, int $idAluno, ?int $idTurma = null): ?array
    {
        $pdo = Database::pdo();
        $turmaClause = $idTurma !== null ? 'AND at.id_turma = :id_turma' : '';
        $stmt = $pdo->prepare(
            "SELECT d.id_desafio, d.titulo, d.descricao, d.objetivo, d.categoria,
                    d.dificuldade, d.pontuacao_maxima, d.nivel_minimo,
                    d.visibilidade, d.id_criador, d.data_criacao,
                    at.id_turma, t.nome AS turma_nome
             FROM desafios d
             LEFT JOIN desafio_turmas dt ON dt.id_desafio = d.id_desafio
             LEFT JOIN aluno_turmas at
                    ON at.id_turma = dt.id_turma
                   AND at.id_aluno = :id_aluno_join
                   AND at.status = 'ATIVO'
                   {$turmaClause}
             LEFT JOIN turmas t ON t.id_turma = at.id_turma
             WHERE d.id_desafio = :id_desafio
               AND d.ativo = TRUE
               AND (
                   d.visibilidade = 'PUBLICO'
                   OR (d.visibilidade = 'PRIVADO' AND at.id_turma IS NOT NULL)
               )
             ORDER BY at.id_turma ASC
             LIMIT 1"
        );
        $params = ['id_aluno_join' => $idAluno, 'id_desafio' => $idDesafio];
        if ($idTurma !== null) {
            $params['id_turma'] = $idTurma;
        }
        $stmt->execute($params);

        return $stmt->fetch() ?: null;
    }

    public static function listarTurmasDoDesafio(int $idDesafio): array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id_turma
             FROM desafio_turmas
             WHERE id_desafio = :id_desafio
             ORDER BY id_turma ASC'
        );
        $stmt->execute(['id_desafio' => $idDesafio]);

        return array_map(static fn(array $row): int => (int) $row['id_turma'], $stmt->fetchAll());
    }

    public static function buscarDoProfessor(int $idDesafio, int $idProfessor): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            'SELECT id_desafio, titulo, descricao, objetivo, categoria, dificuldade,
                    pontuacao_maxima, nivel_minimo, visibilidade, id_criador
             FROM desafios
             WHERE id_desafio = :id_desafio
               AND id_criador = :id_professor
             LIMIT 1'
        );
        $stmt->execute(['id_desafio' => $idDesafio, 'id_professor' => $idProfessor]);

        return $stmt->fetch() ?: null;
    }

    public static function atualizar(int $idDesafio, int $idProfessor, array $dados): void
    {
        $visibilidade = strtoupper((string) ($dados['visibilidade'] ?? ''));
        if (!in_array($visibilidade, self::VISIBILIDADES, true)) {
            throw new InvalidArgumentException('Selecione uma visibilidade válida.');
        }

        $turmaIds = self::normalizarTurmaIds($dados['turma_ids'] ?? []);
        if ($visibilidade === 'PRIVADO' && $turmaIds === []) {
            throw new InvalidArgumentException('Desafios privados precisam de pelo menos uma turma.');
        }

        $pdo = Database::pdo();
        self::validarTurmasDoProfessor($pdo, $idProfessor, $turmaIds);
        $atuais = self::listarTurmasDoDesafio($idDesafio);
        $removidas = array_diff($atuais, $turmaIds);

        foreach ($removidas as $idTurma) {
            $stmt = $pdo->prepare(
                'SELECT COUNT(*)
                 FROM aluno_desafios
                 WHERE id_desafio = :id_desafio AND id_turma = :id_turma'
            );
            $stmt->execute(['id_desafio' => $idDesafio, 'id_turma' => $idTurma]);
            if ((int) $stmt->fetchColumn() > 0) {
                throw new RuntimeException('Não é possível remover uma turma que já possui progresso neste desafio.');
            }
        }

        $pdo->beginTransaction();
        try {
            $dificuldade = strtoupper((string) ($dados['dificuldade'] ?? 'FACIL'));
            if (!in_array($dificuldade, ['FACIL', 'MEDIO', 'DIFICIL'], true)) {
                $dificuldade = 'FACIL';
            }

            $stmt = $pdo->prepare(
                'UPDATE desafios
                 SET titulo = :titulo, descricao = :descricao, objetivo = :objetivo,
                     categoria = :categoria, dificuldade = :dificuldade,
                     pontuacao_maxima = :pontuacao_maxima, nivel_minimo = :nivel_minimo,
                     visibilidade = :visibilidade
                 WHERE id_desafio = :id_desafio AND id_criador = :id_criador'
            );
            $stmt->execute([
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'],
                'objetivo' => $dados['objetivo'] !== '' ? $dados['objetivo'] : null,
                'categoria' => $dados['categoria'] !== '' ? $dados['categoria'] : null,
                'dificuldade' => $dificuldade,
                'pontuacao_maxima' => max(0, (int) $dados['pontuacao_maxima']),
                'nivel_minimo' => max(1, (int) $dados['nivel_minimo']),
                'visibilidade' => $visibilidade,
                'id_desafio' => $idDesafio,
                'id_criador' => $idProfessor,
            ]);

            if ($removidas !== []) {
                $placeholders = implode(',', array_fill(0, count($removidas), '?'));
                $stmt = $pdo->prepare(
                    "DELETE FROM desafio_turmas
                     WHERE id_desafio = ? AND id_turma IN ($placeholders)"
                );
                $stmt->execute(array_merge([$idDesafio], array_values($removidas)));
            }

            $novas = array_values(array_diff($turmaIds, $atuais));
            if ($novas !== []) {
                $ordem = $atuais === [] ? 0 : count($atuais);
                self::inserirAssociacoesComOrdem($pdo, $idDesafio, $novas, $ordem);
            }

            $pdo->commit();
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Cadastra um novo desafio e retorna o id gerado.
     */
    public static function criar(array $dados): int
    {
        $pdo = Database::pdo();

        $visibilidade = strtoupper((string) ($dados['visibilidade'] ?? ''));
        if (!in_array($visibilidade, self::VISIBILIDADES, true)) {
            throw new InvalidArgumentException('Selecione uma visibilidade válida.');
        }

        $idProfessor = (int) ($dados['id_criador'] ?? 0);
        $turmaIds = self::normalizarTurmaIds($dados['turma_ids'] ?? []);
        if ($visibilidade === 'PRIVADO' && $turmaIds === []) {
            throw new InvalidArgumentException('Desafios privados precisam de pelo menos uma turma.');
        }
        self::validarTurmasDoProfessor($pdo, $idProfessor, $turmaIds);

        $pdo->beginTransaction();

        try {
            $dificuldade = strtoupper($dados['dificuldade'] ?? 'FACIL');
            if (!in_array($dificuldade, ['FACIL', 'MEDIO', 'DIFICIL'], true)) {
                $dificuldade = 'FACIL';
            }

            $stmt = $pdo->prepare(
                'INSERT INTO desafios (titulo, descricao, objetivo, categoria, dificuldade,
                    pontuacao_maxima, nivel_minimo, visibilidade, id_criador, ativo)
                 VALUES (:titulo, :descricao, :objetivo, :categoria, :dificuldade,
                    :pontuacao_maxima, :nivel_minimo, :visibilidade, :id_criador, TRUE)'
            );

            $stmt->execute([
                'titulo' => $dados['titulo'],
                'descricao' => $dados['descricao'],
                'objetivo' => $dados['objetivo'] ?? null,
                'categoria' => $dados['categoria'] ?? null,
                'dificuldade' => $dificuldade,
                'pontuacao_maxima' => max(0, (int) ($dados['pontuacao_maxima'] ?? 0)),
                'nivel_minimo' => max(1, (int) ($dados['nivel_minimo'] ?? 1)),
                'visibilidade' => $visibilidade,
                'id_criador' => $idProfessor > 0 ? $idProfessor : null,
            ]);

            $idDesafio = (int) $pdo->lastInsertId();
            self::inserirAssociacoes($pdo, $idDesafio, $turmaIds);
            $pdo->commit();

            return $idDesafio;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    private static function normalizarTurmaIds(array $turmaIds): array
    {
        $ids = array_map('intval', $turmaIds);
        $ids = array_values(array_unique(array_filter($ids, static fn(int $id): bool => $id > 0)));
        sort($ids);
        return $ids;
    }

    private static function validarTurmasDoProfessor(PDO $pdo, int $idProfessor, array $turmaIds): void
    {
        if ($turmaIds === []) {
            return;
        }
        if ($idProfessor <= 0) {
            throw new InvalidArgumentException('Professor inválido para associar turmas.');
        }

        $placeholders = implode(',', array_fill(0, count($turmaIds), '?'));
        $stmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM professor_turmas
             WHERE id_professor = ? AND id_turma IN ($placeholders)"
        );
        $stmt->execute(array_merge([$idProfessor], $turmaIds));
        if ((int) $stmt->fetchColumn() !== count($turmaIds)) {
            throw new InvalidArgumentException('Uma ou mais turmas não podem ser gerenciadas por este professor.');
        }
    }

    private static function inserirAssociacoes(PDO $pdo, int $idDesafio, array $turmaIds): void
    {
        self::inserirAssociacoesComOrdem($pdo, $idDesafio, $turmaIds, 0);
    }

    private static function inserirAssociacoesComOrdem(PDO $pdo, int $idDesafio, array $turmaIds, int $ordemInicial): void
    {
        if ($turmaIds === []) {
            return;
        }

        $stmt = $pdo->prepare(
            'INSERT INTO desafio_turmas (id_desafio, id_turma, ordem, obrigatorio)
             VALUES (:id_desafio, :id_turma, :ordem, TRUE)'
        );
        foreach ($turmaIds as $idTurma) {
            $ordemStmt = $pdo->prepare(
                'SELECT COALESCE(MAX(ordem), 0) + 1
                 FROM desafio_turmas
                 WHERE id_turma = :id_turma'
            );
            $ordemStmt->execute(['id_turma' => $idTurma]);
            $stmt->execute([
                'id_desafio' => $idDesafio,
                'id_turma' => $idTurma,
                'ordem' => max($ordemInicial + 1, (int) $ordemStmt->fetchColumn()),
            ]);
        }
    }
}
