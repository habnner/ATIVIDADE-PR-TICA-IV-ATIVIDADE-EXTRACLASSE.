ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);



<?php
session_start();
require_once __DIR__ . '/../includes/config.php'; // usa a mesma config do login

$erro = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nome = trim($_POST["nome"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $senha = trim($_POST["senha"] ?? '');

    if (empty($nome) || empty($email) || empty($senha)) {
        $erro = "Preencha todos os campos.";
    } else {

        try {
            // Conecta ao banco
            $conn = getConnection();

            // Verifica se já existe usuário
            $stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {

                $erro = "Esse e-mail já está cadastrado.";

            } else {

                // Cria nova senha
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

                $stmtInsert = $conn->prepare(
                    "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)"
                );

                $stmtInsert->bind_param("sss", $nome, $email, $senhaHash);

                if ($stmtInsert->execute()) {
                    header("Location: login.php?msg=usuario_cadastrado");
                    exit();
                } else {
                    $erro = "Erro ao cadastrar usuário.";
                }

                $stmtInsert->close();
            }

            $stmt->close();
            closeConnection($conn);

        } catch (Exception $e) {
            $erro = "Erro interno. Tente novamente mais tarde.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastrar usuário</title>
</head>
<body>
    <h2>Cadastro de Usuário</h2>

    <?php if (!empty($erro)): ?>
        <p style="color:red;"><?= $erro ?></p>
    <?php endif; ?>

    <form method="POST">
        Nome:<br>
        <input type="text" name="nome"><br><br>

        E-mail:<br>
        <input type="email" name="email"><br><br>

        Senha:<br>
        <input type="password" name="senha"><br><br>

        <button type="submit">Cadastrar</button>
    </form>

</body>
</html>
