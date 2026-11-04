-- TechLab EDU - Etapa 1: visibilidade e autoria de desafios
-- MySQL 8.0+. Executar manualmente, após backup validado.
-- Esta migração não classifica desafios existentes: visibilidade e autoria
-- permanecem NULL para revisão manual.

USE techlab_edu;

DELIMITER $$

DROP PROCEDURE IF EXISTS techlab_edu_validar_migracao_desafios$$

CREATE PROCEDURE techlab_edu_validar_migracao_desafios()
BEGIN
    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.TABLES
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A tabela desafios não existe no schema techlab_edu.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
          AND COLUMN_NAME = 'ativo'
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A coluna desafios.ativo não existe no schema techlab_edu.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
          AND COLUMN_NAME = 'visibilidade'
          AND NOT (
              COLUMN_TYPE = 'enum(''PUBLICO'',''PRIVADO'')'
              AND IS_NULLABLE = 'YES'
              AND COLUMN_DEFAULT IS NULL
          )
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A coluna desafios.visibilidade possui definição incompatível.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
          AND COLUMN_NAME = 'id_criador'
          AND NOT (
              COLUMN_TYPE = 'int unsigned'
              AND IS_NULLABLE = 'YES'
              AND COLUMN_DEFAULT IS NULL
          )
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'A coluna desafios.id_criador possui definição incompatível.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.TABLE_CONSTRAINTS tc
        WHERE tc.CONSTRAINT_SCHEMA = DATABASE()
          AND tc.CONSTRAINT_NAME = 'fk_desafio_criador'
          AND (
              tc.TABLE_NAME <> 'desafios'
              OR tc.CONSTRAINT_TYPE <> 'FOREIGN KEY'
              OR NOT EXISTS (
                  SELECT 1
                  FROM information_schema.KEY_COLUMN_USAGE k
                  INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS r
                          ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
                         AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
                         AND r.TABLE_NAME = k.TABLE_NAME
                  WHERE k.CONSTRAINT_SCHEMA = DATABASE()
                    AND k.TABLE_NAME = 'desafios'
                    AND k.CONSTRAINT_NAME = 'fk_desafio_criador'
                    AND k.COLUMN_NAME = 'id_criador'
                    AND k.REFERENCED_TABLE_NAME = 'professores'
                    AND k.REFERENCED_COLUMN_NAME = 'id_usuario'
                    AND r.DELETE_RULE = 'SET NULL'
                    AND r.UPDATE_RULE = 'CASCADE'
              )
          )
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Existe conflito para a constraint fk_desafio_criador no schema techlab_edu.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.KEY_COLUMN_USAGE k
        LEFT JOIN information_schema.REFERENTIAL_CONSTRAINTS r
               ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
              AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
              AND r.TABLE_NAME = k.TABLE_NAME
        WHERE k.CONSTRAINT_SCHEMA = DATABASE()
          AND k.TABLE_NAME = 'desafios'
          AND k.COLUMN_NAME = 'id_criador'
          AND k.REFERENCED_TABLE_NAME IS NOT NULL
          AND NOT (
              k.CONSTRAINT_NAME = 'fk_desafio_criador'
              AND k.REFERENCED_TABLE_NAME = 'professores'
              AND k.REFERENCED_COLUMN_NAME = 'id_usuario'
              AND r.DELETE_RULE = 'SET NULL'
              AND r.UPDATE_RULE = 'CASCADE'
          )
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'Existe uma FK incompatível em desafios.id_criador.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
          AND INDEX_NAME = 'idx_desafios_visibilidade_ativo'
    ) AND NOT EXISTS (
        SELECT 1
        FROM (
            SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') AS colunas,
                   MAX(NON_UNIQUE) AS nao_unico,
                   MAX(INDEX_TYPE = 'BTREE') AS btree
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'desafios'
              AND INDEX_NAME = 'idx_desafios_visibilidade_ativo'
        ) indice
        WHERE colunas = 'visibilidade,ativo'
          AND nao_unico = 1
          AND btree = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O índice idx_desafios_visibilidade_ativo possui definição incompatível.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
          AND INDEX_NAME = 'idx_desafios_criador'
    ) AND NOT EXISTS (
        SELECT 1
        FROM (
            SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') AS colunas,
                   MAX(NON_UNIQUE) AS nao_unico,
                   MAX(INDEX_TYPE = 'BTREE') AS btree
            FROM information_schema.STATISTICS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'desafios'
              AND INDEX_NAME = 'idx_desafios_criador'
        ) indice
        WHERE colunas = 'id_criador'
          AND nao_unico = 1
          AND btree = 1
    ) THEN
        SIGNAL SQLSTATE '45000'
            SET MESSAGE_TEXT = 'O índice idx_desafios_criador possui definição incompatível.';
    END IF;
END$$

