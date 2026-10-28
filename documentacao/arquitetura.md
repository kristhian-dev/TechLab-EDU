# Arquitetura do TechLab EDU

## Visão geral

A aplicação foi organizada em camadas por responsabilidade:

- `banco/`: schema principal e migração oficial do banco TechLab EDU versão 2.
- `configuracoes/`: variáveis de ambiente e conexão PDO.
- `nucleo/`: sessão, autenticação e validações por perfil.
- `modelos/`: acesso à base `usuarios`, `alunos`, `professores`, `turmas`, `desafios` e demais entidades.
- `controllers/`: fluxos de login, cadastro e dashboards.
- `views/`: telas HTML existente preservadas sem redesign.

## Lacunas e decisões

1. O banco oficial não inclui uma tabela de papéis explícita em `usuarios`; o perfil é inferido a partir de `alunos`, `professores` e `administradores`.
2. O cadastro do tipo aluno e professor é permitido, mas a criação de administrador fica bloqueada no cadastro público para evitar privilégios indevidos.
3. O painél de dashboards usa os dados da sessão autenticada em vez de números fictícios.
4. Para preservar a base já desenvolvida, não foram criadas tabelas paralelas de usuários ou desafios.

## Migração recomendada

A estrutura armazenada em `banco/techlab_edu_v2.sql` deve ser usada como base de referência. Quando o banco real estiver disponível, execute esse script usando a conta administrativa do ambiente local. O projeto verifica automaticamente a existência das tabelas `usuarios` e `alunos` antes de tentar criar o esquema.
