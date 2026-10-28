<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';

Auth::exigeLogin();
$usuario = Auth::usuarioLogado() ?: ['nome' => 'Usuário', 'perfil' => 'aluno'];
$nomeUsuario = $usuario['nome'] ?? 'Usuário';
$primeiroNome = explode(' ', trim($nomeUsuario))[0] ?? 'Usuário';
$avatarUrl = Avatar::url();

// Dados de progresso do aluno autenticado
$dadosProgresso = [];

// Usa a sessão já estabelecida durante o login
// O identificador do aluno vem de $_SESSION['usuario']['id'], definido pelo AuthController
if (isset($_SESSION['usuario']['id']) && is_numeric($_SESSION['usuario']['id'])) {
    $idAluno = (int) $_SESSION['usuario']['id'];

    try {
        $pdo = Database::pdo();

        // A. Montagens concluídas
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM builds WHERE id_aluno = :id AND status = "COMPLETA"');
        $stmt->execute(['id' => $idAluno]);
        $montagens = $stmt->fetch();
        $dadosProgresso['montagens_concluidas'] = (int) ($montagens['total'] ?? 0);

        // B. Desafios resolvidos
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM aluno_desafios WHERE id_aluno = :id AND status = "CONCLUIDO"');
        $stmt->execute(['id' => $idAluno]);
        $desafios = $stmt->fetch();
        $dadosProgresso['desafios_resolvidos'] = (int) ($desafios['total'] ?? 0);

        // C. Conquistas (certificados)
        $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM certificados JOIN aluno_desafios ON certificados.id_aluno_desafio = aluno_desafios.id_aluno_desafio WHERE aluno_desafios.id_aluno = :id');
        $stmt->execute(['id' => $idAluno]);
        $conquistas = $stmt->fetch();
        $dadosProgresso['conquistas'] = (int) ($conquistas['total'] ?? 0);
    } catch (Throwable $e) {
        error_log('Erro ao buscar dados do dashboard: ' . $e->getMessage());
        // Mantém valores zerados como fallback
        $dadosProgresso = [
            'montagens_concluidas' => 0,
            'desafios_resolvidos' => 0,
            'conquistas' => 0,
        ];
    }
} else {
    // Usuário não logado ou sessão inválida
    $dadosProgresso = [
        'montagens_concluidas' => 0,
        'desafios_resolvidos' => 0,
        'conquistas' => 0,
    ];
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Dashboard Aluno</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/aluno_dashboard.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>

    <header class="main-header">
        <div class="header-left">
            <div class="logo-container">
                <img src="../../assets/logo/Logo_TechLabEDU.png" alt="TechLab Logo" class="logo-img">
                <div class="logo-text">
                    <span class="brand-name">TechLab EDU</span>
                    <span class="brand-subtitle">Laboratório Virtual de Hardware</span>
                </div>
            </div>
        </div>
        <div class="header-right">
            <div class="search-box">
                <img src="../../assets/icons-dashboard/lupa.png" alt="Search" class="search-icon">
                <input type="text" placeholder="Pesquisar...">
            </div>
            <div class="notification-icon">
                <img src="../../assets/icons-dashboard/bell.png" alt="Notifications">
                <span class="notification-badge">0</span>
            </div>
            <div class="user-profile" id="profileDropdown">
                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="User Avatar" class="avatar">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                    <span class="user-role">Aluno</span>
                </div>
                <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="Arrow" class="chevron">
            </div>
        </div>
    </header>

    <div class="main-container">
        <nav class="sidebar">
            <ul class="menu">
                <li class="menu-item active">
                    <img src="../../assets/Icons-NavBar/home.png" alt="Home">
                    <span>Dashboard</span>
                </li>
                <li class="menu-item">
                    <a href="./turmas.php" style="display: flex; align-items: center; gap: 10px; width: 100%; text-decoration: none; color: inherit;">
                        <img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="Turmas">
                        <span>Turmas</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../laboratorio/index.php" style="display: flex; align-items: center; gap: 10px; width: 100%; text-decoration: none; color: inherit;">
                        <img src="../../assets/Icons-NavBar/support.png" alt="Wrench">
                        <span>Montagem</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../desafios/index.php" style="display: flex; align-items: center; gap: 10px; width: 100%; text-decoration: none; color: inherit;">
                        <img src="../../assets/Icons-NavBar/target.png" alt="Target">
                        <span>Desafios</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../ranking/index.php" style="display: flex; align-items: center; gap: 10px; width: 100%; text-decoration: none; color: inherit;">
                        <img src="../../assets/Icons-NavBar/ranking.png" alt="Trophy">
                        <span>Ranking</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../perfil/perfil.php" style="display: flex; align-items: center; gap: 10px; width: 100%; text-decoration: none; color: inherit;">
                        <img src="../../assets/icons-dashboard/account.png" alt="User">
                        <span>Perfil</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="../configuracao/configuracao.php" style="display: flex; align-items: center; gap: 10px; width: 100%; text-decoration: none; color: inherit;">
                        <img src="../../assets/Icons-NavBar/settings.png" alt="Settings">
                        <span>Configurações</span>
                    </a>
                </li>
            </ul>
            <div class="menu-footer">
                <li class="menu-item logout">
                    <a href="../../logout.php" style="display: flex; align-items: center; gap: 10px; width: 100%; text-decoration: none; color: inherit;">
                        <img src="../../assets/Icons-NavBar/logout.png" alt="Logout">
                        <span>Sair</span>
                    </a>
                </li>
            </div>
        </nav>

        <main class="content">
            <section class="hero-banner">
                <div class="hero-text">
                    <h1>Olá, <?php echo htmlspecialchars($primeiroNome); ?>! 👋</h1>
                    <p>Continue aprendendo e monte, teste e diagnostique diferentes configurações de computadores.</p>
                </div>
                <div class="hero-image">
                    <img src="../../assets/icons-dashboard/sala-techLabDU.png" alt="Lab Illustration">
                </div>

                <div class="metrics-grid">
                    <div class="metric-card bg-green">
                        <div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/star.png" alt="Estrela"></div>
                        <div class="metric-data">
                            <span class="metric-label">XP Atual</span>
                            <span class="metric-value">0 XP</span>
                            <span class="metric-label">Próximo nível: 550 XP</span>
                        </div>
                    </div>
                    <div class="metric-card bg-blue">
                        <div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/level-up.png" alt="Nível"></div>
                        <div class="metric-data">
                            <span class="metric-label">Nivel Atual</span>
                            <span class="metric-value">Nível 1</span>
                            <span class="metric-label">Técnico Jr.</span>
                        </div>
                    </div>
                    <div class="metric-card bg-yellow">
                        <div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/mission-laranja.png" alt="Clipboard"></div>
                        <div class="metric-data">
                            <span class="metric-label">Missôes Concluidas</span>
                            <span class="metric-value">0</span>
                            <span class="metric-label">de 30 missões</span>
                        </div>
                    </div>
                    <div class="metric-card bg-purple">
                        <div class="metric-icon-wrap"><img src="../../assets/icons-dashboard/desempenho-roxo.png" alt="Desempenho"></div>
                        <div class="metric-data">
                            <span class="metric-label">Taxa de Acerto!</span>
                            <span class="metric-value">0%</span>
                            <span class="metric-label">Ótimo desempenho!</span>
                        </div>
                    </div>
                </div>
            </section>

            <div class="central-grid">
                <section class="challenges-section">
                    <div class="section-header">
                        <h2>Desafios Disponíveis</h2>
                        <a href="../desafios/index.php" class="view-all">Ver todos</a>
                    </div>
                    <div class="challenges-list">
                        <div class="challenge-card">
                            <div class="challenge-left">
                                <img src="../../assets/icons-perifericos/monitor-alerta.png" alt="Icon">
                                <div class="challenge-info">
                                    <h3>PC não dá vídeo</h3>
                                    <p>O computador liga, os coolers funcionam, mas não aparece imagem.</p>
                                </div>
                            </div>
                            <div class="challenge-right">
                                <span class="badge badge-hard">Difícil</span>
                                <div class="reward">
                                    <span>150 XP</span>
                                    <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="Seta">
                                </div>
                            </div>
                        </div>
                        <div class="challenge-card">
                            <div class="challenge-left">
                                <img src="../../assets/icons-perifericos/thermometer.png" alt="Icon">
                                <div class="challenge-info">
                                    <h3>Superaquecimento</h3>
                                    <p>O computador desliga sozinho após alguns minutos de uso.</p>
                                </div>
                            </div>
                            <div class="challenge-right">
                                <span class="badge badge-medium">Médio</span>
                                <div class="reward">
                                    <span>120 XP</span>
                                    <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="Seta">
                                </div>
                            </div>
                        </div>
                        <div class="challenge-card">
                            <div class="challenge-left">
                                <img src="../../assets/icons-perifericos/fonte-02.png" alt="Icon">
                                <div class="challenge-info">
                                    <h3>Computador não liga</h3>
                                    <p>Ao pressionar o botão de ligar, nada acontece.</p>
                                </div>
                            </div>
                            <div class="challenge-right">
                                <span class="badge badge-easy">Fácil</span>
                                <div class="reward">
                                    <span>100 XP</span>
                                    <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="Seta">
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="activities-section">
                    <div class="section-header">
                        <h2>Atividades Recentes</h2>
                        <a href="#" class="view-all">Ver todas</a>
                    </div>
                    <div class="activities-list">
                        <div class="activity-item">
                            <div class="activity-text">
                                <p>Nenhuma atividade registrada.</p>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
            
<section class="progress-section">
    <h2>Seu Progresso</h2>
    <div class="progress-grid">
        <div class="progress-card">
            <div class="progress-top">
                <div class="prog-icon-wrap blue">
                    <img src="../../assets/icons-dashboard/imac.png" alt="Ícone de montagens">
                </div>
                <div class="prog-info">
                    <span>Montagens Concluídas</span>
                    <strong><?php echo htmlspecialchars($dadosProgresso['montagens_concluidas'] ?? 0); ?> / 20</strong>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill blue" style="width: <?php echo htmlspecialchars((($dadosProgresso['montagens_concluidas'] ?? 0) / 20) * 100 . '%'); ?>" data-width="<?php echo htmlspecialchars(((int)((($dadosProgresso['montagens_concluidas'] ?? 0) / 20) * 100)) . '%'); ?>"></div>
                <span class="progress-percentage"><?php echo htmlspecialchars(((int)((($dadosProgresso['montagens_concluidas'] ?? 0) / 20) * 100)) . '%'); ?></span>
            </div>
        </div>

        <div class="progress-card">
            <div class="progress-top">
                <div class="prog-icon-wrap purple">
                    <img src="../../assets/icons-dashboard/target-roxo.png" alt="Ícone de desafios">
                </div>
                <div class="prog-info">
                    <span>Desafios Resolvidos</span>
                    <strong><?php echo htmlspecialchars($dadosProgresso['desafios_resolvidos'] ?? 0); ?> / 30</strong>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill purple" style="width: <?php echo htmlspecialchars((($dadosProgresso['desafios_resolvidos'] ?? 0) / 30) * 100 . '%'); ?>" data-width="<?php echo htmlspecialchars(((int)((($dadosProgresso['desafios_resolvidos'] ?? 0) / 30) * 100)) . '%'); ?>"></div>
                <span class="progress-percentage"><?php echo htmlspecialchars(((int)((($dadosProgresso['desafios_resolvidos'] ?? 0) / 30) * 100)) . '%'); ?></span>
            </div>
        </div>

        <div class="progress-card">
            <div class="progress-top">
                <div class="prog-icon-wrap yellow">
                    <img src="../../assets/icons-dashboard/trophy.png" alt="Ícone de conquistas">
                </div>
                <div class="prog-info">
                    <span>Conquistas</span>
                    <strong><?php echo htmlspecialchars($dadosProgresso['conquistas'] ?? 0); ?> / 15</strong>
                </div>
            </div>
            <div class="progress-bar-container">
                <div class="progress-bar-fill yellow" style="width: <?php echo htmlspecialchars((($dadosProgresso['conquistas'] ?? 0) / 15) * 100 . '%'); ?>" data-width="<?php echo htmlspecialchars(((int)((($dadosProgresso['conquistas'] ?? 0) / 15) * 100)) . '%'); ?>"></div>
                <span class="progress-percentage"><?php echo htmlspecialchars(((int)((($dadosProgresso['conquistas'] ?? 0) / 15) * 100)) . '%'); ?></span>
            </div>
        </div>
    </div>
</section>

        </main>
    </div>

    <script src="../js/aluno_dashboard.js"></script>
</body>

</html>