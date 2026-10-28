# RELATÓRIO DE AUDITORIA E TESTES — TechLab EDU

- **Data da auditoria:** 13/10/2026
- **Escopo:** Análise estática do código, testes funcionais via HTTP, testes de integração com banco (somente leitura), verificações de segurança básica.
- **Regra aplicada:** Nenhum arquivo do projeto foi modificado, excluído ou criado até este relatório. Nenhuma operação de escrita (INSERT/UPDATE/DELETE) foi executada no banco de dados. Nenhum dado real foi alterado. Nenhum dado sensível (senhas, tokens, chaves) é reproduzido neste documento.

---

## 1. Resumo geral da auditoria

O TechLab EDU é uma aplicação web PHP (sem framework) + MySQL que implementa autenticação, dashboards de aluno e professor, gestão de turmas, cadastro de desafios, ranking/relatórios do professor e um laboratório virtual de montagem (front-end). A auditoria executou **64 casos de teste**, com o seguinte resultado:

| Status | Quantidade |
|---|---|
| Aprovado (executado com resultado esperado) | 38 |
| Reprovado (executado com defeito confirmado) | 17 |
| Bloqueado (impedido por restrição de ambiente/regra) | 6 |
| Não executado (fora do alcance do ambiente) | 3 |

Foram encontrados **15 defeitos: 2 críticos, 5 de alta, 6 de média e 2 de baixa prioridade**. Os pontos fortes confirmados por teste: proteção de rotas por sessão, controle de perfil aluno/professor nas áreas restritas, validação de formulários no servidor, proteção CSRF, consultas parametrizadas (sem SQL Injection) e escape de saída (sem XSS refletido). Os pontos críticos concentram-se em **exposição de arquivos sensíveis via HTTP** (`.env` e arquivos de sessão) e em **falhas de execução quando o app é servido pelo Apache em subdiretório** (redirecionamentos quebrados e sessões impossíveis).

---

## 2. Descrição do ambiente de testes

| Item | Detalhe |
|---|---|
| Sistema | Linux (host do desenvolvedor), usuário `kristhian` |
| PHP | 8.3.6 (CLI) |
| Node.js | v24.15.0 (usado apenas para `node --check`) |
| MySQL | 8.0.46 (serviço ativo, porta 3306) |
| Servidor primário dos testes | `php -S 127.0.0.1:8000` com diretório-corrente na raiz do projeto (sem script router) |
| Servidor secundário | Apache 2 na porta 80 (projeto acessível em `/TechLab_EDU/`) — usado para testes de compatibilidade de deploy |
| Banco | Banco real `techlab_edu` (24 tabelas) com dados reais: 7 usuários, 5 alunos, 2 professores, 2 turmas, 4 desafios, 5 vínculos aluno-desafio, 1 build, 8 componentes. **Somente leitura durante toda a auditoria.** |
| Navegador gráfico | **Indisponível** (sem Chromium/Firefox headless no ambiente) |
| Autenticação para testes | Sessões já existentes em `storage/sessions` foram reaproveitadas **apenas para requisições GET de leitura** (IDs omitidos deste relatório); nenhuma sessão foi criada ou modificada propositalmente |
| Ferramentas | `php -l`, `node --check`, `curl`, scripts auxiliares PHP/Bash escritos **fora** do projeto (em `/tmp`) |
| Verificação de integridade | Contagens de todas as tabelas relevantes verificadas **antes e depois** dos testes — idênticas (ex.: `usuarios=7`, `desafios=4`, `turmas=2`) |

**Recursos disponíveis:** código-fonte completo, PHP CLI, MySQL ativo com banco real, servidor HTTP funcional, log de erros do Apache legível.
**Limitações declaradas antes do início:** sem navegador (UI interativa/console), sem permissão de escrita no banco, log do servidor `php -S` indisponível (saída no terminal do usuário), sem suíte de testes automatizados no projeto (diretório `testes/` vazio).

---

## 3. Funcionalidades analisadas

1. **Estrutura e tecnologias:** PHP 8 procedural (MVC simplificado: `controllers/`, `modelos/`, `views/`, `nucleo/`, `roteador.php`), MySQL via PDO, HTML/CSS/JS puro, Three.js (CDN) no laboratório.
2. **Autenticação:** login (e-mail ou nome de usuário + senha), cadastro (aluno/professor), logout, CSRF, bloqueio de conta (5 tentativas / 15 min), redirecionamento por perfil.
3. **Proteção de rotas e permissões:** `Auth::exigeLogin()` e `Auth::exigePerfil()`.
4. **Dashboard do aluno:** busca, notificações, cartões de progresso, navegação lateral.
5. **Dashboard do professor:** métricas, atividades rápidas.
6. **Turmas (professor):** criar turma, listar turmas, buscar/adicionar/remover alunos (fluxos com POST).
7. **Desafios:** listagem (15 cartões), detalhe, criação (professor), integração com laboratório via `?id=`.
8. **Laboratório de montagem:** biblioteca de componentes, drag-and-drop 3D (Three.js), encaixes/compatibilidade, botão "Testar Sistema" (15 desafios codificados).
9. **Ranking:** página do aluno (estática/vazia), ranking de turmas do professor (com banco).
10. **Relatórios do professor:** turmas e alunos reais do banco, impressão.
11. **Perfil e configurações:** abas, preferências, alteração de senha (placeholder).
12. **Formulários e botões:** login, cadastro, novo desafio, nova turma, busca de alunos, filtros, paginação.
13. **Rotas:** `/`, `/login`, `/cadastro`, `/logout`, `/dashboard`, `/views/*` e arquivos estáticos.
14. **Integração PHP/MySQL:** `UsuarioModel`, `TurmaModel`, `DesafioModel`, esquema `banco/techlab_edu_v2.sql` (24 tabelas), `Database::initializeSchema()`.
15. **Testes automatizados existentes:** **nenhum** (diretório `testes/` vazio; `instalar.php` e `testes/executar.php` referenciados em comentários não existem).
16. **Dependências/requisitos:** PHP ≥ 8 (funções `str_starts_with`), extensão PDO MySQL, MySQL 8, internet para CDN (Three.js e Google Fonts).

