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

$turmas = TurmaModel::listarPorProfessor($idProfessor);

$totalAlunos = 0;
foreach ($turmas as $turma) {
    $totalAlunos += (int) $turma['total_alunos'];
}

$relatorioTurmas = [];
foreach ($turmas as $turma) {
    $relatorioTurmas[] = [
        'turma' => $turma,
        'alunos' => TurmaModel::listarAlunos((int) $turma['id_turma']),
    ];
}

$dataGeracao = date('d/m/Y \à\s H:i');
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Relatórios</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/prof_dashboard.css?v=2">
    <link rel="stylesheet" href="../css/prof_relatorios.css?v=1">
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
            <label class="search-box" aria-label="Pesquisar nos relatórios">
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
                <li class="menu-item"><a class="menu-link" href="./ranking.php"><img src="../../assets/Icons-NavBar/ranking.png" alt=""><span>Ranking</span></a></li>
                <li class="menu-item active"><a class="menu-link" href="./relatorios.php" aria-current="page"><img src="../../assets/Icons-NavBar/relatorio.png" alt=""><span>Relatórios</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt=""><span>Perfil</span></a></li>
                <li class="menu-item"><a class="menu-link" href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt=""><span>Configuração</span></a></li>
            </ul>
            <div class="menu-footer">
                <div class="menu-item logout"><a class="menu-link" href="../../logout.php"><img src="../../assets/Icons-NavBar/logout.png" alt=""><span>Sair</span></a></div>
            </div>
        </nav>

        <main class="content report-content">
            <header class="report-heading">
                <div>
                    <span class="eyebrow">Área do professor</span>
                    <h1>Relatórios</h1>
                    <p>Acompanhe a situação das suas turmas e exporte o relatório em PDF.</p>
                </div>
                <button class="print-button" type="button" onclick="window.print()">
                    <img src="../../assets/Icons-NavBar/relatorio.png" alt="">Imprimir relatório (PDF)
                </button>
            </header>

            <div class="report-summary">
                <div class="report-summary-card">
                    <span>Turmas</span>
                    <strong><?php echo count($turmas); ?></strong>
                </div>
                <div class="report-summary-card">
                    <span>Total de alunos</span>
                    <strong><?php echo $totalAlunos; ?></strong>
                </div>
                <div class="report-summary-card">
                    <span>Média de alunos por turma</span>
                    <strong><?php echo count($turmas) > 0 ? round($totalAlunos / count($turmas), 1) : 0; ?></strong>
                </div>
            </div>

            <section class="report-panel" aria-labelledby="report-title">
                <h2 id="report-title">Relatório das turmas</h2>

                <?php if (empty($relatorioTurmas)): ?>
                <div class="report-empty">
                    Nenhuma turma cadastrada ainda. Crie turmas para gerar o relatório.
                </div>
                <?php else: ?>
                <?php foreach ($relatorioTurmas as $item): ?>
                <article class="report-turma">
                    <div class="report-turma-heading">
                        <h3><?php echo htmlspecialchars($item['turma']['nome']); ?></h3>
                        <span class="report-turma-code"><?php echo htmlspecialchars($item['turma']['codigo']); ?></span>
                    </div>
                    <?php if (!empty($item['turma']['descricao'])): ?>
                    <p class="report-turma-description"><?php echo htmlspecialchars($item['turma']['descricao']); ?></p>
                    <?php endif; ?>
                    <p class="report-turma-stats">
                        <?php echo (int) $item['turma']['total_alunos']; ?> aluno<?php echo (int) $item['turma']['total_alunos'] === 1 ? '' : 's'; ?>
                        • Criada em <?php echo date('d/m/Y', strtotime($item['turma']['data_criacao'])); ?>
                    </p>

                    <?php if (empty($item['alunos'])): ?>
                    <p class="report-empty-students">Nenhum aluno vinculado a esta turma.</p>
                    <?php else: ?>
                    <table class="report-table">
                        <thead>
                            <tr>
                                <th>Aluno</th>
                                <th>E-mail</th>
                                <th>Matrícula</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($item['alunos'] as $aluno): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($aluno['nome']); ?></td>
                                <td><?php echo htmlspecialchars($aluno['email']); ?></td>
                                <td><?php echo htmlspecialchars($aluno['matricula']); ?></td>
                                <td><?php echo htmlspecialchars($aluno['status']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
                <?php endif; ?>

                <p class="report-generated">Relatório gerado por <?php echo htmlspecialchars($nomeUsuario); ?> em <?php echo htmlspecialchars($dataGeracao); ?>.</p>
            </section>
        </main>
    </div>
</body>

</html>

