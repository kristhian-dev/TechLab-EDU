<?php

/**
 * TechLab EDU - inicializador do servidor de desenvolvimento.
 *
 * Uso:
 *   php techlab.php        -> inicia em http://127.0.0.1:8000
 *   php techlab.php 8001   -> inicia em http://127.0.0.1:8001 (porta alternativa)
 *   Ctrl+C                 -> encerra o servidor
 *
 * O mesmo arquivo atua como roteador seguro do servidor embutido (SAPI
 * "cli-server"): serve apenas /views/* e /assets/*, encaminha as rotas da
 * aplicacao para o index.php/roteador.php existentes e responde 404 para
 * todo o resto (.env, storage/, *.sql, configuracoes/, nucleo/, modelos/,
 * controllers/, banco/, etc.), evitando as exposicoes apontadas em
 * RELATORIO_TESTES_TECHLAB_EDU.md (DEF-01 a DEF-03).
 *
 * Nao altera configuracoes globais de Apache, PHP ou MySQL e nao usa shell.
 */

// ---------------------------------------------------------------------------
// Modo roteador: executado pelo servidor embutido a cada requisicao HTTP.
// ---------------------------------------------------------------------------
if (PHP_SAPI === 'cli-server') {
    $caminho = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $caminho = is_string($caminho) && $caminho !== '' ? urldecode($caminho) : '/';

    // Bloqueia traversal e bytes nulos antes de tocar no sistema de arquivos.
    if (str_contains($caminho, '..') || str_contains($caminho, "\0")) {
        http_response_code(400);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Requisicao invalida.';
        exit;
    }

    // Publicos por arquitetura: paginas/views e estaticos (css/js/imagens).
    if (str_starts_with($caminho, '/views/') || str_starts_with($caminho, '/assets/')) {
        $raiz = realpath(__DIR__);
        $arquivo = realpath(__DIR__ . $caminho);

        if (
            $arquivo === false
            || !str_starts_with($arquivo, $raiz . DIRECTORY_SEPARATOR)
            || !is_file($arquivo)
        ) {
            http_response_code(404);
            header('Content-Type: text/plain; charset=UTF-8');
            echo 'Nao encontrado.';
            exit;
        }

        // Arquivo publico existente: deixa o servidor embutido servir/executar
        // (preserva o roteamento interno: views PHP continuam sendo entrypoints).
        return false;
    }

    // Rotas conhecidas da aplicacao: reaproveita index.php -> roteador.php.
    // Inclui /logout.php porque as views linkam ../../logout.php a partir
    // de /views/* (o arquivo existe na raiz, mas o acesso direto e bloqueado).
    $rotasDaAplicacao = ['/', '/index.php', '/login', '/cadastro', '/logout', '/logout.php', '/dashboard'];
    if (in_array($caminho, $rotasDaAplicacao, true)) {
        require __DIR__ . '/index.php';
        exit;
    }

    // Tudo o mais e interno ao projeto e nao deve ser servido via HTTP.
    http_response_code(404);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'Nao encontrado.';
    exit;
}

// ---------------------------------------------------------------------------
// Modo CLI: daqui so e possivel chegar digitando `php techlab.php`.
// ---------------------------------------------------------------------------
if (PHP_SAPI !== 'cli') {
    // Ex.: arquivo aberto sob outro SAPI (cgi/fpm/apache) - nao faz nada util.
    http_response_code(404);
    exit;
}

$host = '127.0.0.1'; // somente loopback; o servidor nunca e exposto a rede local
$porta = 8000;

if (isset($argv[1])) {
    if (!ctype_digit($argv[1]) || (int) $argv[1] < 1 || (int) $argv[1] > 65535) {
        fwrite(STDERR, "Porta invalida: use um numero entre 1 e 65535 (ex.: php techlab.php 8001).\n");
        exit(1);
    }
    $porta = (int) $argv[1];
}

if (PHP_VERSION_ID < 80000) {
    fwrite(STDERR, "E necessario PHP 8.0 ou superior (versao atual: " . PHP_VERSION . ").\n");
    exit(1);
}

// O diretorio proprio do arquivo vira diretorio de trabalho, de onde o
// comando for digitado - o docroot do servidor sempre sera a raiz do projeto.
chdir(__DIR__);

$endereco = $host . ':' . $porta;

// Verifica se a porta esta livre antes de anunciar o endereco.
$errno = 0;
$errstr = '';
$teste = @stream_socket_server('tcp://' . $endereco, $errno, $errstr);
if ($teste === false) {
    fwrite(STDERR, "Nao foi possivel usar a porta $porta em $host ($errstr).\n");
    fwrite(STDERR, "Encerre o processo que ja usa essa porta ou escolha outra: php techlab.php <porta>\n");
    exit(1);
}
fclose($teste);

// Comando em forma de array: o PHP cria o processo filho sem passar por shell.
$comando = [PHP_BINARY, '-S', $endereco, __FILE__];

// Herda o terminal: logs das requisicoes aparecem aqui e o Ctrl+C tambem
// chega ao filho, pois ele permanece no mesmo grupo de processos.
$descritores = [
    0 => STDIN,
    1 => STDOUT,
    2 => STDERR,
];

$processo = proc_open($comando, $descritores, $pipes, __DIR__);

if (!is_resource($processo)) {
    fwrite(STDERR, "Falha ao criar o processo do servidor PHP.\n");
    exit(1);
}

$encerramentoSolicitado = false;

// Ctrl+C / kill: encerra o filho com elegancia e sai sem deixar orfaos.
// Sem a extensao pcntl, o comportamento padrao do terminal ja encerra o
// grupo de processos (pai e filho) normalmente.
if (function_exists('pcntl_async_signals')) {
    pcntl_async_signals(true);
    $encerrar = function () use ($processo, &$encerramentoSolicitado) {
        if ($encerramentoSolicitado) {
            return;
        }
        $encerramentoSolicitado = true;
        fwrite(STDOUT, "\nEncerrando o servidor TechLab EDU...\n");
        proc_terminate($processo); // envia SIGTERM ao filho
    };
    pcntl_signal(SIGINT, $encerrar);
    pcntl_signal(SIGTERM, $encerrar);
}

echo "TechLab EDU - servidor de desenvolvimento\n";
echo "----------------------------------------\n";
echo "Endereco local: http://$endereco/\n";
echo "Diretorio:      " . __DIR__ . "\n";
echo "Para encerrar:  pressione Ctrl+C\n";
echo "----------------------------------------\n";
echo "Logs do servidor:\n";

// Aguarda o filho consultando o estado 1x/s: nao ha ciclo que trave o
// encerramento - a morte do filho (ou o sinal acima) finaliza o loop.
do {
    sleep(1);
    $estado = proc_get_status($processo);
} while ($estado['running']);

$codigoSaida = $estado['exitcode'];
proc_close($processo);

if ($encerramentoSolicitado || $codigoSaida === 0) {
    echo "Servidor TechLab EDU encerrado.\n";
    exit(0);
}

fwrite(STDERR, "O servidor PHP terminou inesperadamente (codigo $codigoSaida).\n");
exit($codigoSaida > 0 ? $codigoSaida : 1);
