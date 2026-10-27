<?php
$host = "127.0.0.1";
$usuario_db = "root";
$senha_db = "";
$banco = "gestao_cafeteria";
$porta = 3307;

$conexao = mysqli_connect(
    $host, 
    $usuario_db, 
    $senha_db, 
    $banco,
    $porta
    );

if (!$conexao) {
    die("Erro ao conectar ao banco de dados.");
}

mysqli_set_charset($conexao, "utf8mb4");
