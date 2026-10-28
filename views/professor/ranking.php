<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';
require_once __DIR__ . '/../../modelos/TurmaModel.php';

Auth::exigePerfil('professor', 'administrador');

$usuario = Auth::usuarioLogado() ?: ['nome' => 'Professor', 'id' => 0];
$nomeUsuario = $usuario['nome'] ?? 'Professor';
$idProfessor = (int) ($usuario['id'] ?? 0);
$avatarUrl = Avatar::url();

$ranking = TurmaModel::rankingTurmas($idProfessor);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Ranking das Turmas</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/prof_dashboard.css?v=2">
    <link rel="stylesheet" href="../css/prof_ranking.css?v=1">
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
            <label class="search-box" aria-label="Pesquisar no ranking">
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
                <li class="menu-item"><a class="menu-link" href="./turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt=""><span>Turmas</span></a></li>
                <li class="menu-item"><a class="menu-link" href="./turmas.php?aba=alunos"><img src="../../assets/icons-dashboard/account.png" alt=""><span>Alunos</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt=""><span>Desafios</span></a></li>
                <li class="menu-item active"><a class="menu-link" href="./ranking.php" aria-current="page"><img src="../../assets/Icons-NavBar/ranking.png" alt=""><span>Ranking</span></a></li>
                <li class="menu-item"><a class="menu-link" href="./relatorios.php"><img src="../../assets/Icons-NavBar/relatorio.png" alt=""><span>Relatórios</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt=""><span>Perfil</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt=""><span>Configuração</span></a></li>
            </ul>
            <div class="menu-footer">
                <div class="menu-item logout"><a class="menu-link" href="../../logout.php"><img src="../../assets/Icons-NavBar/logout.png" alt=""><span>Sair</span></a></div>
            </div>
        </nav>


        <main class="content ranking-content">
            <header class="ranking-heading">
                <div>
                    <span class="eyebrow">Área do professor</span>
                    <h1>Ranking das turmas</h1>
                    <p>Compare o desempenho das suas turmas e descubra qual está na frente.</p>
                </div>
                <button class="print-button" type="button" onclick="window.print()">Imprimir ranking (PDF)</button>
            </header>

            <?php if (empty($ranking)): ?>
            <section class="ranking-panel">
                <h2>Classificação</h2>
                <div class="ranking-empty">
                    Nenhuma turma cadastrada ainda. Crie turmas para ver o ranking.
                </div>
            </section>
            <?php else: ?>
            <?php $medalhas = ['gold', 'silver', 'bronze']; ?>
            <div class="ranking-podium">
                <?php for ($i = 0; $i < min(3, count($ranking)); $i++): $turmaTop = $ranking[$i]; ?>
                <div class="podium-card <?php echo $medalhas[$i]; ?>">
                    <span class="medal"><?php echo $i + 1; ?></span>
                    <h3><?php echo htmlspecialchars($turmaTop['nome']); ?></h3>
                    <span class="podium-code"><?php echo htmlspecialchars($turmaTop['codigo']); ?></span>
                    <span class="podium-points"><?php echo (int) $turmaTop['total_pontos']; ?> <small>pontos</small></span>
                    <span class="podium-extra"><?php echo (int) $turmaTop['desafios_concluidos']; ?> desafio<?php echo (int) $turmaTop['desafios_concluidos'] === 1 ? '' : 's'; ?> concluído<?php echo (int) $turmaTop['desafios_concluidos'] === 1 ? '' : 's'; ?> • <?php echo (int) $turmaTop['total_alunos']; ?> aluno<?php echo (int) $turmaTop['total_alunos'] === 1 ? '' : 's'; ?></span>
                </div>
                <?php endfor; ?>
            </div>

            <section class="ranking-panel">
                <h2>Classificação completa</h2>
                <table class="ranking-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Turma</th>
                            <th>Código</th>
                            <th>Alunos</th>
                            <th>Desafios concluídos</th>
                            <th>Pontos totais</th>
                            <th>Média por aluno</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ranking as $indice => $turma): ?>
                        <?php $media = (int) $turma['total_alunos'] > 0 ? round((int) $turma['total_pontos'] / (int) $turma['total_alunos'], 1) : 0; ?>
                        <tr class="<?php echo $indice < 3 ? 'top-' . ($indice + 1) : ''; ?>">
                            <td><span class="position"><?php echo $indice + 1; ?></span></td>
                            <td class="turma-name"><?php echo htmlspecialchars($turma['nome']); ?></td>
                            <td><span class="turma-code"><?php echo htmlspecialchars($turma['codigo']); ?></span></td>
                            <td><?php echo (int) $turma['total_alunos']; ?></td>
                            <td><?php echo (int) $turma['desafios_concluidos']; ?></td>
                            <td><strong><?php echo (int) $turma['total_pontos']; ?></strong></td>
                            <td><?php echo htmlspecialchars((string) $media); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <p class="ranking-note">O ranking considera a soma dos pontos obtidos pelos alunos em todos os desafios, com desempate pela quantidade de desafios concluídos e pelo número de alunos.</p>
            </section>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>
