<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes.php';

// Se já estiver logado, manda direto para o painel
if (estaLogado()) {
    redirecionar(rotaPainel($_SESSION['usuario_tipo']));
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if ($email === '' || $senha === '') {
        $erro = 'Preencha todos os campos.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido.';
    } else {
        $pdo  = conectar();
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE email = ? AND ativo = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($senha, $user['senha'])) {
            // Login OK — grava os dados na sessão
            $_SESSION['usuario_id']    = $user['id'];
            $_SESSION['usuario_nome']  = $user['nome'];
            $_SESSION['usuario_email'] = $user['email'];
            $_SESSION['usuario_tipo']  = $user['tipo'];

            redirecionar(rotaPainel($user['tipo']));
        } else {
            $erro = 'E-mail ou senha inválidos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login – CEON</title>
    <link rel="stylesheet" href="/ceon/assets/css/style.css">
</head>
<body>
<div class="login-container">
    <div class="login-box">
        <h1>🎓 CEON</h1>
        <p class="sub">Plataforma Escolar</p>

        <?php if ($erro): ?>
            <div class="alert alert-error"><?= e($erro) ?></div>
        <?php endif; ?>

        <form method="POST" autocomplete="off">
            <label>E-mail</label>
            <input type="email" name="email" required autofocus
                   value="<?= e($_POST['email'] ?? '') ?>">

            <label>Senha</label>
            <input type="password" name="senha" required>

            <button type="submit" class="btn-full">Entrar</button>
        </form>

        <p style="text-align:center; margin-top:18px; font-size:14px;">
            Não tem conta?
            <a href="/ceon/cadastro.php" style="color:#2563eb; font-weight:600;">
                Cadastre-se
            </a>
        </p>
    </div>
</div>
</body>
</html>