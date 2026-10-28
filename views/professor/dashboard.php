<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';

Auth::exigeLogin();
$usuario = Auth::usuarioLogado() ?: ['nome' => 'Professor', 'perfil' => 'professor'];
$nomeUsuario = $usuario['nome'] ?? 'Professor';
$primeiroNome = explode(' ', trim($nomeUsuario))[0] ?? 'Professor';
$avatarUrl = Avatar::url();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Dashboard Professor</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/prof_dashboard.css?v=2">
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
            <label class="search-box" aria-label="Pesquisar no dashboard">
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
                <li class="menu-item active">
                    <a class="menu-link" href="./dashboard.php" aria-current="page">
                        <img src="../../assets/Icons-NavBar/home.png" alt=""><span>Dashboard</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="./turmas.php">
                        <img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt=""><span>Turmas</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="../desafios/index.php">
                        <img src="../../assets/Icons-NavBar/target.png" alt=""><span>Desafios</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="./ranking.php">
                        <img src="../../assets/Icons-NavBar/ranking.png" alt=""><span>Ranking</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="./relatorios.php">
                        <img src="../../assets/Icons-NavBar/relatorio.png" alt=""><span>Relatórios</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="../perfil/perfil.php">
                        <img src="../../assets/icons-dashboard/account.png" alt=""><span>Perfil</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a class="menu-link" href="../configuracao/configuracao.php">
                        <img src="../../assets/Icons-NavBar/settings.png" alt=""><span>Configuração</span>
                    </a>
                </li>
            </ul>
            <div class="menu-footer">
                <div class="menu-item logout">
                    <a class="menu-link" href="../../logout.php">
                        <img src="../../assets/Icons-NavBar/logout.png" alt=""><span>Sair</span>
                    </a>
                </div>
            </div>
        </nav>

        <main class="content">
            <section class="hero-banner">
                <div class="hero-text">
                    <h1>Olá, <?php echo htmlspecialchars($primeiroNome); ?>!</h1>
                    <p>O dashboard está pronto para receber os dados das suas turmas quando o backend estiver disponível.</p>
                </div>
                <div class="hero-image">
                    <img src="../../assets/icons-dashboard/sala-techLabDU.png" alt="Ilustração do laboratório TechLab EDU">
                </div>
                <div class="metrics-grid">
                    <div class="metric-card bg-blue"><div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/multiple-users-silhouette-blue.png" alt="Alunos"></div><div class="metric-data"><span class="metric-label label-blue">Total de alunos</span><span class="metric-value">0</span><span class="metric-label label-blue">Sem dados</span></div></div>
                    <div class="metric-card bg-green"><div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/multiple-users-silhouette-verde.png" alt="Turmas"></div><div class="metric-data"><span class="metric-label">Turmas ativas</span><span class="metric-value">0</span><span class="metric-label">Sem dados</span></div></div>
                    <div class="metric-card bg-yellow"><div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/target-laranja.png" alt="Desafios"></div><div class="metric-data"><span class="metric-label label-orange">Desafios ativos</span><span class="metric-value">0</span><span class="metric-label label-orange">Sem dados</span></div></div>
                    <div class="metric-card bg-purple"><div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/target-roxo.png" alt="Desempenho"></div><div class="metric-data"><span class="metric-label label-purple">Taxa média de acerto</span><span class="metric-value">0%</span><span class="metric-label label-purple">Sem dados</span></div></div>
                </div>
            </section>

            <div class="central-grid">
                <section class="data-section"><div class="section-header"><h2>Desempenho das turmas</h2><span class="view-all disabled">Nenhum dado</span></div><div class="empty-state">Nenhuma turma cadastrada.</div></section>
                <section class="data-section"><div class="section-header"><h2>Desafios recentes</h2><span class="view-all disabled">Nenhum dado</span></div><div class="empty-state">Nenhum desafio cadastrado.</div></section>
                <section class="data-section"><div class="section-header"><h2>Alunos que precisam de atenção</h2><span class="view-all disabled">Nenhum dado</span></div><div class="empty-state">Nenhum aluno cadastrado.</div></section>
            </div>

            <section class="quick-activities">
                <h2>Atividades rápidas</h2>
                <div class="activities-grid">
                    <a href="../desafios/novo.php" class="activity-card">
                        <span class="activity-icon-wrap bg-blue"><img src="../../assets/icons-dashboard/plus.png" alt=""></span>
                        <span class="activity-content"><strong>Novo desafio</strong><span>Crie um desafio para suas turmas</span></span>
                    </a>
                    <a href="./turmas.php" class="activity-card">
                        <span class="activity-icon-wrap bg-green"><img src="../../assets/icons-dashboard/multiple-users-silhouette-verde.png" alt=""></span>
                        <span class="activity-content"><strong>Gerenciar turmas</strong><span>Cadastre turmas e adicione alunos</span></span>
                    </a>
                    <a href="./relatorios.php" class="activity-card">
                        <span class="activity-icon-wrap bg-purple"><img src="../../assets/icons-dashboard/relatorio-roxo.png" alt=""></span>
                        <span class="activity-content"><strong>Gerar relatório</strong><span>Relatório das turmas em PDF</span></span>
                    </a>
                    <a href="./ranking.php" class="activity-card">
                        <span class="activity-icon-wrap bg-yellow"><img src="../../assets/icons-dashboard/trophy.png" alt=""></span>
                        <span class="activity-content"><strong>Ranking das turmas</strong><span>Compare o desempenho das turmas</span></span>
                    </a>
                </div>
            </section>
        </main>
    </div>

    <script src="../js/prof_dashboard.js" defer></script>
</body>

</html>
