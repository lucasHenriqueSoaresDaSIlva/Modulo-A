<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('admin');

$pdo = conectar();
$totalAlunos      = $pdo->query("SELECT COUNT(*) FROM alunos")->fetchColumn();
$totalProfessores = $pdo->query("SELECT COUNT(*) FROM professores")->fetchColumn();
$totalTurmas      = $pdo->query("SELECT COUNT(*) FROM turmas")->fetchColumn();
$totalDisciplinas = $pdo->query("SELECT COUNT(*) FROM disciplinas")->fetchColumn();

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:20px;">Painel Administrativo</h2>

<div class="grid">
    <div class="stat"><h3>Alunos</h3><div class="valor"><?= $totalAlunos ?></div></div>
    <div class="stat"><h3>Professores</h3><div class="valor"><?= $totalProfessores ?></div></div>
    <div class="stat"><h3>Turmas</h3><div class="valor"><?= $totalTurmas ?></div></div>
    <div class="stat"><h3>Disciplinas</h3><div class="valor"><?= $totalDisciplinas ?></div></div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>