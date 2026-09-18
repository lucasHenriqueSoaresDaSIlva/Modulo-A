<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('admin');

$pdo = conectar();

// Cadastrar
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome']);
    $email     = trim($_POST['email']);
    $senha     = $_POST['senha'];
    $matricula = trim($_POST['matricula']);
    $nasc      = $_POST['data_nascimento'] ?: null;
    $turma_id  = $_POST['turma_id'] ?: null;

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, tipo) VALUES (?, ?, ?, 'aluno')");
        $stmt->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
        $usuario_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO alunos (usuario_id, matricula, data_nascimento, turma_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$usuario_id, $matricula, $nasc, $turma_id]);

        $pdo->commit();
        flash('success', 'Aluno cadastrado com sucesso!');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Erro ao cadastrar: ' . $e->getMessage());
    }
    redirecionar('/ceon/admin/alunos.php');
}

// Excluir
if (isset($_GET['excluir'])) {
    $pdo->prepare("DELETE FROM usuarios WHERE id = (SELECT usuario_id FROM alunos WHERE id = ?)")
        ->execute([$_GET['excluir']]);
    flash('success', 'Aluno removido.');
    redirecionar('/ceon/admin/alunos.php');
}

$turmas = $pdo->query("SELECT * FROM turmas ORDER BY nome")->fetchAll();
$alunos = $pdo->query("
    SELECT a.id, u.nome, u.email, a.matricula, a.data_nascimento, t.nome AS turma
    FROM alunos a
    JOIN usuarios u ON u.id = a.usuario_id
    LEFT JOIN turmas t ON t.id = a.turma_id
    ORDER BY u.nome
")->fetchAll();

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:16px;">Gerenciar Alunos</h2>

<div class="card">
    <h2>Novo Aluno</h2>
    <form method="POST">
        <div class="grid">
            <div><label>Nome</label><input type="text" name="nome" required></div>
            <div><label>E-mail</label><input type="email" name="email" required></div>
            <div><label>Senha</label><input type="password" name="senha" required></div>
            <div><label>Matrícula</label><input type="text" name="matricula" required></div>
            <div><label>Data de Nascimento</label><input type="date" name="data_nascimento"></div>
            <div>
                <label>Turma</label>
                <select name="turma_id">
                    <option value="">-- Sem turma --</option>
                    <?php foreach ($turmas as $t): ?>
                        <option value="<?= $t['id'] ?>"><?= e($t['nome']) ?> (<?= $t['ano'] ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <br>
        <button type="submit">Cadastrar</button>
    </form>
</div>

<div class="card">
    <h2>Alunos Cadastrados</h2>
    <table>
        <tr><th>Nome</th><th>E-mail</th><th>Matrícula</th><th>Turma</th><th>Ações</th></tr>
        <?php foreach ($alunos as $a): ?>
            <tr>
                <td><?= e($a['nome']) ?></td>
                <td><?= e($a['email']) ?></td>
                <td><?= e($a['matricula']) ?></td>
                <td><?= e($a['turma'] ?? '—') ?></td>
                <td>
                    <a class="btn btn-danger" href="?excluir=<?= $a['id'] ?>"
                       data-confirmar="Excluir este aluno?">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>