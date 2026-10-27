<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);
if (!$id || $id < 1) {
    header("Location: painel.php");
    exit;
}

$stmt = mysqli_prepare($conexao, "SELECT id, nome_item, categoria, descricao, preco FROM cardapio WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$item = mysqli_fetch_assoc($resultado);
mysqli_stmt_close($stmt);

if (!$item) {
    $_SESSION["mensagem"] = "Item não encontrado.";
    $_SESSION["tipo_mensagem"] = "erro";
    header("Location: painel.php");
    exit;
}

$erro = "";
$categorias_validas = ["Café", "Doce", "Salgado", "Bebida Gelada"];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_item = trim($_POST["nome_item"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco_texto = str_replace(",", ".", trim($_POST["preco"] ?? ""));

    if ($nome_item === "" || !in_array($categoria, $categorias_validas, true) || !is_numeric($preco_texto) || (float)$preco_texto < 0) {
        $erro = "Preencha os campos corretamente. O preço não pode ser negativo.";
        $item["nome_item"] = $nome_item;
        $item["categoria"] = $categoria;
        $item["descricao"] = $descricao;
        $item["preco"] = $preco_texto;
    } else {
        $preco = (float)$preco_texto;
        $sql = "UPDATE cardapio SET nome_item = ?, categoria = ?, descricao = ?, preco = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sssdi", $nome_item, $categoria, $descricao, $preco, $id);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            $_SESSION["mensagem"] = "Item atualizado com sucesso.";
            $_SESSION["tipo_mensagem"] = "sucesso";
            header("Location: painel.php");
            exit;
        }

        $erro = "Não foi possível atualizar o item.";
        mysqli_stmt_close($stmt);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar item | Gestão da Cafeteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <main class="container container-menor">
        <section class="card">
            <h1>Editar item</h1>

            <?php if ($erro !== ""): ?>
                <p class="alerta erro"><?= htmlspecialchars($erro, ENT_QUOTES, "UTF-8") ?></p>
            <?php endif; ?>

            <form method="POST" action="editar_item.php?id=<?= (int)$id ?>" class="formulario">
                <div class="campo-largo">
                    <label for="nome_item">Nome do item</label>
                    <input type="text" id="nome_item" name="nome_item" maxlength="100" required value="<?= htmlspecialchars($item["nome_item"], ENT_QUOTES, "UTF-8") ?>">
                </div>

                <div class="campo-largo">
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" required>
                        <?php foreach ($categorias_validas as $categoria_opcao): ?>
                            <option value="<?= htmlspecialchars($categoria_opcao, ENT_QUOTES, "UTF-8") ?>" <?= $item["categoria"] === $categoria_opcao ? "selected" : "" ?>>
                                <?= htmlspecialchars($categoria_opcao, ENT_QUOTES, "UTF-8") ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="campo-largo">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="4"><?= htmlspecialchars($item["descricao"] ?? "", ENT_QUOTES, "UTF-8") ?></textarea>
                </div>

                <div class="campo-largo">
                    <label for="preco">Preço (R$)</label>
                    <input type="number" id="preco" name="preco" min="0" step="0.01" required value="<?= htmlspecialchars((string)$item["preco"], ENT_QUOTES, "UTF-8") ?>">
                </div>

                <div class="acoes">
                    <button type="submit" class="botao">Salvar alterações</button>
                    <a href="painel.php" class="botao botao-secundario">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
