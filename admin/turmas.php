<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('admin');

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = $pdo->prepare("INSERT INTO turmas (nome, ano, turno) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['nome'], $_POST['ano'], $_POST['turno']]);
    flash('success', 'Turma criada!');
    redirecionar('/ceon/admin/turmas.php');
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM turmas WHERE id = ?")->execute([$_GET['excluir']]);
    flash('success', 'Turma removida.');
    redirecionar('/ceon/admin/turmas.php');
}

$turmas = $pdo->query("SELECT * FROM turmas ORDER BY ano DESC, nome")->fetchAll();

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Turmas</h2>

<div class="card">
    <h2>Nova Turma</h2>
    <form method="POST">
        <div class="grid">
            <div><label>Nome</label><input type="text" name="nome" required></div>
            <div><label>Ano</label><input type="number" name="ano" value="<?= date('Y') ?>" required></div>
            <div>
                <label>Turno</label>
                <select name="turno" required>
                    <option value="manha">Manhã</option>
                    <option value="tarde">Tarde</option>
                    <option value="noite">Noite</option>
                </select>
            </div>
        </div>
        <br><button type="submit">Criar</button>
    </form>
</div>

<div class="card">
    <h2>Lista de Turmas</h2>
    <table>
        <tr><th>Nome</th><th>Ano</th><th>Turno</th><th>Ações</th></tr>
        <?php foreach ($turmas as $t): ?>
            <tr>
                <td><?= e($t['nome']) ?></td>
                <td><?= $t['ano'] ?></td>
                <td><?= ucfirst($t['turno']) ?></td>
                <td>
                    <a class="btn btn-danger" href="?excluir=<?= $t['id'] ?>"
                       data-confirmar="Excluir esta turma?">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>