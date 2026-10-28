<?php
// Ordem de bootstrap: configuracoes/app.php define APP_ROOT e inicia a sessao.
// Deve preceder nucleo/Auth.php, que usa APP_ROOT para carregar nucleo/Database.php.
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';

Auth::exigeLogin();

$challengeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$challengeId = $challengeId !== false && $challengeId !== null ? $challengeId : null;




$challenge = null;
if ($challengeId !== null) {
    $pdo = Database::pdo();
    $stmt = $pdo->prepare(
        'SELECT id_desafio, titulo, descricao, objetivo, dificuldade
         FROM desafios
         WHERE id_desafio = :id AND ativo = TRUE
         LIMIT 1'
    );
    $stmt->execute(['id' => $challengeId]);
    $row = $stmt->fetch();

    if ($row) {
        $dificuldadeMap = [
            'FACIL'   => ['difficulty' => 'FÁCIL',   'difficulty_class' => 'easy'],
            'MEDIO'   => ['difficulty' => 'MÉDIO',   'difficulty_class' => 'medium'],
            'DIFICIL' => ['difficulty' => 'DIFÍCIL', 'difficulty_class' => 'hard'],
        ];
        $diff = $dificuldadeMap[$row['dificuldade']] ?? [
            'difficulty' => $row['dificuldade'],
            'difficulty_class' => 'easy',
        ];

        $challenge = [
            'id'             => $row['id_desafio'],
            'title'          => $row['titulo'],
            'difficulty'     => $diff['difficulty'],
            'difficulty_class' => $diff['difficulty_class'],
            'description'    => $row['descricao'],
            'problem'        => $row['descricao'],
            'objective'      => $row['objetivo'] ?? '',
            'components'     => [],
        ];

        // Carregar condições de resolução do desafio (Etapa 1 — Melhoria 3)
        $dbTipoParaJs = [
            'CPU'         => 'cpu',
            'PLACA_MAE'   => 'motherboard',
            'MEMORIA_RAM' => 'ram',
            'GPU'         => 'gpu',
            'SSD'         => 'storage',
            'HD'          => 'storage',
            'FONTE'       => 'psu',
            'COOLER'      => 'cooler-cpu',
            'GABINETE'    => null,
        ];

        $condicoes = [];
        $stmtCond = $pdo->prepare(
            'SELECT tipo_condicao, tipo_componente, obrigatorio, descricao
             FROM desafio_condicoes_resolucao
             WHERE id_desafio = :id
             ORDER BY ordem ASC'
        );
        $stmtCond->execute(['id' => $row['id_desafio']]);
        while ($cond = $stmtCond->fetch()) {
            $tipoJs = $dbTipoParaJs[$cond['tipo_componente']] ?? null;
            if ($tipoJs === null) {
                continue;
            }
            $condicoes[] = [
                'tipo'          => $cond['tipo_condicao'],
                'componenteJs'  => $tipoJs,
                'obrigatorio'   => (bool) $cond['obrigatorio'],
                'descricao'     => $cond['descricao'] ?? '',
            ];
        }
        $challenge['condicoes_resolucao'] = $condicoes;
    }
}
function escape($value) { return htmlspecialchars($value, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8"><meta name="csrf-token" content="<?= Seguranca::gerarTokenCsrf() ?>"><meta name="lab-usuario" content="<?= htmlspecialchars(json_encode($_SESSION['usuario'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
<meta name="lab-desafio-condicoes" content="<?= htmlspecialchars(json_encode($challenge['condicoes_resolucao'] ?? [], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, 'UTF-8') ?>">
<meta name="lab-desafio-id" content="<?= $challenge["id"] ?? 0 ?>>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laboratório de Montagem - TechLab EDU</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/laboratorio.css?v=3">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .diagnostic-empty { padding: 18px 12px; border: 1px dashed #bfdbfe; border-radius: 8px; background: #f8fafc; }
        .diagnostic-empty h2 { font-size: 15px; }
        .diagnostic-empty p { margin-top: 8px; color: #64748b; font-size: 11px; line-height: 1.5; }
        .component-library { min-height: 220px; }
        .library-description { margin-top: 6px; color: #64748b; font-size: 10px; line-height: 1.4; }
        .lab-right.standalone .component-library { order: 0; border-top: 1px solid #dbe3ef; border-bottom: 0; }
        .component-subcategories { display: grid; gap: 8px; padding-top: 8px; }
        .component-subcategory { margin-top: 0; }
        .lab-avatar { background: #1d4ed8; }
        .lab-logout { padding-left: 28px; }
    </style>
</head>
<body>
    <header class="lab-header">
        <a class="lab-brand" href="../aluno/dashboard.php" aria-label="Ir para o dashboard"><img class="lab-logo" src="../../assets/logo/Logo_TechLabEDU.png" alt=""><span><strong>TechLab EDU</strong><small>Laboratório Virtual de Hardware</small></span></a>
        <div class="lab-title"><div><strong><?= $challenge ? 'Laboratório de Montagem' : 'Laboratório Livre' ?></strong><small>Monte, conecte e teste os componentes do computador</small></div></div>
        <div class="lab-actions"><button class="lab-icon-button" type="button" aria-label="Notificações"><img src="../../assets/icons-dashboard/bell.png" alt=""><span class="lab-badge">3</span></button><button class="lab-user" type="button"><img class="lab-avatar" src="../../assets/icons-login/user.png" alt=""><span><strong>Usuario</strong><small>Nível 1</small></span><img class="lab-chevron" src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt=""></button></div>
    </header>
    <div class="lab-shell">
        <aside class="lab-sidebar" aria-label="Navegação principal"><nav class="lab-nav">
            <a href="../aluno/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="">Dashboard</a>
            <a href="../aluno/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Turmas</a>
            <a href="./index.php" class="active" aria-current="page"><img src="../../assets/Icons-NavBar/support.png" alt="">Montagem</a>
            <a href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt="">Desafios</a>
            <a href="../ranking/index.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="">Ranking</a>
            <a href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt="">Perfil</a>
            <a href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt="">Configurações</a>
        </nav><a class="lab-nav lab-logout" href="../aluno/dashboard.php"><img src="../../assets/Icons-NavBar/logout.png" alt="">Sair</a></aside>
        <main class="lab-center"><div class="lab-toolbar" aria-label="Controles da bancada"><div class="lab-toolbar-group"><button type="button" id="undo-button">&#8630; <span>Desfazer</span></button><button type="button" id="delete-button">&#128465; <span>Excluir</span></button><button type="button" id="save-build">&#128190; <span>Salvar Build</span></button></div><div class="lab-toolbar-group"><button type="button" id="zoom-out" aria-label="Diminuir zoom">−</button><output id="zoom-level">100%</output><button type="button" id="zoom-in" aria-label="Aumentar zoom">+</button><button type="button" class="lab-mode">2D</button></div></div><div class="lab-stage" id="lab-stage" aria-label="Bancada 2D interativa"><div class="lab-stage-hint">Arraste uma peça da biblioteca para a placa-mãe</div><div class="lab-drop-message">Solte a peça em um encaixe compatível</div><canvas id="lab-canvas"></canvas></div></main>
        <aside class="lab-right <?= $challenge ? 'has-challenge' : 'standalone' ?>"><div class="lab-tabs"><button type="button" class="active" data-tab="diagnostic">DIAGNÓSTICO</button><button type="button" data-tab="info">INFORMAÇÕES</button></div><section class="lab-panel" id="diagnostic-panel">
            <?php if ($challenge): ?>
                <article class="challenge-card"><div class="challenge-meta"><span>DESAFIO <?= escape($challengeId) ?></span><span class="<?= escape($challenge['difficulty_class']) ?>"><?= escape($challenge['difficulty']) ?></span></div><h2><?= escape($challenge['title']) ?></h2><p><?= escape($challenge['description']) ?></p></article>
                <div><h3 class="lab-section-title">Situação reportada</h3><p><?= escape($challenge['problem']) ?></p></div>
                <div class="lab-objective"><strong>Objetivo</strong><br><?= escape($challenge['objective']) ?></div>
                <div><h3 class="lab-section-title">Componentes envolvidos</h3><ul class="symptoms"><?php foreach ($challenge['components'] as $component): ?><li><?= escape($component) ?></li><?php endforeach; ?></ul></div>
                <button class="lab-test" id="test-system" type="button">Testar Sistema</button><div class="diagnostic-result" id="diagnostic-result">A montagem ainda não foi testada.</div>
            <?php else: ?>
                <div class="diagnostic-empty"><h2>Laboratório livre</h2><p>Explore a bancada sem um desafio. Escolha componentes na biblioteca, monte sua configuração e teste o sistema quando quiser.</p></div>
                <button class="lab-test" id="test-system" type="button">Testar Sistema</button><div class="diagnostic-result" id="diagnostic-result">A montagem ainda não foi testada.</div>
            <?php endif; ?>
        </section><section class="lab-panel" id="info-panel" hidden><div class="info-box" id="component-info"><strong>Nenhum componente selecionado</strong><p>Selecione uma peça na bancada ou na biblioteca para consultar seus dados disponíveis.</p></div><div class="info-box"><strong>Como funciona</strong><p>Arraste as peças até os encaixes compatíveis. Uma posição inválida não será instalada.</p></div></section><section class="component-library" aria-labelledby="library-title"><h2 class="lab-section-title" id="library-title">Biblioteca de componentes</h2><p class="library-description">Escolha uma peça para explorar a montagem.</p><input id="component-search" type="search" placeholder="Pesquisar peça..." aria-label="Pesquisar peça"><div id="component-list"></div></section></aside>
    </div>
    </section><div class="lab-modal" id="help-modal" hidden><div class="lab-modal-card"><h2>Como montar</h2><p>Escolha uma peça na biblioteca e arraste-a até a área compatível da placa-mãe. A memória RAM é o componente necessário para o diagnóstico inicial do desafio.</p><button type="button" id="close-help">Entendido</button></div></div>
    <script src="https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.min.js"></script><script src="../js/laboratorio.js?v=12"></script>
</body>
</html>
