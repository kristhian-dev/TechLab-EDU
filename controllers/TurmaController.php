<?php

require_once APP_ROOT . '/nucleo/Seguranca.php';
require_once APP_ROOT . '/nucleo/Auth.php';
require_once APP_ROOT . '/modelos/DesafioModel.php';
require_once APP_ROOT . '/modelos/TurmaModel.php';

final class TurmaController
{
    /**
     * Processa a criação de uma nova turma (apenas professores/administradores).
     */
    public static function processarCriacao(): void
    {
        Auth::exigePerfil('professor', 'administrador');

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/professor/turmas.php?aba=nova');
        }

        $nome = trim((string) ($_POST['nome'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));

        if ($nome === '') {
            Seguranca::flash('danger', 'Informe o nome da turma.');
            Seguranca::redirecionar('/views/professor/turmas.php?aba=nova');
        }

        $usuario = Auth::usuarioLogado();
        $idProfessor = (int) ($usuario['id'] ?? 0);

        try {
            $idTurma = TurmaModel::criar($idProfessor, [
                'nome' => $nome,
                'descricao' => $descricao,
            ]);

            // Recupera o código gerado para exibi-lo ao professor (confirmacao + "Copiar codigo").
            // Se a leitura falhar, segue o fluxo normal sem bloco de codigo (sem confirmacao falsa).
            $turmaCriada = TurmaModel::buscarPorId($idTurma);
            if ($turmaCriada && ($turmaCriada['codigo'] ?? '') !== '') {
                $_SESSION['turma_codigo_sucesso'] = [
                    'nome' => (string) ($turmaCriada['nome'] ?? $nome),
                    'codigo' => (string) $turmaCriada['codigo'],
                ];
            }

            Seguranca::flash('success', 'Turma criada com sucesso. Agora adicione os alunos.');
            Seguranca::redirecionar('/views/professor/turmas.php?aba=alunos&id=' . $idTurma);
        } catch (Throwable $e) {
            error_log('Erro ao criar turma: ' . $e->getMessage());
            Seguranca::flash('danger', 'Erro interno ao criar a turma. Tente novamente.');
            Seguranca::redirecionar('/views/professor/turmas.php?aba=nova');
        }
    }

    /**
     * Processa a adição de um aluno à turma.
     */
    public static function processarAdicionarAluno(): void
    {
        Auth::exigePerfil('professor', 'administrador');

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/professor/turmas.php');
        }

        $idTurma = (int) ($_POST['id_turma'] ?? 0);
        $idAluno = (int) ($_POST['id_aluno'] ?? 0);
        $busca = trim((string) ($_POST['busca'] ?? ''));

        $destino = self::destinoAlunos($idTurma, $busca);

        $usuario = Auth::usuarioLogado();
        $idProfessor = (int) ($usuario['id'] ?? 0);

        if ($idTurma <= 0 || $idAluno <= 0 || !TurmaModel::professorGerenciaTurma($idProfessor, $idTurma)) {
            Seguranca::flash('danger', 'Turma inválida para o seu usuário.');
            Seguranca::redirecionar($destino);
        }

        try {
            TurmaModel::adicionarAluno($idTurma, $idAluno);
            Seguranca::flash('success', 'Aluno adicionado à turma.');
        } catch (Throwable $e) {
            error_log('Erro ao adicionar aluno na turma: ' . $e->getMessage());
            Seguranca::flash('danger', 'Erro interno ao adicionar o aluno. Tente novamente.');
        }

