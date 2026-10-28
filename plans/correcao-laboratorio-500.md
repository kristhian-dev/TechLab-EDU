# Correção do HTTP 500 — Laboratório de Montagem

## 1. Causa raiz confirmada

Cadeia de inicialização quebrada em `views/laboratorio/index.php`:

1. `views/laboratorio/index.php` (linha 2-3) carregava `nucleo/Seguranca.php` e `nucleo/Auth.php`,
   mas **não** carregava `configuracoes/app.php`.
2. `nucleo/Auth.php:4` faz `require_once APP_ROOT . '/nucleo/Database.php';`.
3. `APP_ROOT` só é definido em `configuracoes/app.php:3` (`define('APP_ROOT', dirname(__DIR__));`).
4. Como a página é acessada diretamente (link `../laboratorio/index.php` e regra `/views/*` do
   `techlab.php`, que devolve `return false` para o servidor embutido executar o arquivo),
   o bootstrap nunca passa pelo `roteador.php`. Resultado: PHP 8 lança
   `Error: Undefined constant "APP_ROOT"` → HTTP 500.

Efeitos colaterais do mesmo defeito:
- `$_SESSION` nunca é iniciado (`session_start()` só existe em `configuracoes/app.php:33-35`),
  portanto o token CSRF da meta tag não seria gerado.
- A página não exigia autenticação (nenhum `Auth::exigeLogin()`), contrariando o README.

## 2. Padrão existente no projeto (reutilizado, sem criar mecanismo novo)

Todas as demais páginas autenticadas usam exatamente esta ordem:

```php
require_once __DIR__ . '/../../configuracoes/app.php';   // APP_ROOT, .env, sessão
require_once __DIR__ . '/../../nucleo/Seguranca.php';     // CSRF, escape, redirect
require_once __DIR__ . '/../../nucleo/Auth.php';          // APP_ROOT já definido
...
Auth::exigeLogin();                                       // guard de autenticação
```

Referências: `views/aluno/dashboard.php:2-6`, `views/professor/dashboard.php:2-6`,
`views/perfil/perfil.php:2-14`, `views/configuracao/configuracao.php:2-6`.

`configuracoes/app.php` já é idempotente quanto à sessão
(`if (session_status() === PHP_SESSION_NONE) { session_start(); }`), e todos os `require_once`
evitam dupla carga — logo, não há risco de sessão duplicada nem de sobrescrita de `$_SESSION`.

## 3. Alterações (um único arquivo)

Arquivo alterado: `views/laboratorio/index.php` — nada mais é tocado.

### 3.1 Ordem de bootstrap (linhas 2-3)

```php
// antes
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';

// depois
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
```

### 3.2 Guarda de autenticação (logo após os requires)

```php
Auth::exigeLogin();
```

Reaproveita o fluxo existente: `nucleo/Auth.php:44-50` grava o flash "Faça login para continuar."
e redireciona para `/views/auth/login.php`. Nenhuma regra nova é criada.

### 3.3 Serialização segura da meta tag (linha 29)

```php
// antes
<meta name="lab-usuario" content='<?= json_encode($_SESSION['usuario'] ?? []) ?>'>

// depois
<meta name="lab-usuario" content="<?= htmlspecialchars(json_encode($_SESSION['usuario'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
```

Justificativa: o atributo usava aspas simples e `json_encode` sem escape de HTML; um nome
cadastrado com apóstrofo (`D'Ávila`) ou com `<` fecharia o atributo. As flags `JSON_HEX_*`
escapam `< > & ' "` dentro dos valores; o `htmlspecialchars` garante que as aspas estruturais do
JSON não quebrem o atributo HTML. O navegador devolve o valor já decodificado em
`getAttribute('content')`, ou seja, **o formato entregue ao JavaScript não muda** e o
`JSON.parse` de `views/js/laboratorio.js:663` continua funcionando.

## 4. Fora do escopo (não alterado)

- Layout, CSS e JavaScript do laboratório.
- Esquema do banco, regras de cadastro/login/turmas/perfis.
- Os demais defeitos da auditoria (redirects em subdiretório, transação do cadastro,
  arquivos sensíveis expostos no Apache, dashboards estáticos, etc.) — podem ser tratados
  em tarefas separadas.

## 5. Testes executados (Code mode) — 20/10/2026

Ambiente: PHP 8.3.6, servidor de desenvolvimento do projeto (`php techlab.php`,
já em execução em `127.0.0.1:8000` e `127.0.0.1:8123`), MySQL local.
Nenhuma escrita no banco foi executada.

