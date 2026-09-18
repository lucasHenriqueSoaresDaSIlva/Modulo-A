<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes.php';

// Se já estiver logado, manda direto para o painel
if (estaLogado()) {
    redirecionar(rotaPainel($_SESSION['usuario_tipo']));
}

$erro    = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome      = trim($_POST['nome'] ?? '');
    $email     = trim($_POST['email'] ?? '');
    $senha     = $_POST['senha'] ?? '';
    $confirma  = $_POST['confirma'] ?? '';
    $matricula = trim($_POST['matricula'] ?? '');
    $nasc      = $_POST['data_nascimento'] ?: null;

    // ---------- VALIDAÇÕES ----------
    if ($nome === '' || $email === '' || $senha === '' || $matricula === '') {
        $erro = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve ter pelo menos 6 caracteres.';
    } elseif ($senha !== $confirma) {
        $erro = 'As senhas não coincidem.';
    } else {
        $pdo = conectar();

        // Verifica duplicidade de e-mail
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        $emailExiste = $stmt->fetchColumn() > 0;

        // Verifica duplicidade de matrícula
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM alunos WHERE matricula = ?");
        $stmt->execute([$matricula]);
        $matriculaExiste = $stmt->fetchColumn() > 0;

        if ($emailExiste) {
            $erro = 'Este e-mail já está cadastrado.';
        } elseif ($matriculaExiste) {
            $erro = 'Esta matrícula já está em uso.';
        } else {
            try {
                $pdo->beginTransaction();

                // 1) Cria o usuário (tipo fixo: aluno)
                $stmt = $pdo->prepare("
                    INSERT INTO usuarios (nome, email, senha, tipo)
                    VALUES (?, ?, ?, 'aluno')
                ");
                $stmt->execute([
                    $nome,
                    $email,
                    password_hash($senha, PASSWORD_DEFAULT)
                ]);
                $usuario_id = $pdo->lastInsertId();

                // 2) Cria o registro de aluno (sem turma)
                $stmt = $pdo->prepare("
                    INSERT INTO alunos (usuario_id, matricula, data_nascimento, turma_id)
                    VALUES (?, ?, ?, NULL)
                ");
                $stmt->execute([$usuario_id, $matricula, $nasc]);

                $pdo->commit();

                $sucesso = 'Cadastro realizado com sucesso! Redirecionando para o login...';
                header('Refresh: 2; URL=/ceon/login.php');

            } catch (Exception $e) {
                $pdo->rollBack();
                $erro = 'Erro ao cadastrar: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro – CEON</title>
    <link rel="stylesheet" href="/ceon/assets/css/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-box" style="max-width: 480px;">
        <h1>🎓 CEON</h1>
        <p class="sub">Criar conta de aluno</p>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= e($erro) ?></div>
        <?php endif; ?>

        <?php if ($sucesso): ?>
            <div class="alert alert-success"><?= e($sucesso) ?></div>
        <?php endif; ?>

        <?php if (!$sucesso): ?>
        <form method="POST" autocomplete="off">
            <label>Nome completo *</label>
            <input type="text" name="nome" required
                   value="<?= e($_POST['nome'] ?? '') ?>">

            <label>E-mail *</label>
            <input type="email" name="email" required
                   value="<?= e($_POST['email'] ?? '') ?>">

            <label>Matrícula *</label>
            <input type="text" name="matricula" required
                   placeholder="Ex: 2025001"
                   value="<?= e($_POST['matricula'] ?? '') ?>">

            <label>Data de nascimento</label>
            <input type="date" name="data_nascimento"
                   value="<?= e($_POST['data_nascimento'] ?? '') ?>">

            <label>
                Senha *
                <small style="color:#666; font-weight:400;">(mínimo 6 caracteres)</small>
            </label>
            <input type="password" name="senha" required minlength="6">

            <label>Confirmar senha *</label>
            <input type="password" name="confirma" required minlength="6">

            <button type="submit" class="btn-full">Cadastrar</button>
        </form>
        <?php endif; ?>

        <p style="text-align:center; margin-top:18px; font-size:14px;">
            Já tem conta?
            <a href="/ceon/login.php" style="color:#2563eb; font-weight:600;">
                Fazer login
            </a>
        </p>
    </div>
</div>
</body>
</html>