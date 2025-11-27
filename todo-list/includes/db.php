<?php
$host = "sql107.infinityfree.com";
$user = "if0_40530005";
$pass = "habnner123";
$dbname = "if0_40530005_todoweb";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Erro ao conectar ao banco de dados: " . mysqli_connect_error());
}

mysqli_set_charset($conn, "utf8");
?>
