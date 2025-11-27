<?php
// Garantir que a sessão e as variáveis globais estejam prontas
require_once __DIR__ . '/config.php';
// REMOVIDO: require_once __DIR__ . '/db.php'; // A função getConnection() agora está em config.php

// Inclui o auth_check.php para garantir autenticação nas páginas internas
// O login.php não deve incluir o auth_check.php, pois ele faz a checagem.
if (basename($_SERVER['PHP_SELF']) !== 'login.php') {
    require_once __DIR__ . '/auth_check.php';
}

$usuario_nome = $_SESSION['usuario_nome'] ?? 'Usuário';
$usuario_id = $_SESSION['usuario_id'] ?? null;

?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>To-Do List | Gerenciamento de Tarefas</title>
    <!-- 1. Carrega o Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- 2. Carrega o Charts.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>
    <!-- 3. CARREGA O SEU ARQUIVO CSS PERSONALIZADO -->
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body class="bg-gray-50 min-h-screen flex flex-col">
    <!-- Navbar -->
    <nav class="bg-white shadow-lg sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo/Nome do App -->
                    <a href="<?php echo BASE_URL; ?>/pages/dashboard.php" class="flex-shrink-0 text-xl font-bold text-indigo-600">
                        To-Do App
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Links de Navegação -->
                    <a href="<?php echo BASE_URL; ?>/pages/dashboard.php" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition duration-150">
                        Dashboard
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/all_tasks.php" class="text-gray-500 hover:text-gray-900 px-3 py-2 rounded-md text-sm font-medium transition duration-150">
                        Todas as Tarefas
                    </a>
                    <a href="<?php echo BASE_URL; ?>/pages/new_task.php" class="bg-indigo-600 text-white px-3 py-2 rounded-md text-sm font-medium hover:bg-indigo-700 transition duration-150">
                        + Nova Tarefa
                    </a>
                </div>
                <div class="flex items-center">
                    <span class="text-sm font-medium text-gray-700 mr-4 hidden sm:inline">
                        Olá, <?php echo htmlspecialchars($usuario_nome); ?>
                    </span>
                    <!-- Botão de Sair/Logout -->
                    <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="text-sm font-medium text-red-600 hover:text-red-800 transition duration-150">
                        Sair
                    </a>
                </div>
            </div>
        </div>
    </nav>
    
    <!-- Conteúdo Principal -->
    <main class="flex-grow max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8 w-full">