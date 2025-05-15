<?php
$arquivo_ofx = 'extrato.ofx'; // Caminho para o arquivo OFX

// Conexão com o banco PostgreSQL
$conn = pg_connect("host=localhost dbname=financeiro user=admin password=admin");
if (!$conn) {
    die("Erro ao conectar no banco de dados.");
}

// Lê o conteúdo do arquivo
$conteudo = file_get_contents($arquivo_ofx);

// Função para extrair conteúdo mesmo sem tag de fechamento
function extrairTag($texto, $tag) {
    preg_match("/<$tag>(.*?)(?=<|\r|\n)/", $texto, $match);
    return isset($match[1]) ? trim($match[1]) : null;
}

// Extrai dados da conta bancária
$banco_id = extrairTag($conteudo, "BANKID");
$numero_conta = extrairTag($conteudo, "ACCTID");
$tipo_conta = extrairTag($conteudo, "ACCTTYPE");

// Extrai todas as transações
preg_match_all('/<STMTTRN>(.*?)<\/STMTTRN>/s', $conteudo, $transacoes);

foreach ($transacoes[1] as $transacao) {
    $tipo_operacao = extrairTag($transacao, 'TRNTYPE');
    $data_bruta = extrairTag($transacao, 'DTPOSTED');
    $valor = extrairTag($transacao, 'TRNAMT');
    $identificador_operacao = extrairTag($transacao, 'FITID');
    $descricao = pg_escape_string(extrairTag($transacao, 'MEMO'));

    // Ajuste de data para ignorar o fuso horário ([-03:EST])
    $data_operacao = date('Y-m-d', strtotime(substr($data_bruta, 0, 8)));


	// Verifica duplicidade com base em banco_id, numero_conta e identificador_operacao
	$check_query = "
	    SELECT 1 
	    FROM movimentacoes_bancarias 
	    WHERE banco_id = '$banco_id' 
	      AND numero_conta = '$numero_conta' 
	      AND identificador_operacao = '$identificador_operacao'
	    LIMIT 1
	";
	$check_result = pg_query($conn, $check_query);

	if (pg_num_rows($check_result) > 0) {
	    echo "Transação duplicada: $banco_id / $numero_conta / $identificador_operacao - $descricao\n";
	    continue;
	}

    // Insere no banco
    $query = "
        INSERT INTO movimentacoes_bancarias (
            banco_id, numero_conta, tipo_conta,
            tipo_operacao, data_operacao, valor,
            identificador_operacao, descricao
        ) VALUES (
            '$banco_id', '$numero_conta', '$tipo_conta',
            '$tipo_operacao', '$data_operacao', $valor,
            '$identificador_operacao', '$descricao'
        );
    ";

    $resultado = pg_query($conn, $query);
    if (!$resultado) {
        echo "Erro ao inserir transação: $identificador_operacao - $descricao\n";
    } else {
        echo "Transação inserida: $identificador_operacao - $descricao\n";
    }
}

echo "Importação concluída.\n";
pg_close($conn);
?>

