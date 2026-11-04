<?php

require_once APP_ROOT . '/nucleo/Seguranca.php';
require_once APP_ROOT . '/nucleo/Auth.php';
require_once APP_ROOT . '/modelos/DesafioModel.php';

final class DesafioController
{
    /**
     * Processa a criação de um novo desafio (apenas professores/administradores).
     */
    public static function processarCriacao(): void
    {
        Auth::exigePerfil('professor', 'administrador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Seguranca::redirecionar('/views/desafios/novo.php');
        }

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/desafios/novo.php');
        }

        $titulo = trim((string) ($_POST['titulo'] ?? ''));
        $descricao = trim((string) ($_POST['descricao'] ?? ''));
        $objetivo = trim((string) ($_POST['objetivo'] ?? ''));
        $categoria = trim((string) ($_POST['categoria'] ?? ''));
        $dificuldade = (string) ($_POST['dificuldade'] ?? 'FACIL');
        $pontuacao = (int) ($_POST['pontuacao_maxima'] ?? 0);
        $nivelMinimo = (int) ($_POST['nivel_minimo'] ?? 1);
        $visibilidade = strtoupper(trim((string) ($_POST['visibilidade'] ?? '')));
        $turmaIds = is_array($_POST['turma_ids'] ?? null) ? $_POST['turma_ids'] : [];

        if ($titulo === '' || $descricao === '') {
            Seguranca::flash('danger', 'Informe pelo menos o título e a descrição do desafio.');
            Seguranca::redirecionar('/views/desafios/novo.php');
        }

        try {
            $usuario = Auth::usuarioLogado() ?: [];
            DesafioModel::criar([
                'titulo' => $titulo,
                'descricao' => $descricao,
                'objetivo' => $objetivo !== '' ? $objetivo : null,
                'categoria' => $categoria !== '' ? $categoria : null,
                'dificuldade' => $dificuldade,
                'pontuacao_maxima' => $pontuacao,
                'nivel_minimo' => $nivelMinimo,
                'visibilidade' => $visibilidade,
                'id_criador' => (int) ($usuario['id'] ?? 0),
                'turma_ids' => $turmaIds,
            ]);

            Seguranca::flash('success', 'Desafio criado com sucesso.');
            Seguranca::redirecionar('/views/desafios/index.php');
        } catch (Throwable $e) {
            error_log('Erro ao criar desafio: ' . $e->getMessage());
            Seguranca::flash('danger', $e instanceof InvalidArgumentException ? $e->getMessage() : 'Erro interno ao criar o desafio. Tente novamente.');
            Seguranca::redirecionar('/views/desafios/novo.php');
        }
    }

    public static function processarEdicao(int $idDesafio): void
    {
        Auth::exigePerfil('professor', 'administrador');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || $idDesafio <= 0) {
            Seguranca::redirecionar('/views/desafios/index.php');
        }

        if (!Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar('/views/desafios/novo.php?id=' . $idDesafio);
        }

        $usuario = Auth::usuarioLogado() ?: [];
        $idProfessor = (int) ($usuario['id'] ?? 0);
        $desafio = DesafioModel::buscarDoProfessor($idDesafio, $idProfessor);
        if (!$desafio) {
            Seguranca::flash('danger', 'Desafio não encontrado ou não pertence ao seu usuário.');
            Seguranca::redirecionar('/views/desafios/index.php');
        }

        $dados = [
            'titulo' => trim((string) ($_POST['titulo'] ?? '')),
            'descricao' => trim((string) ($_POST['descricao'] ?? '')),
            'objetivo' => trim((string) ($_POST['objetivo'] ?? '')),
            'categoria' => trim((string) ($_POST['categoria'] ?? '')),
            'dificuldade' => (string) ($_POST['dificuldade'] ?? 'FACIL'),
            'pontuacao_maxima' => (int) ($_POST['pontuacao_maxima'] ?? 0),
            'nivel_minimo' => (int) ($_POST['nivel_minimo'] ?? 1),
            'visibilidade' => strtoupper(trim((string) ($_POST['visibilidade'] ?? ''))),
            'turma_ids' => is_array($_POST['turma_ids'] ?? null) ? $_POST['turma_ids'] : [],
        ];

        if ($dados['titulo'] === '' || $dados['descricao'] === '') {
            Seguranca::flash('danger', 'Informe pelo menos o título e a descrição do desafio.');
            Seguranca::redirecionar('/views/desafios/novo.php?id=' . $idDesafio);
        }

        try {
            DesafioModel::atualizar($idDesafio, $idProfessor, $dados);
            Seguranca::flash('success', 'Desafio atualizado com sucesso.');
            Seguranca::redirecionar('/views/desafios/index.php');
        } catch (Throwable $e) {
            error_log('Erro ao editar desafio: ' . $e->getMessage());
            Seguranca::flash('danger', $e instanceof InvalidArgumentException ? $e->getMessage() : 'Erro interno ao editar o desafio. Tente novamente.');
            Seguranca::redirecionar('/views/desafios/novo.php?id=' . $idDesafio);
        }
    }
}
