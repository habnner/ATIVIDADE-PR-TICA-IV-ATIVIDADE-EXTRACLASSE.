<?php
require_once __DIR__ . '/includes/config.php';

// Inicia a sessão para verificar o status de login
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário estiver autenticado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
} else {
    // Caso contrário, redireciona para a tela de login
    header("Location: " . BASE_URL . "/auth/login.php");
}
exit();
?>