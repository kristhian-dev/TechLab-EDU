<?php

require_once APP_ROOT . '/nucleo/Seguranca.php';
require_once APP_ROOT . '/nucleo/Auth.php';
require_once APP_ROOT . '/nucleo/Avatar.php';
require_once APP_ROOT . '/modelos/UsuarioModel.php';

/**
 * Fluxo de alteração da foto de perfil.
 *
 * Segue o mesmo padrão dos demais controllers: métodos estáticos chamados
 * pelas views (entrypoints), com CSRF, redirecionamento no fim (PRG) e
 * mensagens de erro amigáveis sem expor caminhos internos do servidor.
 */
final class PerfilController
{
    /** Limite de 5 MB por imagem. */
    public const TAMANHO_MAXIMO = 5242880;

    /** Extensão aceita => MIME real esperado (validado no servidor). */
    private const FORMATOS = [
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
    ];

    public static function atualizarFoto(): void
    {
        Auth::exigeLogin();

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
            Seguranca::redirecionar('/views/perfil/perfil.php');
        }

        $usuario = Auth::usuarioLogado();
        $idUsuario = (int) ($usuario['id'] ?? 0);
        $destino = '/views/perfil/perfil.php';

        if ($idUsuario <= 0 || !Seguranca::validarTokenCsrf()) {
            Seguranca::flash('danger', 'Sessão inválida. Recarregue a página e tente novamente.');
            Seguranca::redirecionar($destino);
        }

        if (!isset($_FILES['foto_perfil']) || !is_array($_FILES['foto_perfil'])) {
            Seguranca::flash('danger', 'Nenhuma imagem foi enviada. Escolha uma foto e tente novamente.');
            Seguranca::redirecionar($destino);
        }

        $arquivo = $_FILES['foto_perfil'];

        $erroUpload = (int) ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($erroUpload !== UPLOAD_ERR_OK) {
            Seguranca::flash('danger', self::mensagemErroUpload($erroUpload));
            Seguranca::redirecionar($destino);
        }

        $tamanho = (int) ($arquivo['size'] ?? 0);
        if ($tamanho <= 0) {
            Seguranca::flash('danger', 'O arquivo enviado está vazio. Tente novamente.');
            Seguranca::redirecionar($destino);
        }

        if ($tamanho > self::TAMANHO_MAXIMO) {
            Seguranca::flash('danger', 'A imagem excede o limite de 5 MB.');
            Seguranca::redirecionar($destino);
        }

        $caminhoTemporario = (string) ($arquivo['tmp_name'] ?? '');
        if ($caminhoTemporario === '' || !is_uploaded_file($caminhoTemporario)) {
            Seguranca::flash('danger', 'Não foi possível receber o arquivo enviado. Tente novamente.');
            Seguranca::redirecionar($destino);
        }

        // Nunca confia no nome ou no tipo declarado pelo navegador: valida o
        // conteúdo real com getimagesize e confere com fileinfo.
        $info = @getimagesize($caminhoTemporario);
        if ($info === false || !isset($info['mime'])) {
            Seguranca::flash('danger', 'O arquivo enviado não é uma imagem válida.');
            Seguranca::redirecionar($destino);
        }

        $mimeReal = (string) $info['mime'];
        $extensao = array_search($mimeReal, self::FORMATOS, true);
        if ($extensao === false) {
            Seguranca::flash('danger', 'Formato não suportado. Use JPG, JPEG, PNG ou WebP.');
            Seguranca::redirecionar($destino);
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mimeFileinfo = (string) finfo_file($finfo, $caminhoTemporario);
            finfo_close($finfo);

            if ($mimeFileinfo !== $mimeReal) {
                Seguranca::flash('danger', 'O arquivo enviado não corresponde a uma imagem compatível.');
                Seguranca::redirecionar($destino);
            }
        }

