<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db.php';

$userId = $_SESSION['user_id'];
$q = trim($_GET['q'] ?? '');
$status = $_GET['status'] ?? '';

$sql = 'SELECT * FROM tasks WHERE user_id = :uid';
$params = ['uid' => $userId];
if ($q !== '') {
    $sql .= ' AND description LIKE :q';
    $params['q'] = "%$q%";
}
if ($status === 'pending' || $status === 'completed') {
    $sql .= ' AND status = :status';
    $params['status'] = $status;
}
$sql .= ' ORDER BY created_at DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tasks = $stmt->fetchAll();

include __DIR__ . '/templates/header.php';
?>
<h2>Tarefas</h2>
<form method="get">
    <input name="q" placeholder="Pesquisar descrição" value="<?php echo htmlspecialchars($q); ?>">
    <select name="status">
        <option value="">Todos</option>
        <option value="pending" <?php if($status==='pending') echo 'selected'; ?>>Pendentes</option>
        <option value="completed" <?php if($status==='completed') echo 'selected'; ?>>Concluídas</option>
    </select>
    <button>Filtrar</button>
</form>

<?php if (empty($tasks)): ?>
    <p>Nenhuma tarefa encontrada.</p>
<?php else: ?>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead><tr><th>Descrição</th><th>Prazo</th><th>Status</th><th>Ações</th></tr></thead>
        <tbody>
            <?php foreach($tasks as $t): ?>
                <tr>
                    <td><?php echo nl2br(htmlspecialchars($t['description'])); ?></td>
                    <td><?php echo htmlspecialchars($t['due_date']); ?></td>
                    <td><?php echo htmlspecialchars($t['status']); ?></td>
                    <td>
                        <a href="task_edit.php?id=<?php echo $t['id']; ?>">Editar</a>
                        <a href="task_delete.php?id=<?php echo $t['id']; ?>" onclick="return confirm('Remover tarefa?')">Remover</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include __DIR__ . '/templates/footer.php'; ?>
