<?php
// Conexão com o banco
$conn = pg_connect("host=localhost dbname=financeiro user=admin password=admin");
if (!$conn) {
    die("Erro na conexão com o banco.");
}

// Filtros
$banco_id = $_GET['banco_id'] ?? '';
$numero_conta = $_GET['numero_conta'] ?? '';
$tipo_operacao = $_GET['tipo_operacao'] ?? '';
$data_inicio = $_GET['data_inicio'] ?? '';
$data_fim = $_GET['data_fim'] ?? '';
$categoria_id = $_GET['categoria_id'] ?? '';

// Monta a query com filtros
$where = "WHERE 1=1";

if ($banco_id !== '') {
    $where .= " AND banco_id = '" . pg_escape_string($banco_id) . "'";
}
if ($numero_conta !== '') {
    $where .= " AND numero_conta = '" . pg_escape_string($numero_conta) . "'";
}
if ($tipo_operacao !== '') {
    $where .= " AND tipo_operacao = '" . pg_escape_string($tipo_operacao) . "'";
}
if ($data_inicio !== '') {
    $where .= " AND data_operacao >= '" . pg_escape_string($data_inicio) . "'";
}
if ($data_fim !== '') {
    $where .= " AND data_operacao <= '" . pg_escape_string($data_fim) . "'";
}
if ($categoria_id !== '') {
    $where .= " AND categoria_id = " . intval($categoria_id);
}

$query = "
    SELECT m.*, c.descricao AS categoria
    FROM movimentacoes_bancarias m
    LEFT JOIN categorias c ON c.id = m.categoria_id
    $where
    ORDER BY data_operacao DESC
";
$resultado = pg_query($conn, $query);

// Carrega categorias para o filtro
$categorias = pg_query($conn, "SELECT id, descricao FROM categorias ORDER BY descricao");

// Para totalizar valores da página atual:
$total_valor = 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8" />
    <title>Movimentações Bancárias</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-4Q6Gf2aSP4eDXB8Miphtr37CMZZQ5oXLH2yaXMJ2w8e2ZtHTl7GptT4jmndRuHDT" crossorigin="anonymous">
</head>
<body>
<div class="container mt-4">
    <h1 class="mb-4">Movimentações Bancárias</h1>

    <form method="get" class="row g-3 align-items-center">
        <div class="col-md-2">
            <input type="text" name="banco_id" class="form-control" placeholder="Banco" value="<?= htmlspecialchars($banco_id) ?>">
        </div>
        <div class="col-md-2">
            <input type="text" name="numero_conta" class="form-control" placeholder="Conta" value="<?= htmlspecialchars($numero_conta) ?>">
        </div>
        <div class="col-md-2">
            <select name="tipo_operacao" class="form-select">
                <option value="">Tipo</option>
                <option value="DEBIT" <?= $tipo_operacao == 'DEBIT' ? 'selected' : '' ?>>Débito</option>
                <option value="CREDIT" <?= $tipo_operacao == 'CREDIT' ? 'selected' : '' ?>>Crédito</option>
            </select>
        </div>
        <div class="col-md-2">
            <input type="date" name="data_inicio" class="form-control" value="<?= htmlspecialchars($data_inicio) ?>">
        </div>
        <div class="col-md-2">
            <input type="date" name="data_fim" class="form-control" value="<?= htmlspecialchars($data_fim) ?>">
        </div>
        <div class="col-md-2">
            <select name="categoria_id" class="form-select">
                <option value="">Categoria</option>
                <?php while ($cat = pg_fetch_assoc($categorias)): ?>
                    <option value="<?= $cat['id'] ?>" <?= $categoria_id == $cat['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['descricao']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-12">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="listar_movimentacoes.php" class="btn btn-secondary">Limpar</a>
        </div>
    </form>

    <table class="table table-striped table-hover mt-4">
        <thead class="table-dark">
            <tr>
                <th>Data</th>
                <th>Banco</th>
                <th>Conta</th>
                <th>Tipo</th>
                <th>Valor</th>
                <th>Descrição</th>
                <th>Categoria</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = pg_fetch_assoc($resultado)): 
                // Formatar data para dd/mm/yyyy
                $dataFormatada = date('d/m/Y', strtotime($row['data_operacao']));
                // Acumula total
                $total_valor += $row['valor'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($dataFormatada) ?></td>
                    <td><?= htmlspecialchars($row['banco_id']) ?></td>
                    <td><?= htmlspecialchars($row['numero_conta']) ?></td>
                    <td><?= htmlspecialchars($row['tipo_operacao']) ?></td>
                    <td><?= number_format($row['valor'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($row['descricao']) ?></td>
                    <td><?= htmlspecialchars($row['categoria']) ?></td>
                </tr>
            <?php endwhile; ?>
            <?php if(pg_num_rows($resultado) == 0): ?>
                <tr><td colspan="7" class="text-center">Nenhum registro encontrado.</td></tr>
            <?php endif; ?>
        </tbody>
        <tfoot class="table-secondary fw-bold">
            <tr>
                <td colspan="4" class="text-end">Total:</td>
                <td><?= number_format($total_valor, 2, ',', '.') ?></td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js" integrity="sha384-j1CDi7MgGQ12Z7Qab0qlWQ/Qqz24Gc6BM0thvEMVjHnfYGF0rmFCozFSxQBxwHKO" crossorigin="anonymous"></script>
</body>
</html>

