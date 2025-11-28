<?php
require_once __DIR__ . '/db.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $password = $_POST['password'] ?? '';

    if (!$email) $errors[] = 'Email inválido.';
    if (!$password) $errors[] = 'Senha necessária.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, password FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            $errors[] = 'Credenciais inválidas.';
        }
    }
}

include __DIR__ . '/templates/header.php';
?>
<h2>Login</h2>
<?php if ($errors): foreach ($errors as $err) echo '<p style="color:red">'.htmlspecialchars($err).'</p>'; endforeach; ?>
<form method="post">
    <label>Email<br><input name="email" type="email" required></label><br>
    <label>Senha<br><input name="password" type="password" required></label><br>
    <button>Entrar</button>
</form>
<p><a href="register.php">Registrar</a></p>
<?php include __DIR__ . '/templates/footer.php'; ?>
