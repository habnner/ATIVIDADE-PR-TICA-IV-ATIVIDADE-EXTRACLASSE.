<?php
// Inicia a sessão se ainda não tiver sido iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redireciona para o login se o usuário não estiver autenticado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: /auth/login.php");  
    exit();
}

// Se autenticado, armazena o ID do usuário em uma variável de fácil acesso
$usuario_id = $_SESSION['usuario_id'];
?>
