<?php
$challenges = [
    1 => [
        'title' => 'PC Gamer sem Vídeo',
        'category' => 'Diagnóstico',
        'difficulty' => 'Médio',
        'difficulty_class' => 'medium',
        'xp' => 150,
        'time' => '20 minutos',
        'component' => 'Memória RAM e placa de vídeo',
        'description' => 'Um computador gamer chega ao laboratório depois de uma troca de componentes. Ele liga normalmente, mas não entrega imagem ao monitor.',
        'problem' => 'Ao pressionar o botão de ligar, os coolers funcionam e as luzes do gabinete acendem. Mesmo assim, o monitor permanece sem sinal.',
        'objective' => 'Inspecionar as conexões e os componentes de vídeo, identificar a causa da falha e aplicar o procedimento correto para recuperar a imagem.',
        'components' => ['Memória RAM', 'Placa de vídeo', 'Cabo de vídeo', 'Monitor'],
    ],
    2 => [
        'title' => 'Superaquecimento',
        'category' => 'Diagnostico',
        'difficulty' => 'Dificil',
        'difficulty_class' => 'hard',
        'xp' => 200,
        'time' => '25 minutos',
        'component' => 'Sistema de resfriamento',
        'description' => 'Durante jogos e tarefas pesadas, o computador perde desempenho e desliga sozinho depois de alguns minutos.',
        'problem' => 'O sistema inicia normalmente, mas a temperatura sobe rapidamente em uso intenso e o computador desliga para se proteger.',
        'objective' => 'Verificar a dissipacao de calor, encontrar o ponto que compromete o resfriamento e restabelecer a operacao segura do equipamento.',
        'components' => ['Cooler do processador', 'Pasta termica', 'Ventoinhas', 'Dissipador'],
    ],
    3 => [
        'title' => 'Fonte Defeituosa',
        'category' => 'Diagnostico',
        'difficulty' => 'Facil',
        'difficulty_class' => 'easy',
        'xp' => 100,
        'time' => '15 minutos',
        'component' => 'Fonte de alimentacao',
        'description' => 'Um computador nao responde ao comando de ligar e nao apresenta nenhum sinal de energia no gabinete.',
        'problem' => 'Ao apertar o botao de ligar, nao ha luzes, bipes ou movimento nos coolers.',
        'objective' => 'Checar a alimentacao do equipamento, identificar a falha na fonte ou em sua conexao e executar a correcao adequada.',
        'components' => ['Fonte de alimentacao', 'Cabo de energia', 'Conector ATX', 'Botao power'],
    ],
    4 => [
        'title' => 'Memoria RAM Incompativel',
        'category' => 'Diagnostico',
        'difficulty' => 'Medio',
        'difficulty_class' => 'medium',
        'xp' => 150,
        'time' => '20 minutos',
        'component' => 'Memoria RAM',
        'description' => 'Depois de receber um novo modulo de memoria, o computador passou a emitir bipes e nao conclui a inicializacao.',
        'problem' => 'O computador liga, emite bipes repetitivos e nao carrega o sistema operacional.',
        'objective' => 'Comparar as especificacoes da memoria com a placa-mae, localizar a incompatibilidade e deixar a memoria instalada corretamente.',
        'components' => ['Memoria RAM', 'Slots de memoria', 'Placa-mae', 'BIOS'],
    ],
    5 => [
        'title' => 'HD nao Detectado',
        'category' => 'Diagnostico',
        'difficulty' => 'Medio',
        'difficulty_class' => 'medium',
        'xp' => 150,
        'time' => '20 minutos',
        'component' => 'Disco rigido',
        'description' => 'Um disco rigido foi instalado no computador, mas nao aparece como opcao de armazenamento na BIOS nem no sistema.',
        'problem' => 'Mesmo conectado ao gabinete, o HD nao e reconhecido durante a inicializacao.',
        'objective' => 'Verificar a alimentacao e a conexao de dados do disco, identificar a origem da falha e fazer o HD ser detectado.',
        'components' => ['HD', 'Cabo SATA', 'Cabo de energia SATA', 'Porta SATA'],
    ],
    6 => [
        'title' => 'Placa de Video com Falha',
        'category' => 'Diagnostico',
        'difficulty' => 'Dificil',
        'difficulty_class' => 'hard',
        'xp' => 200,
        'time' => '30 minutos',
        'component' => 'Placa de video',
        'description' => 'Em jogos, o computador apresenta artefatos visuais, travamentos e, em alguns casos, uma tela azul.',
        'problem' => 'Linhas e blocos coloridos surgem na tela quando a placa de video e exigida.',
        'objective' => 'Avaliar a placa de video e sua refrigeracao, diferenciar falha fisica de configuracao e aplicar a solucao indicada.',
        'components' => ['Placa de video', 'Slot PCI Express', 'Drivers', 'Fonte de alimentacao'],
    ],
    7 => [
        'title' => 'Tela Azul (BSOD)',
        'category' => 'Diagnostico',
        'difficulty' => 'Medio',
        'difficulty_class' => 'medium',
        'xp' => 150,
        'time' => '25 minutos',
        'component' => 'Hardware e drivers',
        'description' => 'O sistema trava de forma aleatoria e mostra uma tela azul antes de reiniciar.',
        'problem' => 'A tela azul aparece durante o uso comum, sem um padrao claro para o aluno.',
        'objective' => 'Ler os sinais do erro, testar os componentes relacionados e determinar a acao que estabiliza o sistema.',
        'components' => ['Memoria RAM', 'Drivers', 'Disco de armazenamento', 'Placa-mae'],
    ],
    8 => [
        'title' => 'SSD Lento',
        'category' => 'Desempenho',
        'difficulty' => 'Facil',
        'difficulty_class' => 'easy',
        'xp' => 100,
        'time' => '15 minutos',
        'component' => 'SSD',
        'description' => 'Mesmo sem muitos programas abertos, o sistema demora para iniciar e abrir arquivos.',
        'problem' => 'O computador responde lentamente em tarefas simples, apesar de usar uma unidade SSD.',
        'objective' => 'Analisar a saude e a conexao do SSD, encontrar o gargalo de desempenho e restaurar a resposta esperada do sistema.',
        'components' => ['SSD', 'Cabo SATA', 'Porta SATA', 'Sistema operacional'],
    ],
    9 => [
        'title' => 'Cabo SATA Solto',
        'category' => 'Montagem',
        'difficulty' => 'Facil',
        'difficulty_class' => 'easy',
        'xp' => 100,
        'time' => '10 minutos',
        'component' => 'Cabo SATA',
        'description' => 'Um HD acabou de ser instalado, mas nao aparece entre os dispositivos reconhecidos pela BIOS.',
        'problem' => 'O disco recebe energia, porem a comunicacao com a placa-mae foi interrompida.',
        'objective' => 'Localizar a conexao solta e posicionar o cabo de dados corretamente para que a BIOS reconheca o armazenamento.',
        'components' => ['Cabo SATA', 'HD', 'Porta SATA', 'Placa-mae'],
    ],
    10 => [
        'title' => 'CPU mal encaixada',
        'category' => 'Montagem',
        'difficulty' => 'Medio',
        'difficulty_class' => 'medium',
        'xp' => 150,
        'time' => '20 minutos',
        'component' => 'Processador',
        'description' => 'Apos uma manutencao, o computador deixou de inicializar e passou a emitir bipes curtos.',
        'problem' => 'O equipamento nao conclui a partida, indicando uma falha na comunicacao com o processador.',
        'objective' => 'Inspecionar o encaixe do processador, identificar o posicionamento incorreto e montar o conjunto de forma segura.',
        'components' => ['Processador', 'Socket da CPU', 'Cooler', 'Placa-mae'],
    ],
    11 => [
        'title' => 'Curto na Placa Mae',
        'category' => 'Diagnostico',
        'difficulty' => 'Dificil',
        'difficulty_class' => 'hard',
        'xp' => 200,
        'time' => '30 minutos',
        'component' => 'Placa-mae',
        'description' => 'O computador liga por um instante e desliga imediatamente, antes mesmo de iniciar a tela da BIOS.',
        'problem' => 'Ao apertar o botao power, o equipamento interrompe a alimentacao quase instantaneamente.',
        'objective' => 'Isolar os componentes, encontrar o ponto de curto e realizar o procedimento necessario para evitar novos desligamentos.',
        'components' => ['Placa-mae', 'Espacadores do gabinete', 'Fonte de alimentacao', 'Cabos do painel frontal'],
    ],
    12 => [
        'title' => 'Cooler Solto',
        'category' => 'Montagem',
        'difficulty' => 'Medio',
        'difficulty_class' => 'medium',
        'xp' => 150,
        'time' => '15 minutos',
        'component' => 'Cooler do processador',
        'description' => 'O computador inicia, mas desliga depois de cerca de trinta segundos devido ao aumento rapido da temperatura.',
        'problem' => 'O processador aquece imediatamente porque o cooler nao esta firme sobre o dissipador.',
        'objective' => 'Reconhecer a montagem incorreta do cooler e fixar o sistema de resfriamento para manter a temperatura estavel.',
        'components' => ['Cooler do processador', 'Dissipador', 'Pasta termica', 'Conector CPU FAN'],
    ],
    13 => [
        'title' => 'Teclado nao Funciona',
        'category' => 'Perifericos',
        'difficulty' => 'Facil',
        'difficulty_class' => 'easy',
        'xp' => 100,
        'time' => '10 minutos',
        'component' => 'Teclado',
        'description' => 'O computador liga, mas o teclado nao responde durante a inicializacao e nao permite acessar a BIOS.',
        'problem' => 'Nenhuma tecla e reconhecida, mesmo antes de o sistema operacional carregar.',
        'objective' => 'Conferir a conexao e a porta utilizada, identificar a causa da falta de resposta e fazer o teclado voltar a funcionar.',
        'components' => ['Teclado', 'Porta USB', 'Porta PS/2', 'BIOS'],
    ],
    14 => [
        'title' => 'BIOS Desconfigurada',
        'category' => 'Configuracao',
        'difficulty' => 'Medio',
        'difficulty_class' => 'medium',
        'xp' => 150,
        'time' => '20 minutos',
        'component' => 'BIOS',
        'description' => 'O computador liga, mas nao encontra a unidade que possui o sistema operacional para continuar a inicializacao.',
        'problem' => 'A mensagem de falha de boot aparece porque a ordem de inicializacao nao aponta para a unidade correta.',
        'objective' => 'Acessar as configuracoes da BIOS, identificar o parametro alterado e definir a sequencia correta de inicializacao.',
        'components' => ['BIOS', 'SSD ou HD', 'Ordem de boot', 'Placa-mae'],
    ],
    15 => [
        'title' => 'Capacitor Estufado',
        'category' => 'Diagnostico',
        'difficulty' => 'Dificil',
        'difficulty_class' => 'hard',
        'xp' => 200,
        'time' => '30 minutos',
        'component' => 'Placa-mae',
        'description' => 'O computador apresenta instabilidade, reinicializacoes e falhas aparentemente aleatorias durante o uso.',
        'problem' => 'A inspecao visual revela um capacitor deformado na placa-mae, comprometendo a estabilidade eletrica.',
        'objective' => 'Reconhecer o componente danificado, relacionar o defeito aos sintomas e indicar o procedimento seguro para a solucao.',
        'components' => ['Capacitores', 'Placa-mae', 'Fonte de alimentacao', 'Circuito de energia'],
    ],
];

