<?php
require_once __DIR__ . '/../includes/header.php';

$id = $_GET['id'] ?? null;
$tarefa = null;
$erro = '';

// Busca a tarefa existente
if ($id) {
    $conn = getConnection();
    $sql = "SELECT id, titulo, descricao FROM tarefas WHERE id = ? AND usuario_id = ?";
    
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $id, $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $tarefa = $result->fetch_assoc();
        } else {
            $erro = "Tarefa não encontrada ou você não tem permissão para editar.";
        }
        
        $stmt->close();
    } else {
        $erro = "Erro interno ao buscar a tarefa.";
    }
    $conn->close();
} else {
    $erro = "ID da tarefa não fornecido.";
}

// Se a tarefa foi buscada com sucesso OU se há dados de formulário na sessão (em caso de erro de submissão)
if ($tarefa || isset($_SESSION['form_data'])) {
    $titulo_default = $_SESSION['form_data']['titulo'] ?? ($tarefa['titulo'] ?? '');
    $descricao_default = $_SESSION['form_data']['descricao'] ?? ($tarefa['descricao'] ?? '');
    $id_default = $_SESSION['form_data']['id'] ?? $id;
    unset($_SESSION['form_data']); // Limpa os dados do formulário após recuperação
}
?>

<div class="max-w-xl mx-auto space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Editar Tarefa</h1>

    <?php 
        // Exibe mensagem de erro
        if (!empty($erro)): 
    ?>
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
            <p><?php echo htmlspecialchars($erro); ?></p>
        </div>
    <?php 
        // Exibe mensagem de erro após redirecionamento
        elseif (isset($_SESSION['erro_msg'])): 
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                      <p>' . htmlspecialchars($_SESSION['erro_msg']) . '</p>
                  </div>';
            unset($_SESSION['erro_msg']);
        endif; 
    ?>
    
    <?php if ($tarefa || isset($_SESSION['form_data'])): ?>
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="<?php echo BASE_URL; ?>/actions/task_update.php" method="POST" class="space-y-4">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($id_default); ?>">
            
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700">Título da Tarefa <span class="text-red-500">*</span></label>
                <input type="text" id="titulo" name="titulo" required maxlength="100"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       value="<?php echo htmlspecialchars($titulo_default); ?>">
            </div>

            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição (Detalhes)</label>
                <textarea id="descricao" name="descricao" rows="4"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($descricao_default); ?></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/pages/dashboard.php" 
                   class="py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg shadow-md hover:bg-gray-300 transition duration-150">
                    Cancelar
                </a>
                <button type="submit"
                        class="py-2 px-4 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
    <?php endif; ?>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>