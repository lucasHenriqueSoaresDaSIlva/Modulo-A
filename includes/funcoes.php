<?php
// ============================================================
// FUNÇÕES AUXILIARES
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** Escapa saída HTML (segurança contra XSS) */
function e(?string $valor): string {
    return htmlspecialchars($valor ?? '', ENT_QUOTES, 'UTF-8');
}

/** Redireciona e encerra */
function redirecionar(string $url): void {
    header("Location: $url");
    exit;
}

/** Grava mensagem flash na sessão */
function flash(string $tipo, string $msg): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

/** Exibe (e limpa) mensagem flash */
function mostrarFlash(): void {
    if (!empty($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        echo "<div class='alert alert-{$f['tipo']}'>" . e($f['msg']) . "</div>";
        unset($_SESSION['flash']);
    }
}

/** Mapa de rotas iniciais por tipo de usuário */
function rotaPainel(string $tipo): string {
    return match ($tipo) {
        'admin'     => '/ceon/admin/painel_admin.php',
        'professor' => '/ceon/professor/painel_professor.php',
        'aluno'     => '/ceon/aluno/painel_aluno.php',
        default     => '/ceon/login.php',
    };
}