<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db.php';

$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM tasks WHERE user_id = ? GROUP BY status");
$stmt->execute([$userId]);
$rows = $stmt->fetchAll();
$pending = 0; $completed = 0;
foreach ($rows as $r) {
    if ($r['status'] === 'pending') $pending = (int)$r['cnt'];
    if ($r['status'] === 'completed') $completed = (int)$r['cnt'];
}

$stmt = $pdo->prepare('SELECT * FROM tasks WHERE user_id = ? AND status = "pending" ORDER BY created_at DESC LIMIT 10');
$stmt->execute([$userId]);
$pendingTasks = $stmt->fetchAll();

include __DIR__ . '/templates/header.php';
?>
<h2>Dashboard</h2>
<div style="display:flex;gap:20px;">
    <div>
        <canvas id="tasksChart" width="300" height="300"></canvas>
        <script>
        document.addEventListener('DOMContentLoaded', function(){
            const ctx = document.getElementById('tasksChart');
            new Chart(ctx, {
                type: 'doughnut',
                data: {
                    labels: ['Pendentes','Concluídas'],
                    datasets: [{ data: [<?php echo $pending; ?>, <?php echo $completed; ?>] }]
                }
            });
        });
        </script>
    </div>
    <div>
        <h3>Tarefas pendentes</h3>
        <?php if (empty($pendingTasks)): ?>
            <p>Nenhuma tarefa pendente.</p>
        <?php else: ?>
            <ul>
            <?php foreach ($pendingTasks as $t): ?>
                <li>
                    <strong><?php echo htmlspecialchars(substr($t['description'],0,120)); ?></strong>
                    <?php if ($t['due_date']): ?><div>Prazo: <?php echo htmlspecialchars($t['due_date']); ?></div><?php endif; ?>
                    <div>
                        <a href="task_edit.php?id=<?php echo $t['id']; ?>">Editar</a>
                        <form method="post" action="api/toggle_task.php" style="display:inline-block">
                            <input type="hidden" name="id" value="<?php echo $t['id']; ?>">
                            <input type="hidden" name="action" value="complete">
                            <button>Marcar concluída</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
</div>
<?php include __DIR__ . '/templates/footer.php'; ?>
