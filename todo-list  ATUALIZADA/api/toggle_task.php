<?php
require_once __DIR__ . '/../auth.php';
require_login();
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); exit;
}
$id = (int)($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';

if (!$id) { http_response_code(400); echo json_encode(['ok'=>false]); exit; }

if ($action === 'complete') {
    $stmt = $pdo->prepare('UPDATE tasks SET status = "completed", updated_at = NOW() WHERE id = ? AND user_id = ?');
    $stmt->execute([$id, $_SESSION['user_id']]);
    echo json_encode(['ok'=>true]); exit;
}

$stmt = $pdo->prepare('SELECT status FROM tasks WHERE id = ? AND user_id = ?');
$stmt->execute([$id, $_SESSION['user_id']]);
$t = $stmt->fetch();
if (!$t) { echo json_encode(['ok'=>false]); exit; }
$new = ($t['status'] === 'pending') ? 'completed' : 'pending';
$stmt = $pdo->prepare('UPDATE tasks SET status = ?, updated_at = NOW() WHERE id = ? AND user_id = ?');
$stmt->execute([$new, $id, $_SESSION['user_id']]);
echo json_encode(['ok'=>true,'status'=>$new]);
