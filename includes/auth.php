<?php
// ============================================================
// AUTENTICAÇÃO E SESSÃO
// ============================================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';

function estaLogado(): bool {
    return isset($_SESSION['usuario_id']);
}

function exigirLogin(): void {
    if (!estaLogado()) {
        header('Location: /ceon/login.php');
        exit;
    }
}

function exigirTipo(string ...$tipos): void {
    exigirLogin();
    if (!in_array($_SESSION['usuario_tipo'], $tipos)) {
        http_response_code(403);
        die('Acesso negado.');
    }
}

function usuarioAtual(): array {
    return [
        'id'    => $_SESSION['usuario_id']    ?? null,
        'nome'  => $_SESSION['usuario_nome']  ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'tipo'  => $_SESSION['usuario_tipo']  ?? '',
    ];
}