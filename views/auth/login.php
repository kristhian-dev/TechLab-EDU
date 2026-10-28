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
    AuthController::processLogin();
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
    <title>TechLab EDU</title>
    <link rel="shortcut icon" href="../../assets/logo/Logo_TechLabEDU.png" type="image/x-icon">
    <link rel="stylesheet" href="../css/login.css">
    <script src="../js/login.js" defer></script>
</head>

<body class="login-body">
    <div class="container">
        <div class="login_card">
            <header>
                <div class="title">
                    <img src="../../assets/logo/Logo_TechLabEDU.png" alt="Logo TechLab EDU" class="logo">
                    <span class="TechLab">TechLab</span>
                    <span class="edu">EDU</span>
                </div>
                <div class="sub_title">
                    <h2>Bem-vindo de volta!</h2>
                    <p>Faça login para continuar</p>
                </div>
            </header>
            <main>
                <?php if ($flash): ?>
                    <div class="flash-message flash-<?php echo htmlspecialchars($flash['type']); ?>" style="margin-bottom: 16px; padding: 12px 14px; border-radius: 10px; background: rgba(245, 158, 11, 0.12); color: #1f2937; border: 1px solid rgba(245, 158, 11, 0.35);">
                        <?php echo htmlspecialchars($flash['message']); ?>
                    </div>
                <?php endif; ?>
                <form action="" method="post">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    <input type="hidden" name="perfil" id="perfil" value="">
                    <div class="input-field">
                        <label for="email">E-mail</label>
                        <div class="input-group">
                            <img src="<?php echo ICON_EMAIL; ?>" alt="E-mail" class="input-icon">
                            <input type="email" id="email" name="username" placeholder="seu@email.com" required>
                        </div>
                    </div>
                    <div class="input-field">
                        <label for="password">Senha</label>
                        <div class="input-group">
                            <img src="<?php echo ICON_CADEADO; ?>" alt="Senha" class="input-icon">
                            <input type="password" id="password" name="password" placeholder="********" required>
                            <img src="<?php echo ICON_OLHO; ?>" alt="Mostrar senha" class="view-icon">
                        </div>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember"> Lembrar-me
                        </label>
                        <a href="#" class="forgot-password">Esqueceu a senha?</a>
                    </div>
                    <button type="submit" class="btn-login">Entrar</button>
                    <p class="divider">ou continue como</p>
                    <div class="profile-options">
                        <button type="button" class="btn-profile" data-perfil="aluno" aria-pressed="false">Aluno <img src="<?php echo ICON_PERFIL_AZUL; ?>" alt="Aluno"></button>
                        <button type="button" class="btn-profile" data-perfil="professor" aria-pressed="false">Professor <img src="<?php echo ICON_GRADUACAO_AZUL; ?>" alt="Professor"></button>
                    </div>
                </form>
                <footer class="login-footer">
                    <p>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></p>
                </footer>
            </main>
        </div>
    </div>
</body>

</html>