        Seguranca::redirecionar($destino);
    }

    /**
     * Processa a remoção de um aluno da turma.
     */
    public static function processarRemoverAluno(): void
    {
        Auth::exigePerfil('professor', 'administrador');

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/professor/turmas.php');
        }

        $idTurma = (int) ($_POST['id_turma'] ?? 0);
        $idAluno = (int) ($_POST['id_aluno'] ?? 0);

        $destino = self::destinoAlunos($idTurma);

        $usuario = Auth::usuarioLogado();
        $idProfessor = (int) ($usuario['id'] ?? 0);

        if ($idTurma <= 0 || $idAluno <= 0 || !TurmaModel::professorGerenciaTurma($idProfessor, $idTurma)) {
            Seguranca::flash('danger', 'Turma inválida para o seu usuário.');
            Seguranca::redirecionar($destino);
        }

        try {
            TurmaModel::removerAluno($idTurma, $idAluno);
            Seguranca::flash('success', 'Aluno removido da turma.');
        } catch (Throwable $e) {
            error_log('Erro ao remover aluno da turma: ' . $e->getMessage());
            Seguranca::flash('danger', 'Erro interno ao remover o aluno. Tente novamente.');
        }

        Seguranca::redirecionar($destino);
    }

    /**
     * Processa a associação de um desafio a uma turma administrada.
     */
    public static function processarAssociarDesafio(): void
    {
        Auth::exigePerfil('professor', 'administrador');

        $idTurma = (int) ($_POST['id_turma'] ?? 0);
        $destino = self::destinoDesafios($idTurma);

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar($destino);
        }

        $idDesafio = (int) ($_POST['id_desafio'] ?? 0);
        $ordem = (int) ($_POST['ordem'] ?? 0);
        $obrigatorio = isset($_POST['obrigatorio']);
        $usuario = Auth::usuarioLogado();
        $idProfessor = (int) ($usuario['id'] ?? 0);

        if ($idTurma <= 0 || !TurmaModel::professorGerenciaTurma($idProfessor, $idTurma)) {
            Seguranca::flash('danger', 'Turma inválida para o seu usuário.');
            Seguranca::redirecionar($destino);
        }

        try {
            $dataLiberacao = self::normalizarDataLiberacao($_POST['data_liberacao'] ?? '');
            DesafioModel::associarATurma($idDesafio, $idTurma, $ordem, $obrigatorio, $dataLiberacao);
            Seguranca::flash('success', 'Desafio associado à turma.');
        } catch (Throwable $e) {
            error_log('Erro ao associar desafio à turma: ' . $e->getMessage());
            Seguranca::flash('danger', $e->getMessage());
        }

        Seguranca::redirecionar($destino);
    }

    /**
     * Processa a remoção de uma associação desafio-turma.
     */
    public static function processarRemoverDesafio(): void
    {
        Auth::exigePerfil('professor', 'administrador');

        $idTurma = (int) ($_POST['id_turma'] ?? 0);
        $destino = self::destinoDesafios($idTurma);

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar($destino);
        }

        $idDesafio = (int) ($_POST['id_desafio'] ?? 0);
        $usuario = Auth::usuarioLogado();
        $idProfessor = (int) ($usuario['id'] ?? 0);

        if ($idTurma <= 0 || !TurmaModel::professorGerenciaTurma($idProfessor, $idTurma)) {
            Seguranca::flash('danger', 'Turma inválida para o seu usuário.');
            Seguranca::redirecionar($destino);
        }

        try {
            DesafioModel::removerDaTurma($idDesafio, $idTurma);
            Seguranca::flash('success', 'Desafio removido da turma.');
        } catch (Throwable $e) {
            error_log('Erro ao remover desafio da turma: ' . $e->getMessage());
            Seguranca::flash('danger', $e->getMessage());
        }

        Seguranca::redirecionar($destino);
    }

    /**
     * Processa a exclusão lógica de uma turma (apenas o professor dono, via POST + CSRF).
     */
    public static function processarExclusao(): void
    {
        Auth::exigePerfil('professor', 'administrador');

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/professor/turmas.php?aba=turmas');
        }

        $idTurma = (int) ($_POST['id_turma'] ?? 0);
        $usuario = Auth::usuarioLogado();
        $idProfessor = (int) ($usuario['id'] ?? 0);

        if ($idTurma <= 0) {
            Seguranca::flash('danger', 'Turma inválida para exclusão.');
            Seguranca::redirecionar('/views/professor/turmas.php?aba=turmas');
        }

        try {
            // Defesa contra ID manual de outro professor: o UPDATE só afeta linha do dono.
            $excluiu = TurmaModel::excluirLogico($idProfessor, $idTurma);
        } catch (Throwable $e) {
            error_log('Erro ao excluir turma: ' . $e->getMessage());
            Seguranca::flash('danger', 'Não foi possível excluir a turma. Tente novamente.');
            Seguranca::redirecionar('/views/professor/turmas.php?aba=turmas');
        }

        if ($excluiu) {
            Seguranca::flash('success', 'Turma excluída com sucesso.');
        } else {
            Seguranca::flash('danger', 'Turma inválida para o seu usuário.');
        }

        Seguranca::redirecionar('/views/professor/turmas.php?aba=turmas');
    }

    /**
     * Processa a entrada do aluno em uma turma via código (aluno e administrador).
     * Rota de retorno: /views/aluno/turmas.php (PRG + flash).
     */
    public static function processarEntradaPorCodigo(): void
    {
        Auth::exigePerfil('aluno', 'administrador');

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/aluno/turmas.php');
        }

        $codigo = trim((string) ($_POST['codigo'] ?? ''));

        if ($codigo === '') {
            Seguranca::flash('danger', 'Digite o código da turma para continuar.');
            Seguranca::redirecionar('/views/aluno/turmas.php');
        }

        $usuario = Auth::usuarioLogado();
        $idAluno = (int) ($usuario['id'] ?? 0);

        try {
            $resultado = TurmaModel::entrarPorCodigo($idAluno, $codigo);
        } catch (Throwable $e) {
            error_log('Erro ao entrar na turma por código: ' . $e->getMessage());
            Seguranca::flash('danger', 'Erro interno ao entrar na turma. Tente novamente.');
            Seguranca::redirecionar('/views/aluno/turmas.php');
        }

        if ($resultado['status'] === 'ok') {
            $nome = (string) ($resultado['turma']['nome'] ?? '');
            Seguranca::flash('success', $nome !== '' ? 'Você entrou na turma "' . $nome . '" com sucesso.' : 'Você entrou na turma com sucesso.');
        } elseif ($resultado['status'] === 'ja_matriculado') {
            Seguranca::flash('warning', 'Você já participa desta turma.');
        } else {
            Seguranca::flash('danger', 'Código inválido. Confira o código com seu professor e tente novamente.');
        }

        Seguranca::redirecionar('/views/aluno/turmas.php');
    }

    /**
     * Monta o destino de retorno para a aba de alunos.
     */
    private static function destinoAlunos(int $idTurma, string $busca = ''): string
    {
        $destino = '/views/professor/turmas.php?aba=alunos';

        if ($idTurma > 0) {
            $destino .= '&id=' . $idTurma;
        }

        if ($busca !== '') {
            $destino .= '&q=' . rawurlencode($busca);
        }

        return $destino;
    }

    private static function destinoDesafios(int $idTurma): string
    {
        return '/views/professor/turmas.php?aba=desafios&id=' . max(0, $idTurma);
    }

    private static function normalizarDataLiberacao($valor): ?string
    {
        $valor = trim((string) $valor);
        if ($valor === '') {
            return null;
        }

        $data = DateTime::createFromFormat('Y-m-d\\TH:i', $valor);
        $erros = DateTime::getLastErrors();
        if (!$data || ($erros !== false && ($erros['warning_count'] > 0 || $erros['error_count'] > 0))) {
            throw new InvalidArgumentException('Informe uma data de liberação válida.');
        }

        return $data->format('Y-m-d H:i:s');
    }
}
