ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



<?php
// Inclui a configuração global.
require_once __DIR__ . '/../includes/config.php';

// Se o usuário já estiver logado, redireciona para o dashboard
if (isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit();
}

$erro = '';
$email = ''; // Inicializa a variável para evitar erro no formulário

// --- CONFIGURAÇÃO DO USUÁRIO DE TESTE ---
$TEST_EMAIL = 'teste@email.com';
$TEST_PASSWORD_PLAIN = '123456';
$TEST_NAME_DEFAULT = 'Usuário de Teste';
$TEST_PASSWORD_HASH = password_hash($TEST_PASSWORD_PLAIN, PASSWORD_DEFAULT);

// Variáveis para exibição (usarão os valores padrões até serem atualizadas pela consulta)
$teste_email = $TEST_EMAIL;
$teste_nome = $TEST_NAME_DEFAULT;
$teste_senha_fixa = $TEST_PASSWORD_PLAIN;

$conn = null;
try {
    $conn = getConnection(); 
    
    // --- LÓGICA DE CRIAÇÃO AUTOMÁTICA DO USUÁRIO DE TESTE (SOMENTE SE NÃO EXISTIR) ---
    $stmt_check = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
    if ($stmt_check) {
        $stmt_check->bind_param("s", $TEST_EMAIL);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            // Usuário de teste não existe, vamos criar.
            $stmt_insert = $conn->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
            if ($stmt_insert) {
                $stmt_insert->bind_param("sss", $TEST_NAME_DEFAULT, $TEST_EMAIL, $TEST_PASSWORD_HASH);
                $stmt_insert->execute(); // Tenta criar. Erros serão ignorados para não parar o login.
                $stmt_insert->close();
            }
        }
        $stmt_check->close();
    }

    // --- Lógica de Consulta do Nome (Para exibição) ---
    // Consulta para buscar o nome atualizado e e-mail do usuário de teste
    $stmt_teste = $conn->prepare("SELECT nome, email FROM usuarios WHERE email = ?");
    if ($stmt_teste) {
        $stmt_teste->bind_param("s", $TEST_EMAIL);
        $stmt_teste->execute();
        $result_teste = $stmt_teste->get_result();
        
        if ($result_teste->num_rows === 1) {
            $usuario_teste = $result_teste->fetch_assoc();
            $teste_nome = $usuario_teste['nome'];
            $teste_email = $usuario_teste['email'];
        }
        $stmt_teste->close();
    }

} catch (Exception $e) {
    // Em caso de erro de conexão, apenas usamos os valores padrão (ignorar o erro).
    error_log("Erro durante a checagem/criação/consulta do usuário de teste: " . $e->getMessage());
} finally {
    // Fecha a conexão após o bloco de checagem/criação/consulta.
    closeConnection($conn);
    $conn = null;
}
// --- FIM: Lógica de Checagem/Criação/Consulta do Usuário de Teste ---


// Processamento do formulário de login (Lógica original, que se conecta novamente)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');

    if (empty($email) || empty($senha)) {
        $erro = "Por favor, preencha todos os campos.";
    } else {
        $conn = null;
        try {
            // 1. Conexão com o Banco (função agora definida em config.php)
            $conn = getConnection(); 
            
            // 2. Preparação da Consulta
            // Seleciona o ID, nome e o hash da senha
            $stmt = $conn->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
            if ($stmt === false) {
                // Em caso de erro na preparação (geralmente problema de sintaxe SQL)
                throw new Exception("Erro na preparação da consulta.");
            }
            $stmt->bind_param("s", $email);
            
            // 3. Execução
            if (!$stmt->execute()) {
                throw new Exception("Erro na execução da consulta.");
            }
            
            // 4. Obter Resultado
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $usuario = $result->fetch_assoc();
                
                // 5. Verificação da Senha
                if (password_verify($senha, $usuario['senha'])) {
                    // Login bem-sucedido
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['usuario_nome'] = $usuario['nome'];
                    
                    // Redireciona para o dashboard
                    header("Location: " . BASE_URL . "/pages/dashboard.php");
                    exit();
                } else {
                    $erro = "E-mail ou senha incorretos.";
                }
            } else {
                $erro = "E-mail ou senha incorretos.";
            }
            
            // 6. Fechar Statement
            $stmt->close();
            
        } catch (Exception $e) {
            $erro = "Erro interno no sistema. Tente novamente mais tarde.";
        } finally {
            // 7. Fechar Conexão
            closeConnection($conn);
        }
    }
}

// O código HTML de frontend continua aqui
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | To-Do List</title>
    <!-- Carrega o Tailwind CSS via CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Define a fonte Inter (preferencial para Tailwind) */
        html { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="max-w-md w-full mx-auto p-6">
        <div class="bg-white p-8 rounded-xl shadow-2xl space-y-8 border border-gray-200">
            <div class="text-center">
                <h2 class="text-3xl font-extrabold text-gray-900">
                    Acesse sua conta
                </h2>
                <p class="mt-2 text-sm text-gray-600">
                    Gerencie suas tarefas de forma simples e eficiente.
                </p>
            </div>

            <?php if (!empty($erro)): ?>
                <!-- Bloco de Erro com Tailwind CSS -->
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-lg" role="alert">
                    <p class="font-bold">Erro de Login</p>
                    <p><?= htmlspecialchars($erro) ?></p>
                </div>
            <?php endif; ?>

            <form method="POST" class="space-y-6">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">E-mail</label>
                    <input id="email" name="email" type="email" autocomplete="email" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150"
                        placeholder="seu.email@exemplo.com"
                        value="<?= htmlspecialchars($email) ?>">
                </div>

                <div>
                    <label for="senha" class="block text-sm font-medium text-gray-700 mb-1">Senha</label>
                    <input id="senha" name="senha" type="password" autocomplete="current-password" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150"
                        placeholder="••••••••">
                </div>

                <div class="mt-6">
                    <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-md text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                        Entrar
                    </button>
                </div>
            </form>
            
            <p class="mt-6 text-center text-sm text-gray-600">
                Ainda não tem uma conta?
                <a href="<?= BASE_URL ?>/auth/register.php" class="font-medium text-indigo-600 hover:text-indigo-500 transition duration-150">
                    Criar Conta
                </a>
            </p>
            <!-- Credenciais de Teste dinâmicas -->
            <p class="mt-2 text-center text-xs text-gray-400">
                Usuário de Teste: **<?= htmlspecialchars($teste_nome) ?>**
                <br>
                E-mail: **<?= htmlspecialchars($teste_email) ?>**
                <br>
                Senha: **<?= htmlspecialchars($teste_senha_fixa) ?>**
            </p>
        </div>
    </div>

</body>
</html>