---

## 4. Tabela dos casos de teste e resultados

> **Legenda de status:** APROVADO = executado e conformidade confirmada · REPROVADO = executado com defeito · BLOQUEADO = não foi possível executar por restrição/regra · NÃO EXECUTADO = fora do alcance do ambiente.
> Todas as mensagens de evidência abaixo foram capturadas das respostas reais do servidor.

### 4.1 Sintaxe e estrutura

| ID | Funcionalidade | Objetivo | Procedimento | Esperado | Observado | Status |
|---|---|---|---|---|---|---|
| T01 | Sintaxe PHP | Detectar erros de sintaxe em todo o código PHP | `php -l` em todos os 30 arquivos `.php` (fora `storage/`) | 0 erros | 30/30 "No syntax errors detected", exit 0 | **APROVADO** |
| T02 | Sintaxe JavaScript | Detectar erros de sintaxe nos JS | `node --check` nos 10 arquivos de `views/js/` | 0 erros | 10/10 OK | **APROVADO** |
| T03 | Suíte de testes automatizados | Localizar testes existentes | Inspeção de `testes/` e referências no código | Existir suíte | Diretório `testes/` vazio; `testes/executar.php` e `instalar.php` referenciados em comentários mas inexistentes | **REPROVADO** (ver DEF-14) |

### 4.2 Rotas e navegação

| ID | Funcionalidade | Objetivo | Procedimento | Esperado | Observado | Status |
|---|---|---|---|---|---|---|
| T04 | Rota raiz | `/` leva ao login | `GET /` | Redirect ao login | 302 → `/views/auth/login.php` | **APROVADO** |
| T05 | Rotas amigáveis | Aliases do roteador | `GET /login`, `/cadastro`, `/dashboard`, `/logout` | 302 corretos | Todos 302 para os destinos esperados | **APROVADO** |
| T06 | Páginas públicas | Login/cadastro sem sessão | `GET /views/auth/login.php` e `/cadastro.php` | 200 | 200 (3.237 e 5.995 bytes) | **APROVADO** |
| T07 | Proteção sem sessão | Páginas privadas redirecionam | `GET` em 9 páginas privadas sem cookie | 302 → login | 302 em todas (dashboards aluno/professor, turmas, ranking prof., relatórios, lista de desafios, novo desafio, perfil, configurações) | **APROVADO** |
| T08 | Rotas inexistentes | 404 ou redirect controlado | `GET /views/naoexiste.php` e `/rota-inexistente` | Sem erro 500 | 404 para arquivo PHP inexistente; 302→login para rota não mapeada; nenhum 500 | **APROVADO** |
| T09 | Ativos estáticos | CSS/JS/imagens servidos | `GET` em CSS, JS, logo e ícones | 200 | 200 | **APROVADO** |
| T10 | Verificador de links (estático) | Achar referências locais quebradas | Script varrendo todos os `src`/`href` das views (466 referências) | 0 locais faltantes | 0 locais faltantes (restantes: URLs externas e âncoras `#`) | **APROVADO** |
| T11 | Verificador de links (runtime) | Validar links renderizados | Páginas baixadas via `curl` e cada destino verificado | Sem link quebrado | Nenhum destino inexistente; não-200 eram apenas redirecionamentos de destinos protegidos checados sem cookie (com sessão, todos 200) | **APROVADO** |
| T12 | CDN do laboratório | Three.js acessível | `GET` no CDN `three@0.160.0/build/three.min.js` | 200 | HTTP 200, 669.884 bytes | **APROVADO** |

### 4.3 Formulários, login e cadastro (validações do servidor)

| ID | Funcionalidade | Objetivo | Procedimento | Esperado | Observado | Status |
|---|---|---|---|---|---|---|
| T13 | Login — campos vazios | Obrigatoriedade | POST com token CSRF válido e campos vazios | Mensagem de erro, sem login | 302 + flash: "Informe o e-mail ou nome de usuário e a senha." | **APROVADO** |
| T14 | Login — usuário inexistente | Credenciais inválidas | POST com e-mail inexistente (sem escrita no BD) | Mensagem genérica | 302 + flash: "Credenciais inválidas." | **APROVADO** |
| T15 | Login — sem CSRF | Bloquear POST sem token | POST sem campo `csrf_token` | Rejeição | 302 + flash: "Sessão inválida. Recarregue a página e tente novamente." | **APROVADO** |
| T16 | Login — CSRF inválido | Bloquear token errado | POST com token aleatório | Rejeição | 302 + flash: "Sessão inválida..." | **APROVADO** |
| T17 | Cadastro — campos vazios | Obrigatoriedade | POST vazio com CSRF | Mensagem | 302 + flash: "Todos os campos são obrigatórios." | **APROVADO** |
| T18 | Cadastro — sem termos | Aceite obrigatório | POST completo sem `terms` | Mensagem | 302 + flash: "Você precisa aceitar os termos para continuar." | **APROVADO** |
| T19 | Cadastro — senhas divergentes | Confirmação de senha | POST com senhas diferentes | Mensagem | 302 + flash: "As senhas informadas não conferem." | **APROVADO** |
| T20 | Cadastro — senha curta | Mínimo de 6 caracteres | POST com senha de 5 caracteres | Mensagem | 302 + flash: "A senha deve ter pelo menos 6 caracteres." | **APROVADO** |
| T21 | Cadastro — e-mail inválido | Validação de e-mail | POST com e-mail inválido | Mensagem | 302 + flash: "Informe um e-mail válido." | **APROVADO** |
| T22 | Cadastro — e-mail duplicado | Unicidade | POST com e-mail já existente no BD | Rejeição sem INSERT | 302 + flash: "Este e-mail já está em uso."; contagem `usuarios` inalterada (7) | **APROVADO** |
| T23 | Login com credenciais válidas | Autenticar usuário de verdade | — | Login + sessão + redirect ao dashboard | Não executado: nenhuma credencial de teste conhecida; adivinhar senhas de usuários reais foi considerado inaceitável | **BLOQUEADO** |
| T24 | Cadastro com sucesso | Criar conta de teste | — | INSERT | Não executado: regra da auditoria proíbe qualquer escrita no banco | **BLOQUEADO** |
| T25 | Bloqueio por tentativas | 5 falhas → bloqueio de 15 min | — | Conta bloqueada | Não executado: exige 5 POSTs com senha errada de usuário real (UPDATE em `tentativas_incorretas_login`) | **BLOQUEADO** |

