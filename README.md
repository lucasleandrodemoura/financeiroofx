# financeiroofx


CREATE TABLE movimentacoes_bancarias (
    id SERIAL PRIMARY KEY,
    banco_id VARCHAR(20),
    numero_conta VARCHAR(30),
    tipo_conta VARCHAR(20),
    tipo_operacao VARCHAR(10), -- CREDIT ou DEBIT
    data_operacao TIMESTAMP,
    valor NUMERIC(15,2),
    identificador_operacao VARCHAR(50),
    descricao TEXT
);
