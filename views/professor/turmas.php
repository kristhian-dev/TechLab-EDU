<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';
require_once __DIR__ . '/../../modelos/TurmaModel.php';
require_once __DIR__ . '/../../controllers/TurmaController.php';

Auth::exigePerfil('professor', 'administrador');

$avatarUrl = Avatar::url();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = (string) ($_POST['acao'] ?? '');

    if ($acao === 'criar_turma') {
        TurmaController::processarCriacao();
    } elseif ($acao === 'excluir_turma') {
        TurmaController::processarExclusao();
    } elseif ($acao === 'adicionar_aluno') {
        TurmaController::processarAdicionarAluno();
    } elseif ($acao === 'remover_aluno') {
        TurmaController::processarRemoverAluno();
    }
}

$usuario = Auth::usuarioLogado() ?: ['nome' => 'Professor', 'id' => 0];
$nomeUsuario = $usuario['nome'] ?? 'Professor';
$idProfessor = (int) ($usuario['id'] ?? 0);

$aba = (string) ($_GET['aba'] ?? 'turmas');
if (!in_array($aba, ['turmas', 'nova', 'alunos'], true)) {
    $aba = 'turmas';
}

$idTurmaSelecionada = (int) ($_GET['id'] ?? 0);
$buscaAluno = trim((string) ($_GET['q'] ?? ''));

$turmas = TurmaModel::listarPorProfessor($idProfessor);

$turmaSelecionada = null;
if ($aba === 'alunos') {
    if ($idTurmaSelecionada > 0 && TurmaModel::professorGerenciaTurma($idProfessor, $idTurmaSelecionada)) {
        $turmaSelecionada = TurmaModel::buscarPorId($idTurmaSelecionada);
    } elseif (!empty($turmas)) {
        // Sem turma escolhida, abre a primeira para facilitar
        header('Location: ./turmas.php?aba=alunos&id=' . (int) $turmas[0]['id_turma']);
        exit;
    }
}

$alunosTurma = $turmaSelecionada ? TurmaModel::listarAlunos((int) $turmaSelecionada['id_turma']) : [];
$resultadosBusca = ($turmaSelecionada && $buscaAluno !== '')
    ? TurmaModel::buscarAlunos($buscaAluno, (int) $turmaSelecionada['id_turma'])
    : [];

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Bloco de sucesso exibido uma unica vez logo apos a criacao da turma,
// com o codigo de entrada real persistido no banco.
$codigoSucesso = $_SESSION['turma_codigo_sucesso'] ?? null;
unset($_SESSION['turma_codigo_sucesso']);
if (!is_array($codigoSucesso) || trim((string) ($codigoSucesso['codigo'] ?? '')) === '') {
    $codigoSucesso = null;
}
// Defesa extra: so exibe o bloco se a turma em foco pertence ao professor logado.
if ($codigoSucesso !== null && $aba === 'alunos' && $turmaSelecionada !== null) {
    $codigoDaTurmaEmFoco = (string) ($turmaSelecionada['codigo'] ?? '');
    if ($codigoDaTurmaEmFoco === '' || $codigoDaTurmaEmFoco !== (string) $codigoSucesso['codigo']) {
        $codigoSucesso = null;
    }
}
$csrfToken = Seguranca::gerarTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Turmas</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/prof_dashboard.css?v=2">
    <link rel="stylesheet" href="../css/prof_turmas.css?v=2">
</head>