---

### 4.4 Permissões de aluno e professor

| ID | Funcionalidade | Objetivo | Procedimento | Esperado | Observado | Status |
|---|---|---|---|---|---|---|
| T26 | Aluno → áreas do professor | Bloqueio por perfil | `GET` nas 4 páginas de professor com sessão de aluno | Bloqueado | 302 → login em turmas, ranking, relatórios e novo desafio | **APROVADO** |
| T27 | Aluno → áreas do aluno | Acesso permitido | `GET` dashboard, desafios, perfil, configurações com sessão de aluno | 200 | 200 em todas | **APROVADO** |
| T28 | Professor → áreas do professor | Acesso permitido | `GET` nas 4 páginas de professor + novo desafio com sessão de professor | 200 | 200 em todas | **APROVADO** |
| T29 | Rota `/dashboard` por perfil | Redirect correto | `GET /dashboard` com cada sessão | Destino por perfil | Aluno → `/views/aluno/dashboard.php`; Professor → `/views/professor/dashboard.php` | **APROVADO** |
| T30 | Professor → dashboard do aluno | Verificar restrição | `GET /views/aluno/dashboard.php` com sessão de professor | Ideal: bloqueado | 200 (página usa apenas `exigeLogin`; ver DEF-08) | **REPROVADO** (ver DEF-08) |
| T31 | Aluno → dashboard do professor | Verificar restrição | `GET /views/professor/dashboard.php` com sessão de aluno | Ideal: bloqueado | 200 (página usa apenas `exigeLogin`; ver DEF-08) | **REPROVADO** (ver DEF-08) |
| T32 | Páginas públicas sem sessão (lab, desafio, ranking) | Avaliar proteção | `GET` sem cookie em `laboratorio/index.php`, `desafios/desafio.php`, `ranking/index.php` | Ideal: exigir login | 200 sem sessão nas 3 (nenhuma chama `Auth`; ver DEF-09) | **REPROVADO** (ver DEF-09) |
| T33 | Exibição da mensagem de permissão negada | Feedback ao usuário | Seguiu a cadeia completa de redirect do aluno negado em `turmas.php` (`-L`) | Mensagem visível | Cadeia termina no dashboard do aluno **sem** a mensagem; nenhum dashboard renderiza `$_SESSION['flash']` (ver DEF-10) | **REPROVADO** (ver DEF-10) |
| T34 | Isolamento entre professores | Dados por professor | Sessão do professor 2 vê apenas suas turmas | Só suas turmas | `turmas.php` listou apenas a turma vinculada a ele (1 das 2 do banco) | **APROVADO** |

### 4.5 Desafios e laboratório de montagem

| ID | Funcionalidade | Objetivo | Procedimento | Esperado | Observado | Status |
|---|---|---|---|---|---|---|
| T35 | Lista de desafios | Exibir desafios | `GET /views/desafios/index.php` com sessão | 200 + itens | 200; 15 cartões estáticos; **nenhum dos 4 desafios reais do banco aparece** (ver DEF-05) | **REPROVADO** (ver DEF-05) |
| T36 | Detalhe do desafio | Exibir desafio por id | `GET desafio.php?id=1` e `?id=999` | 200 + fallback | id=1 → desafio; id=999 → "Desafio não encontrado" | **APROVADO** |
| T37 | Criação de desafio — CSRF | Rejeitar POST sem token | POST em `novo.php` com sessão de professor **sem** `csrf_token` | Rejeição, sem INSERT | 302 de volta a `novo.php`; contagem `desafios` inalterada (4) | **APROVADO** |
| T38 | Criação de desafio com sucesso | INSERT no banco | — | Desafio criado e listado | Não executado: INSERT é escrita no banco (proibida). Fluxo também falharia em T35 por DEF-05 | **BLOQUEADO** |
| T39 | Fallback do laboratório | id inválido → modo livre | `GET laboratorio/index.php?id=999` | Fallback | "Laboratório livre" exibido | **APROVADO** |
| T40 | Consistência de IDs dos desafios | Alinhar lista/detalhe/lab | Contagem de entradas nos 3 arquivos | 15/15/15, ids 1–15 | 15 cartões, 15 entradas em `desafio.php`, 15 em `laboratorio/index.php` (ids 1–15) | **APROVADO** |
| T41 | Interações do laboratório (arrastar peças, encaixes, "Testar Sistema") | Validar UX 3D | — | Componentes encaixam, diagnóstico executa | Não executado: requer navegador com WebGL (sem navegador no ambiente). CDN do Three.js verificado disponível (T12) | **NÃO EXECUTADO** |
| T42 | Criação de turma / adicionar aluno | Escritas no banco | — | Turma/aluno criados | Não executado: INSERT/UPDATE proibidos pela regra da auditoria (fluxo validado apenas estaticamente) | **BLOQUEADO** |

### 4.6 Integração com banco de dados

