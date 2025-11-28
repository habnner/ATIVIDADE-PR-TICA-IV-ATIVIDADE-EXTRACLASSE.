<?php
// register.php - cria usuário (apenas para facilitar testes)
require_once __DIR__ . '/db.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email) $errors[] = 'Email inválido.';
    if ($password === '') $errors[] = 'Senha necessária.';

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('INSERT INTO users (email, password, name) VALUES (?, ?, ?)');
        try {
            $stmt->execute([$email, $hash, $name]);
            header('Location: login.php');
            exit;
        } catch (PDOException $e) {
            if ($e->getCode() === '23000') $errors[] = 'Email já cadastrado.';
            else $errors[] = 'Erro no cadastro: ' . $e->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Registrar</title></head>
<body>
<h2>Registrar usuário (teste)</h2>
<?php if (!empty($errors)): foreach ($errors as $err) echo '<p style="color:red">'.htmlspecialchars($err).'</p>'; endif; ?>
<form method="post">
    <label>Nome<br><input name="name"></label><br>
    <label>Email<br><input name="email" type="email" required></label><br>
    <label>Senha<br><input name="password" type="password" required></label><br>
    <button>Registrar</button>
</form>
<p><a href="login.php">Voltar ao login</a></p>
</body>
</html>