        // Continua no próximo bloco...
        self::finalizarUpload($caminhoTemporario, $extensao, $idUsuario);
    }

    /**
     * Grava o arquivo já validado em disco, atualiza o banco e limpa a
     * foto anterior. Chamado por atualizarFoto() após todas as checagens.
     */
    private static function finalizarUpload(string $caminhoTemporario, string $extensao, int $idUsuario): void
    {
        $destino = '/views/perfil/perfil.php';

        $dimensoes = @getimagesize($caminhoTemporario);
        $largura = (int) ($dimensoes[0] ?? 0);
        $altura = (int) ($dimensoes[1] ?? 0);
        if ($largura < 1 || $altura < 1 || $largura > 10000 || $altura > 10000) {
            Seguranca::flash('danger', 'As dimensões da imagem são inválidas.');
            Seguranca::redirecionar($destino);
        }

        $diretorio = APP_ROOT . '/storage/uploads/perfil';
        if (!is_dir($diretorio) && !@mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            error_log('Perfil: não foi possível criar o diretório de uploads de perfil.');
            Seguranca::flash('danger', 'Não foi possível preparar o armazenamento da imagem. Tente mais tarde.');
            Seguranca::redirecionar($destino);
        }

        if (!is_writable($diretorio)) {
            error_log('Perfil: diretório de uploads de perfil sem permissão de escrita.');
            Seguranca::flash('danger', 'O servidor não tem permissão para salvar a imagem. Contate o suporte.');
            Seguranca::redirecionar($destino);
        }

        // Nome único gerado no servidor: evita colisão/sobrescrita entre
        // usuários e descarta por completo o nome original do arquivo.
        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensao;
        $destinoArquivo = $diretorio . DIRECTORY_SEPARATOR . $nomeArquivo;

        if (!@move_uploaded_file($caminhoTemporario, $destinoArquivo)) {
            error_log('Perfil: falha ao gravar a foto de perfil em disco.');
            Seguranca::flash('danger', 'Não foi possível salvar a imagem. Tente novamente.');
            Seguranca::redirecionar($destino);
        }

        @chmod($destinoArquivo, 0644);

        // Foto anterior do PRÓPRIO usuário autenticado: o UPDATE do banco
        // também só alcança o id da sessão, logo é impossível mexer em foto
        // de outro usuário.
        $fotoAnterior = UsuarioModel::buscarFotoPerfil($idUsuario);

        try {
            UsuarioModel::atualizarFotoPerfil($idUsuario, $nomeArquivo);
        } catch (Throwable $e) {
            error_log('Perfil: erro ao gravar foto no banco: ' . $e->getMessage());
            @unlink($destinoArquivo);
            Seguranca::flash('danger', 'Não foi possível atualizar sua foto de perfil. Tente novamente.');
            Seguranca::redirecionar($destino);
        }

        // Sessão passa a apontar para a foto nova sem exigir novo login.
        $_SESSION['usuario']['foto'] = $nomeArquivo;

        // Remove a foto antiga somente depois de a nova estar salva, e
        // somente se o nome vier do banco do próprio usuário e o arquivo
        // esteja no diretório de uploads.
        self::removerFotoAntiga($fotoAnterior, $nomeArquivo);

        Seguranca::flash('success', 'Foto de perfil atualizada com sucesso.');
        Seguranca::redirecionar($destino);
    }

    private static function removerFotoAntiga(?string $fotoAnterior, string $fotoAtual): void
    {
        if ($fotoAnterior === null || $fotoAnterior === '' || $fotoAnterior === $fotoAtual) {
            return;
        }

        if (basename($fotoAnterior) !== $fotoAnterior) {
            return;
        }

        $caminho = APP_ROOT . '/storage/uploads/perfil/' . $fotoAnterior;
        if (is_file($caminho)) {
            @unlink($caminho);
        }
    }

    private static function mensagemErroUpload(int $erro): string
    {
        if (in_array($erro, [UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE], true)) {
            return 'A imagem excede o limite de 5 MB.';
        }

        if ($erro === UPLOAD_ERR_PARTIAL) {
            return 'O envio da imagem foi interrompido. Tente novamente.';
        }

        if ($erro === UPLOAD_ERR_NO_FILE) {
            return 'Nenhuma imagem foi selecionada. Escolha uma foto e tente novamente.';
        }

        return 'Não foi possível enviar a imagem. Tente novamente.';
    }
}
