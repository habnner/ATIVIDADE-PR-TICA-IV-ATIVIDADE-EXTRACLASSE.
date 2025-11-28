<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM tasks WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$id, $_SESSION['user_id']]);
$task = $stmt->fetch();
if (!$task) { header('Location: tasks.php'); exit; }

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?: null;
    $status = ($_POST['status'] === 'completed') ? 'completed' : 'pending';

    if ($description === '') $errors[] = 'Descrição é obrigatória.';
    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE tasks SET description = :desc, due_date = :due, status = :status, updated_at = NOW() WHERE id = :id AND user_id = :uid');
        $stmt->execute(['desc'=>$description,'due'=>$due_date,'status'=>$status,'id'=>$id,'uid'=>$_SESSION['user_id']]);
        header('Location: tasks.php'); exit;
    }
}

include __DIR__ . '/templates/header.php';
?>
<h2>Editar Tarefa</h2>
<?php if ($errors): foreach ($errors as $err) echo '<p style="color:red">'.htmlspecialchars($err).'</p>'; endforeach; ?>
<form method="post">
    <label>Descrição<br><textarea name="description"><?php echo htmlspecialchars($task['description']); ?></textarea></label><br>
    <label>Prazo<br><input type="date" name="due_date" value="<?php echo htmlspecialchars($task['due_date']); ?>"></label><br>
    <label>Status<br>
        <select name="status">
            <option value="pending" <?php if($task['status']==='pending') echo 'selected'; ?>>Pendente</option>
            <option value="completed" <?php if($task['status']==='completed') echo 'selected'; ?>>Concluída</option>
        </select>
    </label><br>
    <button>Salvar</button>
</form>
<?php include __DIR__ . '/templates/footer.php'; ?>
