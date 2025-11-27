<?php
session_start();

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

$usuario_id = $_SESSION['usuario_id']; // AGORA A VARIÁVEL EXISTE!

$conn = getConnection();
$tarefas_pendentes = [];
$total_pendentes = 0;
$total_concluidas = 0;


// 1. Contar tarefas para o gráfico
$sql_counts = "SELECT 
    SUM(CASE WHEN concluida = 0 THEN 1 ELSE 0 END) AS pendentes,
    SUM(CASE WHEN concluida = 1 THEN 1 ELSE 0 END) AS concluidas
FROM tarefas WHERE usuario_id = ?";

if ($stmt_counts = $conn->prepare($sql_counts)) {
    $stmt_counts->bind_param("i", $usuario_id);
    $stmt_counts->execute();
    $result_counts = $stmt_counts->get_result()->fetch_assoc();
    $total_pendentes = $result_counts['pendentes'] ?? 0;
    $total_concluidas = $result_counts['concluidas'] ?? 0;
    $stmt_counts->close();
}

// 2. Listar tarefas pendentes
$sql_pendentes = "SELECT id, titulo, descricao, data_criacao FROM tarefas WHERE usuario_id = ? AND concluida = 0 ORDER BY data_criacao ASC";

if ($stmt_pendentes = $conn->prepare($sql_pendentes)) {
    $stmt_pendentes->bind_param("i", $usuario_id);
    $stmt_pendentes->execute();
    $tarefas_pendentes = $stmt_pendentes->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt_pendentes->close();
}

$conn->close();
?>

<div class="space-y-8">
    <h1 class="text-3xl font-bold text-gray-900">Dashboard</h1>

    <!-- GRÁFICO DE PROGRESSO -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Progresso das Tarefas</h2>
        <div class="w-full max-w-sm mx-auto">
            <canvas id="tasksChart"></canvas>
        </div>
        <div class="text-center mt-4">
            <p class="text-sm text-gray-600">Você tem <span class="font-bold text-red-500"><?php echo $total_pendentes; ?></span> pendentes e <span class="font-bold text-green-500"><?php echo $total_concluidas; ?></span> concluídas.</p>
        </div>
    </div>

    <!-- LISTA DE TAREFAS PENDENTES -->
    <div class="bg-white p-6 rounded-xl shadow-lg">
        <h2 class="text-xl font-semibold mb-4 text-gray-700">Tarefas Pendentes</h2>

        <?php if (empty($tarefas_pendentes)): ?>
            <div class="p-4 bg-yellow-50 text-yellow-700 border-l-4 border-yellow-400 rounded-md">
                Você não tem nenhuma tarefa pendente. Que tal <a href="<?php echo BASE_URL; ?>/pages/new_task.php" class="font-bold hover:underline">adicionar uma nova</a>?
            </div>
        <?php else: ?>
            <ul class="divide-y divide-gray-200">
                <?php foreach ($tarefas_pendentes as $tarefa): ?>
                    <li class="py-4 flex justify-between items-center hover:bg-gray-50 transition duration-100 px-2 rounded-lg">
                        <div class="flex items-center flex-1 min-w-0">
                            <!-- Link para marcar como concluída -->
                            <a href="<?php echo BASE_URL; ?>/actions/task_toggle.php?id=<?php echo $tarefa['id']; ?>&status=1" 
                               class="text-gray-400 hover:text-green-500 mr-4 transition duration-150" 
                               title="Marcar como Concluída">
                                <!-- Ícone de círculo vazio -->
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </a>
                            <div class="min-w-0">
                                <p class="text-lg font-medium text-gray-900 truncate"><?php echo htmlspecialchars($tarefa['titulo']); ?></p>
                                <p class="text-sm text-gray-500 truncate"><?php echo htmlspecialchars($tarefa['descricao']); ?></p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-3 text-sm">
                            <span class="text-gray-400 hidden sm:inline">Criada em: <?php echo date('d/m/Y', strtotime($tarefa['data_criacao'])); ?></span>
                            <a href="<?php echo BASE_URL; ?>/pages/edit_task.php?id=<?php echo $tarefa['id']; ?>" class="text-indigo-600 hover:text-indigo-900 transition" title="Editar">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zm-3.828 3.828L14.586 12H12v2.586l-4.793-4.793 2.586-2.586zM4 14V8a2 2 0 012-2h3a1 1 0 100-2H6a4 4 0 00-4 4v6a4 4 0 004 4h6a4 4 0 004-4v-3a1 1 0 10-2 0v3a2 2 0 01-2 2H6a2 2 0 01-2-2z"/></svg>
                            </a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>

<script>
    // Dados para o gráfico Chart.js
    const totalPendentes = <?php echo $total_pendentes; ?>;
    const totalConcluidas = <?php echo $total_concluidas; ?>;

    const data = {
        labels: ['Pendentes', 'Concluídas'],
        datasets: [{
            data: [totalPendentes, totalConcluidas],
            backgroundColor: [
                'rgb(239, 68, 68)', // Tailwind red-500
                'rgb(16, 185, 129)' // Tailwind green-500
            ],
            hoverOffset: 4
        }]
    };

    const config = {
        type: 'doughnut', // Gráfico de Rosca
        data: data,
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed !== null) {
                                label += context.parsed + ' tarefas';
                            }
                            return label;
                        }
                    }
                }
            }
        },
    };

    // Renderiza o gráfico
    window.onload = function() {
        const ctx = document.getElementById('tasksChart').getContext('2d');
        new Chart(ctx, config);
    };
</script>

<?php
require_once __DIR__ . '/../includes/footer.php';
?>