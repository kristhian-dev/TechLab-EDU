<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';
require_once __DIR__ . '/../../controllers/PerfilController.php';

// Alteração da foto de perfil: POST multipart processado no topo da view,
// mesmo padrão do login.php. Sempre termina em redirecionamento (PRG).
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    PerfilController::atualizarFoto();
}

Auth::exigeLogin();
$usuario = Auth::usuarioLogado() ?: ['nome' => 'Usuário', 'perfil' => 'aluno'];
$nomeUsuario = $usuario['nome'] ?? 'Usuário';
$perfil = $usuario['perfil'] ?? 'aluno';
$ehProfessor = in_array($perfil, ['professor', 'administrador'], true);
$rotuloPapel = $ehProfessor ? 'Professor' : 'Aluno';
$dashboardUrl = $ehProfessor ? '../professor/dashboard.php' : '../aluno/dashboard.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$csrfToken = Seguranca::gerarTokenCsrf();
$fotoUrl = Avatar::url();
$temFotoPersonalizada = $fotoUrl !== Avatar::PADRAO;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil - TechLab EDU</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/perfil.css?v=9">
</head>

<body>
    <header class="profile-header">
        <a class="profile-brand" href="<?php echo htmlspecialchars($dashboardUrl); ?>" aria-label="Ir para o dashboard TechLab EDU">
            <img src="../../assets/logo/Logo_TechLabEDU.png" alt="" class="profile-logo">
            <span>
                <strong>TechLab EDU</strong>
                <small>Laboratório Virtual de Hardware</small>
            </span>
        </a>

        <label class="profile-search">
            <img src="../../assets/icons-dashboard/lupa.png" alt="">
            <input type="search" placeholder="Buscar desafios, componentes, tutoriais..." aria-label="Buscar no TechLab EDU">
        </label>

        <div class="profile-user-area">
            <button class="profile-notifications" type="button" aria-label="3 notificações">
                <img src="../../assets/icons-dashboard/bell.png" alt="">
                <span>0</span>
            </button>
            <button class="profile-user-menu" type="button" aria-label="Abrir menu do usuário" aria-expanded="false">
                <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="" class="profile-header-avatar">
                <span>
                    <strong><?php echo htmlspecialchars($nomeUsuario); ?></strong>
                    <small><?php echo htmlspecialchars($rotuloPapel); ?></small>
                </span>
                <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="" class="profile-chevron">
            </button>
        </div>
    </header>

    <div class="profile-shell">
        <aside class="profile-sidebar" aria-label="Navegação principal">
            <nav>
                <?php if ($ehProfessor): ?>
                <a href="../professor/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="">Dashboard</a>
                <a href="../professor/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Turmas</a>
                <a href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt="">Desafios</a>
                <a href="../professor/ranking.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="">Ranking</a>
                <a href="../professor/relatorios.php"><img src="../../assets/Icons-NavBar/relatorio.png" alt="">Relatórios</a>
                <a href="./perfil.php" class="active" aria-current="page"><img src="../../assets/icons-dashboard/account.png" alt="">Perfil</a>
                <?php else: ?>
                <a href="../aluno/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="">Dashboard</a>
                <a href="../aluno/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Turmas</a>
                <a href="../laboratorio/index.php"><img src="../../assets/Icons-NavBar/support.png" alt="">Montagem</a>
                <a href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt="">Desafios</a>
                <a href="../ranking/index.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="">Ranking</a>
                <a href="./perfil.php" class="active" aria-current="page"><img src="../../assets/icons-dashboard/account.png" alt="">Perfil</a>
                <?php endif; ?>
                <a href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt="">Configurações</a>
            </nav>

            <div class="profile-sidebar-footer">
                <a href="/logout" class="profile-logout"><img src="../../assets/Icons-NavBar/logout.png" alt="">Sair</a>
            </div>
        </aside>

        <main class="profile-main">
            <?php if ($flash): ?>
                <div class="profile-flash profile-flash-<?php echo htmlspecialchars($flash['type']); ?>" role="status">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
            <?php endif; ?>

            <header class="profile-page-heading">
                <div>
                    <h1><img src="../../assets/icons-login/user-blue.png" alt=""><?php echo $ehProfessor ? 'Perfil do Professor' : 'Perfil do Aluno'; ?></h1>
                    <p>Aqui você pode visualizar e gerenciar suas informações pessoais, acompanhar seu progresso e manter seus dados sempre atualizados.</p>
                </div>
            </header>

            <section class="profile-overview" aria-labelledby="student-name">
                <div class="profile-identity">
                    <div class="profile-photo-wrap">
                        <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($nomeUsuario); ?>" class="profile-photo<?php echo $temFotoPersonalizada ? ' profile-photo-custom' : ''; ?>">
                        <button type="button" class="profile-photo-action" data-change-photo aria-label="Alterar foto de perfil">Alterar</button>
                    </div>
                    <div class="profile-student-data">
                        <h2 id="student-name"><?php echo htmlspecialchars($nomeUsuario); ?></h2>
                        <span class="profile-role"><?php echo htmlspecialchars($rotuloPapel); ?></span>
                        <ul class="profile-academic-list">
                            <li><img src="../../assets/icons-login/graduate-blue.png" alt="">Técnico em Informática</li>
                            <li><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Colégio Técnico de Teresina (UFPI/CTT)</li>
                            <li><img src="../../assets/icons-login/user.png" alt="">19 anos</li>
                        </ul>
                    </div>
                <section class="profile-level" aria-labelledby="level-title">
                    <div class="profile-level-copy">
                        <h2 id="level-title">Nível atual</h2>
                        <p>Próximo nível: 2</p>
                    </div>
                    <div class="profile-level-content">
                        <div class="profile-level-badge" aria-label="Nível 1">1</div>
                        <div class="profile-xp">
                            <strong><img src="../../assets/icons-dashboard/level-up.png" alt="">0 / 250 XP</strong>
                            <div class="profile-progress" role="progressbar" aria-label="Progresso para o nível 2" aria-valuemin="0" aria-valuemax="250" aria-valuenow="0">
                                <span style="width: 0%;"></span>
                            </div>
                        </div>
                    </div>
                </section>
            </section>

            <nav class="profile-tabs" aria-label="Seções do perfil" role="tablist">
                <button type="button" class="active" role="tab" aria-selected="true" data-tab="information"><img src="../../assets/icons-dashboard/account.png" alt="">Informações</button>
                <button type="button" role="tab" aria-selected="false" data-tab="progress"><img src="../../assets/icons-dashboard/desempenho-roxo.png" alt="">Progresso</button>
                <button type="button" role="tab" aria-selected="false" data-tab="achievements"><img src="../../assets/Icons-NavBar/conquistas.png" data-icon-default="../../assets/Icons-NavBar/conquistas.png" data-icon-active="../../assets/Icons-NavBar/conquistas.png" alt="">Conquistas</button>
                <button type="button" role="tab" aria-selected="false" data-tab="history"><img src="../../assets/Icons-NavBar/relatorio.png" data-icon-default="../../assets/Icons-NavBar/relatorio.png" data-icon-active="../../assets/Icons-NavBar/relatorio.png" alt="">Histórico</button>
            </nav>

            <section class="profile-details-grid" aria-label="Informações pessoais">
                <article class="profile-card profile-personal-card">
                    <header class="profile-card-header">
                        <h2><img src="../../assets/icons-login/user-blue.png" alt="">Dados pessoais</h2>
                        <button type="button" class="profile-edit-button">Editar</button>
                    </header>

                    <dl class="profile-data-list">
                        <div>
                            <dt><img src="../../assets/icons-login/user.png" alt="">Nome completo</dt>
                            <dd><?php echo htmlspecialchars($nomeUsuario); ?></dd>
                        </div>
                        <div>
                            <dt><img src="../../assets/icons-login/mail.png" alt="">E-mail</dt>
                            <dd><?php echo htmlspecialchars($usuario['email'] ?? 'Email@gmail.com'); ?></dd>
                        </div>
                        <div>
                            <dt><img src="../../assets/Icons-NavBar/relatorio.png" alt="">Data de nascimento</dt>
                            <dd>00/00/0000</dd>
                        </div>
                        <div>
                            <dt><img src="../../assets/icons-login/user.png" alt="">Telefone</dt>
                            <dd>(00)0 0000-0000</dd>
                        </div>
                    </dl>

                    <hr>

                    <dl class="profile-data-list profile-academic-data">
                        <div>
                            <dt><img src="../../assets/icons-login/graduate-blue.png" alt="">Curso</dt>
                            <dd>Técnico em Informática</dd>
                        </div>
                        <div>
                            <dt><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Instituição</dt>
                            <dd>Colégio Técnico de Teresina (UFPI/CTT)</dd>
                        </div>
                        <div>
                            <dt><img src="../../assets/icons-login/user.png" alt="">Período</dt>
                            <dd>1º período</dd>
                        </div>
                    </dl>
                </article>

                <article class="profile-card profile-progress-card" data-panel="progress" aria-labelledby="student-progress-title" hidden>
                    <header class="profile-card-header">
                        <h2 id="student-progress-title"><img src="../../assets/icons-dashboard/desempenho-roxo.png" alt="">Progresso do aluno</h2>
                    </header>

                    <div class="profile-progress-summary">
                        <article class="profile-progress-stat">
                            <div class="profile-progress-stat-heading">

                                <span>Desafios concluídos</span>
                            </div>
                            <strong>0 <small>/ 15</small></strong>
                            <div class="profile-progress-track" role="progressbar" aria-label="Desafios concluídos" aria-valuemin="0" aria-valuemax="15" aria-valuenow="0">
                                <span style="width: 0%;"></span>
                            </div>
                            <small>de 15 desafios cadastrados</small>
                        </article>
                        <article class="profile-progress-stat">
                            <div class="profile-progress-stat-heading">
                                <span>Experiência obtida</span>
                            </div>
                            <strong>0 <small>XP</small></strong>
                            <small>XP acumulado com os desafios</small>
                        </article>
                        <article class="profile-progress-stat">
                            <div class="profile-progress-stat-heading">
                                <span>Tempo total</span>
                            </div>
                            <strong>0 <small>h</small> 0 <small>min</small></strong>
                            <small>tempo dedicado aos desafios</small>
                        </article>
                    </div>
                </article>

                <div class="profile-side-cards">
                    <article class="profile-card profile-photo-card">
                        <header class="profile-card-header">
                            <h2><img src="../../assets/icons-login/user.png" alt="">Foto de perfil</h2>
                        </header>
                        <img src="<?php echo htmlspecialchars($fotoUrl); ?>" alt="Foto de perfil de <?php echo htmlspecialchars($nomeUsuario); ?>" class="profile-photo-preview<?php echo $temFotoPersonalizada ? ' profile-photo-custom' : ''; ?>">
                        <button type="button" class="profile-change-photo" data-change-photo>Alterar foto</button>
                    </article>

                    <article class="profile-card profile-about-card">
                        <header class="profile-card-header">
                            <h2><img src="../../assets/icons-login/user.png" alt="">Sobre mim</h2>
                        </header>
                        <p>Olá! Meu nome é Usuario, tenho 19 anos e estou no último período do Técnico em Informática. Tenho interesse em desenvolvimento web, principalmente front-end, e estou sempre buscando aprender mais sobre tecnologia e hardware.</p>
                    </article>
                </div>
            </section>
        </main>
    </div>

    <!-- Alteração da foto de perfil: input real de arquivo + modal de prévia.
         A validação de segurança acontece no servidor (PerfilController);
         o JS aqui cuida apenas da experiência (prévia e confirmação). -->
    <div id="profile-photo-modal" class="profile-photo-modal" hidden>
        <form id="profile-photo-form" class="profile-photo-modal-card" action="./perfil.php" method="post" enctype="multipart/form-data" role="dialog" aria-modal="true" aria-labelledby="profile-photo-modal-title">
            <h3 id="profile-photo-modal-title">Confirmar nova foto?</h3>
            <img id="profile-photo-modal-preview" src="" alt="Prévia da nova foto de perfil" class="profile-photo-modal-preview">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="file" id="profile-photo-input" name="foto_perfil" accept="image/jpeg,image/png,image/webp" class="profile-photo-input-hidden" tabindex="-1" aria-hidden="true">
            <p class="profile-photo-modal-hint">JPG, JPEG, PNG ou WebP de até 5 MB.</p>
            <p id="profile-photo-modal-error" class="profile-photo-modal-error" hidden></p>
            <div class="profile-photo-modal-actions">
                <button type="button" id="profile-photo-cancel" class="profile-change-photo">Cancelar</button>
                <button type="submit" id="profile-photo-confirm" class="profile-change-photo profile-photo-confirm">Confirmar foto</button>
            </div>
        </form>
    </div>

    <script src="../js/perfil.js?v=10"></script>
</body>

</html>