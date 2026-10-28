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

        if ($titulo === '' || $descricao === '') {
            Seguranca::flash('danger', 'Informe pelo menos o título e a descrição do desafio.');
            Seguranca::redirecionar('/views/desafios/novo.php');
        }

        try {
            DesafioModel::criar([
                'titulo' => $titulo,
                'descricao' => $descricao,
                'objetivo' => $objetivo !== '' ? $objetivo : null,
                'categoria' => $categoria !== '' ? $categoria : null,
                'dificuldade' => $dificuldade,
                'pontuacao_maxima' => $pontuacao,
                'nivel_minimo' => $nivelMinimo,
            ]);

            Seguranca::flash('success', 'Desafio criado com sucesso.');
            Seguranca::redirecionar('/views/desafios/index.php');
        } catch (Throwable $e) {
            error_log('Erro ao criar desafio: ' . $e->getMessage());
            Seguranca::flash('danger', 'Erro interno ao criar o desafio. Tente novamente.');
            Seguranca::redirecionar('/views/desafios/novo.php');
        }
    }
}
