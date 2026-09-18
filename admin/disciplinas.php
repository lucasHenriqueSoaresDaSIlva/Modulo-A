<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('admin');

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO disciplinas (nome, carga_horaria) VALUES (?, ?)");
    $stmt->execute([$_POST['nome'], $_POST['carga_horaria']]);
    flash('success', 'Disciplina criada!');
    redirecionar('/ceon/admin/disciplinas.php');
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM disciplinas WHERE id = ?")->execute([$_GET['excluir']]);
    flash('success', 'Disciplina removida.');
    redirecionar('/ceon/admin/disciplinas.php');
}

$disciplinas = $pdo->query("SELECT * FROM disciplinas ORDER BY nome")->fetchAll();

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Disciplinas</h2>

<div class="card">
    <h2>Nova Disciplina</h2>
    <form method="POST">
        <div class="grid">
            <div><label>Nome</label><input type="text" name="nome" required></div>
            <div><label>Carga Horária (h)</label><input type="number" name="carga_horaria" value="80"></div>
        </div>
        <br><button type="submit">Criar</button>
    </form>
</div>

<div class="card">
    <h2>Lista</h2>
    <table>
        <tr><th>Nome</th><th>Carga Horária</th><th>Ações</th></tr>
        <?php foreach ($disciplinas as $d): ?>
            <tr>
                <td><?= e($d['nome']) ?></td>
                <td><?= $d['carga_horaria'] ?>h</td>
                <td>
                    <a class="btn btn-danger" href="?excluir=<?= $d['id'] ?>"
                       data-confirmar="Excluir esta disciplina?">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>