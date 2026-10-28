<?php

require_once APP_ROOT . '/nucleo/Database.php';
require_once APP_ROOT . '/modelos/UsuarioModel.php';

/**
 * Resolve a URL da foto de perfil do usuário logado.
 *
 * Enquanto o usuário não tiver foto (ou o arquivo não existir mais),
 * devolve o ícone padrão já usado pelo TechLab EDU. Quando há foto,
 * devolve o endpoint que entrega o arquivo (views/perfil/foto.php) com
 * um "?v=" baseado no modtime, forçando o navegador a buscar a versão
 * nova após uma troca e evitando cache de imagem antiga.
 *
 * As fotos ficam em storage/uploads/perfil/, diretório não exposto
 * diretamente via HTTP (bloqueado pelo roteador do techlab.php); a
 * entrega é feita pelo endpoint, que só devolve a foto do próprio
 * usuário logado.
 */
final class Avatar
{
    public const PADRAO = '../../assets/icons-login/user.png';

    public static function url(): string
    {
        $usuario = $_SESSION['usuario'] ?? null;
        if (!is_array($usuario) || !isset($usuario['id'])) {
            return self::PADRAO;
        }

        return self::urlPara(self::fotoAtual((int) $usuario['id']));
    }

    public static function urlPara(?string $foto): string
    {
        if ($foto === null || $foto === '' || basename($foto) !== $foto) {
            return self::PADRAO;
        }

        $caminho = APP_ROOT . '/storage/uploads/perfil/' . $foto;
        if (!is_file($caminho)) {
            return self::PADRAO;
        }

        return '../../views/perfil/foto.php?v=' . filemtime($caminho);
    }

    /**
     * Mantém a foto do usuário logado em sincronia com o banco dentro da
     * sessão atual, sem exigir novo login: a chave "foto" é lida uma única
     * vez (ou já gravada pelo upload) e reutilizada nas demais telas.
     */
    private static function fotoAtual(int $idUsuario): ?string
    {
        if (array_key_exists('foto', $_SESSION['usuario'])) {
            return is_string($_SESSION['usuario']['foto']) && $_SESSION['usuario']['foto'] !== ''
                ? $_SESSION['usuario']['foto']
                : null;
        }

        try {
            $foto = UsuarioModel::buscarFotoPerfil($idUsuario);
        } catch (Throwable $e) {
            error_log('Erro ao buscar foto de perfil: ' . $e->getMessage());
            return null;
        }

        $_SESSION['usuario']['foto'] = $foto;

        return $foto;
    }
}
