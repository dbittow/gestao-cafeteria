<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION["usuario_id"])) {
    header("Location: login.php");
    exit;
}

$mensagem = "";
$tipo_mensagem = "sucesso";

if (isset($_SESSION["mensagem"])) {
    $mensagem = $_SESSION["mensagem"];
    $tipo_mensagem = $_SESSION["tipo_mensagem"] ?? "sucesso";
    unset($_SESSION["mensagem"], $_SESSION["tipo_mensagem"]);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nome_item = trim($_POST["nome_item"] ?? "");
    $categoria = trim($_POST["categoria"] ?? "");
    $descricao = trim($_POST["descricao"] ?? "");
    $preco_texto = str_replace(",", ".", trim($_POST["preco"] ?? ""));
    $categorias_validas = ["Café", "Doce", "Salgado", "Bebida Gelada"];

    if ($nome_item === "" || !in_array($categoria, $categorias_validas, true) || !is_numeric($preco_texto) || (float)$preco_texto < 0) {
        $mensagem = "Preencha os campos corretamente. O preço não pode ser negativo.";
        $tipo_mensagem = "erro";
    } else {
        $preco = (float)$preco_texto;
        $sql = "INSERT INTO cardapio (nome_item, categoria, descricao, preco) VALUES (?, ?, ?, ?)";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sssd", $nome_item, $categoria, $descricao, $preco);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION["mensagem"] = "Item cadastrado com sucesso.";
            $_SESSION["tipo_mensagem"] = "sucesso";
            mysqli_stmt_close($stmt);
            header("Location: painel.php");
            exit;
        }

        $mensagem = "Não foi possível cadastrar o item.";
        $tipo_mensagem = "erro";
        mysqli_stmt_close($stmt);
    }
}

$sql = "SELECT id, nome_item, categoria, descricao, preco, disponivel FROM cardapio ORDER BY id DESC";
$resultado = mysqli_query($conexao, $sql);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel | Gestão da Cafeteria</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="topo">
        <div>
            <h1>Gestão da Cafeteria</h1>
            <p>Olá, <?= htmlspecialchars($_SESSION["usuario_nome"], ENT_QUOTES, "UTF-8") ?>.</p>
        </div>
        <a class="botao botao-secundario" href="logout.php">Sair</a>
    </header>

    <main class="container">
        <?php if ($mensagem !== ""): ?>
            <p class="alerta <?= $tipo_mensagem === "erro" ? "erro" : "sucesso" ?>">
                <?= htmlspecialchars($mensagem, ENT_QUOTES, "UTF-8") ?>
            </p>
        <?php endif; ?>

        <section class="card">
            <h2>Cadastrar item</h2>
            <form method="POST" action="painel.php" class="formulario">
                <div>
                    <label for="nome_item">Nome do item</label>
                    <input type="text" id="nome_item" name="nome_item" maxlength="100" required>
                </div>

                <div>
                    <label for="categoria">Categoria</label>
                    <select id="categoria" name="categoria" required>
                        <option value="">Selecione</option>
                        <option value="Café">Café</option>
                        <option value="Doce">Doce</option>
                        <option value="Salgado">Salgado</option>
                        <option value="Bebida Gelada">Bebida Gelada</option>
                    </select>
                </div>

                <div class="campo-largo">
                    <label for="descricao">Descrição</label>
                    <textarea id="descricao" name="descricao" rows="3"></textarea>
                </div>

                <div>
                    <label for="preco">Preço (R$)</label>
                    <input type="number" id="preco" name="preco" min="0" step="0.01" required>
                </div>

                <div class="campo-largo">
                    <button type="submit" class="botao">Cadastrar item</button>
                </div>
            </form>
        </section>

        <section class="card">
            <h2>Itens do cardápio</h2>
            <div class="tabela-responsiva">
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Descrição</th>
                            <th>Preço</th>
                            <th>Disponível</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (mysqli_num_rows($resultado) === 0): ?>
                            <tr><td colspan="6">Nenhum item cadastrado.</td></tr>
                        <?php else: ?>
                            <?php while ($item = mysqli_fetch_assoc($resultado)): ?>
                                <tr>
                                    <td><?= htmlspecialchars($item["nome_item"], ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($item["categoria"], ENT_QUOTES, "UTF-8") ?></td>
                                    <td><?= htmlspecialchars($item["descricao"] ?? "", ENT_QUOTES, "UTF-8") ?></td>
                                    <td>R$ <?= number_format((float)$item["preco"], 2, ",", ".") ?></td>
                                    <td><?= $item["disponivel"] ? "Sim" : "Não" ?></td>
                                    <td class="acoes">
                                        <a class="botao botao-editar" href="editar_item.php?id=<?= (int)$item["id"] ?>">Editar</a>
                                        <form method="POST" action="excluir_item.php" onsubmit="return confirm('Tem certeza que deseja excluir este item?');">
                                            <input type="hidden" name="id" value="<?= (int)$item["id"] ?>">
                                            <button type="submit" class="botao botao-excluir">Excluir</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</body>
</html>
