<?php
require_once __DIR__ . '/../includes/config.php'; // Agora contém getConnection()
require_once __DIR__ . '/../includes/auth_check.php'; 

// Valida que a requisição é um POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: " . BASE_URL . "/pages/all_tasks.php");
    exit();
}

$conn = getConnection(); // getConnection() agora está em config.php
$id = $_POST['id'] ?? null;

// 1. Validação
if (empty($id)) {
    // Não usamos $_SESSION['erro_msg'] aqui, apenas redirecionamos.
    header("Location: " . BASE_URL . "/pages/all_tasks.php");
    exit();
}

// 2. Preparar e Executar a Remoção
$sql = "DELETE FROM tarefas WHERE id = ? AND usuario_id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $id, $usuario_id);
    
    if ($stmt->execute()) {
        // Sucesso
        header("Location: " . BASE_URL . "/pages/all_tasks.php");
        exit();
    } else {
        // Erro na execução
        // Em um sistema real, você registraria esse erro. Aqui, apenas redireciona.
        header("Location: " . BASE_URL . "/pages/all_tasks.php");
        exit();
    }
    
    $stmt->close();
} else {
    // Erro na preparação
    header("Location: " . BASE_URL . "/pages/all_tasks.php");
    exit();
}

closeConnection($conn); // closeConnection() agora está em config.php
?>