<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../nucleo/Avatar.php';
require_once __DIR__ . '/../../modelos/TurmaModel.php';

Auth::exigePerfil('professor', 'administrador');

$usuario = Auth::usuarioLogado() ?: ['nome' => 'Professor', 'id' => 0];
$nomeUsuario = $usuario['nome'] ?? 'Professor';
$idProfessor = (int) ($usuario['id'] ?? 0);
$avatarUrl = Avatar::url();

$idTurma = (int) ($_GET['id_turma'] ?? 0);
$idAluno = (int) ($_GET['id_aluno'] ?? 0);

$turma = null;
$detalhe = null;
$erroAutorizacao = '';

if ($idTurma <= 0 || $idAluno <= 0) {
    $erroAutorizacao = 'Parâmetros inválidos para consultar o aluno.';
} elseif (!TurmaModel::professorGerenciaTurma($idProfessor, $idTurma)) {
    $erroAutorizacao = 'Turma inválida para o seu usuário.';
} else {
    $turma = TurmaModel::buscarPorId($idTurma);
    $detalhe = TurmaModel::detalheAlunoNaTurma($idTurma, $idAluno);
    if ($detalhe === null) {
        $erroAutorizacao = 'Aluno não encontrado nesta turma.';
    }
}

$perfil = $detalhe['perfil'] ?? null;