| ID | Funcionalidade | Objetivo | Procedimento | Esperado | Observado | Status |
|---|---|---|---|---|---|---|
| T43 | Conexão com o banco | PDO conecta com as credenciais do `.env` | Script PHP de leitura com as constantes da aplicação | Conecta | `CONEXAO_OK`; 24 tabelas listadas | **APROVADO** |
| T44 | Esquema real × arquivo SQL | Comparar estruturas | `SHOW TABLES` × `banco/techlab_edu_v2.sql` | 24 = 24 | 24 tabelas idênticas em ambos | **APROVADO** |
| T45 | Renderização com dados reais | Páginas do professor usam o banco | `GET` turmas/relatórios/ranking com sessão do professor e comparação com o banco | Dados reais | Turma "informatica subsequente 2025" (código `INFORMAT-1278`), alunos e pódio vindos do BD exibidos corretamente | **APROVADO** |
| T46 | Integridade pós-testes | Nenhum dado alterado | Contagens antes × depois de todos os testes | Inalterado | `usuarios=7`, `alunos=5`, `professores=2`, `turmas=2`, `desafios=4`, `aluno_turmas=3` etc. — idênticas | **APROVADO** |
| T47 | Dashboard do aluno/professor | Métricas do banco | `GET` dashboards autenticados | Números reais | Valores fixos "0 / 20", "0 / 30", "0" e rótulo "Sem dados" codificados no HTML; nenhum acesso ao banco (ver DEF-06) | **REPROVADO** (ver DEF-06) |

---

### 4.7 Segurança e deploy

| ID | Funcionalidade | Objetivo | Procedimento | Esperado | Observado | Status |
|---|---|---|---|---|---|---|
| T48 | Exposição do `.env` | Ver se credenciais ficam acessíveis via HTTP | `GET /.env` no servidor em execução | Bloqueado (403/404) | **HTTP 200, 112 bytes** — arquivo servido integralmente (conteúdo não reproduzido aqui) (ver DEF-01) | **REPROVADO** (ver DEF-01) |
| T49 | Exposição de arquivos de sessão | Ver se sessões são acessíveis | `GET /storage/sessions/sess_<id>` | Bloqueado | **HTTP 200** com conteúdo da sessão (dados de usuário legíveis) → sequestro de sessão possível (ver DEF-02) | **REPROVADO** (ver DEF-02) |
| T50 | Exposição de SQL com credencial padrão | Arquivos de instalação acessíveis | `GET /init_database.sql`, `/banco/techlab_edu_v2.sql`, `/.gitignore`, `/anotação.html` | Bloqueados | Todos HTTP 200 (o `init_database.sql` contém literal de senha padrão) (ver DEF-03) | **REPROVADO** (ver DEF-03) |
| T51 | CSRF nas rotas de criação | Token obrigatório | POST sem token (T37) + leitura de código de turmas | Todos validam | Login, cadastro, novo desafio e turmas validam `Seguranca::validarTokenCsrf()` | **APROVADO** |
| T52 | SQL Injection | Consultas parametrizadas | `GET turmas.php?...&q=' OR 1=1--` com sessão de professor | Sem erro/fuga | HTTP 200; nenhum "SQLSTATE/syntax error/PDOException" na resposta; todas as queries dos models usam prepared statements | **APROVADO** |
| T53 | XSS refletido | Escape de saída | `GET turmas.php?...&q=<script>alert(1)</script>` | Escape | Payload refletido **escapado** (`&lt;script&gt;...`); versão bruta ausente | **APROVADO** |
| T54 | Sessão — regeneração e logout | Proteção de sessão | Leitura de código + `GET /logout.php` seguido de `GET` na área logada | Destruída | Logout 302 → login; após logout, dashboard → 302 → login. `session_regenerate_id(true)` presente no login (código) | **APROVADO** |
| T55 | Cookie de sessão — HttpOnly | Mitigação XSS | Inspeção do cabeçalho `Set-Cookie` e `php -i` | HttpOnly ativo | `Set-Cookie: PHPSESSID=...; path=/` **sem** `HttpOnly`; `session.cookie_httponly=Off` (ver DEF-04) | **REPROVADO** (ver DEF-04) |
| T56 | Cabeçalhos de segurança | Headers básicos | Inspeção de resposta HTTP | X-Frame-Options etc. | Ausentes: `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`, CSP (ver DEF-11) | **REPROVADO** (ver DEF-11) |
| T57 | Execução sob Apache (porta 80) | Compatibilidade de deploy | `GET /TechLab_EDU/` e páginas protegidas via Apache | Funcional | Redirects apontam para `/views/...` **fora** do subdiretório → destino `http://<host>/views/auth/login.php` = **404** (ver DEF-12) | **REPROVADO** (ver DEF-12) |
| T58 | Sessões sob Apache | Permissões de `storage/sessions` | Requisições via Apache + leitura de `/var/log/apache2/error.log` | Sessão funciona | Warnings: `session_start(): open(.../storage/sessions/sess_..., O_RDWR) failed: Permission denied (13)` — diretório 775 e arquivos 600 pertencentes a outro usuário → login impossível via Apache (ver DEF-13) | **REPROVADO** (ver DEF-13) |
| T59 | Credenciais hardcoded | Segredos no repositório | Inspeção de `configuracoes/banco.php` | Sem credenciais fixas | Contém host/usuário/senha de banco fixos no código-fonte (valor não reproduzido) (ver DEF-07) | **REPROVADO** (ver DEF-07) |
| T60 | Bloqueio de cadastro de administrador | Impedir privilégio indevido | Análise do fluxo + tentativa segura | Bloqueio antes do INSERT | Evidência estática: `AuthController` rejeita `perfil=administrador/admin` (linha 128) **antes** de qualquer INSERT; teste em runtime não executado por risco de escrita no banco | **BLOQUEADO** (evidência estática favorável) |
| T61 | "Esqueceu a senha?", login social, "Lembrar-me", "continuar como" | Funcionalidades anunciadas | Leitura de HTML/JS + clique simulado via código | Ação efetiva | Sem backend nem handlers: `href="#"`, botões `type="button"` sem ação, `remember` ignorado pelo servidor (ver DEF-15) | **REPROVADO** (ver DEF-15) |
| T62 | Alteração de senha e preferências | Configurações funcionais | Leitura de `configuracao.js` | Salva configuração | Intercepta o submit e exibe "Pronto para integração com o backend." — placeholder (ver DEF-15) | **REPROVADO** (ver DEF-15) |
| T63 | Console do navegador e logs do servidor `php -S` | Erros em runtime | Tentativa de leitura dos logs | Acessível | Log do `php -S` vai ao terminal do usuário (inacessível); `display_errors=Off`; erros fatais não observados nas respostas (todas 200/302/404). Log do Apache legível (usado em T58) | **NÃO EXECUTADO** (parcialmente coberto) |
| T64 | Testes de desempenho, responsividade, acessibilidade, cross-browser | Qualidade não funcional | — | — | Sem navegador/ferramentas no ambiente | **NÃO EXECUTADO** |

