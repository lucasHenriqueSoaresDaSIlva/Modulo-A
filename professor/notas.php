<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('professor');

$pdo = conectar();

// Lançar nota
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO notas (aluno_id, disciplina_id, bimestre, nota) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_POST['aluno_id'],
        $_POST['disciplina_id'],
        $_POST['bimestre'],
        $_POST['nota']
    ]);
    flash('success', 'Nota lançada com sucesso!');
    redirecionar('/ceon/professor/notas.php');
}

// Excluir nota
if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM notas WHERE id = ?")->execute([$_GET['excluir']]);
    flash('success', 'Nota removida.');
    redirecionar('/ceon/professor/notas.php');
}

$alunos = $pdo->query("
    SELECT a.id, u.nome, a.matricula
    FROM alunos a
    JOIN usuarios u ON u.id = a.usuario_id
    ORDER BY u.nome
")->fetchAll();

$disciplinas = $pdo->query("SELECT * FROM disciplinas ORDER BY nome")->fetchAll();

$notas = $pdo->query("
    SELECT n.id, u.nome AS aluno, d.nome AS disciplina, n.bimestre, n.nota
    FROM notas n
    JOIN alunos a ON a.id = n.aluno_id
    JOIN usuarios u ON u.id = a.usuario_id
    JOIN disciplinas d ON d.id = n.disciplina_id
    ORDER BY n.lancado_em DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Lançamento de Notas</h2>

<div class="card">
    <h2>Nova Nota</h2>
    <form method="POST">
        <div class="grid">
            <div>
                <label>Aluno</label>
                <select name="aluno_id" required>
                    <option value="">-- Selecione --</option>
                    <?php foreach ($alunos as $a): ?>
                        <option value="<?= $a['id'] ?>">
                            <?= e($a['nome']) ?> (<?= e($a['matricula']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Disciplina</label>
                <select name="disciplina_id" required>
                    <option value="">-- Selecione --</option>
                    <?php foreach ($disciplinas as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= e($d['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label>Bimestre</label>
                <select name="bimestre" required>
                    <option value="1">1º Bimestre</option>
                    <option value="2">2º Bimestre</option>
                    <option value="3">3º Bimestre</option>
                    <option value="4">4º Bimestre</option>
                </select>
            </div>
            <div>
                <label>Nota (0 a 10)</label>
                <input type="number" name="nota" step="0.01" min="0" max="10" required>
            </div>
        </div>
        <br><button type="submit">Lançar</button>
    </form>
</div>

<div class="card">
    <h2>Notas Lançadas</h2>
    <table>
        <tr><th>Aluno</th><th>Disciplina</th><th>Bimestre</th><th>Nota</th><th>Ações</th></tr>
        <?php foreach ($notas as $n): ?>
            <tr>
                <td><?= e($n['aluno']) ?></td>
                <td><?= e($n['disciplina']) ?></td>
                <td><?= $n['bimestre'] ?>º</td>
                <td><?= number_format($n['nota'], 2, ',', '.') ?></td>
                <td>
                    <a class="btn btn-danger" href="?excluir=<?= $n['id'] ?>"
                       data-confirmar="Excluir esta nota?">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>