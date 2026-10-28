<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Auth.php';

Auth::exigeLogin();
$usuario = Auth::usuarioLogado() ?: ['nome' => 'Usuário', 'perfil' => 'aluno'];
$nomeUsuario = $usuario['nome'] ?? 'Usuário';
$perfil = $usuario['perfil'] ?? 'aluno';
$ehProfessor = in_array($perfil, ['professor', 'administrador'], true);
$rotuloPapel = $ehProfessor ? 'Professor' : 'Aluno';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Desafios - TechLab EDU</title>
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
                    <h2>Desafios</h2>
                    <p>Escolha um desafio e teste seus conhecimentos</p>
                </div>
            </div>
            <div class="header-right">
                <div class="notifications">
                    <img src="../../assets/icons-dashboard/bell.png" alt="Notificações">
                    <span class="badge">3</span>
                </div>
                <div class="user-card">
                    <img src="../../assets/icons-dashboard/account.png" alt="Avatar" class="avatar">
                    <div class="user-info">
                        <span class="username"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                        <span class="user-level"><?php echo htmlspecialchars($rotuloPapel); ?></span>
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
                        <?php if ($ehProfessor): ?>
                        <li><a href="../professor/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="Dashboard" class="nav-icon"> Dashboard</a></li>
                        <li><a href="../professor/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="Turmas" class="nav-icon"> Turmas</a></li>
                        <li><a href="#" class="active"><img src="../../assets/Icons-NavBar/target.png" alt="Desafios" class="nav-icon"> Desafios</a></li>
                        <li><a href="../professor/ranking.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="Ranking" class="nav-icon"> Ranking</a></li>
                        <li><a href="../professor/relatorios.php"><img src="../../assets/Icons-NavBar/relatorio.png" alt="Relatórios" class="nav-icon"> Relatórios</a></li>
                        <li><a href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt="Perfil" class="nav-icon"> Perfil</a></li>
                        <li><a href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt="Configurações" class="nav-icon"> Configurações</a></li>
                        <?php else: ?>
                        <li><a href="../aluno/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="Dashboard" class="nav-icon"> Dashboard</a></li>
                        <li><a href="../aluno/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="Turmas" class="nav-icon"> Turmas</a></li>
                        <li><a href="../laboratorio/index.php"><img src="../../assets/Icons-NavBar/support.png" alt="Montagem" class="nav-icon"> Montagem</a></li>
                        <li><a href="#" class="active"><img src="../../assets/Icons-NavBar/target.png" alt="Desafios" class="nav-icon"> Desafios</a></li>
                        <li><a href="../ranking/index.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="Ranking" class="nav-icon"> Ranking</a></li>
                        <li><a href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt="Perfil" class="nav-icon"> Perfil</a></li>
                        <li><a href="#"><img src="../../assets/Icons-NavBar/conquistas.png" alt="Conquistas" class="nav-icon"> Conquistas</a></li>
                        <li><a href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt="Configurações" class="nav-icon"> Configurações</a></li>
                        <?php endif; ?>
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
                <?php
                $flash = $_SESSION['flash'] ?? null;
                unset($_SESSION['flash']);
                if ($flash): ?>
                <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>">
                    <?php echo htmlspecialchars($flash['message']); ?>
                </div>
                <?php endif; ?>
                <div class="content-header">
                    <h1>Todos os Desafios</h1>
                    <div class="controls">
                        <input type="text" id="search-challenges" placeholder="Pesquisar desafio...">
                        <select class="sort-dropdown">
                            <option>Mais recentes</option>
                            <option>Mais difíceis</option>
                            <option>Mais fáceis</option>
                        </select>
                    </div>
                    <div class="filter-tabs">
                        <button class="tab active" data-filter="all">Todos</button>
                        <button class="tab" data-filter="facil"><span class="dot green"></span> Fácil</button>
                        <button class="tab" data-filter="medio"><span class="dot yellow"></span> Médio</button>
                        <button class="tab" data-filter="dificil"><span class="dot red"></span> Difícil</button>
                        <button class="tab" data-filter="pendentes"><span class="dot gray"></span> Pendentes</button>
                        <button class="tab" data-filter="concluidos"><span class="dot check"></span> Concluídos</button>
                        <?php if ($ehProfessor): ?>
                        <button class="tab tab-add" type="button" onclick="window.location.href='novo.php'">+ Novo Desafio</button>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="challenges-grid" id="challenges-grid">
                    <!-- Cards generated by JS or statically for this requirement -->
                    <div class="challenge-card" data-difficulty="medio">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/monitor-alerta.png" alt="PC Gamer sem Vídeo">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge medio">MÉDIO</span>
                                <span class="xp-badge">150 XP</span>
                            </div>
                            <h3>PC Gamer sem Vídeo</h3>
                            <p>O computador liga normalmente, mas não exibe imagem no monitor.</p>
                            <div class="card-stats">
                                <span>⏱️ 20 min</span>
                                <span>📈 82%</span>
                                <span>👤 1.245</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=1'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="dificil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/thermometer.png" alt="Superaquecimento">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge dificil">DIFÍCIL</span>
                                <span class="xp-badge">200 XP</span>
                            </div>
                            <h3>Superaquecimento</h3>
                            <p>O computador desliga após alguns minutos de uso em jogos.</p>
                            <div class="card-stats">
                                <span>⏱️ 25 min</span>
                                <span>📈 65%</span>
                                <span>👤 987</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=2'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="facil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/fonte-02.png" alt="Fonte Defeituosa">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge facil">FÁCIL</span>
                                <span class="xp-badge">100 XP</span>
                            </div>
                            <h3>Fonte Defeituosa</h3>
                            <p>O computador não liga e não há sinais de energia.</p>
                            <div class="card-stats">
                                <span>⏱️ 15 min</span>
                                <span>📈 91%</span>
                                <span>👤 1.532</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=3'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="medio">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/Memoria-RAM.png" alt="Memória RAM Incompatível">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge medio">MÉDIO</span>
                                <span class="xp-badge">150 XP</span>
                            </div>
                            <h3>Memória RAM Incompatível</h3>
                            <p>O computador liga, mas apresenta bips e não inicia o sistema.</p>
                            <div class="card-stats">
                                <span>⏱️ 20 min</span>
                                <span>📈 73%</span>
                                <span>👤 1.102</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=4'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="medio">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/HD.png" alt="HD não Detectado">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge medio">MÉDIO</span>
                                <span class="xp-badge">150 XP</span>
                            </div>
                            <h3>HD não Detectado</h3>
                            <p>O HD não aparece na BIOS ou no sistema operacional.</p>
                            <div class="card-stats">
                                <span>⏱️ 20 min</span>
                                <span>📈 68%</span>
                                <span>👤 892</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=5'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="dificil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/GPU.png" alt="Placa de Video com Falha">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge dificil">DIFÍCIL</span>
                                <span class="xp-badge">200 XP</span>
                            </div>
                            <h3>Placa de Video com Falha</h3>
                            <p>O computador apresenta artefatos ou tela azul ao iniciar jogos.</p>
                            <div class="card-stats">
                                <span>⏱️ 30 min</span>
                                <span>📈 54%</span>
                                <span>👤 755</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=6'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="medio">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/Tela-erro.png" alt="Tela Azul (BSOD)">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge medio">MÉDIO</span>
                                <span class="xp-badge">150 XP</span>
                            </div>
                            <h3>Tela Azul (BSOD)</h3>
                            <p>O sistema trava e exibe tela azul aleatoriamente.</p>
                            <div class="card-stats">
                                <span>⏱️ 25 min</span>
                                <span>📈 61%</span>
                                <span>👤 978</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=7'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="facil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/SSD.png" alt="SSD Lento">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge facil">FÁCIL</span>
                                <span class="xp-badge">100 XP</span>
                            </div>
                            <h3>SSD Lento</h3>
                            <p>O sistema está muito lento mesmo sem muitos programas abertos.</p>
                            <div class="card-stats">
                                <span>⏱️ 15 min</span>
                                <span>📈 86%</span>
                                <span>👤 1.278</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=8'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="facil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/HD.png" alt="Cabo SATA Solto">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge facil">FÁCIL</span>
                                <span class="xp-badge">100 XP</span>
                            </div>
                            <h3>Cabo SATA Solto</h3>
                            <p>O HD foi instalado, mas não é reconhecido na BIOS.</p>
                            <div class="card-stats">
                                <span>⏱️ 10 min</span>
                                <span>📈 95%</span>
                                <span>👤 2.100</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=9'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="medio">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/CPU.png" alt="CPU mal encaixada">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge medio">MÉDIO</span>
                                <span class="xp-badge">150 XP</span>
                            </div>
                            <h3>CPU mal encaixada</h3>
                            <p>O computador não liga e apresenta bips curtos.</p>
                            <div class="card-stats">
                                <span>⏱️ 20 min</span>
                                <span>📈 70%</span>
                                <span>👤 850</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=10'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="dificil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/placa-mae.png" alt="Curto na Placa Mãe">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge dificil">DIFÍCIL</span>
                                <span class="xp-badge">200 XP</span>
                            </div>
                            <h3>Curto na Placa Mãe</h3>
                            <p>O PC desliga instantaneamente ao apertar o botão de ligar.</p>
                            <div class="card-stats">
                                <span>⏱️ 30 min</span>
                                <span>📈 40%</span>
                                <span>👤 600</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=11'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="medio">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/Cooler.png" alt="Cooler Solto">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge medio">MÉDIO</span>
                                <span class="xp-badge">150 XP</span>
                            </div>
                            <h3>Cooler Solto</h3>
                            <p>O PC liga, mas desliga em 30 segundos por temperatura.</p>
                            <div class="card-stats">
                                <span>⏱️ 15 min</span>
                                <span>📈 88%</span>
                                <span>👤 1.100</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=12'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="facil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/Tela-erro.png" alt="Teclado não Funciona">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge facil">FÁCIL</span>
                                <span class="xp-badge">100 XP</span>
                            </div>
                            <h3>Teclado não Funciona</h3>
                            <p>O computador liga, mas o teclado não responde na BIOS.</p>
                            <div class="card-stats">
                                <span>⏱️ 10 min</span>
                                <span>📈 98%</span>
                                <span>👤 2.500</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=13'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="medio">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/monitor-sem-sinal.png" alt="BIOS Desconfigurada">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge medio">MÉDIO</span>
                                <span class="xp-badge">150 XP</span>
                            </div>
                            <h3>BIOS Desconfigurada</h3>
                            <p>O PC liga, mas não encontra a unidade de boot.</p>
                            <div class="card-stats">
                                <span>⏱️ 20 min</span>
                                <span>📈 75%</span>
                                <span>👤 920</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=14'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                    <div class="challenge-card" data-difficulty="dificil">
                        <div class="card-image">
                            <img src="../../assets/icons-perifericos/Fonte.png" alt="Capacitor Estufado">
                        </div>
                        <div class="card-body">
                            <div class="card-top">
                                <span class="difficulty-badge dificil">DIFÍCIL</span>
                                <span class="xp-badge">200 XP</span>
                            </div>
                            <h3>Capacitor Estufado</h3>
                            <p>O computador apresenta instabilidade e reinicializações.</p>
                            <div class="card-stats">
                                <span>⏱️ 30 min</span>
                                <span>📈 50%</span>
                                <span>👤 700</span>
                            </div>
                            <button class="btn-solve" onclick="window.location.href='desafio.php?id=15'">▶ Resolver Desafio</button>
                        </div>
                    </div>
                </div>

                <div class="pagination" id="pagination-container">
                    <!-- Paginação gerada dinamicamente pelo JS -->
                </div>
            </main>

            <!-- Right Widgets -->
            <aside class="right-panel">
            </aside>
        </div>
    </div>
    <script src="../js/desafios.js"></script>
</body>

</html>
