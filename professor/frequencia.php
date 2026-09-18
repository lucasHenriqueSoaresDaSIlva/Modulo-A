<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('professor');

$pdo = conectar();

// Registrar frequência
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("
        INSERT INTO frequencia (aluno_id, disciplina_id, data_aula, presente)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([
        $_POST['aluno_id'],
        $_POST['disciplina_id'],
        $_POST['data_aula'],
        isset($_POST['presente']) ? 1 : 0
    ]);
    flash('success', 'Frequência registrada!');
    redirecionar('/ceon/professor/frequencia.php');
}

// Excluir registro
if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM frequencia WHERE id = ?")->execute([$_GET['excluir']]);
    flash('success', 'Registro removido.');
    redirecionar('/ceon/professor/frequencia.php');
}

$alunos = $pdo->query("
    SELECT a.id, u.nome, a.matricula
    FROM alunos a
    JOIN usuarios u ON u.id = a.usuario_id
    ORDER BY u.nome
")->fetchAll();

$disciplinas = $pdo->query("SELECT * FROM disciplinas ORDER BY nome")->fetchAll();

$registros = $pdo->query("
    SELECT f.id, u.nome AS aluno, d.nome AS disciplina, f.data_aula, f.presente
    FROM frequencia f
    JOIN alunos a ON a.id = f.aluno_id
    JOIN usuarios u ON u.id = a.usuario_id
    JOIN disciplinas d ON d.id = f.disciplina_id
    ORDER BY f.data_aula DESC
")->fetchAll();

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Registro de Frequência</h2>

<div class="card">
    <h2>Novo Registro</h2>
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
                <label>Data da Aula</label>
                <input type="date" name="data_aula" value="<?= date('Y-m-d') ?>" required>
            </div>
            <div>
                <label>Presença</label>
                <label style="font-weight:400;">
                    <input type="checkbox" name="presente" value="1" checked
                           style="width:auto; margin-right:6px;">
                    Presente
                </label>
            </div>
        </div>
        <br><button type="submit">Registrar</button>
    </form>
</div>

<div class="card">
    <h2>Últimos Registros</h2>
    <table>
        <tr><th>Aluno</th><th>Disciplina</th><th>Data</th><th>Status</th><th>Ações</th></tr>
        <?php foreach ($registros as $r): ?>
            <tr>
                <td><?= e($r['aluno']) ?></td>
                <td><?= e($r['disciplina']) ?></td>
                <td><?= date('d/m/Y', strtotime($r['data_aula'])) ?></td>
                <td><?= $r['presente'] ? '✅ Presente' : '❌ Ausente' ?></td>
                <td>
                    <a class="btn btn-danger" href="?excluir=<?= $r['id'] ?>"
                       data-confirmar="Excluir este registro?">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>