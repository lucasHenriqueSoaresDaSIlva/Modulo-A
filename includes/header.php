<?php
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/funcoes.php';
$u = usuarioAtual();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CEON – Plataforma Escolar</title>
    <link rel="stylesheet" href="/ceon/assets/css/style.css">
</head>
<body>
<div class="topbar">
    <h1>🎓 CEON</h1>
    <nav>
        <?php if ($u['tipo'] === 'admin'): ?>
            <a href="/ceon/admin/painel_admin.php">Painel</a>
            <a href="/ceon/admin/alunos.php">Alunos</a>
            <a href="/ceon/admin/professores.php">Professores</a>
            <a href="/ceon/admin/turmas.php">Turmas</a>
            <a href="/ceon/admin/disciplinas.php">Disciplinas</a>
        <?php elseif ($u['tipo'] === 'professor'): ?>
            <a href="/ceon/professor/painel_professor.php">Painel</a>
            <a href="/ceon/professor/notas.php">Notas</a>
            <a href="/ceon/professor/frequencia.php">Frequência</a>
        <?php elseif ($u['tipo'] === 'aluno'): ?>
            <a href="/ceon/aluno/painel_aluno.php">Painel</a>
            <a href="/ceon/aluno/notas.php">Minhas Notas</a>
            <a href="/ceon/aluno/frequencia.php">Frequência</a>
        <?php endif; ?>
        <a href="/ceon/logout.php">Sair (<?= e($u['nome']) ?>)</a>
    </nav>
</div>
<div class="container">