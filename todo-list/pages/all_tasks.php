<?php
require_once __DIR__ . '/../includes/header.php';

$conn = getConnection();
$tarefas = [];
$status_filtro = $_GET['status'] ?? 'all'; // 'all', '0', '1'
$busca = trim($_GET['busca'] ?? '');

$where_clauses = ["usuario_id = ?"];
$bind_types = "i";
$bind_params = [$usuario_id];
$sql = "SELECT id, titulo, descricao, concluida, data_criacao FROM tarefas";

// 1. Filtrar por status
if ($status_filtro !== 'all') {
    $where_clauses[] = "concluida = ?";
    $bind_types .= "i";
    $bind_params[] = (int)$status_filtro;
}

// 2. Buscar por descrição
if (!empty($busca)) {
    $where_clauses[] = "descricao LIKE CONCAT('%', ?, '%')";
    $bind_types .= "s";
    $bind_params[] = $busca;
}

// Constrói a query final
if (!empty($where_clauses)) {
    $sql .= " WHERE " . implode(" AND ", $where_clauses);
}

$sql .= " ORDER BY data_criacao DESC";

if ($stmt = $conn->prepare($sql)) {
    // Adiciona o primeiro parâmetro (usuario_id) ao array de parâmetros para o bind
    // Já está na posição 0 do $bind_params

    // Chama o bind_param dinamicamente
    if (!empty($bind_params)) {
        $stmt->bind_param($bind_types, ...$bind_params);
    }
    
    $stmt->execute();
    $tarefas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

$conn->close();
?>

<div class="space-y-6">
    <h1 class="text-3xl font-bold text-gray-900">Todas as Tarefas</h1>

    <!-- FORMULÁRIO DE FILTRO E BUSCA -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" class="flex flex-wrap items-end gap-4">
            <div class="flex-grow">
                <label for="busca" class="block text-sm font-medium text-gray-700">Buscar por Descrição</label>
                <input type="text" id="busca" name="busca" 
                       class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                       value="<?php echo htmlspecialchars($busca); ?>" placeholder="Busque palavras na descrição...">
            </div>

            <div>
                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                <select id="status" name="status" 
                        class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-lg">
                    <option value="all" <?php echo $status_filtro === 'all' ? 'selected' : ''; ?>>Todas</option>
                    <option value="0" <?php echo $status_filtro === '0' ? 'selected' : ''; ?>>Pendentes</option>
                    <option value="1" <?php echo $status_filtro === '1' ? 'selected' : ''; ?>>Concluídas</option>
                </select>
            </div>

            <button type="submit" 
                    class="py-2 px-4 bg-indigo-600 text-white font-medium rounded-lg shadow-md hover:bg-indigo-700 transition duration-150">
                Filtrar
            </button>
            <a href="<?php echo BASE_URL; ?>/pages/all_tasks.php" class="py-2 px-4 bg-gray-200 text-gray-700 font-medium rounded-lg shadow-md hover:bg-gray-300 transition duration-150">Limpar</a>
        </form>
    </div>

    <!-- LISTAGEM DE TAREFAS -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <?php if (empty($tarefas)): ?>
            <div class="p-4 bg-blue-50 text-blue-700 border-l-4 border-blue-400 rounded-md">
                Nenhuma tarefa encontrada com os filtros e buscas aplicados.
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($tarefas as $tarefa): ?>
                    <?php 
                        $is_concluida = (bool)$tarefa['concluida'];
                        $text_color = $is_concluida ? 'text-green-600' : 'text-gray-900';
                        $line_through = $is_concluida ? 'line-through' : 'no-underline';
                        $icon_class = $is_concluida ? 'text-green-500' : 'text-gray-400 hover:text-green-500';
                        $icon_path = $is_concluida ? 
                            '<path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />' : 
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />';
                        $toggle_status = $is_concluida ? 0 : 1;
                        $toggle_title = $is_concluida ? 'Marcar como Pendente' : 'Marcar como Concluída';
                    ?>
                    <li class="py-4 flex justify-between items-center hover:bg-gray-50 transition duration-100 px-2 rounded-lg">
                        <div class="flex items-center flex-1 min-w-0">
                            <!-- Link para alternar status -->
                            <a href="<?php echo BASE_URL; ?>/actions/task_toggle.php?id=<?php echo $tarefa['id']; ?>&status=<?php echo $toggle_status; ?>&redirect=all" 
                               class="<?php echo $icon_class; ?> mr-4 transition duration-150" 
                               title="<?php echo $toggle_title; ?>">
                                <!-- Ícone de círculo -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" viewBox="0 0 20 20" fill="currentColor">
                                    <?php echo $icon_path; ?>
                                </svg>
                            </a>
                            <div class="min-w-0">
                                <p class="text-lg font-medium <?php echo $text_color; ?> <?php echo $line_through; ?> truncate"><?php echo htmlspecialchars($tarefa['titulo']); ?></p>
                                <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($tarefa['descricao']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 text-sm">
                            <span class="text-gray-400 hidden sm:inline">Criada em: <?php echo date('d/m/Y', strtotime($tarefa['data_criacao'])); ?></span>
                            
                            <!-- Botão Editar -->
                            <a href="<?php echo BASE_URL; ?>/pages/edit_task.php?id=<?php echo $tarefa['id']; ?>" class="text-indigo-600 hover:text-indigo-900 transition" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zm-3.828 3.828L14.586 12H12v2.586l-4.793-4.793 2.586-2.586zM4 14V8a2 2 0 012-2h3a1 1 0 100-2H6a4 4 0 00-4 4v6a4 4 0 004 4h6a4 4 0 004-4v-3a1 1 0 10-2 0v3a2 2 0 01-2 2H6a2 2 0 01-2-2z"/></svg>
                            </a>
                            
                            <!-- Botão Remover (com JavaScript para confirmação) -->
                            <form action="<?php echo BASE_URL; ?>/actions/task_delete.php" method="POST" onsubmit="return confirm('Tem certeza que deseja remover esta tarefa?');" class="inline">
                                <input type="hidden" name="id" value="<?php echo $tarefa['id']; ?>">
                                <button type="submit" class="text-red-600 hover:text-red-900 transition" title="Remover">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm4 0a1 1 0 10-2 0v6a1 1 0 102 0V8z" clip-rule="evenodd" /></svg>
                                </button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>