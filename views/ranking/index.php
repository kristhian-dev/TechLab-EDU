<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ranking - TechLab EDU</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/ranking.css?v=1">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
</head>
<body>
    <header class="ranking-header">
        <a class="ranking-brand" href="../aluno/dashboard.php" aria-label="Ir para o dashboard">
            <img src="../../assets/logo/Logo_TechLabEDU.png" alt="" class="ranking-brand-logo">
            <span>
                <strong>TechLab EDU</strong>
                <small>Laboratório Virtual de Hardware</small>
            </span>
        </a>
        <div class="ranking-page-title">
            <strong>Ranking</strong>
            <small>Veja os melhores alunos e suas conquistas</small>
        </div>
        <label class="ranking-search">
            <img src="../../assets/icons-dashboard/lupa.png" alt="">
            <input id="student-search" type="search" placeholder="Pesquisar aluno..." aria-label="Pesquisar aluno">
        </label>
        <button class="ranking-notification" type="button" aria-label="Notificações">
            <img src="../../assets/icons-dashboard/bell.png" alt="">
            <span>0</span>
        </button>
        <button class="ranking-user" type="button" aria-label="Menu do usuário">
            <img src="../../assets/icons-login/user.png" alt="" class="ranking-avatar">
            <span><strong>Usuario</strong><small>Nível 1</small></span>
            <img src="../../assets/icons-dashboard/sinal-de-seta-para-baixo-para-navegar.png" alt="" class="ranking-chevron">
        </button>
    </header>

    <div class="ranking-layout">
        <aside class="ranking-sidebar" aria-label="Navegação principal">
            <nav>
                <a href="../aluno/dashboard.php"><img src="../../assets/Icons-NavBar/home.png" alt="">Dashboard</a>
                <a href="../aluno/turmas.php"><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt="">Turmas</a>
                <a href="../laboratorio/index.php"><img src="../../assets/Icons-NavBar/support.png" alt="">Montagem</a>
                <a href="../desafios/index.php"><img src="../../assets/Icons-NavBar/target.png" alt="">Desafios</a>
                <a href="./index.php" class="active" aria-current="page"><img src="../../assets/Icons-NavBar/ranking.png" alt="">Ranking</a>
                <a href="../perfil/perfil.php"><img src="../../assets/icons-dashboard/account.png" alt="">Perfil</a>
                <a href="../configuracao/configuracao.php"><img src="../../assets/Icons-NavBar/settings.png" alt="">Configurações</a>
            </nav>
            <a href="#" class="ranking-logout"><img src="../../assets/Icons-NavBar/logout.png" alt="">Sair</a>
        </aside>

        <main class="ranking-main">
            <div class="ranking-content">
                <section class="ranking-board" aria-labelledby="ranking-title">
                    <header class="ranking-board-heading">
                        <div>
                            <h1 id="ranking-title"><img src="../../assets/icons-dashboard/trophy.png" alt="">Ranking Geral</h1>
                            <p>Veja quem são os melhores alunos do TechLab EDU</p>
                        </div>
                        <div class="ranking-filters">
                            <label><img src="../../assets/Icons-NavBar/multiple-users-silhouette.png" alt=""><select aria-label="Filtrar por turma"><option>Todos</option><option>Minha turma</option></select></label>
                            <label><img src="../../assets/Icons-NavBar/relatorio.png" alt=""><select aria-label="Filtrar período"><option>Geral</option><option>Este mês</option><option>Esta semana</option></select></label>
                        </div>
                    </header>

                    <div class="podium podium-empty" aria-label="Pódio vazio">
                        <div class="ranking-empty-state">
                            <strong>Nenhum aluno no ranking</strong>
                            <span>Os melhores colocados aparecerão aqui quando houver dados.</span>
                        </div>
                    </div>

                    <div class="ranking-table-wrap">
                        <table class="ranking-table">
                            <thead><tr><th>Posição</th><th>Aluno</th><th>Nível</th><th>XP</th><th>Conquistas</th></tr></thead>
                            <tbody id="ranking-rows">
                                <tr class="empty-table-row"><td colspan="5">Nenhum aluno cadastrado no ranking.</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <footer class="ranking-footer"><span>0 alunos cadastrados</span></footer>
                </section>
            </div>

            <aside class="ranking-rail">
                <section class="rail-card position-card"><h2>Sua Posição</h2><strong>#--</strong><p>Entre 0 alunos</p><hr><div class="rail-level"><span>1</span><div><b>Nível 1</b><div class="mini-progress"><i></i></div><small>0 / 0 XP</small></div></div><p>Sem dados de progresso</p></section>
                <section class="rail-card"><h2>Estatísticas Gerais</h2><dl><div><dt>♧</dt><dd>Total de Alunos</dd><strong>0</strong></div><div><dt>☆</dt><dd>Maior XP</dd><strong>0</strong></div><div><dt>◎</dt><dd>Desafios Concluídos</dd><strong>0</strong></div><div><dt>▥</dt><dd>Média de XP</dd><strong>0</strong></div></dl></section>
                <section class="rail-card specialty-card"><h2>Ranking por Especialidade</h2><p class="ranking-empty-rail">Nenhuma especialidade disponível.</p></section>
            </aside>
        </main>
    </div>
    <script src="../js/ranking.js?v=1"></script>
</body>
</html>
