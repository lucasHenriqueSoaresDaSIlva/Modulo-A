<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('admin');

$pdo = conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'professor')");
        $stmt->execute([$_POST['nome'], $_POST['email'], password_hash($_POST['senha'], PASSWORD_DEFAULT)]);
        $uid = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO professores (usuario_id, disciplina_id) VALUES (?, ?)");
        $stmt->execute([$uid, $_POST['disciplina_id'] ?: null]);

        $pdo->commit();
        flash('success', 'Professor cadastrado!');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Erro: ' . $e->getMessage());
    }
    redirecionar('/ceon/admin/professores.php');
}

if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM usuarios WHERE id = (SELECT usuario_id FROM professores WHERE id = ?)")
        ->execute([$_GET['excluir']]);
    flash('success', 'Professor removido.');
    redirecionar('/ceon/admin/professores.php');
}

$disciplinas = $pdo->query("SELECT * FROM disciplinas ORDER BY nome")->fetchAll();
$professores = $pdo->query("
    SELECT p.id, u.nome, u.email, d.nome AS disciplina
    FROM professores p
    JOIN usuarios u ON u.id = p.usuario_id
    LEFT JOIN disciplinas d ON d.id = p.disciplina_id
    ORDER BY u.nome
")->fetchAll();

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Professores</h2>

<div class="card">
    <h2>Novo Professor</h2>
    <form method="POST">
        <div class="grid">
            <div><label>Nome</label><input type="text" name="nome" required></div>
            <div><label>E-mail</label><input type="email" name="email" required></div>
            <div><label>Senha</label><input type="password" name="senha" required></div>
            <div>
                <label>Disciplina Principal</label>
                <select name="disciplina_id">
                    <option value="">-- Nenhuma --</option>
                    <?php foreach ($disciplinas as $d): ?>
                        <option value="<?= $d['id'] ?>"><?= e($d['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <br><button type="submit">Cadastrar</button>
    </form>
</div>

<div class="card">
    <h2>Lista</h2>
    <table>
        <tr><th>Nome</th><th>E-mail</th><th>Disciplina</th><th>Ações</th></tr>
        <?php foreach ($professores as $p): ?>
            <tr>
                <td><?= e($p['nome']) ?></td>
                <td><?= e($p['email']) ?></td>
                <td><?= e($p['disciplina'] ?? '—') ?></td>
                <td>
                    <a class="btn btn-danger" href="?excluir=<?= $p['id'] ?>"
                       data-confirmar="Excluir este professor?">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>