function formatarDataHora($data) {
    if (!$data) { return '—'; }
    $ts = strtotime($data);
    if (!$ts) { return '—'; }
    return date('d/m/Y H:i', $ts);
}
function rotuloStatus($status) {
    if ($status === 'CONCLUIDO') { return 'Concluído'; }
    if ($status === 'EM_ANDAMENTO') { return 'Em andamento'; }
    if ($status === 'DISPONIVEL') { return 'Disponível'; }
    if ($status === 'BLOQUEADO') { return 'Bloqueado'; }
    return (string) $status;
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Informações do aluno</title>
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
            <div class="user-profile">
                <img src="<?php echo htmlspecialchars($avatarUrl); ?>" alt="User Avatar" class="avatar">
                <div class="user-info">
                    <span class="user-name"><?php echo htmlspecialchars($nomeUsuario); ?></span>
                    <span class="user-role">Professor</span>
                </div>
            </div>
        </div>
    </header>
    <div class="main-container">
        <nav class="sidebar">
            <ul class="menu">
                <li class="menu-item"><a class="menu-link" href="./dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="Dashboard"><span>Dashboard</span></a></li>
                <li class="menu-item active" aria-current="page"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="Turmas"><span>Turmas</span></li>
                <li class="menu-item"><a class="menu-link" href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt="Desafios"><span>Desafios</span></a></li>
                <li class="menu-item"><a class="menu-link" href="./ranking.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="Ranking"><span>Ranking</span></a></li>
            </ul>
            <div class="menu-footer">
                <li class="menu-item logout"><a class="menu-link" href="../../logout.php"><img src="../../assets/Icons-NavBar/logout.png" alt="Sair"><span>Sair</span></a></li>
            </div>
        </nav>
        <main class="content classes-content">
            <a class="back-link" href="./turmas.php?aba=alunos&amp;id=<?php echo (int) $idTurma; ?>">&larr; Voltar para a turma</a>
            <header class="classes-heading">
                <div>
                    <span class="eyebrow">Área do professor</span>
                    <h1>Informações do aluno</h1>
                    <p><?php echo $turma ? 'Turma ' . htmlspecialchars($turma['nome']) . ' • Código ' . htmlspecialchars($turma['codigo']) : 'Detalhe individual por turma.'; ?></p>
                </div>
            </header>
            <?php if ($erroAutorizacao !== ''): ?>
            <section class="classes-panel">
                <div class="classes-empty">
                    <h3>Não foi possível exibir o aluno</h3>
                    <p><?php echo htmlspecialchars($erroAutorizacao); ?></p>
                    <a class="secondary-action" href="./turmas.php?aba=turmas">Voltar às turmas</a>
                </div>
            </section>
            <?php else: ?>
            <section class="classes-panel student-block" aria-labelledby="perfil-title">
                <div class="panel-heading"><div><h2 id="perfil-title">Perfil e nível</h2><p>Dados básicos do vínculo do aluno com esta turma.</p></div></div>
                <div class="student-summary">
                    <div><span>Nome</span><strong><?php echo htmlspecialchars($perfil['nome']); ?></strong></div>
                    <div><span>Usuário</span><strong><?php echo htmlspecialchars($perfil['nome_usuario']); ?></strong></div>
                    <div><span>Matrícula</span><strong><?php echo htmlspecialchars($perfil['matricula']); ?></strong></div>
                    <div><span>Entrada na turma</span><strong><?php echo htmlspecialchars(formatarDataHora($perfil['data_entrada'])); ?></strong></div>
                </div>
                <p class="aluno-empty-hint">Nível atual e XP acumulado ainda não são registrados pelo sistema, por isso não são exibidos.</p>
            </section>
            <section class="classes-panel student-block" aria-labelledby="desempenho-title">
                <div class="panel-heading"><div><h2 id="desempenho-title">Desempenho e progresso</h2><p>Aproveitamento de pontos nos desafios desta turma.</p></div></div>
                <?php if ($detalhe['total_desafios'] === 0 || $detalhe['aproveitamento'] === null): ?>
                <p class="aluno-empty-hint">Dados insuficientes para calcular o desempenho.</p>
                <?php else: ?>
                <div class="student-summary">
                    <div><span>Aproveitamento</span><strong><?php echo htmlspecialchars(number_format((float) $detalhe['aproveitamento'], 1, ',', '.')); ?>%</strong></div>
                    <div><span>Pontuação acumulada</span><strong><?php echo (int) $detalhe['pontos_obtidos']; ?> de <?php echo (int) $detalhe['pontos_maximos']; ?> pontos</strong></div>
                    <div><span>Concluídos</span><strong><?php echo (int) $detalhe['concluidos']; ?> de <?php echo (int) $detalhe['total_desafios']; ?></strong></div>
                    <div><span>Tentativas</span><strong><?php echo (int) $detalhe['tentativas']; ?></strong></div>
                </div>
                <div class="progress-bar-container student-progress"><div class="progress-bar-fill blue" style="width: <?php echo max(0, min(100, (float) $detalhe['aproveitamento'])); ?>%"></div></div>
                <?php endif; ?>
            </section>
            <section class="classes-panel student-block" aria-labelledby="missoes-title">
                <div class="panel-heading"><div><h2 id="missoes-title">Missões concluídas</h2><p>Registro de missões do sistema.</p></div></div>
                <p class="aluno-empty-hint">O projeto ainda não registra missões ou suas conclusões, por isso não há registros disponíveis.</p>
            </section>
            <section class="classes-panel student-block" aria-labelledby="desafios-title">
                <div class="panel-heading"><div><h2 id="desafios-title">Desafios realizados</h2><p><?php echo (int) $detalhe['total_desafios']; ?> desafio(s) • <?php echo (int) $detalhe['concluidos']; ?> concluído(s).</p></div></div>
                <?php if (empty($detalhe['lista_concluidos']) && empty($detalhe['lista_em_andamento'])): ?>
                <p class="aluno-empty-hint">Este aluno ainda não possui participação em desafios nesta turma.</p>
                <?php else: ?>
                <?php if (!empty($detalhe['lista_concluidos'])): ?>
                <div class="aluno-results"><h3>Concluídos (<?php echo count($detalhe['lista_concluidos']); ?>)</h3>
                    <ul class="aluno-list">
                        <?php foreach ($detalhe['lista_concluidos'] as $d): ?>
                        <li class="aluno-item"><div class="aluno-info"><strong><?php echo htmlspecialchars($d['titulo']); ?></strong><small><?php echo (int) $d['pontuacao_obtida']; ?> de <?php echo (int) $d['pontuacao_maxima']; ?> pontos • <?php echo (int) $d['numero_tentativas']; ?> tentativa(s) • <?php echo htmlspecialchars(formatarDataHora($d['concluido_em'])); ?></small></div><span class="aluno-badge"><?php echo htmlspecialchars(rotuloStatus($d['status'])); ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php if (!empty($detalhe['lista_em_andamento'])): ?>
                <div class="aluno-results"><h3>Em andamento / disponíveis (<?php echo count($detalhe['lista_em_andamento']); ?>)</h3>
                    <ul class="aluno-list">
                        <?php foreach ($detalhe['lista_em_andamento'] as $d): ?>
                        <li class="aluno-item"><div class="aluno-info"><strong><?php echo htmlspecialchars($d['titulo']); ?></strong><small><?php echo (int) $d['pontuacao_obtida']; ?> pontos • <?php echo (int) $d['numero_tentativas']; ?> tentativa(s) • <?php echo htmlspecialchars(formatarDataHora($d['iniciado_em'])); ?></small></div><span class="aluno-badge"><?php echo htmlspecialchars(rotuloStatus($d['status'])); ?></span></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
                <?php endif; ?>
            </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>
