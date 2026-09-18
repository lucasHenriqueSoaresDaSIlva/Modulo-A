<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/funcoes.php';
exigirTipo('professor');

include __DIR__ . '/../includes/header.php';
mostrarFlash();
?>
<h2 style="margin-bottom:20px;">Painel do Professor</h2>

<div class="card">
    <h2>Bem-vindo, <?= e($_SESSION['usuario_nome']) ?>!</h2>
    <p>Utilize o menu acima para lançar notas e registrar frequência.</p>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>