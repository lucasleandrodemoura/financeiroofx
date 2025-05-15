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



CREATE TABLE categorias (
    id SERIAL PRIMARY KEY,
    descricao TEXT NOT NULL,
    categoria_pai_id INTEGER,
    
    CONSTRAINT fk_categoria_pai
        FOREIGN KEY (categoria_pai_id)
        REFERENCES categorias(id)
        ON DELETE SET NULL
);




-- Categoria pai
INSERT INTO categorias (descricao) VALUES ('Alimentação');

-- Subcategoria
INSERT INTO categorias (descricao, categoria_pai_id) VALUES ('Restaurantes', 1);