---

## 5. Erros encontrados e passos para reproduzi-los

> Nenhuma credencial ou token é reproduzido abaixo. O servidor de testes era `http://127.0.0.1:8000` (PHP built-in) e `http://127.0.0.1` (Apache).

### DEF-01 — CRÍTICA — Arquivo `.env` servido publicamente via HTTP
- **Impacto:** Qualquer pessoa acessa as credenciais do banco pelo navegador; com o app exposto fora de localhost, acesso total ao banco.
- **Evidência:** `GET /.env` → **HTTP 200, 112 bytes** (conteúdo omitido por política do relatório).
- **Reprodução:** `curl -i http://127.0.0.1:8000/.env`
- **Causa provável:** O servidor PHP embutido (e o Apache) servem qualquer arquivo da raiz; não há regra negando arquivos ocultos/`storage`. O `.gitignore` protege o Git, mas não o acesso HTTP.

### DEF-02 — CRÍTICA — Arquivos de sessão expostos via HTTP (sequestro de sessão)
- **Impacto:** Um atacante lê o conteúdo da sessão de qualquer usuário logado (id, nome, perfil, token CSRF) e assume a conta.
- **Evidência:** `GET /storage/sessions/sess_<id-de-um-usuário>` → **HTTP 200** com os dados serializados da sessão (chave `usuario` presente).
- **Reprodução:** `curl -i http://127.0.0.1:8000/storage/sessions/sess_<id-listado-no-diretório>`
- **Causa provável:** Diretório de sessões dentro da raiz pública, sem bloqueio de acesso.

### DEF-03 — ALTA — Arquivos de instalação/documentação expostos (um com literal de senha)
- **Impacto:** `init_database.sql` revela o comando de criação de usuário do banco com a senha padrão literal; `banco/techlab_edu_v2.sql`, `.gitignore` e `anotação.html` expõem estrutura/intenções do projeto.
- **Evidência:** `GET /init_database.sql` → 200 (657 bytes); `/banco/techlab_edu_v2.sql` → 200 (12.750 bytes); `/.gitignore` → 200; `/anotação.html` → 200.
- **Reprodução:** `curl -i http://127.0.0.1:8000/init_database.sql`

### DEF-04 — MÉDIA — Cookie de sessão sem `HttpOnly` (e sem `SameSite` declarado)
- **Impacto:** JavaScript malicioso (em caso de XSS) lê o `PHPSESSID`; aumenta o impacto de qualquer XSS futura.
- **Evidência:** resposta com `Set-Cookie: PHPSESSID=...; path=/` (sem `HttpOnly`); `php -i` → `session.cookie_httponly => Off`, `session.cookie_samesite => no value`, `session.use_strict_mode => Off`.
- **Reprodução:** `curl -i http://127.0.0.1:8000/views/auth/login.php | grep -i set-cookie`
- **Correção sugerida:** `session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax'])` em `configuracoes/app.php` e `session.use_strict_mode=1`.

### DEF-05 — ALTA — Lista de desafios é estática: desafios criados pelo professor nunca aparecem
- **Impacto:** O professor cadastra um desafio (INSERT no banco), é redirecionado para a lista e **não o vê lá**. A criação de desafios está incomunicada com a exibição; a tabela `desafio_turmas` também não tem UI de vínculo a turmas.
- **Evidência:** `views/desafios/index.php` não consulta banco (requer apenas `app.php` e `Auth.php`); a página servida tem 15 cartões fixos; nenhum dos 4 títulos reais do banco ("Computador sem vídeo", "Fonte não liga", "Memória incompatível") existe no HTML; "Superaquecimento" aparece apenas por card estático homônimo. `DesafioModel` só possui `criar()` (INSERT) — não há método de listagem.
- **Reprodução:** Comparar `SELECT titulo FROM desafios` (4 itens) com o HTML de `/views/desafios/index.php` (15 cards fixos, 0 do banco).

### DEF-06 — MÉDIA — Dashboards (aluno/professor) e perfil exibem métricas/dados fixos, ignorando o banco
- **Impacto:** O usuário vê sempre "0 / 20", "0 / 30", "Sem dados", mesmo havendo 5 registros em `aluno_desafios`, 2 turmas e 7 usuários — percepção de sistema quebrado. O perfil exibe dados pessoais fictícios fixos em vez dos dados do usuário logado.
- **Evidência:** `views/aluno/dashboard.php` (~linhas 233–263: `0 / 20`, `0 / 30` fixos); `views/professor/dashboard.php` (linhas 110–120: valores `0`/`0%` + "Sem dados" fixos); `views/perfil/perfil.php` com "Colégio Técnico de Teresina", "1º período" e texto "tenho 19 anos" fixos. Nenhum `require` de model nesses arquivos.
- **Reprodução:** `curl -H 'Cookie: PHPSESSID=<sessão-aluno>' http://127.0.0.1:8000/views/aluno/dashboard.php | grep '0 / 20'`

