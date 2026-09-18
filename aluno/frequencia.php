<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('aluno');

$pdo = conectar();
$uid = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("SELECT id FROM alunos WHERE usuario_id = ?");
$stmt->execute([$uid]);
$aluno = $stmt->fetch();

$registros = [];
$porDisciplina = [];

if ($aluno) {
    $stmt = $pdo->prepare("
        SELECT d.nome AS disciplina, f.data_aula, f.presente
        FROM frequencia f
        JOIN disciplinas d ON d.id = f.disciplina_id
        WHERE f.aluno_id = ?
        ORDER BY f.data_aula DESC
    ");
    $stmt->execute([$aluno['id']]);
    $registros = $stmt->fetchAll();

    $stmt = $pdo->prepare("
        SELECT d.nome AS disciplina,
               SUM(f.presente) AS presencas,
               COUNT(*) AS total
        FROM frequencia f
        JOIN disciplinas d ON d.id = f.disciplina_id
        WHERE f.aluno_id = ?
        GROUP BY d.id, d.nome
        ORDER BY d.nome
    ");
    $stmt->execute([$aluno['id']]);
    $porDisciplina = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Minha Frequência</h2>

<?php if (!$aluno): ?>
    <div class="card"><p>Cadastro de aluno não encontrado.</p></div>
<?php else: ?>

<div class="card">
    <h2>Resumo por Disciplina</h2>
    <?php if (empty($porDisciplina)): ?>
        <p>Nenhum registro de frequência ainda.</p>
    <?php else: ?>
        <table>
            <tr><th>Disciplina</th><th>Presenças</th><th>Total</th><th>%</th></tr>
            <?php foreach ($porDisciplina as $p): ?>
                <?php $pct = $p['total'] > 0 ? round(($p['presencas'] / $p['total']) * 100, 1) : 0; ?>
                <tr>
                    <td><?= e($p['disciplina']) ?></td>
                    <td><?= $p['presencas'] ?></td>
                    <td><?= $p['total'] ?></td>
                    <td><?= $pct ?>%</td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Histórico Detalhado</h2>
    <?php if (empty($registros)): ?>
        <p>Sem registros.</p>
    <?php else: ?>
        <table>
            <tr><th>Data</th><th>Disciplina</th><th>Status</th></tr>
            <?php foreach ($registros as $r): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($r['data_aula'])) ?></td>
                    <td><?= e($r['disciplina']) ?></td>
                    <td><?= $r['presente'] ? '✅ Presente' : '❌ Ausente' ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
    <?php endif; ?>
</div>

<?php endif; ?>
<?php include __DIR__ . '/../includes/footer.php'; ?>