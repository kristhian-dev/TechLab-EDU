<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';

Auth::exigeLogin();
$usuario = Auth::usuarioLogado() ?: ['nome' => 'Usuário', 'perfil' => 'aluno'];
$nomeUsuario = $usuario['nome'] ?? 'Usuário';
$perfil = $usuario['perfil'] ?? 'aluno';
$ehProfessor = in_array($perfil, ['professor', 'administrador'], true);
$rotuloPapel = $ehProfessor ? 'Professor' : 'Aluno';
$dashboardUrl = $ehProfessor ? '../professor/dashboard.php' : '../aluno/dashboard.php';
$avatarUrl = Avatar::url();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - TechLab EDU</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/configuracao.css?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>

<body>
    <header class="settings-header">
        <a class="settings-brand" href="<?php echo htmlspecialchars($dashboardUrl); ?>" aria-label="Ir para o dashboard">
            <img src="../../assets/logo/Logo_TechLabEDU.png" alt="" class="settings-brand-logo">
            <span><strong>TechLab EDU</strong><small>Laboratório Virtual de Hardware</small></span>
        </a>
        <label class="settings-search">
            <img src="../../assets/icons-dashboard/lupa.png" alt="">
            <input type="search" placeholder="Pesquisar..." aria-label="Pesquisar">
        </label>
        <div class="settings-user-area">
            <button class="settings-notifications" type="button" aria-label="Notificações"><img src="../../assets/icons-dashboard/bell.png" alt=""><span>0</span></button>
            <button class="settings-user" type="button" aria-label="Menu do usuário"><img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="" class="settings-avatar"><span><strong><?php echo htmlspecialchars($nomeUsuario); ?></strong><small><?php echo htmlspecialchars($rotuloPapel); ?></small></span><img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="" class="settings-chevron"></button>
        </div>
    </header>

    <div class="settings-shell">
        <aside class="settings-sidebar" aria-label="Navegação principal">
            <nav>
                <?php if ($ehProfessor): ?>
                <a href="../professor/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="">Dashboard</a>
                <a href="../professor/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Turmas</a>
                <a href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt="">Desafios</a>
                <a href="../professor/ranking.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="">Ranking</a>
                <a href="../professor/relatorios.php"><img src="../../assets/Icons-NavBar/relatorio.png" alt="">Relatórios</a>
                <a href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt="">Perfil</a>
                <?php else: ?>
                <a href="../aluno/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="">Dashboard</a>
                <a href="../aluno/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Turmas</a>
                <a href="../laboratorio/index.php"><img src="../../assets/Icons-NavBar/support.png" alt="">Montagem</a>
                <a href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt="">Desafios</a>
                <a href="../ranking/index.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="">Ranking</a>
                <a href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt="">Perfil</a>
                <?php endif; ?>
                <a href="./configuracao.php" class="active" aria-current="page"><img src="../../assets/Icons-NavBar/settings.png" alt="">Configurações</a>
            </nav>
            <a href="../../logout.php" class="settings-logout"><img src="../../assets/Icons-NavBar/logout.png" alt="">Sair</a>
        </aside>

        <main class="settings-main">
            <header class="settings-heading">
                <div>
                    <h1><img src="../../assets/Icons-NavBar/settings.png" alt="">Configurações</h1>
                    <p>Personalize sua experiência no TechLab EDU e gerencie suas preferências.</p>
                </div>
            </header>

            <div class="settings-grid">
                <section class="settings-card settings-account" aria-labelledby="account-title">
                    <header class="settings-card-heading">
                        <div>
                            <h2 id="account-title"><img src="../../assets/icons-login/user-blue.png" alt="">Conta e dados pessoais</h2>
                            <p>Confira os dados usados no seu perfil.</p>
                        </div><a class="settings-button secondary" href="../perfil/perfil.php">Editar perfil</a>
                    </header>
                    <dl class="settings-data-list">
                        <div>
                            <dt>Nome completo</dt>
                            <dd>Usuario</dd>
                        </div>
                        <div>
                            <dt>E-mail</dt>
                            <dd>Email@gmail.com</dd>
                        </div>
                        <div>
                            <dt>Tipo de conta</dt>
                            <dd><span class="account-badge">Aluno</span></dd>
                        </div>
                    </dl>
                </section>

                <section class="settings-card" aria-labelledby="security-title">
                    <header class="settings-card-heading">
                        <div>
                            <h2 id="security-title"><img src="../../assets/icons-login/padlock.png" alt="">Segurança e senha</h2>
                            <p>Prepare uma nova senha para sua conta.</p>
                        </div>
                    </header>
                    <form id="password-form" class="password-form">
                        <label>Senha atual<div class="password-field"><input type="password" name="current-password" placeholder="Digite sua senha atual"><button type="button" class="password-toggle" aria-label="Mostrar senha"><img src="../../assets/icons-login/eye.png" alt=""></button></div></label>
                        <div class="password-columns"><label>Nova senha<div class="password-field"><input type="password" name="new-password" placeholder="Digite a nova senha"><button type="button" class="password-toggle" aria-label="Mostrar senha"><img src="../../assets/icons-login/eye.png" alt=""></button></div></label><label>Confirmar nova senha<div class="password-field"><input type="password" name="confirm-password" placeholder="Repita a nova senha"><button type="button" class="password-toggle" aria-label="Mostrar senha"><img src="../../assets/icons-login/eye.png" alt=""></button></div></label></div>
                        <div class="form-actions"><button class="settings-button primary" type="submit">Salvar alteração</button><span id="password-status" role="status"></span></div>
                    </form>
                </section>

                <section class="settings-card" aria-labelledby="notifications-title">
                    <header class="settings-card-heading">
                        <div>
                            <h2 id="notifications-title"><img src="../../assets/icons-dashboard/bell.png" alt="">Notificações</h2>
                            <p>Escolha os avisos que deseja receber.</p>
                        </div>
                    </header>
                    <div class="preference-list"><label class="preference-row"><span><strong>Novos desafios educacionais</strong><small>Receber avisos quando uma atividade estiver disponível.</small></span><input class="toggle-input" type="checkbox" checked><span class="toggle" aria-hidden="true"></span></label><label class="preference-row"><span><strong>Atividades e prazos</strong><small>Lembrar de tarefas e prazos importantes.</small></span><input class="toggle-input" type="checkbox" checked><span class="toggle" aria-hidden="true"></span></label><label class="preference-row"><span><strong>Progresso nas atividades</strong><small>Acompanhar atualizações sobre seu desempenho.</small></span><input class="toggle-input" type="checkbox" checked><span class="toggle" aria-hidden="true"></span></label><label class="preference-row"><span><strong>Conquistas e ranking</strong><small>Ser avisado sobre novas conquistas e evolução.</small></span><input class="toggle-input" type="checkbox" checked><span class="toggle" aria-hidden="true"></span></label></div>
                </section>

                <section class="settings-card" aria-labelledby="learning-title">
                    <header class="settings-card-heading">
                        <div>
                            <h2 id="learning-title"><img src="../../assets/icons-login/graduate-blue.png" alt="">Preferências de aprendizagem</h2>
                            <p>Adapte a forma como você pratica no laboratório.</p>
                        </div>
                    </header>
                    <div class="preference-list"><label class="preference-row"><span><strong>Exibir dicas nos desafios</strong><small>Mostrar sugestões durante a resolução das atividades.</small></span><input class="toggle-input" type="checkbox" checked><span class="toggle" aria-hidden="true"></span></label><label class="preference-row"><span><strong>Confirmar antes de limpar uma montagem</strong><small>Solicitar confirmação antes de remover os componentes.</small></span><input class="toggle-input" type="checkbox" checked><span class="toggle" aria-hidden="true"></span></label><label class="preference-row"><span><strong>Exibir orientações de ajuda</strong><small>Manter orientações visíveis durante as atividades práticas.</small></span><input class="toggle-input" type="checkbox" checked><span class="toggle" aria-hidden="true"></span></label></div>
                </section>

                <section class="settings-card about-card" aria-labelledby="about-title">
                    <header class="settings-card-heading">
                        <div>
                            <h2 id="about-title"><img src="../../assets/logo/Logo_TechLabEDU.png" alt="">Sobre o TechLab EDU</h2>
                        </div>
                    </header>
                    <p><strong>TechLab EDU</strong> é uma plataforma educacional para montagem e diagnóstico de computadores.</p>
                    <p>Seu objetivo é auxiliar estudantes no aprendizado prático de hardware, compatibilidade de componentes e diagnóstico de problemas.</p>
                </section>
            </div>
        </main>
    </div>
    <script src="../js/configuracao.js?v=1"></script>
</body>

</html>