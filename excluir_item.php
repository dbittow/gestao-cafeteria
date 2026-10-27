<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: painel.php");
    exit;
}

$id = filter_input(INPUT_POST, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    $_SESSION["mensagem"] = "ID de item inválido.";
    $_SESSION["tipo_mensagem"] = "erro";
    header("Location: painel.php");
    exit;
}

$stmt = mysqli_prepare($conexao, "DELETE FROM cardapio WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);

if (mysqli_stmt_affected_rows($stmt) > 0) {
    $_SESSION["mensagem"] = "Item excluído com sucesso.";
    $_SESSION["tipo_mensagem"] = "sucesso";
} else {
    $_SESSION["mensagem"] = "Item não encontrado.";
    $_SESSION["tipo_mensagem"] = "erro";
}

mysqli_stmt_close($stmt);
header("Location: painel.php");
exit;
