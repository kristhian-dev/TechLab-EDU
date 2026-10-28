<?php

require_once APP_ROOT . '/nucleo/Seguranca.php';
require_once APP_ROOT . '/nucleo/Auth.php';
require_once APP_ROOT . '/modelos/UsuarioModel.php';

final class AuthController
{
    public static function processLogin(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/auth/login.php');
        }

        $usuarioInput = trim((string) ($_POST['username'] ?? ''));
        $senha = (string) ($_POST['password'] ?? '');
        $perfilSolicitado = strtolower(trim((string) ($_POST['perfil'] ?? '')));

        if ($usuarioInput === '' || $senha === '') {
            Seguranca::flash('danger', 'Informe o e-mail ou nome de usuário e a senha.');
            Seguranca::redirecionar('/views/auth/login.php');
        }

        if (!in_array($perfilSolicitado, ['aluno', 'professor'], true)) {
            Seguranca::flash('danger', 'Selecione o tipo de conta (Aluno ou Professor) para entrar.');
            Seguranca::redirecionar('/views/auth/login.php');
        }

        try {
            $usuario = UsuarioModel::buscarPorCredencial($usuarioInput);
            if (!$usuario) {
                Seguranca::flash('danger', 'Credenciais inválidas.');
                Seguranca::redirecionar('/views/auth/login.php');
            }

            if (!empty($usuario['bloqueado_ate']) && new DateTimeImmutable() < new DateTimeImmutable($usuario['bloqueado_ate'])) {
                Seguranca::flash('warning', 'Sua conta está temporariamente bloqueada. Tente novamente mais tarde.');
                Seguranca::redirecionar('/views/auth/login.php');
            }

            if (!password_verify($senha, $usuario['senha_hash'])) {
                UsuarioModel::registrarFalhaLogin((int) $usuario['id_usuario']);
                $tentativas = (int) $usuario['tentativas_incorretas_login'] + 1;
                if ($tentativas >= 5) {
                    UsuarioModel::bloquearConta((int) $usuario['id_usuario']);
                    Seguranca::flash('warning', 'Muitas tentativas incorretas. Sua conta foi bloqueada por 15 minutos.');
                } else {
                    Seguranca::flash('danger', 'Senha incorreta. Tentativas restantes: ' . (5 - $tentativas));
                }
                Seguranca::redirecionar('/views/auth/login.php');
            }

            UsuarioModel::resetarTentativasLogin((int) $usuario['id_usuario']);
            $perfil = Auth::perfilDoUsuario((int) $usuario['id_usuario']);

            // Hierarquia da conta (gravada no cadastro) deve bater com o botão selecionado.
            if (in_array($perfil, ['aluno', 'professor'], true) && $perfil !== $perfilSolicitado) {
                $rotulo = $perfil === 'professor' ? 'Professor' : 'Aluno';
                Seguranca::flash('danger', 'Esta conta é do tipo ' . $rotulo . '. Selecione o botão "' . $rotulo . '" para entrar.');
                Seguranca::redirecionar('/views/auth/login.php');
            }

            $dadosSessao = [
                'id' => (int) $usuario['id_usuario'],
                'nome' => $usuario['nome'],
                'nome_usuario' => $usuario['nome_usuario'],
                'email' => $usuario['email'],
                'perfil' => $perfil,
            ];

            try {
                $dadosSessao['foto'] = UsuarioModel::buscarFotoPerfil((int) $usuario['id_usuario']);
            } catch (Throwable $e) {
                error_log('Login: erro ao carregar foto de perfil: ' . $e->getMessage());
                $dadosSessao['foto'] = null;
            }

            session_regenerate_id(true);
            $_SESSION['usuario'] = $dadosSessao;
            Seguranca::flash('success', 'Login realizado com sucesso.');
            Auth::redirecionarDashboard($dadosSessao);
        } catch (Throwable $e) {
            error_log('Erro no login: ' . $e->getMessage());
            Seguranca::flash('danger', 'Erro interno ao realizar login.');
            Seguranca::redirecionar('/views/auth/login.php');
        }
    }

    public static function processCadastro(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        $nome = trim((string) ($_POST['fullname'] ?? ''));
        $email = strtolower(trim((string) ($_POST['email'] ?? '')));
        $nomeUsuario = strtolower(trim((string) ($_POST['username'] ?? '')));
        $senha = (string) ($_POST['password'] ?? '');
        $confirmarSenha = (string) ($_POST['confirm_password'] ?? '');
        // Hierarquia da conta: o cadastro público só permite aluno ou professor.
        $perfilSolicitado = strtolower(trim((string) ($_POST['perfil'] ?? '')));
        $aceitouTermos = !empty($_POST['terms']);

        if (!in_array($perfilSolicitado, ['aluno', 'professor'], true)) {
            Seguranca::flash('danger', 'Selecione o tipo de conta: Aluno ou Professor.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        if ($nome === '' || $email === '' || $nomeUsuario === '' || $senha === '' || $confirmarSenha === '') {
            Seguranca::flash('danger', 'Todos os campos são obrigatórios.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        if (!$aceitouTermos) {
            Seguranca::flash('danger', 'Você precisa aceitar os termos para continuar.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        if (strlen($senha) < 6) {
            Seguranca::flash('danger', 'A senha deve ter pelo menos 6 caracteres.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        if ($senha !== $confirmarSenha) {
            Seguranca::flash('danger', 'As senhas informadas não conferem.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        $emailValido = filter_var($email, FILTER_VALIDATE_EMAIL);
        if ($emailValido === false) {
            Seguranca::flash('danger', 'Informe um e-mail válido.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        if (UsuarioModel::emailExiste($email)) {
            Seguranca::flash('danger', 'Este e-mail já está em uso.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        if (UsuarioModel::nomeUsuarioExiste($nomeUsuario)) {
            Seguranca::flash('danger', 'Este nome de usuário já foi cadastrado.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        if (in_array($perfilSolicitado, ['administrador', 'admin'], true)) {
            Seguranca::flash('danger', 'Não é permitido criar uma conta administrativa diretamente.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }

        try {
            if (UsuarioModel::emailExiste($email)) {
                Seguranca::flash('danger', 'Este e-mail já está em uso.');
                Seguranca::redirecionar('/views/auth/cadastro.php');
            }

            if (UsuarioModel::nomeUsuarioExiste($nomeUsuario)) {
                Seguranca::flash('danger', 'Este nome de usuário já foi cadastrado.');
                Seguranca::redirecionar('/views/auth/cadastro.php');
            }

            $matricula = strtoupper(substr($nomeUsuario, 0, 4) . random_int(100, 9999));
            if ($perfilSolicitado === 'professor') {
                $matricula = 'PROF-' . random_int(1000, 9999);
            }

            if ($perfilSolicitado === 'aluno' && UsuarioModel::matriculaExiste($matricula)) {
                $matricula = strtoupper('ALU' . random_int(1000, 9999));
            }

            $pdo = Database::pdo();
            $pdo->beginTransaction();

            $idUsuario = UsuarioModel::criarUsuario([
                'nome' => $nome,
                'nome_usuario' => $nomeUsuario,
                'email' => $email,
                'senha' => $senha,
                'tipo' => $perfilSolicitado === 'professor' ? 'PROFESSOR' : 'ALUNO',
            ]);

            if ($perfilSolicitado === 'professor') {
                UsuarioModel::criarPerfilProfessor($idUsuario);
            } else {
                UsuarioModel::criarPerfilAluno($idUsuario, $matricula);
            }

            $pdo->commit();
            Seguranca::flash('success', 'Cadastro realizado com sucesso. Faça login para continuar.');
            Seguranca::redirecionar('/views/auth/login.php');
        } catch (Throwable $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Erro no cadastro: ' . $e->getMessage());
            Seguranca::flash('danger', 'Não foi possível concluir o cadastro. Tente novamente.');
            Seguranca::redirecionar('/views/auth/cadastro.php');
        }
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            session_destroy();
        }

        Seguranca::flash('success', 'Sessão encerrada com sucesso.');
        Seguranca::redirecionar('/views/auth/login.php');
    }
}
