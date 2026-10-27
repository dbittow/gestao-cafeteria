<?php
session_start();
require_once "conexao.php";

if (isset($_SESSION["usuario_id"])) {
    header("Location: painel.php");
    exit;
}

$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario = trim($_POST["usuario"] ?? "");
    $senha = $_POST["senha"] ?? "";

    if ($usuario !== "" && $senha !== "") {
        $sql = "SELECT id, nome, usuario, senha FROM usuarios WHERE usuario = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "s", $usuario);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);
        $dados = mysqli_fetch_assoc($resultado);

        if ($dados) {
            $senha_valida = password_verify($senha, $dados["senha"]);

            if (!$senha_valida && hash_equals($dados["senha"], $senha)) {
                $nova_senha = password_hash($senha, PASSWORD_DEFAULT);
                $atualizar = mysqli_prepare($conexao, "UPDATE usuarios SET senha = ? WHERE id = ?");
                mysqli_stmt_bind_param($atualizar, "si", $nova_senha, $dados["id"]);
                mysqli_stmt_execute($atualizar);
                mysqli_stmt_close($atualizar);
                $senha_valida = true;
            }

            if ($senha_valida) {
                session_regenerate_id(true);
                $_SESSION["usuario_id"] = $dados["id"];
                $_SESSION["usuario_nome"] = $dados["nome"];
                header("Location: painel.php");
                exit;
            }
        }

        mysqli_stmt_close($stmt);
    }

    $erro = "Usuário ou senha inválidos.";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Gestão da Cafeteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="pagina-login">
    <main class="login-card">
        <h1>Gestão da Cafeteria</h1>
        <p class="subtitulo">Entre para gerenciar o cardápio</p>

        <?php if ($erro !== ""): ?>
            <p class="alerta erro"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="usuario">Usuário</label>
            <input type="text" id="usuario" name="usuario" required autocomplete="username">

            <label for="senha">Senha</label>
            <input type="password" id="senha" name="senha" required autocomplete="current-password">

            <button type="submit" class="botao">Entrar</button>
        </form>
        <p class="dica">Acesso inicial: admin / admin123</p>
    </main>
</body>
</html>
