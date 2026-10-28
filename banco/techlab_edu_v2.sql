-- ============================================================
-- TECHLAB EDU
-- Estrutura inicial revisada do banco de dados - Versão 2
-- SGBD: MySQL 8.0+
-- ============================================================

CREATE DATABASE IF NOT EXISTS techlab_edu
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_0900_ai_ci;

USE techlab_edu;

CREATE TABLE usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    nome_usuario VARCHAR(60) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    tipo_usuario ENUM('ALUNO', 'PROFESSOR') NOT NULL DEFAULT 'ALUNO',
    senha_hash VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255) NULL,
    tentativas_incorretas_login SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    bloqueado_ate DATETIME NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE alunos (
    id_usuario INT UNSIGNED PRIMARY KEY,
    matricula VARCHAR(30) NOT NULL UNIQUE,
    CONSTRAINT fk_aluno_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE professores (
    id_usuario INT UNSIGNED PRIMARY KEY,
    CONSTRAINT fk_professor_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE administradores (
    id_usuario INT UNSIGNED PRIMARY KEY,
    CONSTRAINT fk_administrador_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE turmas (
    id_turma INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(120) NOT NULL,
    codigo VARCHAR(40) NOT NULL UNIQUE,
    descricao TEXT NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE professor_turmas (
    id_professor INT UNSIGNED NOT NULL,
    id_turma INT UNSIGNED NOT NULL,
    data_vinculo TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_professor, id_turma),
    CONSTRAINT fk_professor_turma_professor
        FOREIGN KEY (id_professor)
        REFERENCES professores(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_professor_turma_turma
        FOREIGN KEY (id_turma)
        REFERENCES turmas(id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE aluno_turmas (
    id_aluno INT UNSIGNED NOT NULL,
    id_turma INT UNSIGNED NOT NULL,
    data_entrada TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('ATIVO', 'CONCLUIDO', 'TRANCADO', 'INATIVO') NOT NULL DEFAULT 'ATIVO',
    PRIMARY KEY (id_aluno, id_turma),
    CONSTRAINT fk_aluno_turma_aluno
        FOREIGN KEY (id_aluno)
        REFERENCES alunos(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_aluno_turma_turma
        FOREIGN KEY (id_turma)
        REFERENCES turmas(id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE desafios (
    id_desafio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(150) NOT NULL,
    descricao TEXT NOT NULL,
    objetivo TEXT NULL,
    categoria VARCHAR(80) NULL,
    dificuldade ENUM('FACIL', 'MEDIO', 'DIFICIL') NOT NULL DEFAULT 'FACIL',
    pontuacao_maxima INT UNSIGNED NOT NULL DEFAULT 0,
    nivel_minimo INT UNSIGNED NOT NULL DEFAULT 1,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_desafio_nivel CHECK (nivel_minimo >= 1)
) ENGINE=InnoDB;

CREATE TABLE desafio_turmas (
    id_desafio INT UNSIGNED NOT NULL,
    id_turma INT UNSIGNED NOT NULL,
    ordem INT UNSIGNED NOT NULL DEFAULT 1,
    obrigatorio BOOLEAN NOT NULL DEFAULT TRUE,
    data_liberacao DATETIME NULL,
    PRIMARY KEY (id_desafio, id_turma),
    UNIQUE KEY uq_desafio_turma_ordem (id_turma, ordem),
    CONSTRAINT chk_desafio_turma_ordem CHECK (ordem >= 1),
    CONSTRAINT fk_desafio_turma_desafio FOREIGN KEY (id_desafio) REFERENCES desafios(id_desafio) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_desafio_turma_turma FOREIGN KEY (id_turma) REFERENCES turmas(id_turma) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE aluno_desafios (
    id_aluno_desafio BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT UNSIGNED NOT NULL,
    id_desafio INT UNSIGNED NOT NULL,
    id_turma INT UNSIGNED NOT NULL,
    status ENUM('BLOQUEADO', 'DISPONIVEL', 'EM_ANDAMENTO', 'CONCLUIDO') NOT NULL DEFAULT 'BLOQUEADO',
    pontuacao_obtida INT UNSIGNED NOT NULL DEFAULT 0,
    numero_tentativas INT UNSIGNED NOT NULL DEFAULT 0,
    iniciado_em DATETIME NULL,
    concluido_em DATETIME NULL,
    UNIQUE KEY uq_aluno_desafio_turma (id_aluno, id_desafio, id_turma),
    CONSTRAINT fk_aluno_desafio_aluno_turma
        FOREIGN KEY (id_aluno, id_turma)
        REFERENCES aluno_turmas(id_aluno, id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_aluno_desafio_desafio_turma
        FOREIGN KEY (id_desafio, id_turma)
        REFERENCES desafio_turmas(id_desafio, id_turma)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE certificados (
    id_certificado BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_aluno_desafio BIGINT UNSIGNED NOT NULL UNIQUE,
    codigo_validacao CHAR(36) NOT NULL UNIQUE,
    data_emissao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_certificado_aluno_desafio
        FOREIGN KEY (id_aluno_desafio)
        REFERENCES aluno_desafios(id_aluno_desafio)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE componentes (
    id_componente INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(150) NOT NULL,
    fabricante VARCHAR(100) NOT NULL,
    tipo ENUM('CPU', 'PLACA_MAE', 'MEMORIA_RAM', 'GPU', 'SSD', 'HD', 'FONTE', 'COOLER', 'GABINETE') NOT NULL,
    descricao TEXT NULL,
    watts_consumo DECIMAL(7,2) NOT NULL DEFAULT 0.00,
    imagem_url VARCHAR(500) NULL,
    ativo BOOLEAN NOT NULL DEFAULT TRUE,
    data_cadastro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE cpu_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    socket VARCHAR(40) NOT NULL,
    nucleos SMALLINT UNSIGNED NULL,
    threads SMALLINT UNSIGNED NULL,
    frequencia_ghz DECIMAL(4,2) NULL,
    tdp_watts DECIMAL(7,2) NULL,
    CONSTRAINT fk_cpu_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE placa_mae_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    socket_cpu VARCHAR(40) NOT NULL,
    tipo_memoria VARCHAR(30) NOT NULL,
    formato VARCHAR(40) NOT NULL,
    slots_memoria SMALLINT UNSIGNED NULL,
    capacidade_max_ram_gb INT UNSIGNED NULL,
    CONSTRAINT fk_placa_mae_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE memoria_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    tipo_ddr VARCHAR(30) NOT NULL,
    capacidade_gb INT UNSIGNED NOT NULL,
    frequencia_mhz INT UNSIGNED NULL,
    CONSTRAINT fk_memoria_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE gpu_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    memoria_gb INT UNSIGNED NULL,
    interface_pcie VARCHAR(30) NULL,
    comprimento_mm DECIMAL(7,2) NULL,
    potencia_recomendada_w DECIMAL(7,2) NULL,
    CONSTRAINT fk_gpu_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE armazenamento_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    capacidade_gb INT UNSIGNED NOT NULL,
    interface_armazenamento VARCHAR(40) NOT NULL,
    formato VARCHAR(40) NULL,
    CONSTRAINT fk_armazenamento_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE fonte_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    potencia_watts DECIMAL(7,2) NOT NULL,
    certificacao VARCHAR(50) NULL,
    formato VARCHAR(40) NULL,
    CONSTRAINT fk_fonte_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE gabinete_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    formato VARCHAR(40) NULL,
    comprimento_max_gpu_mm DECIMAL(7,2) NULL,
    CONSTRAINT fk_gabinete_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cooler_especificacoes (
    id_componente INT UNSIGNED PRIMARY KEY,
    tipo_cooler VARCHAR(40) NULL,
    tdp_suportado_watts DECIMAL(7,2) NULL,
    CONSTRAINT fk_cooler_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE builds (
    id_build BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_aluno INT UNSIGNED NOT NULL,
    nome VARCHAR(120) NOT NULL,
    descricao TEXT NULL,
    status ENUM('EM_MONTAGEM', 'COMPLETA', 'ARQUIVADA') NOT NULL DEFAULT 'EM_MONTAGEM',
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_build_aluno
        FOREIGN KEY (id_aluno)
        REFERENCES alunos(id_usuario)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE build_componentes (
    id_build_componente BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_build BIGINT UNSIGNED NOT NULL,
    id_componente INT UNSIGNED NOT NULL,
    slot_instalacao VARCHAR(60) NULL,
    ordem_instalacao INT UNSIGNED NULL,
    data_instalacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_build_componente_build
        FOREIGN KEY (id_build)
        REFERENCES builds(id_build)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_build_componente_componente
        FOREIGN KEY (id_componente)
        REFERENCES componentes(id_componente)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_build_componentes_build ON build_componentes(id_build);
CREATE INDEX idx_build_componentes_componente ON build_componentes(id_componente);

CREATE TABLE diagnosticos (
    id_diagnostico BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_build BIGINT UNSIGNED NOT NULL,
    id_desafio INT UNSIGNED NULL,
    tipo VARCHAR(60) NOT NULL,
    status ENUM('SUCESSO', 'ALERTA', 'FALHA') NOT NULL,
    descricao TEXT NULL,
    resultado TEXT NULL,
    temperatura_simulada DECIMAL(5,2) NULL,
    potencia_total_simulada DECIMAL(8,2) NULL,
    data_execucao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_diagnostico_build
        FOREIGN KEY (id_build)
        REFERENCES builds(id_build)
        ON DELETE CASCADE
        ON UPDATE CASCADE,
    CONSTRAINT fk_diagnostico_desafio
        FOREIGN KEY (id_desafio)
        REFERENCES desafios(id_desafio)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_diagnosticos_build ON diagnosticos(id_build);

CREATE TABLE logs_auditoria (
    id_log BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNSIGNED NULL,
    acao VARCHAR(100) NOT NULL,
    entidade VARCHAR(100) NULL,
    id_registro VARCHAR(100) NULL,
    detalhes TEXT NULL,
    data_hora TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_log_usuario
        FOREIGN KEY (id_usuario)
        REFERENCES usuarios(id_usuario)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE INDEX idx_logs_usuario ON logs_auditoria(id_usuario);
CREATE INDEX idx_logs_data_hora ON logs_auditoria(data_hora);