| # | Teste | Resultado |
|---|-------|-----------|
| 1 | `php -l` em `views/laboratorio/index.php`, `nucleo/Auth.php`, `nucleo/Seguranca.php`, `configuracoes/app.php` | **APROVADO** — "No syntax errors detected" nos 4 arquivos |
| 2 | Prova da causa raiz: bootstrap antigo (`Seguranca` → `Auth` sem `app.php`) | **REPRODUZIDO** — `Error: Undefined constant "APP_ROOT"` (o erro do relato) |
| 3 | Prova do bootstrap novo (com `configuracoes/app.php` primeiro) | **APROVADO** — `APP_ROOT` definido, sessão ativa, `Auth` carregado, sem erro fatal |
| 4 | `GET /views/laboratorio/index.php` **sem login** (portas 8000 e 8123) | **APROVADO** — HTTP 302 → `Location: /views/auth/login.php` (antes: 500). Cookie `PHPSESSID` emitido, confirmando sessão iniciada |
| 5 | `GET /views/laboratorio/index.php?id=3` **sem login** | **APROVADO** — HTTP 302 → login (mesmo fluxo) |
| 6 | Login real (POST em `/views/auth/login.php` com CSRF válido) | **APROVADO** — HTTP 302 → `/views/aluno/dashboard.php` |
| 7 | `GET /views/laboratorio/index.php` **autenticado** | **APROVADO** — HTTP 200, 6.623 bytes; marcadores presentes: "Laboratório de Montagem", `csrf-token`, `lab-usuario`, `lab-canvas`, `laboratorio.js`, "Biblioteca de componentes", "Testar Sistema". Nenhum `Fatal error`/`Warning`/`Notice` na saída |
| 8 | `GET /views/laboratorio/index.php?id=3` **autenticado** | **APROVADO** — HTTP 200; cartão "DESAFIO 3" e título "Fonte Defeituosa" renderizados |
| 9 | Navegação do laboratório: `id=1`, `id=15`, `id=99` (id inexistente) | **APROVADO** — HTTP 200 nos quatro casos |
| 10 | Geração do token CSRF | **APROVADO** — meta `csrf-token` com 64 caracteres hexadecimais; é o **mesmo** token da sessão de login (1 ocorrência de `csrf_token|` no arquivo de sessão) → sessão única, sem duplicidade nem sobrescrita |
| 11 | Token CSRF contra `POST /views/api/salvar-build.php` (token na query string, como faz `views/js/laboratorio.js:602`) | **APROVADO** — o CSRF **foi aceito**; a resposta foi HTTP 400 "Nome da build e obrigatorio" (validação de payload, ou seja, a requisição já passou da checagem de CSRF) |
| 12 | Token CSRF contra `POST /views/api/salvar-diagnostico.php` | **APROVADO** — HTTP 400 "Id da build e obrigatorio" (CSRF aceito) |
| 13 | Controle: `POST /views/api/salvar-build.php` **sem** token | **APROVADO** — HTTP 403 "Token CSRF invalido" (rejeição preservada) |
| 14 | Sessão: estrutura real do arquivo em `storage/sessions/sess_*` | **APROVADO** — chaves `csrf_token`, `usuario`, `flash`; 1 ocorrência de `csrf_token|` e 1 de `usuario|` (sem duplicação); `perfil` = `aluno` |
| 15 | Sessão preservada após abrir o laboratório | **APROVADO** — `GET /views/aluno/dashboard.php` com a mesma sessão → HTTP 200 (302 indicaria sessão perdida) |
| 16 | Serialização da meta tag: `DOMDocument` (parser HTML) + `JSON.parse` no Node | **APROVADO** — valor entregue ao JS idêntico ao original: `{"id":14,"nome":"joão lucas","nome_usuario":"joan","email":"joao@gmail.com","perfil":"aluno","foto":null}` |
| 17 | Payload hostil (`'`, `"`, `<`, `>`, `&` no nome) | **APROVADO** — `JSON.parse` sem erro e resultado **idêntico** ao payload original; 0 tags `<script>` formadas (o payload permanece dentro do valor do atributo) |
| 18 | Regressão: 9 páginas (`dashboard`, `turmas`, `desafios/index`, `desafios/desafio`, `perfil`, `configuracao`, `ranking`, `professor/dashboard`, `laboratorio`) sem e com login | **APROVADO** — sem login: 302 → login (exceto `desafios/desafio.php` e `ranking/index.php`, que **já** não tinham guarda, ver seção 6); com login: 200 e 0 erros PHP em todas |
| 19 | Varredura de HTTP 500 em todas as rotas testadas | **APROVADO** — nenhuma rota retornou 500 |
| 20 | Logs de erro | **APROVADO** — `/var/log/apache2/error.log`: 0 ocorrências de "Undefined constant" e 0 de "Fatal error" (log completo); nenhuma linha referente ao laboratório nas 100 últimas linhas. Nenhum warning/notice na resposta HTTP do laboratório |

### Resumo

Todos os 20 testes executados foram **APROVADOS**. A página do Laboratório de
Montagem abre sem `Undefined constant "APP_ROOT"`, mantendo autenticação, sessão,
token CSRF e as funcionalidades atuais.

## 6. Problemas remanescentes (fora do escopo desta correção)

Nada que impeça o funcionamento do laboratório. Registrados para tarefas futuras:

1. **Apache (porta 80) responde 404 em todas as `/views/`** — inclusive em páginas
   nunca alteradas (`/views/auth/login.php`, `/views/aluno/dashboard.php`). O
   `DocumentRoot` é `/var/www/html` e não há vhost/alias para o subdiretório
   `TechLab_EDU`. **Não é causado por esta correção**; o servidor de desenvolvimento
   configurado no projeto (`php techlab.php`, documentado em
   `documentacao/localhost.md`) funciona normalmente.
2. **`views/desafios/desafio.php` e `views/ranking/index.php` respondem 200 sem
   autenticação** — essas páginas não chamam `Auth::exigeLogin()`. Situação
   pré-existente, mantida deliberadamente: o pedido era corrigir o laboratório
   sem alterar o comportamento das demais páginas.
3. **Logs do `php -S` vão para o terminal** onde o servidor foi iniciado
   (não há `error_log` configurado no `php.ini`); por isso a verificação de
   logs foi feita pelo log do Apache e pela própria resposta HTTP.
4. Os demais defeitos já catalogados em `RELATORIO_TESTES_TECHLAB_EDU.md`
   (DEF-01 a DEF-15) permanecem — não são relacionados a este erro 500.
