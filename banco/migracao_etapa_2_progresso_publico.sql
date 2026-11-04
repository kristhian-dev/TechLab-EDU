-- TechLab EDU - Etapa 2: progresso público de desafios
-- MySQL 8.0+. Esta migração não altera dados existentes nem concede XP.
-- O progresso institucional permanece em aluno_desafios.

USE techlab_edu;

CREATE TABLE IF NOT EXISTS aluno_desafios_publicos (
    id_aluno_desafio_publico BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT UNSIGNED NOT NULL,
    id_desafio INT UNSIGNED NOT NULL,
    id_build BIGINT UNSIGNED NULL,
    id_diagnostico BIGINT UNSIGNED NULL,
    status ENUM('BLOQUEADO', 'DISPONIVEL', 'EM_ANDAMENTO', 'CONCLUIDO') NOT NULL DEFAULT 'DISPONIVEL',
    pontuacao_obtida INT UNSIGNED NOT NULL DEFAULT 0,
    numero_tentativas INT UNSIGNED NOT NULL DEFAULT 0,
    iniciado_em DATETIME NULL,
    concluido_em DATETIME NULL,
    xp_concedido_em DATETIME NULL,

    UNIQUE KEY uq_aluno_desafio_publico (id_aluno, id_desafio),
    KEY idx_publicos_aluno_status (id_aluno, status),
    KEY idx_publicos_desafio_status (id_desafio, status),
    KEY idx_publicos_build (id_build),
    KEY idx_publicos_diagnostico (id_diagnostico),

    CONSTRAINT fk_publico_aluno
        FOREIGN KEY (id_aluno)
        REFERENCES alunos(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_publico_desafio
        FOREIGN KEY (id_desafio)
        REFERENCES desafios(id_desafio)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_publico_build
        FOREIGN KEY (id_build)
        REFERENCES builds(id_build)
        ON DELETE SET NULL
        ON UPDATE CASCADE,
    CONSTRAINT fk_publico_diagnostico
        FOREIGN KEY (id_diagnostico)
        REFERENCES diagnosticos(id_diagnostico)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;