$challengeId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$challenge = $challengeId !== false && $challengeId !== null && isset($challenges[$challengeId]) ? $challenges[$challengeId] : null;

function escape($value)
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $challenge ? escape($challenge['title']) . ' - TechLab EDU' : 'Desafio nao encontrado - TechLab EDU' ?></title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/desafio.css">
</head>

<body>
    <div class="app-container">
        <header class="main-header">
            <div class="header-left">
                <a class="logo-area" href="../aluno/dashboard.php" aria-label="Ir para o dashboard">
                    <img src="../../assets/logo/Logo_TechLabEDU.png" alt="" class="logo">
                    <span class="logo-text">
                        <strong>TechLab EDU</strong>
                        <small>Laboratório Virtual de Hardware</small>
                    </span>
                </a>
                <div class="page-info">
                    <h1>Desafios</h1>
                    <p>Prepare-se antes de entrar no laboratório</p>
                </div>
            </div>
            <div class="header-right">
                <div class="notifications" aria-label="3 notificacoes">
                    <img src="../../assets/icons-dashboard/bell.png" alt="">
                    <span class="notification-count">3</span>
                </div>
                <div class="user-card">
                    <img src="../../assets/icons-dashboard/account.png" alt="" class="avatar">
                    <span><strong>KrisXSB_</strong><small>Nível 7</small></span>
                </div>
            </div>
        </header>

        <div class="main-layout">
            <aside class="sidebar" aria-label="Navegação principal">
                <nav>
                    <a href="../aluno/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="">Dashboard</a>
                    <a href="../laboratorio/index.php"><img src="../../assets/Icons-NavBar/support.png" alt="">Montagem</a>
                    <a href="index.php" class="active"><img src="../../assets/Icons-NavBar/target.png" alt="">Desafios</a>
                    <a href="../ranking/index.php"><img src="../../assets/Icons-NavBar/ranking.png" alt="">Ranking</a>
                    <a href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt="">Perfil</a>
                    <a href="#"><img src="../../assets/Icons-NavBar/conquistas.png" alt="">Conquistas</a>
                    <a href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt="">Configurações</a>
                </nav>
                <nav class="sidebar-footer">
                    <a href="../aluno/dashboard.php" class="exit"><img src="../../assets/Icons-NavBar/logout.png" alt="">Sair</a>
                </nav>
            </aside>

            <main class="content">
                <?php if (!$challenge): ?>
                    <section class="not-found" aria-labelledby="not-found-title">
                        <h2 id="not-found-title">Desafio não encontrado</h2>
                        <p>Este desafio não está disponível. Volte para a lista e escolha um desafio válido.</p>
                        <a class="button button-primary" href="index.php">Voltar aos desafios</a>
                    </section>
                <?php else: ?>
                    <a class="back-link" href="index.php">Voltar aos desafios</a>

                    <header class="challenge-header">
                        <div>
                            <p class="challenge-category"><?= escape($challenge['category']) ?></p>
                            <h2><?= escape($challenge['title']) ?></h2>
                            <p class="challenge-summary"><?= escape($challenge['component']) ?></p>
                        </div>
                        <div class="challenge-reward" aria-label="Recompensa de <?= escape($challenge['xp']) ?> XP">
                            <span>Recompensa</span>
                            <strong>+<?= escape($challenge['xp']) ?> XP</strong>
                        </div>
                    </header>

                    <div class="challenge-layout">
                        <div class="challenge-reading">
                            <section class="content-section" aria-labelledby="about-title">
                                <h2 id="about-title">Sobre o desafio</h2>
                                <p><?= escape($challenge['description']) ?></p>
                            </section>

                            <section class="problem-section" aria-labelledby="problem-title">
                                <div>
                                    <h2 id="problem-title">Situação-problema</h2>
                                    <p><?= escape($challenge['problem']) ?></p>
                                </div>
                            </section>

                            <section class="content-section" aria-labelledby="objective-title">
                                <h2 id="objective-title">Objetivo</h2>
                                <p><?= escape($challenge['objective']) ?></p>
                            </section>
                        </div>

                        <aside class="challenge-info" aria-labelledby="info-title">
                            <h2 id="info-title">Informações do desafio</h2>
                            <dl>
                                <div>
                                    <dt>Dificuldade</dt>
                                    <dd><span class="difficulty <?= escape($challenge['difficulty_class']) ?>"><?= escape($challenge['difficulty']) ?></span></dd>
                                </div>
                                <div>
                                    <dt>Recompensa</dt>
                                    <dd>+<?= escape($challenge['xp']) ?> XP</dd>
                                </div>
                                <div>
                                    <dt>Categoria</dt>
                                    <dd><?= escape($challenge['category']) ?></dd>
                                </div>
                                <div>
                                    <dt>Componente principal</dt>
                                    <dd><?= escape($challenge['component']) ?></dd>
                                </div>
                                <div>
                                    <dt>Tempo estimado</dt>
                                    <dd><?= escape($challenge['time']) ?></dd>
                                </div>
                            </dl>

                            <section class="components" aria-labelledby="components-title">
                                <h3 id="components-title">Componentes envolvidos</h3>
                                <ul>
                                    <?php foreach ($challenge['components'] as $component): ?>
                                        <li><?= escape($component) ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        </aside>
                    </div>

                    <footer class="challenge-actions">
                        <a class="button button-secondary" href="index.php">Voltar aos desafios</a>
                        <a class="button button-primary" href="../laboratorio/index.php?id=<?= escape($challengeId) ?>">Iniciar desafio</a>
                    </footer>
                <?php endif; ?>
            </main>
        </div>
    </div>
    <script src="../js/desafio.js"></script>
</body>

</html>