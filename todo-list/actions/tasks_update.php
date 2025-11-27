<?php
require_once __DIR__ . '/../includes/config.php'; // Agora contém getConnection()
require_once __DIR__ . '/../includes/auth_check.php'; 

// Espera-se que seja um GET
if ($_SERVER["REQUEST_METHOD"] !== "GET") {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit();
}

$conn = getConnection(); // getConnection() agora está em config.php
$id = $_GET['id'] ?? null;
$status = $_GET['status'] ?? null; // 0 para pendente, 1 para concluída
$redirect_page = $_GET['redirect'] ?? 'dashboard'; // Opcional: página de retorno

// 1. Validação
if (empty($id) || $status === null || ($status != 0 && $status != 1)) {
    // Redireciona para o dashboard em caso de dados inválidos
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit();
}

// 2. Preparar e Executar a Atualização
$sql = "UPDATE tarefas SET concluida = ? WHERE id = ? AND usuario_id = ?";
$status_int = (int)$status;

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("iii", $status_int, $id, $usuario_id);
    
    if ($stmt->execute()) {
        // Sucesso, redireciona para a página correta
        if ($redirect_page === 'all') {
            header("Location: " . BASE_URL . "/pages/all_tasks.php");
        } else {
            header("Location: " . BASE_URL . "/pages/dashboard.php");
        }
        exit();
    } else {
        // Erro na execução, redireciona para o dashboard
        // Em um sistema real, registraria o erro
        header("Location: " . BASE_URL . "/pages/dashboard.php");
        exit();
    }
    
    $stmt->close();
} else {
    // Erro na preparação, redireciona para o dashboard
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit();
}

closeConnection($conn); // closeConnection() agora está em config.php
?>