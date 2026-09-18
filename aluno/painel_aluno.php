<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('aluno');

$pdo = conectar();
$uid = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT a.id, a.matricula, t.nome AS turma
    FROM alunos a
    LEFT JOIN turmas t ON t.id = a.turma_id
    WHERE a.usuario_id = ?
");
$stmt->execute([$uid]);
$aluno = $stmt->fetch();

$media = null;
$freq  = 0;

if ($aluno) {
    $stmt = $pdo->prepare("SELECT AVG(nota) FROM notas WHERE aluno_id = ?");
    $stmt->execute([$aluno['id']]);
    $media = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM frequencia WHERE aluno_id = ? AND presente = 1");
    $stmt->execute([$aluno['id']]);
    $presencas = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM frequencia WHERE aluno_id = ?");
    $stmt->execute([$aluno['id']]);
    $total = (int) $stmt->fetchColumn();

    $freq = $total > 0 ? round(($presencas / $total) * 100, 1) : 0;
}

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:20px;">Painel do Aluno</h2>

<div class="card">
    <h2>Olá, <?= e($_SESSION['usuario_nome']) ?>!</h2>
    <?php if ($aluno): ?>
        <p><strong>Matrícula:</strong> <?= e($aluno['matricula']) ?></p>
        <p><strong>Turma:</strong> <?= e($aluno['turma'] ?? 'Sem turma') ?></p>
    <?php else: ?>
        <p>Seu cadastro de aluno ainda não foi finalizado pelo administrador.</p>
    <?php endif; ?>
</div>

<?php if ($aluno): ?>
<div class="grid">
    <div class="stat">
        <h3>Média Geral</h3>
        <div class="valor">
            <?= $media !== null ? number_format($media, 2, ',', '.') : '—' ?>
        </div>
    </div>
    <div class="stat">
        <h3>Frequência</h3>
        <div class="valor"><?= $freq ?>%</div>
    </div>
</div>
<?php endif; ?>

<?php include __DIR__ . '/../includes/footer.php'; ?>