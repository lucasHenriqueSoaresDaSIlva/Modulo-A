<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('aluno');

$pdo = conectar();
$uid = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT id FROM alunos WHERE usuario_id = ?");
$stmt->execute([$uid]);
$aluno = $stmt->fetch();

$notas = [];
$mediasPorDisciplina = [];

if ($aluno) {
    $stmt = $pdo->prepare("
        SELECT d.nome AS disciplina, n.bimestre, n.nota
        FROM notas n
        JOIN disciplinas d ON d.id = n.disciplina_id
        WHERE n.aluno_id = ?
        ORDER BY d.nome, n.bimestre
    ");
    $stmt->execute([$aluno['id']]);
    $notas = $stmt->fetchAll();

    // Média por disciplina
    $stmt = $pdo->prepare("
        SELECT d.nome AS disciplina, AVG(n.nota) AS media
        FROM notas n
        JOIN disciplinas d ON d.id = n.disciplina_id
        WHERE n.aluno_id = ?
        GROUP BY d.id, d.nome
        ORDER BY d.nome
    ");
    $stmt->execute([$aluno['id']]);
    $mediasPorDisciplina = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Minhas Notas</h2>

<?php if (!$aluno): ?>
    <div class="card"><p>Cadastro de aluno não encontrado.</p></div>
<?php else: ?>

<div class="card">
    <h2>Notas por Bimestre</h2>
    <?php if (empty($notas)): ?>
        <p>Nenhuma nota lançada ainda.</p>
    <?php else: ?>
        <table>
            <tr><th>Disciplina</th><th>Bimestre</th><th>Nota</th></tr>
            <?php foreach ($notas as $n): ?>
                <tr>
                    <td><?= e($n['disciplina']) ?></td>
                    <td><?= $n['bimestre'] ?>º</td>
                    <td><?= number_format($n['nota'], 2, ',', '.') ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Média por Disciplina</h2>
    <?php if (empty($mediasPorDisciplina)): ?>
        <p>Sem dados suficientes.</p>
    <?php else: ?>
        <table>
            <tr><th>Disciplina</th><th>Média</th><th>Situação</th></tr>
            <?php foreach ($mediasPorDisciplina as $m): ?>
                <?php $situacao = $m['media'] >= 7 ? '✅ Aprovado' : ($m['media'] >= 5 ? '⚠️ Recuperação' : '❌ Reprovado'); ?>
                <tr>
                    <td><?= e($m['disciplina']) ?></td>
                    <td><?= number_format($m['media'], 2, ',', '.') ?></td>
                    <td><?= $situacao ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>