### DEF-07 — ALTA — Credenciais de banco hardcoded em `configuracoes/banco.php`
- **Impacto:** Senha de conta administradora do MySQL versionada no repositório (valor não reproduzido aqui). O arquivo hoje é "órfão" (nenhum código o inclui), mas permanece no código-fonte e em backups.
- **Evidência:** `configuracoes/banco.php` contém array `mysql` com `host`, `usuario` e `senha` em texto puro; `grep -rn 'banco.php'` → somente o próprio arquivo (nenhum `require`).
- **Reprodução:** Abrir `configuracoes/banco.php` (não exibir o valor em público).

### DEF-08 — MÉDIA — Dashboards não respeitam o perfil (cruzamento aluno ↔ professor)
- **Impacto:** Aluno acessa a página do professor e vice-versa (ações internas restritas continuam protegidas, mas a UI é indevida e contradiz o README, que afirma proteção por perfil).
- **Evidência:** `GET /views/professor/dashboard.php` com sessão de aluno → **200**; `GET /views/aluno/dashboard.php` com sessão de professor → **200**. Causa: ambos chamam apenas `Auth::exigeLogin()`.
- **Reprodução:** `curl -H 'Cookie: PHPSESSID=<sessão-aluno>' .../views/professor/dashboard.php`

### DEF-09 — MÉDIA — Laboratório, detalhe de desafio e ranking do aluno sem autenticação
- **Impacto:** Conteúdo da plataforma acessível sem login; contradiz o README ("código protege contra acessos sem autenticação em páginas privadas"). Não há vazamento de dados pessoais, mas quebra a política de acesso.
- **Evidência:** `GET` sem cookie: `/views/laboratorio/index.php` → 200, `/views/desafios/desafio.php?id=1` → 200, `/views/ranking/index.php` → 200. Nenhum desses arquivos requer `nucleo/Auth.php` (o `ranking/index.php` sequer contém PHP).
- **Reprodução:** `curl -i http://127.0.0.1:8000/views/laboratorio/index.php` (sem cookie).

### DEF-10 — MÉDIA — Mensagem de "sem permissão" nunca é exibida ao usuário logado
- **Impacto:** Usuário negado cai na página de login e dali volta ao próprio dashboard sem qualquer aviso; a negação parece um "travamento silencioso".
- **Evidência:** Cadeia com `-L`: `/views/professor/turmas.php` (sessão de aluno) → 302 login → 302 dashboard do aluno; a mensagem "Você não possui permissão para acessar esta área." **ausente** na página final (`grep -c` = 0). `views/aluno/dashboard.php`, `views/professor/dashboard.php`, `views/perfil/perfil.php` e `views/configuracao/configuracao.php` não renderizam `$_SESSION['flash']` (confirmado por `grep -L flash`), e `login.php` redireciona usuários logados **antes** de exibir o flash.
- **Reprodução:** `curl -L -H 'Cookie: PHPSESSID=<sessão-aluno>' http://127.0.0.1:8000/views/professor/turmas.php | grep -c 'não possui permissão'` → 0.

### DEF-11 — BAIXA — Cabeçalhos de segurança HTTP ausentes
- **Impacto:** Clickjacking (`X-Frame-Options`), MIME-sniffing e vazamento de referrer; agrava eventuais XSS.
- **Evidência:** Respostas do servidor não contêm `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` nem `Content-Security-Policy`.
- **Reprodução:** `curl -i http://127.0.0.1:8000/views/auth/login.php`

### DEF-12 — ALTA — Redirecionamentos quebrados quando servido em subdiretório (Apache)
- **Impacto:** Sob `http://<host>/TechLab_EDU/`, todo redirect leva a `/views/auth/login.php` **fora** do subdiretório → 404. Login, proteção de rotas e logout ficam inutilizáveis nesse modo.
- **Evidência:** `GET http://127.0.0.1/TechLab_EDU/` → 302 com destino `http://127.0.0.1/views/auth/login.php`; `GET http://127.0.0.1/views/auth/login.php` → **404**. Causa: `Seguranca::redirecionar()` usa caminhos absolutos iniciados em `/`.
- **Reprodução:** `curl -i http://127.0.0.1/TechLab_EDU/` e seguir o cabeçalho `Location`.

### DEF-13 — ALTA — Sessões impossíveis sob Apache (permissões de `storage/sessions`)
- **Impacto:** Via Apache o login/sessão falha sempre: `session_start()` não consegue criar/ler arquivos → sessão vazia em toda requisição.
- **Evidência:** `/var/log/apache2/error.log`: `PHP Warning: session_start(): open(/var/www/html/TechLab_EDU/storage/sessions/sess_..., O_RDWR) failed: Permission denied (13)`. Diretório `drwxrwxr-x` (775) e arquivos `-rw-------` (600) pertencentes ao usuário que roda o `php -S`; o usuário `www-data` não tem permissão de escrita no diretório nem de leitura nos arquivos.
- **Reprodução:** `curl -i http://127.0.0.1/TechLab_EDU/views/auth/login.php` e observar o log do Apache.

### DEF-14 — BAIXA — Suíte de testes inexistente e referências a arquivos inexistentes
- **Impacto:** Sem regressão automatizada; instruções enganosas na documentação/comentários.
- **Evidência:** diretório `testes/` vazio; `instalar.php` e `testes/executar.php` citados em `configuracoes/banco.php` não existem; o README descreve `testes/` como "scripts de validação".