<body>
    <header class="main-header">
        <div class="header-left">
            <div class="logo-container">
                <img src="../../assets/logo/Logo_TechLabEDU.png" alt="Logo TechLab EDU" class="logo-img">
                <div class="logo-text">
                    <span class="brand-name">TechLab EDU</span>
                    <span class="brand-subtitle">Laboratório Virtual de Hardware</span>
                </div>
            </div>
        </div>
        <div class="header-right">
            <label class="search-box" aria-label="Pesquisar nas turmas">
                <img src="../../assets/icons-dashboard/lupa.png" alt="" class="search-icon">
                <input type="search" placeholder="Pesquisar...">
            </label>
            <div class="notification-icon" aria-label="Notificações">
                <img src="../../assets/icons-dashboard/bell.png" alt="" class="notification-icon-image">
                <span class="notification-badge">0</span>
            </div>
            <div class="user-profile" id="profileDropdown">
                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="Perfil do professor" class="avatar">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                    <span class="user-role">Professor</span>
                </div>
                <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="Abrir perfil" class="chevron">
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav class="sidebar" aria-label="Navegação principal">
            <ul class="menu">
                <li class="menu-item"><a class="menu-link" href="./dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt=""><span>Dashboard</span></a></li>
                <li class="menu-item <?php echo $aba !== 'alunos' ? 'active' : ''; ?>"><a class="menu-link" href="./turmas.php" <?php echo $aba !== 'alunos' ? 'aria-current="page"' : ''; ?>><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt=""><span>Turmas</span></a></li>
                <li class="menu-item <?php echo $aba === 'alunos' ? 'active' : ''; ?>"><a class="menu-link" href="./turmas.php?aba=alunos" <?php echo $aba === 'alunos' ? 'aria-current="page"' : ''; ?>><img src="../../assets/icons-dashboard/account.png" alt=""><span>Alunos</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt=""><span>Desafios</span></a></li>
                <li class="menu-item"><a class="menu-link" href="./ranking.php"><img src="../../assets/Icons-NavBar/ranking.png" alt=""><span>Ranking</span></a></li>
                <li class="menu-item"><a class="menu-link" href="./relatorios.php"><img src="../../assets/Icons-NavBar/relatorio.png" alt=""><span>Relatórios</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt=""><span>Perfil</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt=""><span>Configuração</span></a></li>
            </ul>
            <div class="menu-footer">
                <div class="menu-item logout"><a class="menu-link" href="../../logout.php"><img src="../../assets/Icons-NavBar/logout.png" alt=""><span>Sair</span></a></div>
            </div>
        </nav>

        <main class="content classes-content">
            <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
                <?php echo htmlspecialchars($flash['message']); ?>
            </div>
            <?php endif; ?>

            <?php if ($codigoSucesso !== null): ?>
            <section class="code-success" aria-labelledby="code-success-title" aria-live="polite">
                <div class="code-success-info">
                    <span class="code-success-eyebrow">Turma criada com sucesso</span>
                    <h2 id="code-success-title">Compartilhe o codigo com seus alunos</h2>
                    <p>Turma <strong><?php echo htmlspecialchars($codigoSucesso['nome']); ?></strong>. Envie este codigo pelo WhatsApp, e-mail ou outro meio para que os alunos entrem pela pagina Minhas turmas.</p>
                    <div class="code-success-row">
                        <code id="codigo-turma-criada"><?php echo htmlspecialchars($codigoSucesso['codigo']); ?></code>
                        <button type="button" class="mini-action code-copy" data-copy-target="codigo-turma-criada">Copiar codigo</button>
                    </div>
                    <p class="code-copy-feedback" id="codigo-turma-criada-feedback" role="status" aria-live="polite"></p>
                </div>
            </section>
            <?php endif; ?>

            <header class="classes-heading">
                <div>
                    <span class="eyebrow">Área do professor</span>
                    <h1>Turmas</h1>
                    <p>Organize suas turmas e acompanhe o desempenho dos alunos.</p>
                </div>
                <?php if ($aba !== 'nova'): ?>
                <a class="primary-action" href="./turmas.php?aba=nova">+ Nova turma</a>
                <?php endif; ?>
            </header>

            <div class="classes-tabs" role="tablist" aria-label="Abas de turmas">
                <a role="tab" class="classes-tab <?php echo $aba === 'turmas' ? 'active' : ''; ?>" href="./turmas.php?aba=turmas">Minhas turmas</a>
                <a role="tab" class="classes-tab <?php echo $aba === 'nova' ? 'active' : ''; ?>" href="./turmas.php?aba=nova">Nova turma</a>
                <?php if ($aba === 'alunos' && $turmaSelecionada): ?>
                <a role="tab" class="classes-tab active" href="./turmas.php?aba=alunos&amp;id=<?php echo (int) $turmaSelecionada['id_turma']; ?>">Alunos</a>
                <?php endif; ?>
            </div>

            <?php if ($aba === 'nova'): ?>
            <!-- Aba: Nova turma -->
            <section class="classes-panel" aria-labelledby="nova-turma-title">
                <div class="panel-heading">
                    <div>
                        <h2 id="nova-turma-title">Nova turma</h2>
                        <p>Preencha os dados para criar a turma. Em seguida você poderá adicionar os alunos.</p>
                    </div>
                    <img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">
                </div>

                <form method="post" action="./turmas.php" class="turma-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="acao" value="criar_turma">

                    <div class="form-field">
                        <label for="nome">Nome da turma *</label>
                        <input type="text" id="nome" name="nome" maxlength="120" required placeholder="Ex.: 1º Ano - Manhã">
                    </div>

                    <div class="form-field">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" rows="3" placeholder="Ex.: Turma de introdução à manutenção de computadores"></textarea>
                    </div>

                    <div class="form-buttons">
                        <a class="btn-neutral" href="./turmas.php?aba=turmas">Cancelar</a>
                        <button class="primary-action" type="submit">Criar turma</button>
                    </div>
                </form>
            </section>

            <?php elseif ($aba === 'alunos' && $turmaSelecionada): ?>
            <!-- Aba: Alunos da turma -->
            <section class="classes-panel" aria-labelledby="alunos-title">
                <div class="panel-heading">
                    <div>
                        <h2 id="alunos-title">Alunos — <?php echo htmlspecialchars($turmaSelecionada['nome']); ?></h2>
                        <p class="turma-code-line">Código da turma: <code id="codigo-turma-atual"><?php echo htmlspecialchars($turmaSelecionada['codigo']); ?></code>
                            <button type="button" class="mini-action code-copy code-copy-inline" data-copy-target="codigo-turma-atual">Copiar código</button>
                        </p>
                        <p class="code-copy-feedback" id="codigo-turma-atual-feedback" role="status" aria-live="polite"></p>
                    </div>
                    <img src="../../assets/icons-dashboard/account.png" alt="">
                </div>

                <form method="get" action="./turmas.php" class="aluno-search-form">
                    <input type="hidden" name="aba" value="alunos">
                    <input type="hidden" name="id" value="<?php echo (int) $turmaSelecionada['id_turma']; ?>">
                    <label class="classes-search">
                        <span aria-hidden="true">⌕</span>
                        <input type="search" name="q" value="<?php echo htmlspecialchars($buscaAluno); ?>" placeholder="Buscar aluno por nome, e-mail ou matrícula..." aria-label="Buscar aluno">
                    </label>
                    <button class="secondary-action" type="submit">Buscar</button>
                </form>

                <?php if ($buscaAluno !== ''): ?>
                <div class="aluno-results">
                    <h3>Resultados da busca</h3>
                    <?php if (empty($resultadosBusca)): ?>
                    <p class="aluno-empty-hint">Nenhum aluno encontrado para "<?php echo htmlspecialchars($buscaAluno); ?>".</p>
                    <?php else: ?>
                    <ul class="aluno-list">
                        <?php foreach ($resultadosBusca as $resultado): ?>
                        <li class="aluno-item">
                            <div class="aluno-info">
                                <strong><?php echo htmlspecialchars($resultado['nome']); ?></strong>
                                <small><?php echo htmlspecialchars($resultado['email']); ?> • Matrícula <?php echo htmlspecialchars($resultado['matricula']); ?></small>
                            </div>
                            <?php if ((int) $resultado['na_turma'] > 0): ?>
                            <span class="aluno-badge">Já na turma</span>
                            <?php else: ?>
                            <form method="post" action="./turmas.php">
                                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                <input type="hidden" name="acao" value="adicionar_aluno">
                                <input type="hidden" name="id_turma" value="<?php echo (int) $turmaSelecionada['id_turma']; ?>">
                                <input type="hidden" name="id_aluno" value="<?php echo (int) $resultado['id_usuario']; ?>">
                                <input type="hidden" name="busca" value="<?php echo htmlspecialchars($buscaAluno); ?>">
                                <button class="mini-action" type="submit">+ Adicionar</button>
                            </form>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <div class="aluno-results">
                    <h3>Alunos na turma (<?php echo count($alunosTurma); ?>)</h3>
                    <?php if (empty($alunosTurma)): ?>
                    <p class="aluno-empty-hint">Nenhum aluno nesta turma ainda. Use a busca acima para adicionar.</p>
                    <?php else: ?>
                    <ul class="aluno-list">
                        <?php foreach ($alunosTurma as $aluno): ?>
                        <li class="aluno-item">
                            <div class="aluno-info">
                                <strong><?php echo htmlspecialchars($aluno['nome']); ?></strong>
                                <small><?php echo htmlspecialchars($aluno['email']); ?> • Matrícula <?php echo htmlspecialchars($aluno['matricula']); ?></small>
                            </div>
                            <div class="aluno-actions">
                                <a class="mini-action" href="./aluno.php?id_turma=<?php echo (int) $turmaSelecionada['id_turma']; ?>&amp;id_aluno=<?php echo (int) $aluno['id_usuario']; ?>">Ver informações</a>
                                <form method="post" action="./turmas.php">
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                                    <input type="hidden" name="acao" value="remover_aluno">
                                    <input type="hidden" name="id_turma" value="<?php echo (int) $turmaSelecionada['id_turma']; ?>">
                                    <input type="hidden" name="id_aluno" value="<?php echo (int) $aluno['id_usuario']; ?>">
                                    <button class="mini-action danger" type="submit">Remover</button>
                                </form>
                            </div>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    <?php endif; ?>
                </div>
            </section>

            <?php else: ?>
            <!-- Aba: Minhas turmas -->
            <section class="classes-toolbar" aria-label="Filtros de turmas">
                <label class="classes-search">
                    <span aria-hidden="true">⌕</span>
                    <input type="search" placeholder="Pesquisar turma..." aria-label="Pesquisar turma" id="turma-search">
                </label>
                <span class="classes-count"><?php echo count($turmas); ?> turma<?php echo count($turmas) === 1 ? '' : 's'; ?> cadastrada<?php echo count($turmas) === 1 ? '' : 's'; ?></span>
            </section>

            <section class="classes-panel" aria-labelledby="classes-list-title">
                <div class="panel-heading">
                    <div>
                        <h2 id="classes-list-title">Minhas turmas</h2>
                        <p>As turmas cadastradas aparecerão nesta área.</p>
                    </div>
                    <img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">
                </div>

                <?php if (empty($turmas)): ?>
                <div class="classes-empty">
                    <div class="empty-icon"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt=""></div>
                    <h3>Nenhuma turma cadastrada</h3>
                    <p>Crie sua primeira turma para começar a organizar seus alunos.</p>
                    <a class="secondary-action" href="./turmas.php?aba=nova">Criar primeira turma</a>
                </div>
                <?php else: ?>
                <ul class="turma-grid" id="turma-grid">
                    <?php foreach ($turmas as $turma): ?>
                    <li class="turma-card" data-nome="<?php echo strtolower(htmlspecialchars($turma['nome'])); ?>">
                        <div class="turma-card-top">
                            <span class="turma-code"><?php echo htmlspecialchars($turma['codigo']); ?></span>
                            <span class="turma-students"><?php echo (int) $turma['total_alunos']; ?> aluno<?php echo (int) $turma['total_alunos'] === 1 ? '' : 's'; ?></span>
                        </div>
                        <h3 class="turma-name"><?php echo htmlspecialchars($turma['nome']); ?></h3>
                        <?php if (!empty($turma['descricao'])): ?>
                        <p class="turma-description"><?php echo htmlspecialchars($turma['descricao']); ?></p>
                        <?php endif; ?>
                        <a class="secondary-action" href="./turmas.php?aba=alunos&amp;id=<?php echo (int) $turma['id_turma']; ?>">Gerenciar alunos</a>
                        <form method="post" action="./turmas.php" class="turma-delete-form" data-confirm-exclusao="Tem certeza que deseja excluir a turma &quot;<?php echo htmlspecialchars($turma['nome']); ?>&quot;? Os alunos não serão apagados, mas perderão o acesso por esta turma.">
                            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                            <input type="hidden" name="acao" value="excluir_turma">
                            <input type="hidden" name="id_turma" value="<?php echo (int) $turma['id_turma']; ?>">
                            <button class="mini-action danger turma-delete-btn" type="submit">Excluir turma</button>
                        </form>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </main>


    </div>

    <script src="../js/prof_dashboard.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const turmaSearch = document.getElementById('turma-search');
            const turmaGrid = document.getElementById('turma-grid');

            if (turmaSearch && turmaGrid) {
                turmaSearch.addEventListener('input', () => {
                    const termo = turmaSearch.value.toLowerCase();
                    turmaGrid.querySelectorAll('.turma-card').forEach(card => {
                        const nome = card.getAttribute('data-nome') || '';
                        card.style.display = nome.includes(termo) ? '' : 'none';
                    });
                });
            }

            // Confirmacao de exclusao de turma (cancelar nao envia o POST).
            document.querySelectorAll('.turma-delete-form').forEach(form => {
                form.addEventListener('submit', (e) => {
                    const msg = form.getAttribute('data-confirm-exclusao') || 'Tem certeza que deseja excluir esta turma?';
                    const div = document.createElement('div');
                    div.innerHTML = msg;
                    if (!window.confirm(div.textContent || msg)) {
                        e.preventDefault();
                    }
                });
            });

            // Copia o codigo da turma (Clipboard API + fallback execCommand).
            document.querySelectorAll('.code-copy').forEach(button => {
                button.addEventListener('click', async () => {
                    const targetId = button.getAttribute('data-copy-target');
                    const codeEl = targetId ? document.getElementById(targetId) : null;
                    const feedbackEl = targetId ? document.getElementById(targetId + '-feedback') : null;
                    const codigo = codeEl ? codeEl.textContent.trim() : '';

                    const showFeedback = (msg, ok) => {
                        if (!feedbackEl) return;
                        feedbackEl.textContent = msg;
                        feedbackEl.classList.toggle('ok', !!ok);
                        feedbackEl.classList.toggle('error', !ok);
                    };

                    if (codigo === '') {
                        showFeedback('Código indisponível para cópia.', false);
                        return;
                    }

                    const original = button.textContent;
                    const fallbackCopy = () => {
                        const temp = document.createElement('textarea');
                        temp.value = codigo;
                        temp.setAttribute('readonly', '');
                        temp.style.position = 'fixed';
                        temp.style.opacity = '0';
                        document.body.appendChild(temp);
                        temp.select();
                        let ok = false;
                        try {
                            ok = document.execCommand('copy');
                        } catch (err) {
                            ok = false;
                        }
                        document.body.removeChild(temp);
                        return ok;
                    };

                    let ok = false;
                    try {
                        if (navigator.clipboard && navigator.clipboard.writeText) {
                            await navigator.clipboard.writeText(codigo);
                            ok = true;
                        } else {
                            ok = fallbackCopy();
                        }
                    } catch (err) {
                        ok = fallbackCopy();
                    }

                    if (ok) {
                        button.textContent = 'Copiado!';
                        showFeedback('Código copiado. Compartilhe com seus alunos.', true);
                    } else {
                        showFeedback('Não foi possível copiar. Selecione o código manualmente.', false);
                    }
                    setTimeout(() => { button.textContent = original; }, 2000);
                });
            });
        });
    </script>
</body>

</html>
