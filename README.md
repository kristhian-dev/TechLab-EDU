# TechLab EDU - Backend PHP + MySQL

Este projeto reúne o frontend já desenvolvido com um backend mínimo em PHP, conectando autenticação, cadastro, dashboards e rotas às tabelas do banco `techlab_edu`.

## Estrutura

- `banco/`: script SQL oficial e migrações
- `configuracoes/`: configuração de ambiente e conexão
- `controllers/`: autenticação e dashboards
- `modelos/`: acesso ao banco
- `nucleo/`: helpers de sessão, segurança e autenticação
- `views/`: telas existentes do frontend
- `assets/`: css, js e mídias do projeto
- `storage/`: arquivos gerados
- `documentacao/`: arquitetura e documentação
- `testes/`: scripts de validação

## Configuração

Crie um arquivo `.env` na raiz do projeto ou defina as variáveis de ambiente antes de executar:

- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_NAME=techlab_edu`
- `DB_USER=techlab_user`
- `DB_PASS=techlab_pass`

O projeto tenta usar estes valores por padrão quando as variáveis não existem.

## Executar localmente

```
php -S localhost:8000
```

Acesse:

- http://localhost:8000/
- http://localhost:8000/views/auth/login.php
- http://localhost:8000/views/auth/cadastro.php

## Observações

- O banco segue a estrutura oficial da versão 2 do TechLab EDU.
- O backend não redesenha a interface; apenas conecta as telas ao servidor e autentica usuários.
- Usuários administrativos não podem ser criados por cadastro normal.
- O código protege contra acessos sem autenticação em dashboards e páginas privadas.
