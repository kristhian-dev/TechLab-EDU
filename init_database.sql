-- Script de inicialização do banco de dados TechLab EDU
-- Execute este script como root para criar o banco de dados e o usuário

-- Criar banco de dados techlab_edu se não existir
CREATE DATABASE IF NOT EXISTS techlab_edu CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;

-- Criar usuário techlab_user se não existir
CREATE USER IF NOT EXISTS 'techlab_user'@'localhost' IDENTIFIED BY 'techlab_pass';

-- Conceder todos os privilégios no banco techlab_edu
GRANT ALL PRIVILEGES ON techlab_edu.* TO 'techlab_user'@'localhost';

-- Aplicar as alterações
FLUSH PRIVILEGES;

-- Verificar se as tabelas existem (opcional)
SHOW TABLES FROM techlab_edu;
