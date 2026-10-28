<?php
require_once __DIR__ . '/../../configuracoes/app.php';
require_once __DIR__ . '/../../configuracoes/icones.php';
require_once __DIR__ . '/../../nucleo/Seguranca.php';
require_once __DIR__ . '/../../nucleo/Auth.php';
require_once __DIR__ . '/../../modelos/UsuarioModel.php';
require_once __DIR__ . '/../../controllers/AuthController.php';

if (isset($_SESSION['usuario'])) {
    Auth::redirecionarDashboard($_SESSION['usuario']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    AuthController::processCadastro();
}

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);
$csrfToken = Seguranca::gerarTokenCsrf();
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TechLab EDU - Cadastro</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/cadastro.css">
    <script src="../js/cadastro.js" defer></script>
</head>

<body class="cadastro-body">
    <div class="container">
        <div class="cadastro_card">
            <div class="right-panel">
                <header>
                    <h1>Criar conta</h1>
                    <p>Preencha seus dados para se cadastrar</p>
                </header>
                <main>
                    <?php if ($flash): ?>
                        <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>" style="margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; background: rgba(34, 197, 94, 0.12); color: #1f2937; border: 1px solid rgba(34, 197, 94, 0.35);">
                            <?php echo htmlspecialchars($flash['message']); ?>
                        </div>
                    <?php endif; ?>
                    <form action="" method="post">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                        <input type="hidden" name="perfil" id="perfil" value="aluno">
                        <div class="input-field">
                            <label for="fullname">Nome completo</label>
                            <div class="input-group">
                                <img src="<?php echo ICON_USUARIO; ?>" alt="Usuário" class="input-icon">
                                <input type="text" id="fullname" name="fullname" placeholder="Ex.: João Pedro da Silva" required>
                            </div>
                        </div>
                        <div class="input-field">
                            <label for="email">E-mail</label>
                            <div class="input-group">
                                <img src="<?php echo ICON_EMAIL; ?>" alt="E-mail" class="input-icon">
                                <input type="email" id="email" name="email" placeholder="Ex.: joaopedro@email.com" required>
                            </div>
                        </div>
                        <div class="input-field">
                            <label for="username">Nome de usuário</label>
                            <div class="input-group">
                                <img src="<?php echo ICON_USUARIO; ?>" alt="Usuário" class="input-icon">
                                <input type="text" id="username" name="username" placeholder="Ex.: joaopedro123" required>
                            </div>
                        </div>
                        <div class="input-field">
                            <label for="password">Senha</label>
                            <div class="input-group">
                                <img src="<?php echo ICON_CADEADO; ?>" alt="Senha" class="input-icon">
                                <input type="password" id="password" name="password" placeholder="Mínimo de 6 caracteres" required>
                                <img src="<?php echo ICON_OLHO_FECHADO; ?>" alt="Mostrar senha" class="view-icon">
                            </div>
                        </div>
                        <div class="input-field">
                            <label for="confirm_password">Confirmar senha</label>
                            <div class="input-group">
                                <img src="<?php echo ICON_CADEADO; ?>" alt="Senha" class="input-icon">
                                <input type="password" id="confirm_password" name="confirm_password" placeholder="Digite sua senha novamente" required>
                                <img src="<?php echo ICON_OLHO_FECHADO; ?>" alt="Mostrar senha" class="view-icon">
                            </div>
                        </div>
                        <div class="user-type">
                            <label>Tipo de conta</label>
                            <div class="profile-options">
                                <button type="button" class="btn-profile selected" data-perfil="aluno">
                                    <img src="<?php echo ICON_GRADUACAO_AZUL; ?>" alt="">
                                    <div class="profile-text">
                                        <strong>Aluno</strong>
                                        <span>Estudante</span>
                                    </div>
                                </button>
                                <button type="button" class="btn-profile" data-perfil="professor">
                                    <img src="<?php echo ICON_PERFIL_AZUL; ?>" alt="">
                                    <div class="profile-text">
                                        <strong>Professor</strong>
                                        <span>Educador</span>
                                    </div>
                                </button>
                            </div>
                        </div>
                        <div class="terms">
                            <label>
                                <input type="checkbox" name="terms" required> Eu concordo com os <a href="#">Termos de Uso e Política de Privacidade</a>
                            </label>
                        </div>
                        <button type="submit" class="btn-submit">Cadastrar</button>
                        <p class="divider">ou cadastre-se com</p>
                        <div class="social-login">
                            <button type="button" class="btn-social">
                                <img src="<?php echo ICON_GOOGLE; ?>" alt="Google"> Google
                            </button>
                            <button type="button" class="btn-social">
                                <img src="<?php echo ICON_MICROSOFT; ?>" alt="Microsoft"> Microsoft
                            </button>
                        </div>
                    </form>
                </main>
                <footer>
                    <p>Já tem uma conta? <a href="login.php">Fazer login</a></p>
                </footer>
            </div>
        </div>
    </div>
</body>

</html>