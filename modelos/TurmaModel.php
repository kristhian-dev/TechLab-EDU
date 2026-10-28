<?php

require_once APP_ROOT . '/nucleo/Database.php';

final class TurmaModel
{
    /**
     * Lista as turmas vinculadas a um professor, com a contagem de alunos ativos.
     */
    public static function listarPorProfessor(int $idProfessor): array
    {
        $pdo = Database::pdo();

        $sql = 'SELECT t.id_turma, t.nome, t.codigo, t.descricao, t.data_criacao,
                       (SELECT COUNT(*) FROM aluno_turmas at2
                        WHERE at2.id_turma = t.id_turma AND at2.status = \'ATIVO\') AS total_alunos
                FROM turmas t
                INNER JOIN professor_turmas pt ON pt.id_turma = t.id_turma
                WHERE pt.id_professor = :id AND t.ativo = TRUE
                ORDER BY t.data_criacao DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idProfessor]);

        return $stmt->fetchAll();
    }

    /**
     * Busca uma turma pelo id.
     */
    public static function buscarPorId(int $idTurma): ?array
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id_turma, nome, codigo, descricao, ativo FROM turmas WHERE id_turma = :id');
        $stmt->execute(['id' => $idTurma]);

        $turma = $stmt->fetch();

        return $turma ?: null;
    }

    /**
     * Verifica se o professor é responsável pela turma.
     */
    public static function professorGerenciaTurma(int $idProfessor, int $idTurma): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT 1 FROM professor_turmas WHERE id_professor = :professor AND id_turma = :turma');
        $stmt->execute(['professor' => $idProfessor, 'turma' => $idTurma]);

        return (bool) $stmt->fetch();
    }

    /**
     * Cria a turma, gera um código único e vincula o professor.
     */
    public static function criar(int $idProfessor, array $dados): int
    {
        $pdo = Database::pdo();
        $pdo->beginTransaction();

        try {
            $codigo = self::gerarCodigo($dados['nome']);

            $stmt = $pdo->prepare('INSERT INTO turmas (nome, codigo, descricao, ativo) VALUES (:nome, :codigo, :descricao, TRUE)');
            $stmt->execute([
                'nome' => $dados['nome'],
                'codigo' => $codigo,
                'descricao' => ($dados['descricao'] ?? '') !== '' ? $dados['descricao'] : null,
            ]);

            $idTurma = (int) $pdo->lastInsertId();

            $stmt = $pdo->prepare('INSERT INTO professor_turmas (id_professor, id_turma) VALUES (:professor, :turma)');
            $stmt->execute(['professor' => $idProfessor, 'turma' => $idTurma]);

            $pdo->commit();

            return $idTurma;
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /**
     * Gera um código único para a turma a partir do nome.
     */
    private static function gerarCodigo(string $nome): string
    {
        $pdo = Database::pdo();

        $base = preg_replace('/[^A-Za-z0-9]/', '', strtoupper($nome));
        $base = $base !== '' ? substr($base, 0, 8) : 'TURMA';

        for ($i = 0; $i < 20; $i++) {
            $codigo = $base . '-' . random_int(1000, 9999);

            $stmt = $pdo->prepare('SELECT 1 FROM turmas WHERE codigo = :codigo');
            $stmt->execute(['codigo' => $codigo]);

            if (!$stmt->fetch()) {
                return $codigo;
            }
        }

        return $base . '-' . random_int(100000, 999999);
    }

    /**
     * Lista os alunos vinculados a uma turma.
     */
    public static function listarAlunos(int $idTurma): array
    {
        $pdo = Database::pdo();

        $sql = 'SELECT u.id_usuario, u.nome, u.nome_usuario, u.email, a.matricula, at.status
                FROM aluno_turmas at
                INNER JOIN alunos a ON a.id_usuario = at.id_aluno
                INNER JOIN usuarios u ON u.id_usuario = a.id_usuario
                WHERE at.id_turma = :turma
                ORDER BY u.nome ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['turma' => $idTurma]);

        return $stmt->fetchAll();
    }

    /**
     * Busca alunos por nome, e-mail, nome de usuário ou matrícula.
     * Indica quais já estão na turma informada.
     */
    public static function buscarAlunos(string $termo, ?int $idTurma = null): array
    {
        $pdo = Database::pdo();

        $sql = 'SELECT u.id_usuario, u.nome, u.nome_usuario, u.email, a.matricula,
                       (SELECT COUNT(*) FROM aluno_turmas vinc
                        WHERE vinc.id_aluno = u.id_usuario AND vinc.id_turma = :turma) AS na_turma
                FROM alunos a
                INNER JOIN usuarios u ON u.id_usuario = a.id_usuario
                WHERE (u.nome LIKE :busca1 OR u.email LIKE :busca2
                       OR u.nome_usuario LIKE :busca3 OR a.matricula LIKE :busca4)
                ORDER BY u.nome ASC
                LIMIT 12';

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'turma' => $idTurma ?? 0,
            'busca1' => '%' . $termo . '%',
            'busca2' => '%' . $termo . '%',
            'busca3' => '%' . $termo . '%',
            'busca4' => '%' . $termo . '%',
        ]);

        return $stmt->fetchAll();
    }

    /**
     * Ranking das turmas do professor: pontos totais, desafios concluídos e alunos.
     * Ordenado do melhor para o pior (pontos, depois conclusões, depois alunos).
     */
    public static function rankingTurmas(int $idProfessor): array
    {
        $pdo = Database::pdo();

        $sql = 'SELECT t.id_turma, t.nome, t.codigo, t.data_criacao,
                       (SELECT COUNT(*) FROM aluno_turmas at
                        WHERE at.id_turma = t.id_turma AND at.status = \'ATIVO\') AS total_alunos,
                       (SELECT COALESCE(SUM(ad.pontuacao_obtida), 0) FROM aluno_desafios ad
                        WHERE ad.id_turma = t.id_turma) AS total_pontos,
                       (SELECT COUNT(*) FROM aluno_desafios ad
                        WHERE ad.id_turma = t.id_turma AND ad.status = \'CONCLUIDO\') AS desafios_concluidos
                FROM turmas t
                INNER JOIN professor_turmas pt ON pt.id_turma = t.id_turma
                WHERE pt.id_professor = :id AND t.ativo = TRUE
                ORDER BY total_pontos DESC, desafios_concluidos DESC, total_alunos DESC, t.nome ASC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $idProfessor]);

        return $stmt->fetchAll();
    }

    /**
     * Lista as turmas vinculadas a um aluno, com professor responsável e data de entrada.
     * Usado exclusivamente pela página "Turmas" do aluno.
     */
    public static function listarPorAluno(int $idAluno): array
    {
        $pdo = Database::pdo();

        $sql = 'SELECT t.id_turma, t.nome, t.codigo, t.descricao, t.data_criacao,
                       at.data_entrada, at.status,
                       (SELECT u.nome FROM professor_turmas pt
                        INNER JOIN usuarios u ON u.id_usuario = pt.id_professor
                        WHERE pt.id_turma = t.id_turma
                        ORDER BY pt.data_vinculo ASC LIMIT 1) AS professor_nome
                FROM aluno_turmas at
                INNER JOIN turmas t ON t.id_turma = at.id_turma
                WHERE at.id_aluno = :aluno AND t.ativo = TRUE
                ORDER BY at.data_entrada DESC';

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['aluno' => $idAluno]);

        return $stmt->fetchAll();
    }

    /**
     * Busca uma turma ativa pelo código de entrada (comparação exata após trim).
     */
    public static function buscarPorCodigo(string $codigo): ?array
    {
        $codigo = trim($codigo);
        if ($codigo === '') {
            return null;
        }

        $pdo = Database::pdo();
        $stmt = $pdo->prepare('SELECT id_turma, nome, codigo, descricao, ativo FROM turmas WHERE codigo = :codigo LIMIT 1');
        $stmt->execute(['codigo' => $codigo]);

        $turma = $stmt->fetch();

        return $turma ?: null;
    }

    /**
     * Matricula o aluno na turma identificada pelo código.
     *
     * Retorna ['status' => 'ok'|'ja_matriculado'|'codigo_invalido', 'turma' => ?array].
     * Reativa vínculos não-ATIVOS em vez de duplicar (PK id_aluno+id_turma).
     */
    public static function entrarPorCodigo(int $idAluno, string $codigo): array
    {
        $codigo = trim($codigo);
        $turma = self::buscarPorCodigo($codigo);

        if (!$turma || !(int) ($turma['ativo'] ?? 0)) {
            return ['status' => 'codigo_invalido', 'turma' => null];
        }

        $idTurma = (int) $turma['id_turma'];
        $pdo = Database::pdo();

        $stmt = $pdo->prepare('SELECT status FROM aluno_turmas WHERE id_aluno = :aluno AND id_turma = :turma LIMIT 1');
        $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);
        $vinculo = $stmt->fetch();

        if ($vinculo) {
            if (($vinculo['status'] ?? '') === 'ATIVO') {
                return ['status' => 'ja_matriculado', 'turma' => $turma];
            }

            $stmt = $pdo->prepare("UPDATE aluno_turmas SET status = 'ATIVO' WHERE id_aluno = :aluno AND id_turma = :turma");
            $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);

            return ['status' => 'ok', 'turma' => $turma];
        }

        $stmt = $pdo->prepare("INSERT INTO aluno_turmas (id_aluno, id_turma, status) VALUES (:aluno, :turma, 'ATIVO')");
        $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);

        return ['status' => 'ok', 'turma' => $turma];
    }

    /**
     * Exclusão lógica da turma (preserva históricos): marca ativo=FALSE.
     * Retorna true se a turma pertencia ao professor e foi desativada.
     */
    public static function excluirLogico(int $idProfessor, int $idTurma): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("UPDATE turmas t
                INNER JOIN professor_turmas pt ON pt.id_turma = t.id_turma
                SET t.ativo = FALSE
                WHERE pt.id_professor = :professor AND t.id_turma = :turma AND t.ativo = TRUE");
        $stmt->execute(['professor' => $idProfessor, 'turma' => $idTurma]);

        return $stmt->rowCount() > 0;
    }

    /**
     * Verifica vínculo ATIVO do aluno com a turma.
     */
    public static function alunoVinculadoAtivo(int $idTurma, int $idAluno): bool
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare("SELECT 1 FROM aluno_turmas WHERE id_turma = :turma AND id_aluno = :aluno AND status = 'ATIVO'");
        $stmt->execute(['turma' => $idTurma, 'aluno' => $idAluno]);

        return (bool) $stmt->fetch();
    }

    /**
     * Detalhe do aluno dentro da turma para o painel do professor.
     * Retorna perfil básico + agregados reais de aluno_desafios + listas por status.
     */
    public static function detalheAlunoNaTurma(int $idTurma, int $idAluno): ?array
    {
        $pdo = Database::pdo();

        $stmt = $pdo->prepare('SELECT u.id_usuario, u.nome, u.nome_usuario, u.email, u.data_cadastro, a.matricula, at.status AS status_vinculo, at.data_entrada
                FROM aluno_turmas at
                INNER JOIN alunos a ON a.id_usuario = at.id_aluno
                INNER JOIN usuarios u ON u.id_usuario = a.id_usuario
                WHERE at.id_turma = :turma AND at.id_aluno = :aluno AND at.status = \'ATIVO\' LIMIT 1');
        $stmt->execute(['turma' => $idTurma, 'aluno' => $idAluno]);
        $perfil = $stmt->fetch();

        if (!$perfil) {
            return null;
        }

        $stmt = $pdo->prepare('SELECT COUNT(*) AS total, COALESCE(SUM(CASE WHEN ad.status = \'CONCLUIDO\' THEN 1 ELSE 0 END), 0) AS concluidos,
                       COALESCE(SUM(CASE WHEN ad.status = \'EM_ANDAMENTO\' THEN 1 ELSE 0 END), 0) AS em_andamento,
                       COALESCE(SUM(ad.pontuacao_obtida), 0) AS pontos_obtidos,
                       COALESCE(SUM(ad.numero_tentativas), 0) AS tentativas
                FROM aluno_desafios ad
                WHERE ad.id_aluno = :aluno AND ad.id_turma = :turma');
        $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);
        $resumo = $stmt->fetch() ?: [];

        $stmt = $pdo->prepare('SELECT COALESCE(SUM(d.pontuacao_maxima), 0) AS pontos_maximos
                FROM aluno_desafios ad
                INNER JOIN desafios d ON d.id_desafio = ad.id_desafio
                WHERE ad.id_aluno = :aluno AND ad.id_turma = :turma AND d.pontuacao_maxima > 0');
        $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);
        $pontosMaximos = (int) ($stmt->fetchColumn() ?: 0);

        $stmt = $pdo->prepare('SELECT d.titulo, d.pontuacao_maxima, ad.status, ad.pontuacao_obtida, ad.numero_tentativas, ad.iniciado_em, ad.concluido_em
                FROM aluno_desafios ad
                INNER JOIN desafios d ON d.id_desafio = ad.id_desafio
                WHERE ad.id_aluno = :aluno AND ad.id_turma = :turma
                ORDER BY ad.concluido_em DESC, ad.iniciado_em DESC');
        $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);
        $desafios = $stmt->fetchAll();

        $concluidos = array_values(array_filter($desafios, static fn($d) => ($d['status'] ?? '') === 'CONCLUIDO'));
        $emAndamento = array_values(array_filter($desafios, static fn($d) => ($d['status'] ?? '') !== 'CONCLUIDO'));

        $pontosObtidos = (int) ($resumo['pontuacao_obtida'] ?? $resumo['pontos_obtidos'] ?? 0);
        $aproveitamento = null;
        if ($pontosMaximos > 0 && (int) ($resumo['total'] ?? 0) > 0) {
            $aproveitamento = round(($pontosObtidos / $pontosMaximos) * 100, 1);
        }

        return [
            'perfil' => $perfil,
            'total_desafios' => (int) ($resumo['total'] ?? 0),
            'concluidos' => (int) ($resumo['concluidos'] ?? 0),
            'em_andamento' => (int) ($resumo['em_andamento'] ?? 0),
            'pontos_obtidos' => $pontosObtidos,
            'pontos_maximos' => $pontosMaximos,
            'tentativas' => (int) ($resumo['tentativas'] ?? 0),
            'aproveitamento' => $aproveitamento,
            'lista_concluidos' => $concluidos,
            'lista_em_andamento' => $emAndamento,
        ];
    }

    /**
     * Vincula um aluno à turma (idempotente).
     */
    public static function adicionarAluno(int $idTurma, int $idAluno): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('INSERT IGNORE INTO aluno_turmas (id_aluno, id_turma, status) VALUES (:aluno, :turma, \'ATIVO\')');
        $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);
    }

    /**
     * Remove o vínculo do aluno com a turma.
     */
    public static function removerAluno(int $idTurma, int $idAluno): void
    {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare('DELETE FROM aluno_turmas WHERE id_aluno = :aluno AND id_turma = :turma');
        $stmt->execute(['aluno' => $idAluno, 'turma' => $idTurma]);
    }
}
