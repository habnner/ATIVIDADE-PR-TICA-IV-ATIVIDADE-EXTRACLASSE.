<?php
require_once __DIR__ . '/../includes/header.php';

$titulo = '';
$descricao = '';
$erro = '';

// Se for um POST, a ação é tratada em actions/task_create.php
?>

<div class="max-w-xl mx-auto space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Adicionar Nova Tarefa</h1>

    <?php 
        // Exibe mensagem de erro após redirecionamento, se houver
        if (isset($_SESSION['erro_msg'])) {
            echo '<div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                      <p>' . htmlspecialchars($_SESSION['erro_msg']) . '</p>
                  </div>';
            unset($_SESSION['erro_msg']);
        }
    ?>

    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form action="<?php echo BASE_URL; ?>/actions/task_create.php" method="POST" class="space-y-4">
            <div>
                <label for="titulo" class="block text-sm font-medium text-gray-700">Título da Tarefa <span class="text-red-500">*</span></label>
                <input type="text" id="titulo" name="titulo" required maxlength="100"
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                       value="<?php echo htmlspecialchars($_SESSION['form_data']['titulo'] ?? $titulo); unset($_SESSION['form_data']['titulo']); ?>">
            </div>

            <div>
                <label for="descricao" class="block text-sm font-medium text-gray-700">Descrição (Detalhes)</label>
                <textarea id="descricao" name="descricao" rows="4"
                          class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"><?php echo htmlspecialchars($_SESSION['form_data']['descricao'] ?? $descricao); unset($_SESSION['form_data']['descricao']); ?></textarea>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="<?php echo BASE_URL; ?>/pages/dashboard.php" 
                   class="py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg shadow-md hover:bg-gray-300 transition duration-150">
                    Cancelar
                </a>
                <button type="submit"
                        class="py-2 px-4 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150">
                    Salvar Tarefa
                </button>
            </div>
        </form>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>