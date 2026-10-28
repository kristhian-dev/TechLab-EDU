<?php

final class DashboardController
{
    public static function redirectToDashboard(): void
    {
        $usuario = $_SESSION['usuario'] ?? null;

        if (!$usuario) {
            Seguranca::flash('warning', 'Faça login para continuar.');
            Seguranca::redirecionar('/views/auth/login.php');
        }

        $perfil = $usuario['perfil'] ?? 'aluno';
        $destino = '/views/aluno/dashboard.php';

        if ($perfil === 'professor') {
            $destino = '/views/professor/dashboard.php';
        }

        $dadosProgresso = self::buscarDadosProgresso($usuario['id']);
        
        Seguranca::redirecionar($destino . '?' . http_build_query($dadosProgresso));
    }

    public static function buscarDadosProgresso(int $idUsuario): array
    {
        $pdo = Database::pdo();
        $resultado = [
            'montagens_concluidas' => 0,
            'desafios_resolvidos' => 0,
            'conquistas' => 0,
        ];

        try {
            // A. Montagens concluídas
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM builds WHERE id_aluno = :id AND status = "COMPLETA"');
            $stmt->execute(['id' => $idUsuario]);
            $montagens = $stmt->fetch();
            $resultado['montagens_concluidas'] = (int) ($montagens['total'] ?? 0);

            // B. Desafios resolvidos
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM aluno_desafios WHERE id_aluno = :id AND status = "CONCLUIDO"');
            $stmt->execute(['id' => $idUsuario]);
            $desafios = $stmt->fetch();
            $resultado['desafios_resolvidos'] = (int) ($desafios['total'] ?? 0);

            // C. Conquistas (certificados)
            $stmt = $pdo->prepare('SELECT COUNT(*) AS total FROM certificados JOIN aluno_desafios ON certificados.id_aluno_desafio = aluno_desafios.id_aluno_desafio WHERE aluno_desafios.id_aluno = :id');
            $stmt->execute(['id' => $idUsuario]);
            $conquistas = $stmt->fetch();
            $resultado['conquistas'] = (int) ($conquistas['total'] ?? 0);

        } catch (Throwable $e) {
            error_log('Erro ao buscar dados do dashboard: ' . $e->getMessage());
            // Mantém valores zerados como fallback
        }

        return $resultado;
    }
}
