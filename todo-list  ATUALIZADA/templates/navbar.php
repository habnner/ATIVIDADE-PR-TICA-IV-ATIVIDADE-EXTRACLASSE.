<?php
require_once __DIR__ . '/../auth.php';
$user = null;
if (isset($pdo)) $user = current_user($pdo);
?>
<nav>
    <a href="index.php">Início</a> |
    <?php if (is_logged_in()): ?>
        <a href="tasks.php">Tarefas</a> |
        <a href="task_create.php">Nova tarefa</a> |
        <a href="logout.php">Logout</a>
    <?php else: ?>
        <a href="login.php">Login</a> |
        <a href="register.php">Registrar</a>
    <?php endif; ?>
</nav>
<hr>