### DEF-15 — MÉDIA — Funcionalidades anunciadas na UI são placeholders sem efeito
- **Impacto:** Usuário clica e nada acontece: "Esqueceu a senha?" (`href="#"`), "Lembrar-me" (ignorado pelo servidor), login Google/Microsoft e "continuar como Aluno/Professor" (botões sem ação), alteração de senha (exibe "Pronto para integração com o backend."), badges de notificação fixos.
- **Evidência:** `views/auth/login.php` (linhas 70–81), `views/auth/cadastro.php` (linhas 113–119), `views/js/configuracao.js` (linhas 19–24, intercepta o submit e exibe texto placeholder); não há endpoints correspondentes em `roteador.php`.
- **Reprodução:** Abrir login/cadastro/configurações e inspecionar os botões citados.

---

## 6. Funcionalidades aprovadas (com base em testes efetivamente executados)

1. **Sintaxe e integridade do código:** 30 arquivos PHP e 10 JavaScript sem erros de sintaxe (T01, T02).
2. **Mapeamento de rotas principais:** `/`, `/login`, `/cadastro`, `/dashboard`, `/logout` e `/views/*` com comportamento correto; rotas inválidas sem erro 500 (T04–T08).
3. **Proteção de páginas privadas sem sessão:** todas as 9 páginas testadas redirecionam ao login (T07).
4. **Validação de formulários no servidor:** todas as 10 validações de login/cadastro testadas retornaram a mensagem esperada e nenhuma escrita indevida no banco (T13–T22).
5. **Proteção CSRF:** login, cadastro e criação de desafio rejeitam POSTs sem/ com token inválido (T15, T16, T37, T51).
6. **Controle de permissão por perfil nas áreas restritas:** aluno bloqueado em turmas, ranking, relatórios e novo desafio; professor com acesso liberado; `/dashboard` redireciona por perfil (T26–T29).
7. **Isolamento de dados entre professores:** cada professor vê apenas suas turmas (T34).
8. **Logout efetivo:** sessão destruída; área protegida volta a redirecionar (T54).
9. **Segurança de dados:** consultas parametrizadas sem SQL Injection (T52) e saída escapada contra XSS refletido (T53).
10. **Banco de dados:** conexão OK, esquema real idêntico ao arquivo SQL (24 tabelas), integridade confirmada antes/depois dos testes — nenhum dado alterado (T43, T44, T46).
11. **Renderização com dados reais nas páginas do professor:** turmas, relatórios e ranking exibem registros do banco corretamente (T45).
12. **Navegação e ativos:** zero links/ativos locais quebrados em verificação estática e runtime; CDN do Three.js acessível (T09–T12).
13. **Fallbacks de conteúdo:** ids inválidos de desafio e laboratório tratados graciosamente; consistência de ids 1–15 entre lista, detalhe e laboratório (T36, T39, T40).

---

## 7. Testes bloqueados ou não executados e motivos

| ID | Teste | Motivo | Como executar manualmente (quando permitido) |
|---|---|---|---|
| T23 | Login com credenciais válidas | Nenhuma credencial de teste conhecida; adivinhar senhas de usuários reais é inaceitável | Criar usuário de teste em ambiente de homologação e fazer `POST /views/auth/login.php` com CSRF válido; validar redirect ao dashboard por perfil |
| T24 | Cadastro com sucesso | Regra da auditoria proíbe escrita no banco | POST completo com dados únicos; conferir INSERT em `usuarios` + `alunos`/`professores` e login em seguida |
| T25 | Bloqueio por 5 tentativas | Exige UPDATE em `tentativas_incorretas_login` de usuário real | Usar usuário de teste: 5 POSTs com senha errada → conferir `bloqueado_ate = NOW()+15min` e recusa de login |
| T38/T42 | Criação de desafio/turma e adição de aluno | INSERT/UPDATE proibidos | Como professor: preencher `novo.php`/`turmas.php` com CSRF válido; conferir tabelas `desafios`, `turmas`, `professor_turmas`, `aluno_turmas` e — importante — verificar DEF-05 |
| T60 | Cadastro de administrador em runtime | Risco de INSERT acidental (o código bloqueia antes do INSERT, mas não foi arriscado) | POST com `perfil=administrador` e e-mail único; esperar "Não é permitido criar uma conta administrativa" sem INSERT |
| T41 | Interações do laboratório (drag-and-drop, "Testar Sistema") | Sem navegador/WebGL no ambiente | Abrir `http://localhost:8000/views/laboratorio/index.php` no navegador, arrastar peças aos encaixes, clicar em "Testar Sistema" e conferir o diagnóstico |
| T63 | Console do navegador e log do `php -S` | Log do servidor vai ao terminal do usuário (inacessível); sem navegador | Subir `php -S` redirecionando para arquivo, abrir DevTools (aba Console/Network) e navegar no app |
| T64 | Desempenho, responsividade, acessibilidade, cross-browser | Sem navegador/ferramentas (Lighthouse, axe, etc.) | Executar Lighthouse/axe no navegador e testar viewport móvel |

**Testes automatizados existentes:** nenhum (diretório `testes/` vazio) — não havia o que executar (T03/DEF-14).

---

## 8. Recomendações de correção, priorizadas

### Prioridade CRÍTICA (corrigir imediatamente antes de qualquer exposição da rede)
1. **Bloquear arquivos sensíveis no servidor (DEF-01, DEF-02, DEF-03):**
   - Mover `.env`, `storage/`, `banco/`, `configuracoes/`, `nucleo/`, `modelos/`, `controllers/` para **fora** da raiz pública, ou
   - Negar acesso via configuração: para `php -S`, usar um script router que retorne 404 para `/.env`, `/storage/*`, `/*.sql`, `/.gitignore`; para Apache, regras `<Files>`/`Require all denied`.
   - **Rotacionar as credenciais do banco** expostas no `.env` e em `init_database.sql`, pois podem ter sido visualizadas.
2. **Remover a senha hardcoded de `configuracoes/banco.php` (DEF-07)** — apagar o arquivo (está órfão) ou migrar tudo para `.env` e rotacionar a credencial.

