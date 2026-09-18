<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/funcoes.php';

if (estaLogado()) {
    redirecionar(rotaPainel($_SESSION['usuario_tipo']));
}
redirecionar('/ceon/login.php');