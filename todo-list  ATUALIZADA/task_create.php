<?php
require_once __DIR__ . '/auth.php';
require_login();
require_once __DIR__ . '/db.php';

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $due_date = $_POST['due_date'] ?? null;

    if ($description === '') $errors[] = 'Descrição é obrigatória.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('INSERT INTO tasks (user_id, description, due_date) VALUES (:uid, :desc, :due)');
        $stmt->execute(['uid' => $_SESSION['user_id'], 'desc' => $description, 'due' => $due_date ?: null]);
        header('Location: tasks.php');
        exit;
    }
}
include __DIR__ . '/templates/header.php';
?>
<h2>Nova Tarefa</h2>
<?php if ($errors): foreach ($errors as $err) echo '<p style="color:red">'.htmlspecialchars($err).'</p>'; endforeach; ?>
<form method="post">
    <label>Descrição<br><textarea name="description" required></textarea></label><br>
    <label>Prazo<br><input type="date" name="due_date"></label><br>
    <button>Criar</button>
</form>
<?php include __DIR__ . '/templates/footer.php'; ?>
