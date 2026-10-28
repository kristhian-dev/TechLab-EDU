<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';
require_once __DIR__ . '/../../modelos/TurmaModel.php';
require_once __DIR__ . '/../../controllers/TurmaController.php';

Auth::exigePerfil('aluno', 'administrador');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = (string) ($_POST['acao'] ?? '');
    if ($acao === 'entrar_turma') {
        TurmaController::processarEntradaPorCodigo();
    }
}

$usuario = Auth::usuarioLogado() ?: ['nome' => 'Usuario', 'id' => 0];
$nomeUsuario = $usuario['nome'] ?? 'Usuario';
$idAluno = (int) ($usuario['id'] ?? 0);
$avatarUrl = Avatar::url();

try {
    $turmas = $idAluno > 0 ? TurmaModel::listarPorAluno($idAluno) : [];
} catch (Throwable $e) {
    error_log('Erro ao listar turmas do aluno: ' . $e->getMessage());
    $turmas = [];
    $_SESSION['flash'] = ['type' => 'danger', 'message' => 'Nao foi possivel carregar suas turmas. Tente novamente.'];
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$csrfToken = Seguranca::gerarTokenCsrf();

function formatarDataEntrada($data) {
    if (!$data) { return ''; }
    $ts = strtotime($data);
    if (!$ts) { return ''; }
    return date('d/m/Y', $ts);
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Minhas Turmas</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/aluno_dashboard.css">
    <link rel="stylesheet" href="../css/aluno_turmas.css?v=1">
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
                    <span class="brand-subtitle">Laboratorio Virtual de Hardware</span>
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
        <nav class="sidebar" aria-label="Navegacao principal">
            <ul class="menu">
                <li class="menu-item"><a href="./dashboard.php" class="menu-link"><img src="../../assets/Icons-NavBar/home.png" alt="Home"><span>Dashboard</span></a></li>
                <li class="menu-item active" aria-current="page"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="Turmas"><span>Turmas</span></li>
                <li class="menu-item"><a href="../laboratorio/index.php" class="menu-link"><img src="../../assets/Icons-NavBar/support.png" alt="Montagem"><span>Montagem</span></a></li>
                <li class="menu-item"><a href="../desafios/index.php" class="menu-link"><img src="../../assets/Icons-NavBar/target.png" alt="Desafios"><span>Desafios</span></a></li>
                <li class="menu-item"><a href="../ranking/index.php" class="menu-link"><img src="../../assets/Icons-NavBar/ranking.png" alt="Ranking"><span>Ranking</span></a></li>
                <li class="menu-item"><a href="../perfil/perfil.php" class="menu-link"><img src="../../assets/icons-dashboard/account.png" alt="Perfil"><span>Perfil</span></a></li>
                <li class="menu-item"><a href="../configuracao/configuracao.php" class="menu-link"><img src="../../assets/Icons-NavBar/settings.png" alt="Configuracoes"><span>Configuracoes</span></a></li>
            </ul>
            <div class="menu-footer">
                <li class="menu-item logout"><a href="../../logout.php" class="menu-link"><img src="../../assets/Icons-NavBar/logout.png" alt="Sair"><span>Sair</span></a></li>
            </div>
        </nav>
        <main class="content turmas-content">
            <?php if ($flash): ?>
            <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>" role="alert"><?php echo htmlspecialchars($flash['message']); ?></div>
            <?php endif; ?>
            <header class="turmas-heading">
                <div>
                    <h1>Minhas turmas</h1>
                    <p>Acesse suas turmas e acompanhe suas atividades de aprendizagem</p>
                </div>
            </header>
            <section class="join-panel" aria-labelledby="join-title">
                <div class="panel-heading">
                    <div>
                        <h2 id="join-title">Entrar em uma turma</h2>
                        <p>Digite o codigo fornecido pelo seu professor para participar da turma.</p>
                    </div>
                    <img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">
                </div>
                <form id="join-form" method="post" action="./turmas.php" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="acao" value="entrar_turma">
                    <div class="join-row">
                        <div class="form-field">
                            <label for="codigo">Codigo da turma</label>
                            <input type="text" id="codigo" name="codigo" placeholder="Ex.: TURMA-1234" autocomplete="off" maxlength="40" required>
                            <small class="field-help">O codigo e informado pelo professor responsavel.</small>
                        </div>
                        <button id="join-button" class="primary-action" type="submit"><span class="btn-label">Entrar na turma</span></button>
                    </div>
                    <p id="codigo-feedback" class="field-feedback" role="status" aria-live="polite"></p>
                </form>
            </section>
            <section class="classes-panel" aria-labelledby="classes-list-title">
                <div class="panel-heading">
                    <div>
                        <h2 id="classes-list-title">Minhas turmas</h2>
                        <p>Lista de turmas vinculadas</p>
                    </div>
                    <img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">
                </div>
                <?php if (empty($turmas)): ?>
                <div class="classes-empty">
                    <div class="empty-icon"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt=""></div>
                    <h3>Nenhuma turma por aqui ainda</h3>
                    <p>Peca o codigo ao seu professor e digite no campo acima para entrar.</p>
                </div>
                <?php else: ?>
                <ul class="turma-grid">
                    <?php foreach ($turmas as $turma): ?>
                    <li class="turma-card">
                        <div class="turma-card-top">
                            <span class="turma-code"><?php echo htmlspecialchars($turma['codigo'] ?? ''); ?></span>
                            <?php $dataEntrada = formatarDataEntrada($turma['data_entrada'] ?? null); ?>
                            <?php if ($dataEntrada !== ''): ?><span class="turma-students">Desde <?php echo htmlspecialchars($dataEntrada); ?></span><?php endif; ?>
                        </div>
                        <h3 class="turma-name"><?php echo htmlspecialchars($turma['nome'] ?? 'Turma'); ?></h3>
                        <?php if (!empty($turma['professor_nome'])): ?><p class="turma-teacher">Professor(a): <?php echo htmlspecialchars($turma['professor_nome']); ?></p><?php endif; ?>
                        <?php if (!empty($turma['descricao'])): ?><p class="turma-description"><?php echo htmlspecialchars($turma['descricao']); ?></p><?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>
            </section>
        </main>
        </div>
    </div>
    <script src="../js/aluno_turmas.js?v=1" defer></script>
</body>
</html>