CALL techlab_edu_validar_migracao_desafios()$$

DROP PROCEDURE techlab_edu_validar_migracao_desafios$$

DELIMITER ;

SET @column_visibility_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'desafios'
      AND COLUMN_NAME = 'visibilidade'
);

SET @add_column_visibility := IF(
    @column_visibility_exists = 0,
    'ALTER TABLE desafios ADD COLUMN visibilidade ENUM(''PUBLICO'', ''PRIVADO'') NULL DEFAULT NULL AFTER ativo',
    'SELECT 1'
);
PREPARE stmt_column_visibility FROM @add_column_visibility;
EXECUTE stmt_column_visibility;
DEALLOCATE PREPARE stmt_column_visibility;

SET @column_creator_exists := (
    SELECT COUNT(*)
    FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
      AND TABLE_NAME = 'desafios'
      AND COLUMN_NAME = 'id_criador'
);

SET @add_column_creator := IF(
    @column_creator_exists = 0,
    'ALTER TABLE desafios ADD COLUMN id_criador INT UNSIGNED NULL DEFAULT NULL AFTER visibilidade',
    'SELECT 1'
);
PREPARE stmt_column_creator FROM @add_column_creator;
EXECUTE stmt_column_creator;
DEALLOCATE PREPARE stmt_column_creator;

SET @fk_exists := (
    SELECT COUNT(*)
    FROM information_schema.KEY_COLUMN_USAGE k
    INNER JOIN information_schema.REFERENTIAL_CONSTRAINTS r
            ON r.CONSTRAINT_SCHEMA = k.CONSTRAINT_SCHEMA
           AND r.CONSTRAINT_NAME = k.CONSTRAINT_NAME
           AND r.TABLE_NAME = k.TABLE_NAME
    WHERE k.CONSTRAINT_SCHEMA = DATABASE()
      AND k.TABLE_NAME = 'desafios'
                    AND k.CONSTRAINT_NAME = 'fk_desafio_criador'
      AND k.COLUMN_NAME = 'id_criador'
      AND k.REFERENCED_TABLE_NAME = 'professores'
      AND k.REFERENCED_COLUMN_NAME = 'id_usuario'
      AND r.DELETE_RULE = 'SET NULL'
      AND r.UPDATE_RULE = 'CASCADE'
);

SET @add_fk := IF(
    @fk_exists = 0,
    'ALTER TABLE desafios ADD CONSTRAINT fk_desafio_criador FOREIGN KEY (id_criador) REFERENCES professores(id_usuario) ON DELETE SET NULL ON UPDATE CASCADE',
    'SELECT 1'
);
PREPARE stmt_fk FROM @add_fk;
EXECUTE stmt_fk;
DEALLOCATE PREPARE stmt_fk;

SET @idx_visibility_exists := (
    SELECT COUNT(*)
    FROM (
        SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') AS colunas,
               MAX(NON_UNIQUE) AS nao_unico,
               MAX(INDEX_TYPE = 'BTREE') AS btree
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
          AND INDEX_NAME <> 'PRIMARY'
        GROUP BY INDEX_NAME
    ) indices
    WHERE colunas = 'visibilidade,ativo'
      AND nao_unico = 1
      AND btree = 1
);

SET @add_idx_visibility := IF(
    @idx_visibility_exists = 0,
    'ALTER TABLE desafios ADD INDEX idx_desafios_visibilidade_ativo (visibilidade, ativo)',
    'SELECT 1'
);
PREPARE stmt_idx_visibility FROM @add_idx_visibility;
EXECUTE stmt_idx_visibility;
DEALLOCATE PREPARE stmt_idx_visibility;

SET @idx_creator_exists := (
    SELECT COUNT(*)
    FROM (
        SELECT GROUP_CONCAT(COLUMN_NAME ORDER BY SEQ_IN_INDEX SEPARATOR ',') AS colunas,
               MAX(NON_UNIQUE) AS nao_unico,
               MAX(INDEX_TYPE = 'BTREE') AS btree
        FROM information_schema.STATISTICS
        WHERE TABLE_SCHEMA = DATABASE()
          AND TABLE_NAME = 'desafios'
          AND INDEX_NAME <> 'PRIMARY'
        GROUP BY INDEX_NAME
    ) indices
    WHERE colunas = 'id_criador'
      AND nao_unico = 1
      AND btree = 1
);

SET @add_idx_creator := IF(
    @idx_creator_exists = 0,
    'ALTER TABLE desafios ADD INDEX idx_desafios_criador (id_criador)',
    'SELECT 1'
);
PREPARE stmt_idx_creator FROM @add_idx_creator;
EXECUTE stmt_idx_creator;
DEALLOCATE PREPARE stmt_idx_creator;

-- Não executar UPDATE aqui. Desafios antigos continuam sem classificação.
