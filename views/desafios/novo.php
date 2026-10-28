<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../modelos/DesafioModel.php';
require_once __DIR__ . '/../../controllers/DesafioController.php';

Auth::exigePerfil('professor', 'administrador');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    DesafioController::processarCriacao();
}

$usuario = Auth::usuarioLogado() ?: ['nome' => 'Professor', 'perfil' => 'professor'];
$nomeUsuario = $usuario['nome'] ?? 'Professor';
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$csrfToken = Seguranca::gerarTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novo Desafio - TechLab EDU</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/desafios.css">
</head>

<body>
    <div class="app-container">
        <!-- Header -->
        <header class="main-header">
            <div class="header-left">
                <div class="logo-area">
                    <img src="../../assets/logo/Logo_TechLabEDU.png" alt="TechLab EDU Logo" class="logo">
                    <div class="logo-text">
                        <h1>TechLab EDU</h1>
                        <span>Laboratório Virtual de Hardware</span>
                    </div>
                </div>
                <div class="page-info">
                    <h2>Novo Desafio</h2>
                    <p>Cadastre um desafio para suas turmas</p>
                </div>
            </div>
            <div class="header-right">
                <div class="notifications">
                    <img src="../../assets/icons-dashboard/bell.png" alt="Notificações">
                    <span class="badge">0</span>
                </div>
                <div class="user-card">
                    <img src="../../assets/icons-login/user.png" alt="Avatar" class="avatar">
                    <div class="user-info">
                        <span class="username"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-level">Professor</span>
                    </div>
                    <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="Arrow" class="dropdown-arrow">
                </div>
            </div>
        </header>

        <div class="main-layout">
            <!-- Sidebar -->
            <aside class="sidebar">
                <nav class="sidebar-nav">
                    <ul class="nav-list">
                        <li><a href="../professor/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="Dashboard" class="nav-icon"> Dashboard</a></li>
                        <li><a href="../professor/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="Turmas" class="nav-icon"> Turmas</a></li>
                        <li><a href="index.php" class="active"><img src="../../assets/Icons-NavBar/target.png" alt="Desafios" class="nav-icon"> Desafios</a></li>
                        <li><a href="../professor/ranking.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="Ranking" class="nav-icon"> Ranking</a></li>
                        <li><a href="../professor/relatorios.php"><img src="../../assets/Icons-NavBar/relatorio.png" alt="Relatórios" class="nav-icon"> Relatórios</a></li>
                        <li><a href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt="Configurações" class="nav-icon"> Configurações</a></li>
                    </ul>
                </nav>
                <div class="sidebar-bottom">
                    <nav class="sidebar-nav">
                        <ul>
                            <li><a href="../../logout.php" class="exit"><img src="../../assets/Icons-NavBar/logout.png" alt="Sair" class="nav-icon"> Sair</a></li>
                        </ul>
                    </nav>
                </div>
            </aside>


            <!-- Main Content -->
            <main class="content">
                <?php if ($flash): ?>
                <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
                <?php endif; ?>

                <div class="challenge-form-card">
                    <h1>Cadastrar novo desafio</h1>
                    <p class="form-subtitle">Preencha os dados abaixo para criar um desafio. Ele ficará disponível para vincular às suas turmas.</p>

                    <form method="post" action="novo.php" class="challenge-form">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">

                        <div class="form-group">
                            <label for="titulo">Título *</label>
                            <input type="text" id="titulo" name="titulo" maxlength="150" required placeholder="Ex.: Computador não liga">
                        </div>

                        <div class="form-group">
                            <label for="descricao">Descrição *</label>
                            <textarea id="descricao" name="descricao" rows="4" required placeholder="Descreva o problema apresentado no desafio"></textarea>
                        </div>

                        <div class="form-group">
                            <label for="objetivo">Objetivo de aprendizagem</label>
                            <textarea id="objetivo" name="objetivo" rows="3" placeholder="O que o aluno deve aprender ao resolver este desafio?"></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="categoria">Categoria</label>
                                <input type="text" id="categoria" name="categoria" maxlength="80" placeholder="Ex.: Fonte de alimentação">
                            </div>
                            <div class="form-group">
                                <label for="dificuldade">Dificuldade</label>
                                <select id="dificuldade" name="dificuldade">
                                    <option value="FACIL">Fácil</option>
                                    <option value="MEDIO">Médio</option>
                                    <option value="DIFICIL">Difícil</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="pontuacao_maxima">Pontuação máxima (XP)</label>
                                <input type="number" id="pontuacao_maxima" name="pontuacao_maxima" min="0" value="100">
                            </div>
                            <div class="form-group">
                                <label for="nivel_minimo">Nível mínimo</label>
                                <input type="number" id="nivel_minimo" name="nivel_minimo" min="1" value="1">
                            </div>
                        </div>

                        <div class="form-actions">
                            <button type="button" class="btn-cancel" onclick="window.location.href='index.php'">Cancelar</button>
                            <button type="submit" class="btn-submit">Criar desafio</button>
                        </div>
                    </form>
                </div>
            </main>

            <!-- Right Widgets -->
            <aside class="right-panel">
            </aside>
        </div>
    </div>
</body>

</html>