### Prioridade ALTA
3. **Tornar os redirects portáveis (DEF-12):** usar caminhos relativos ou prefixo base (`Seguranca::redirecionar()` derivando o `basePath` de `$_SERVER['SCRIPT_NAME']`/`REQUEST_URI`) para funcionar em subdiretórios.
4. **Corrigir permissões de `storage/sessions` (DEF-13):** dono/grupo comuns entre os servidores (`chmod`/`chown` adequados); idealmente mover `session_save_path` para fora da raiz (ex.: `/var/lib/php/sessions` ou `/tmp`).
5. **Integrar a lista de desafios ao banco (DEF-05):** criar `DesafioModel::listar()` e renderizar os desafios do banco em `views/desafios/index.php`; adicionar UI de vínculo professor→turma (`desafio_turmas`).

### Prioridade MÉDIA
6. **Ativar `HttpOnly` + `SameSite` + `use_strict_mode` (DEF-04)** na inicialização da sessão.
7. **Exibir mensagens de flash nos dashboards (DEF-10)** — adicionar renderização de `$_SESSION['flash']` em `aluno/dashboard.php` e `professor/dashboard.php`.
8. **Restringir dashboards por perfil (DEF-08):** `professor/dashboard.php` → `Auth::exigePerfil('professor','administrador')`.
9. **Exigir login nas páginas públicas (DEF-09):** adicionar `Auth::exigeLogin()` em `laboratorio/index.php`, `desafios/desafio.php` e `ranking/index.php`.
10. **Alimentar dashboards/perfil com dados reais (DEF-06):** progresso do aluno (`aluno_desafios`, `builds`), métricas do professor e dados do usuário no perfil.
11. **Remover placeholders ou marcá-los como "em breve" (DEF-15):** esqueci a senha, login social, lembrar-me, alteração de senha (implementar com `password_verify`/`password_hash` + transação).

### Prioridade BAIXA
12. **Adicionar cabeçalhos de segurança (DEF-11):** `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `Referrer-Policy: same-origin`.
13. **Criar uma suíte de testes mínima (DEF-14):** aproveitar a ideia do banco de testes citada em `configuracoes/banco.php` (recriado a cada execução) cobrindo validações, CSRF, permissões e rotas; corrigir as referências a `instalar.php`/`testes/executar.php` inexistentes.
14. **Higiene de repositório:** remover `anotação.html` da raiz (ou mover para `documentacao/`).

---

## 9. Limitações da auditoria

1. **Sem navegador gráfico/headless:** não foram executados testes de interação real (drag-and-drop do laboratório, console do DevTools, rendering WebGL com Three.js, responsividade, acessibilidade). A UI foi coberta por análise estática + requisições HTTP.
2. **Sem escrita no banco:** login com credenciais válidas, cadastro, criação de turmas/desafios, bloqueio por tentativas e vínculos ficaram **bloqueados** por regra da auditoria (§7).
3. **Credenciais desconhecidas:** não foi possível autenticar um usuário de forma legítima; os testes autenticados reaproveitaram sessões já existentes em `storage/sessions` (somente leitura; IDs não divulgados).
4. **Log do servidor `php -S` inacessível:** `display_errors=Off` e a saída vai ao terminal do usuário; a checagem de erros de PHP se apoiou no log do Apache (acessível) e na ausência de respostas 500.
5. **Testes não funcionais ausentes:** desempenho, carga, compatibilidade entre navegadores e testes de upgrade não foram realizados.
6. **Escopo do banco:** o banco real foi tratado como fonte de verdade (24 tabelas conferidas com o SQL); a criação automática do esquema (`Database::initializeSchema()`) em banco vazio não foi testada (exigiria escrita).
7. **Dependências externas:** disponibilidade do CDN (Three.js/Google Fonts) verificada apenas no momento do teste.
8. **Nenhum arquivo do projeto foi modificado** — única exceção: este relatório, conforme instruído. Nenhum INSERT/UPDATE/DELETE foi executado (contagens idênticas antes/depois, T46).

---

## 10. Conclusão sobre o estado atual do projeto

O TechLab EDU apresenta **base sólida de segurança de aplicação** nos pontos críticos do dia a dia: roteamento protegido por sessão, controle de perfil nas áreas restritas, validações de servidor, CSRF, consultas parametrizadas e escape de saída — todos **confirmados por teste executado**. A integração com MySQL funciona e as páginas do professor (turmas, relatórios, ranking) exibem dados reais corretamente, com isolamento entre usuários.

Entretanto, o projeto **não está apto para exposição em rede** em seu estado atual:

1. **Dois defeitos críticos de exposição** (`.env` e arquivos de sessão servidos via HTTP) permitem comprometer banco e contas com uma única requisição.
2. **O deploy via Apache — modo compatível com o `/var/www/html` do ambiente — está quebrado** (DEF-12 e DEF-13: redirects 404 e sessões impossíveis); o app só funciona no modo `php -S` executado na raiz do projeto, como documentado no README.
3. **A funcionalidade de desafios está incompleta:** a criação grava no banco, mas a lista é estática e nunca mostra o que foi criado (DEF-05) — falharia em um teste de aceitação do usuário.
4. Dashboards e perfil exibem dados fixos (DEF-06) e várias peças de UI não têm implementação (DEF-15), contradizendo a percepção de um sistema integrado.
5. **Não existe suíte de testes automatizados** (DEF-14), o que expõe o projeto a regressões.

**Recomendação de encerramento:** corrigir primeiro os itens críticos/altos (§8, passos 1–5), executar depois a rodada manual em navegador cobrindo os testes bloqueados do §7 (login/cadastro completos, criação de desafio/turma e o laboratório) e, por fim, reexecutar este plano de testes como regressão antes de liberar para homologação.

---

*Relatório gerado por auditoria QA baseada em evidências coletadas no ambiente em 13/10/2026. Nenhum dado sensível (senhas, tokens, chaves) é reproduzido neste documento.*
