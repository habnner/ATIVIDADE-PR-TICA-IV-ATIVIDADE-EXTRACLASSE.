<?php
// Inclui a configuração global (necessário para BASE_URL e, agora, getConnection())
require_once __DIR__ . '/../includes/config.php';

// Inicia a sessão se não estiver iniciada (já feito em config.php, mas manter por segurança se a ordem mudar)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Se o usuário já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit();
}

$erro = '';
$email = ''; // Inicializa a variável para evitar erro no formulário

// Processamento do formulário de login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $conn = null;
        try {
            // 1. Conexão com o Banco (getConnection agora está em config.php)
            $conn = getConnection(); 
            
            // 2. Preparação da Consulta
            $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
            if ($stmt === false) {
                throw new Exception("Erro na preparação da consulta: " . $conn->error);
            }
            $stmt->bind_param("s", $email);
            
            // 3. Execução
            if (!$stmt->execute()) {
                throw new Exception("Erro na execução da consulta: " . $stmt->error);
            }

            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                
                // 4. Verificação da Senha
                if (password_verify($senha, $usuario['senha'])) {
                    // Login bem-sucedido
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    
                    // Redireciona para o dashboard
                    header("Location: " . BASE_URL . "/pages/dashboard.php");
                    exit();
                } else {
                    // Senha incorreta
                    $erro = "E-mail ou senha incorretos.";
                }
            } else {
                // E-mail não encontrado
                $erro = "E-mail ou senha incorretos.";
            }

            // 5. Fechar Statement
            $stmt->close();

        } catch (Exception $e) {
            // Em caso de erro, exibe uma mensagem genérica (logar o $e->getMessage() em um ambiente real)
            $erro = "Ocorreu um erro ao tentar fazer login. Tente novamente.";
            error_log($e->getMessage()); // Logar o erro real
        } finally {
            // 6. Fechar Conexão
            closeConnection($conn); // closeConnection agora está em config.php
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | To-Do List</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen p-4">

    <div class="max-w-md w-full bg-white p-8 rounded-2xl shadow-xl space-y-6">
        <h2 class="text-3xl font-extrabold text-center text-gray-900">
            Acessar sua conta
        </h2>
        <p class="text-center text-sm text-gray-600">
            Gerencie suas tarefas com eficiência.
        </p>

        <?php if ($erro): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                <p><?= htmlspecialchars($erro) ?></p>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-6">
            <div class="rounded-md shadow-sm space-y-4">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        placeholder="seu@email.com"
                        value="<?= htmlspecialchars($email) ?>">
                </div>

                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input id="senha" name="senha" type="password" autocomplete="current-password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                        placeholder="••••••••">
                </div>
            </div>

            <div class="mt-6">
                <button type="submit"
                    class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    Entrar
                </button>
            </div>
        </form>
        
        <p class="mt-6 text-center text-sm text-gray-600">
            Ainda não tem uma conta?
            <a href="<?= BASE_URL ?>/auth/register.php" class="font-medium text-indigo-600 hover:text-indigo-500">
                Criar Conta
            </a>
        </p>
        <p class="mt-2 text-center text-xs text-gray-400">
            Use o usuário de teste: <span class="font-semibold">teste@email.com</span> / <span class="font-semibold">senha123</span>
        </p>
    </div>
</body>
</html>