-- =====================================================
-- PORTAL - Script SQL Completo para PostgreSQL
-- Gerado a partir das migrations Laravel
-- Idempotente: Pode ser executado múltiplas vezes
-- =====================================================

-- =====================================================
-- TABELA: users
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    data_nasc DATE NULL,
    papel VARCHAR(255) NOT NULL,
    cgc VARCHAR(255) NULL,
    celular VARCHAR(255) NULL,
    valor_hora VARCHAR(255) NULL,
    valor_desloc VARCHAR(255) NULL,
    valor_km VARCHAR(255) NULL,
    salario_base VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    codigo VARCHAR(255) NULL
);

-- Índices e constraints para users
CREATE UNIQUE INDEX IF NOT EXISTS users_email_unique ON users(email);
CREATE UNIQUE INDEX IF NOT EXISTS users_codigo_unique ON users(codigo);
CREATE INDEX IF NOT EXISTS users_papel_idx ON users(papel);

-- Adicionar colunas se não existirem em users
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='codigo') THEN
        ALTER TABLE users ADD COLUMN codigo VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='data_nasc') THEN
        ALTER TABLE users ADD COLUMN data_nasc DATE NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='valor_desloc') THEN
        ALTER TABLE users ADD COLUMN valor_desloc VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='valor_km') THEN
        ALTER TABLE users ADD COLUMN valor_km VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='users' AND column_name='salario_base') THEN
        ALTER TABLE users ADD COLUMN salario_base VARCHAR(255) NULL;
    END IF;
END $$;

-- =====================================================
-- TABELA: password_reset_tokens
-- =====================================================
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- =====================================================
-- TABELA: sessions
-- =====================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS sessions_user_id_idx ON sessions(user_id);
CREATE INDEX IF NOT EXISTS sessions_last_activity_idx ON sessions(last_activity);

-- =====================================================
-- TABELA: cache
-- =====================================================
CREATE TABLE IF NOT EXISTS cache (
    key VARCHAR(255) PRIMARY KEY,
    value TEXT NOT NULL,
    expiration INTEGER NOT NULL
);

-- =====================================================
-- TABELA: cache_locks
-- =====================================================
CREATE TABLE IF NOT EXISTS cache_locks (
    key VARCHAR(255) PRIMARY KEY,
    owner VARCHAR(255) NOT NULL,
    expiration INTEGER NOT NULL
);

-- =====================================================
-- TABELA: jobs
-- =====================================================
CREATE TABLE IF NOT EXISTS jobs (
    id BIGSERIAL PRIMARY KEY,
    queue VARCHAR(255) NOT NULL,
    payload TEXT NOT NULL,
    attempts SMALLINT NOT NULL,
    reserved_at INTEGER NULL,
    available_at INTEGER NOT NULL,
    created_at INTEGER NOT NULL
);

CREATE INDEX IF NOT EXISTS jobs_queue_idx ON jobs(queue);

-- =====================================================
-- TABELA: job_batches
-- =====================================================
CREATE TABLE IF NOT EXISTS job_batches (
    id VARCHAR(255) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    total_jobs INTEGER NOT NULL,
    pending_jobs INTEGER NOT NULL,
    failed_jobs INTEGER NOT NULL,
    failed_job_ids TEXT NOT NULL,
    options TEXT NULL,
    cancelled_at INTEGER NULL,
    created_at INTEGER NOT NULL,
    finished_at INTEGER NULL
);

-- =====================================================
-- TABELA: failed_jobs
-- =====================================================
CREATE TABLE IF NOT EXISTS failed_jobs (
    id BIGSERIAL PRIMARY KEY,
    uuid VARCHAR(255) NOT NULL,
    connection TEXT NOT NULL,
    queue TEXT NOT NULL,
    payload TEXT NOT NULL,
    exception TEXT NOT NULL,
    failed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE UNIQUE INDEX IF NOT EXISTS failed_jobs_uuid_unique ON failed_jobs(uuid);

-- =====================================================
-- TABELA: fornecedor
-- =====================================================
CREATE TABLE IF NOT EXISTS fornecedor (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    loja VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NULL,
    tipo VARCHAR(255) NULL,
    cgc VARCHAR(255) NULL,
    contato VARCHAR(255) NULL,
    endereco VARCHAR(255) NULL,
    municipio VARCHAR(255) NULL,
    estado VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =====================================================
-- TABELA: produto
-- =====================================================
CREATE TABLE IF NOT EXISTS produto (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    narrativa TEXT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS produto_codigo_unique ON produto(codigo);

-- =====================================================
-- TABELA: tabela_preco
-- =====================================================
CREATE TABLE IF NOT EXISTS tabela_preco (
    id BIGSERIAL PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    data_inicio DATE NULL,
    data_vencimento DATE NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Adicionar colunas de data se não existirem
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='tabela_preco' AND column_name='data_inicio') THEN
        ALTER TABLE tabela_preco ADD COLUMN data_inicio DATE NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='tabela_preco' AND column_name='data_vencimento') THEN
        ALTER TABLE tabela_preco ADD COLUMN data_vencimento DATE NULL;
    END IF;
END $$;

-- =====================================================
-- TABELA: estados
-- =====================================================
CREATE TABLE IF NOT EXISTS estados (
    id BIGSERIAL PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sigla VARCHAR(2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS estados_nome_unique ON estados(nome);
CREATE UNIQUE INDEX IF NOT EXISTS estados_sigla_unique ON estados(sigla);

-- =====================================================
-- TABELA: cidades
-- =====================================================
CREATE TABLE IF NOT EXISTS cidades (
    id BIGSERIAL PRIMARY KEY,
    estado_id BIGINT NOT NULL,
    nome VARCHAR(150) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para cidades
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'cidades_estado_id_foreign') THEN
        ALTER TABLE cidades ADD CONSTRAINT cidades_estado_id_foreign
            FOREIGN KEY (estado_id) REFERENCES estados(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE UNIQUE INDEX IF NOT EXISTS cidades_estado_id_nome_unique ON cidades(estado_id, nome);

-- =====================================================
-- TABELA: cliente
-- =====================================================
CREATE TABLE IF NOT EXISTS cliente (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    loja VARCHAR(255) NOT NULL,
    nome VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NULL,
    tipo VARCHAR(255) NULL,
    cgc VARCHAR(255) NULL,
    cep VARCHAR(10) NULL,
    contato VARCHAR(255) NULL,
    endereco VARCHAR(255) NULL,
    numero VARCHAR(50) NULL,
    complemento VARCHAR(255) NULL,
    municipio VARCHAR(255) NULL,
    estado VARCHAR(255) NULL,
    km VARCHAR(255) NULL,
    deslocamento VARCHAR(255) NULL,
    tabela_preco_id BIGINT NULL,
    valor_hora DECIMAL(10, 2) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Adicionar colunas se não existirem em cliente
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='cliente' AND column_name='cep') THEN
        ALTER TABLE cliente ADD COLUMN cep VARCHAR(10) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='cliente' AND column_name='numero') THEN
        ALTER TABLE cliente ADD COLUMN numero VARCHAR(50) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='cliente' AND column_name='complemento') THEN
        ALTER TABLE cliente ADD COLUMN complemento VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='cliente' AND column_name='valor_hora') THEN
        ALTER TABLE cliente ADD COLUMN valor_hora DECIMAL(10, 2) NULL;
    END IF;

    -- Alterar tamanho das colunas se necessário
    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='cliente' AND column_name='estado'
               AND character_maximum_length < 255) THEN
        ALTER TABLE cliente ALTER COLUMN estado TYPE VARCHAR(255);
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='cliente' AND column_name='municipio'
               AND character_maximum_length < 255) THEN
        ALTER TABLE cliente ALTER COLUMN municipio TYPE VARCHAR(255);
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS cliente_tabela_preco_id_idx ON cliente(tabela_preco_id);

-- =====================================================
-- TABELA: projetos
-- =====================================================
CREATE TABLE IF NOT EXISTS projetos (
    id BIGSERIAL PRIMARY KEY,
    codigo VARCHAR(255) NOT NULL,
    numero_atendimento VARCHAR(255) NULL,
    cliente_id BIGINT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    descricao TEXT NULL,
    status VARCHAR(50) DEFAULT 'ativo',
    data_inicio DATE NULL,
    data_fim DATE NULL,
    horas_alocadas DECIMAL(10, 2) DEFAULT 0,
    horas_consumidas DECIMAL(10, 2) DEFAULT 0,
    horas_restantes DECIMAL(10, 2) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS projetos_codigo_unique ON projetos(codigo);

-- Foreign key para projetos
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'projetos_cliente_id_foreign') THEN
        ALTER TABLE projetos ADD CONSTRAINT projetos_cliente_id_foreign
            FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE CASCADE;
    END IF;
END $$;

-- Adicionar colunas de horas se não existirem em projetos
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='projetos' AND column_name='horas_alocadas') THEN
        ALTER TABLE projetos ADD COLUMN horas_alocadas DECIMAL(10, 2) DEFAULT 0;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='projetos' AND column_name='horas_consumidas') THEN
        ALTER TABLE projetos ADD COLUMN horas_consumidas DECIMAL(10, 2) DEFAULT 0;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='projetos' AND column_name='horas_restantes') THEN
        ALTER TABLE projetos ADD COLUMN horas_restantes DECIMAL(10, 2) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='projetos' AND column_name='numero_atendimento') THEN
        ALTER TABLE projetos ADD COLUMN numero_atendimento VARCHAR(255) NULL;
    END IF;
END $$;

-- =====================================================
-- TABELA: ordem_servico
-- =====================================================
CREATE TABLE IF NOT EXISTS ordem_servico (
    id BIGSERIAL PRIMARY KEY,
    consultor_id BIGINT NOT NULL,
    cliente_id BIGINT NOT NULL,
    data_emissao VARCHAR(255) NOT NULL,
    tipo_despesa VARCHAR(255) NULL,
    valor_despesa VARCHAR(255) NULL,
    detalhamento_despesa VARCHAR(255) NULL,
    status INTEGER NOT NULL,
    approval_status VARCHAR(255) DEFAULT 'pending',
    approved_at TIMESTAMP NULL,
    approved_by BIGINT NULL,
    motivo_contestacao TEXT NULL,
    assunto VARCHAR(255) NULL,
    projeto VARCHAR(255) NULL,
    nr_atendimento VARCHAR(255) NULL,
    preco_produto VARCHAR(255) NULL,
    valor_total VARCHAR(255) NULL,
    horas_trabalhadas DECIMAL(8, 2) DEFAULT 0,
    km VARCHAR(255) NULL,
    deslocamento DECIMAL(10, 2) NULL,
    is_presencial BOOLEAN DEFAULT FALSE,
    observacao TEXT NULL,
    descricao TEXT NULL,
    produto_tabela_id BIGINT NOT NULL,
    hora_inicio VARCHAR(255) NULL,
    hora_final VARCHAR(255) NULL,
    hora_desconto VARCHAR(255) NULL,
    qtde_total VARCHAR(255) NULL,
    detalhamento TEXT NULL,
    nr_rps INTEGER NULL,
    cond_pagto VARCHAR(255) NULL,
    valor_rps VARCHAR(255) NULL,
    projeto_id BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para ordem_servico
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ordem_servico_approved_by_foreign') THEN
        ALTER TABLE ordem_servico ADD CONSTRAINT ordem_servico_approved_by_foreign
            FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ordem_servico_projeto_id_foreign') THEN
        ALTER TABLE ordem_servico ADD CONSTRAINT ordem_servico_projeto_id_foreign
            FOREIGN KEY (projeto_id) REFERENCES projetos(id) ON DELETE SET NULL;
    END IF;
END $$;

-- Índices para ordem_servico
CREATE INDEX IF NOT EXISTS ordem_servico_consultor_id_idx ON ordem_servico(consultor_id);
CREATE INDEX IF NOT EXISTS ordem_servico_cliente_id_idx ON ordem_servico(cliente_id);
CREATE INDEX IF NOT EXISTS ordem_servico_status_idx ON ordem_servico(status);
CREATE INDEX IF NOT EXISTS ordem_servico_created_at_idx ON ordem_servico(created_at);

-- Adicionar colunas se não existirem em ordem_servico
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='approval_status') THEN
        ALTER TABLE ordem_servico ADD COLUMN approval_status VARCHAR(255) DEFAULT 'pending';
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='approved_at') THEN
        ALTER TABLE ordem_servico ADD COLUMN approved_at TIMESTAMP NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='approved_by') THEN
        ALTER TABLE ordem_servico ADD COLUMN approved_by BIGINT NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='horas_trabalhadas') THEN
        ALTER TABLE ordem_servico ADD COLUMN horas_trabalhadas DECIMAL(8, 2) DEFAULT 0;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='km') THEN
        ALTER TABLE ordem_servico ADD COLUMN km VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='deslocamento') THEN
        ALTER TABLE ordem_servico ADD COLUMN deslocamento DECIMAL(10, 2) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='observacao') THEN
        ALTER TABLE ordem_servico ADD COLUMN observacao TEXT NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='descricao') THEN
        ALTER TABLE ordem_servico ADD COLUMN descricao TEXT NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='projeto_id') THEN
        ALTER TABLE ordem_servico ADD COLUMN projeto_id BIGINT NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='ordem_servico' AND column_name='is_presencial') THEN
        ALTER TABLE ordem_servico ADD COLUMN is_presencial BOOLEAN DEFAULT FALSE;
    END IF;
END $$;

-- =====================================================
-- TABELA: contato
-- =====================================================
CREATE TABLE IF NOT EXISTS contato (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(255) NULL,
    recebe_email_os BOOLEAN DEFAULT TRUE,
    aniversario VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para contato
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'contato_cliente_id_foreign') THEN
        ALTER TABLE contato ADD CONSTRAINT contato_cliente_id_foreign
            FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS contato_cliente_id_idx ON contato(cliente_id);

-- Adicionar colunas se não existirem em contato
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='contato' AND column_name='telefone') THEN
        ALTER TABLE contato ADD COLUMN telefone VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='contato' AND column_name='recebe_email_os') THEN
        ALTER TABLE contato ADD COLUMN recebe_email_os BOOLEAN DEFAULT TRUE;
    END IF;
END $$;

-- =====================================================
-- TABELA: produto_tabela
-- =====================================================
CREATE TABLE IF NOT EXISTS produto_tabela (
    id BIGSERIAL PRIMARY KEY,
    produto_id BIGINT NOT NULL,
    tabela_preco_id BIGINT NOT NULL,
    preco VARCHAR(255) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para produto_tabela
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'produto_tabela_produto_id_foreign') THEN
        ALTER TABLE produto_tabela ADD CONSTRAINT produto_tabela_produto_id_foreign
            FOREIGN KEY (produto_id) REFERENCES produto(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'produto_tabela_tabela_preco_id_foreign') THEN
        ALTER TABLE produto_tabela ADD CONSTRAINT produto_tabela_tabela_preco_id_foreign
            FOREIGN KEY (tabela_preco_id) REFERENCES tabela_preco(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS produto_tabela_produto_id_idx ON produto_tabela(produto_id);
CREATE INDEX IF NOT EXISTS produto_tabela_tabela_preco_id_idx ON produto_tabela(tabela_preco_id);

-- =====================================================
-- TABELA: produto_ordem
-- =====================================================
CREATE TABLE IF NOT EXISTS produto_ordem (
    id BIGSERIAL PRIMARY KEY,
    produto_id BIGINT NOT NULL,
    ordem_servico_id BIGINT NOT NULL,
    hora_inicio VARCHAR(255) NOT NULL,
    hora_final VARCHAR(255) NOT NULL,
    hora_desconto VARCHAR(255) NOT NULL,
    qtde_total VARCHAR(255) NOT NULL,
    detalhamento VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para produto_ordem
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'produto_ordem_produto_id_foreign') THEN
        ALTER TABLE produto_ordem ADD CONSTRAINT produto_ordem_produto_id_foreign
            FOREIGN KEY (produto_id) REFERENCES produto(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'produto_ordem_ordem_servico_id_foreign') THEN
        ALTER TABLE produto_ordem ADD CONSTRAINT produto_ordem_ordem_servico_id_foreign
            FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servico(id) ON DELETE CASCADE;
    END IF;
END $$;

-- =====================================================
-- TABELA: recibo_provisorio
-- =====================================================
CREATE TABLE IF NOT EXISTS recibo_provisorio (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL,
    numero INTEGER NOT NULL,
    serie INTEGER NULL,
    data_emissao VARCHAR(255) NULL,
    cond_pagto VARCHAR(255) NOT NULL,
    valor VARCHAR(255) NOT NULL,
    consolidada BOOLEAN DEFAULT FALSE,
    ordens_consolidadas TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para recibo_provisorio
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'recibo_provisorio_cliente_id_foreign') THEN
        ALTER TABLE recibo_provisorio ADD CONSTRAINT recibo_provisorio_cliente_id_foreign
            FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE UNIQUE INDEX IF NOT EXISTS recibo_provisorio_numero_unique ON recibo_provisorio(numero);
CREATE INDEX IF NOT EXISTS recibo_provisorio_cliente_id_idx ON recibo_provisorio(cliente_id);

-- Adicionar colunas se não existirem em recibo_provisorio
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='recibo_provisorio' AND column_name='consolidada') THEN
        ALTER TABLE recibo_provisorio ADD COLUMN consolidada BOOLEAN DEFAULT FALSE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='recibo_provisorio' AND column_name='ordens_consolidadas') THEN
        ALTER TABLE recibo_provisorio ADD COLUMN ordens_consolidadas TEXT NULL;
    END IF;
END $$;

-- =====================================================
-- TABELA: pagamento_parcelas
-- =====================================================
CREATE TABLE IF NOT EXISTS pagamento_parcelas (
    id BIGSERIAL PRIMARY KEY,
    recibo_provisorio_id BIGINT NOT NULL,
    numero_parcela INTEGER NOT NULL,
    total_parcelas INTEGER NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    data_vencimento DATE NOT NULL,
    data_pagamento DATE NULL,
    status VARCHAR(50) DEFAULT 'pendente',
    observacao TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para pagamento_parcelas
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'pagamento_parcelas_recibo_provisorio_id_foreign') THEN
        ALTER TABLE pagamento_parcelas ADD CONSTRAINT pagamento_parcelas_recibo_provisorio_id_foreign
            FOREIGN KEY (recibo_provisorio_id) REFERENCES recibo_provisorio(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS pagamento_parcelas_recibo_provisorio_id_idx ON pagamento_parcelas(recibo_provisorio_id);
CREATE INDEX IF NOT EXISTS pagamento_parcelas_status_idx ON pagamento_parcelas(status);
CREATE INDEX IF NOT EXISTS pagamento_parcelas_data_vencimento_idx ON pagamento_parcelas(data_vencimento);

-- =====================================================
-- TABELA: condicoes_pagamento
-- =====================================================
CREATE TABLE IF NOT EXISTS condicoes_pagamento (
    id BIGSERIAL PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    numero_parcelas INTEGER DEFAULT 1,
    intervalo_dias INTEGER DEFAULT 0,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- =====================================================
-- TABELA: relatorio_fechamento
-- =====================================================
CREATE TABLE IF NOT EXISTS relatorio_fechamento (
    id BIGSERIAL PRIMARY KEY,
    consultor_id BIGINT NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NOT NULL,
    valor_total DECIMAL(12, 2) DEFAULT 0,
    total_os INTEGER DEFAULT 0,
    status VARCHAR(50) DEFAULT 'rascunho',
    data_envio_email TIMESTAMP NULL,
    observacoes TEXT NULL,
    aprovado_por BIGINT NULL,
    data_aprovacao TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para relatorio_fechamento
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'relatorio_fechamento_consultor_id_foreign') THEN
        ALTER TABLE relatorio_fechamento ADD CONSTRAINT relatorio_fechamento_consultor_id_foreign
            FOREIGN KEY (consultor_id) REFERENCES users(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'relatorio_fechamento_aprovado_por_foreign') THEN
        ALTER TABLE relatorio_fechamento ADD CONSTRAINT relatorio_fechamento_aprovado_por_foreign
            FOREIGN KEY (aprovado_por) REFERENCES users(id) ON DELETE SET NULL;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS relatorio_fechamento_consultor_id_idx ON relatorio_fechamento(consultor_id);
CREATE INDEX IF NOT EXISTS relatorio_fechamento_aprovado_por_idx ON relatorio_fechamento(aprovado_por);

-- =====================================================
-- TABELA: sequences
-- =====================================================
CREATE TABLE IF NOT EXISTS sequences (
    id BIGSERIAL PRIMARY KEY,
    entity_type VARCHAR(255) NOT NULL,
    current_number BIGINT DEFAULT 0,
    prefix VARCHAR(255) DEFAULT '',
    min_digits INTEGER DEFAULT 4,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

CREATE UNIQUE INDEX IF NOT EXISTS sequences_entity_type_unique ON sequences(entity_type);

-- =====================================================
-- TABELA: rps
-- =====================================================
CREATE TABLE IF NOT EXISTS rps (
    id BIGSERIAL PRIMARY KEY,
    cliente_id BIGINT NOT NULL,
    numero_rps VARCHAR(255) NOT NULL,
    data_emissao DATE NOT NULL,
    data_vencimento DATE NULL,
    valor_total DECIMAL(12, 2) NOT NULL,
    valor_servicos DECIMAL(12, 2) DEFAULT 0,
    valor_deducoes DECIMAL(12, 2) DEFAULT 0,
    valor_impostos DECIMAL(12, 2) DEFAULT 0,
    status VARCHAR(255) DEFAULT 'emitida',
    observacoes TEXT NULL,
    criado_por BIGINT NOT NULL,
    cancelado_em TIMESTAMP NULL,
    cancelado_por BIGINT NULL,
    motivo_cancelamento TEXT NULL,
    revertido_em TIMESTAMP NULL,
    revertido_por BIGINT NULL,
    motivo_reversao TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para rps
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'rps_cliente_id_foreign') THEN
        ALTER TABLE rps ADD CONSTRAINT rps_cliente_id_foreign
            FOREIGN KEY (cliente_id) REFERENCES cliente(id) ON DELETE RESTRICT;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'rps_criado_por_foreign') THEN
        ALTER TABLE rps ADD CONSTRAINT rps_criado_por_foreign
            FOREIGN KEY (criado_por) REFERENCES users(id) ON DELETE RESTRICT;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'rps_cancelado_por_foreign') THEN
        ALTER TABLE rps ADD CONSTRAINT rps_cancelado_por_foreign
            FOREIGN KEY (cancelado_por) REFERENCES users(id) ON DELETE SET NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'rps_revertido_por_foreign') THEN
        ALTER TABLE rps ADD CONSTRAINT rps_revertido_por_foreign
            FOREIGN KEY (revertido_por) REFERENCES users(id) ON DELETE SET NULL;
    END IF;
END $$;

CREATE UNIQUE INDEX IF NOT EXISTS rps_numero_rps_unique ON rps(numero_rps);
CREATE INDEX IF NOT EXISTS rps_cliente_id_idx ON rps(cliente_id);
CREATE INDEX IF NOT EXISTS rps_numero_rps_idx ON rps(numero_rps);
CREATE INDEX IF NOT EXISTS rps_data_emissao_idx ON rps(data_emissao);
CREATE INDEX IF NOT EXISTS rps_status_idx ON rps(status);

-- =====================================================
-- TABELA: ordem_servico_rps
-- =====================================================
CREATE TABLE IF NOT EXISTS ordem_servico_rps (
    id BIGSERIAL PRIMARY KEY,
    ordem_servico_id BIGINT NOT NULL,
    rps_id BIGINT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para ordem_servico_rps
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ordem_servico_rps_ordem_servico_id_foreign') THEN
        ALTER TABLE ordem_servico_rps ADD CONSTRAINT ordem_servico_rps_ordem_servico_id_foreign
            FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servico(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ordem_servico_rps_rps_id_foreign') THEN
        ALTER TABLE ordem_servico_rps ADD CONSTRAINT ordem_servico_rps_rps_id_foreign
            FOREIGN KEY (rps_id) REFERENCES rps(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE UNIQUE INDEX IF NOT EXISTS ordem_servico_rps_ordem_servico_id_rps_id_unique ON ordem_servico_rps(ordem_servico_id, rps_id);
CREATE INDEX IF NOT EXISTS ordem_servico_rps_ordem_servico_id_idx ON ordem_servico_rps(ordem_servico_id);
CREATE INDEX IF NOT EXISTS ordem_servico_rps_rps_id_idx ON ordem_servico_rps(rps_id);

-- =====================================================
-- TABELA: ordem_servico_audits
-- =====================================================
CREATE TABLE IF NOT EXISTS ordem_servico_audits (
    id BIGSERIAL PRIMARY KEY,
    ordem_servico_id BIGINT NOT NULL,
    event VARCHAR(255) NOT NULL,
    user_id BIGINT NULL,
    action VARCHAR(255) NOT NULL,
    old_values JSONB NULL,
    new_values JSONB NULL,
    changed_fields JSONB NULL,
    status_from VARCHAR(255) NULL,
    status_to VARCHAR(255) NULL,
    description TEXT NULL,
    ip_address VARCHAR(255) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para ordem_servico_audits
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ordem_servico_audits_ordem_servico_id_foreign') THEN
        ALTER TABLE ordem_servico_audits ADD CONSTRAINT ordem_servico_audits_ordem_servico_id_foreign
            FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servico(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'ordem_servico_audits_user_id_foreign') THEN
        ALTER TABLE ordem_servico_audits ADD CONSTRAINT ordem_servico_audits_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS ordem_servico_audits_ordem_servico_id_idx ON ordem_servico_audits(ordem_servico_id);
CREATE INDEX IF NOT EXISTS ordem_servico_audits_user_id_idx ON ordem_servico_audits(user_id);
CREATE INDEX IF NOT EXISTS ordem_servico_audits_created_at_idx ON ordem_servico_audits(created_at);
CREATE INDEX IF NOT EXISTS ordem_servico_audits_ordem_servico_id_created_at_idx ON ordem_servico_audits(ordem_servico_id, created_at);

-- =====================================================
-- TABELA: reports
-- =====================================================
CREATE TABLE IF NOT EXISTS reports (
    id BIGSERIAL PRIMARY KEY,
    type VARCHAR(255) NOT NULL,
    status VARCHAR(255) DEFAULT 'pending',
    filters JSONB NULL,
    path VARCHAR(255) NULL,
    sent_at TIMESTAMP NULL,
    error TEXT NULL,
    ordem_servico_id BIGINT NULL,
    created_by BIGINT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para reports
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'reports_ordem_servico_id_foreign') THEN
        ALTER TABLE reports ADD CONSTRAINT reports_ordem_servico_id_foreign
            FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servico(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'reports_created_by_foreign') THEN
        ALTER TABLE reports ADD CONSTRAINT reports_created_by_foreign
            FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL;
    END IF;
END $$;

-- =====================================================
-- TABELA: report_email_logs
-- =====================================================
CREATE TABLE IF NOT EXISTS report_email_logs (
    id BIGSERIAL PRIMARY KEY,
    report_id BIGINT NOT NULL,
    recipient_email VARCHAR(255) NOT NULL,
    recipient_name VARCHAR(255) NULL,
    status VARCHAR(255) DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    error TEXT NULL,
    attempts INTEGER DEFAULT 0,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para report_email_logs
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'report_email_logs_report_id_foreign') THEN
        ALTER TABLE report_email_logs ADD CONSTRAINT report_email_logs_report_id_foreign
            FOREIGN KEY (report_id) REFERENCES reports(id) ON DELETE CASCADE;
    END IF;
END $$;

-- =====================================================
-- TABELA: notifications
-- =====================================================
CREATE TABLE IF NOT EXISTS notifications (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    ordem_servico_id BIGINT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(255) DEFAULT 'system',
    action_url VARCHAR(255) NULL,
    data JSONB NULL,
    email_sent BOOLEAN DEFAULT FALSE,
    related_model VARCHAR(255) NULL,
    related_id BIGINT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para notifications
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'notifications_user_id_foreign') THEN
        ALTER TABLE notifications ADD CONSTRAINT notifications_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS notifications_user_id_idx ON notifications(user_id);
CREATE INDEX IF NOT EXISTS notifications_is_read_idx ON notifications(is_read);

-- Adicionar colunas se não existirem em notifications
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='notifications' AND column_name='ordem_servico_id') THEN
        ALTER TABLE notifications ADD COLUMN ordem_servico_id BIGINT NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='notifications' AND column_name='action_url') THEN
        ALTER TABLE notifications ADD COLUMN action_url VARCHAR(255) NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='notifications' AND column_name='data') THEN
        ALTER TABLE notifications ADD COLUMN data JSONB NULL;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_name='notifications' AND column_name='email_sent') THEN
        ALTER TABLE notifications ADD COLUMN email_sent BOOLEAN DEFAULT FALSE;
    END IF;
END $$;

-- =====================================================
-- TABELA: comments
-- =====================================================
CREATE TABLE IF NOT EXISTS comments (
    id BIGSERIAL PRIMARY KEY,
    ordem_servico_id BIGINT NOT NULL,
    user_id BIGINT NOT NULL,
    content TEXT NOT NULL,
    mentions JSONB NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign keys para comments
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'comments_ordem_servico_id_foreign') THEN
        ALTER TABLE comments ADD CONSTRAINT comments_ordem_servico_id_foreign
            FOREIGN KEY (ordem_servico_id) REFERENCES ordem_servico(id) ON DELETE CASCADE;
    END IF;

    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'comments_user_id_foreign') THEN
        ALTER TABLE comments ADD CONSTRAINT comments_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS comments_ordem_servico_id_idx ON comments(ordem_servico_id);
CREATE INDEX IF NOT EXISTS comments_user_id_idx ON comments(user_id);

-- =====================================================
-- TABELA: saved_filters
-- =====================================================
CREATE TABLE IF NOT EXISTS saved_filters (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    name VARCHAR(255) NOT NULL,
    filters JSONB NOT NULL,
    is_favorite BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para saved_filters
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'saved_filters_user_id_foreign') THEN
        ALTER TABLE saved_filters ADD CONSTRAINT saved_filters_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS saved_filters_user_id_idx ON saved_filters(user_id);
CREATE INDEX IF NOT EXISTS saved_filters_is_favorite_idx ON saved_filters(is_favorite);

-- =====================================================
-- TABELA: pagamento_usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS pagamento_usuario (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    titular_conta VARCHAR(255) NOT NULL,
    cpf_cnpj_titular VARCHAR(255) NULL,
    banco VARCHAR(255) NOT NULL,
    agencia VARCHAR(255) NOT NULL,
    conta VARCHAR(255) NOT NULL,
    tipo_conta VARCHAR(50) DEFAULT 'corrente',
    pix_key VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para pagamento_usuario
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'pagamento_usuario_user_id_foreign') THEN
        ALTER TABLE pagamento_usuario ADD CONSTRAINT pagamento_usuario_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE INDEX IF NOT EXISTS pagamento_usuario_user_id_idx ON pagamento_usuario(user_id);

-- =====================================================
-- TABELA: pessoa_juridica_usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS pessoa_juridica_usuario (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    cnpj VARCHAR(255) NOT NULL,
    razao_social VARCHAR(255) NOT NULL,
    nome_fantasia VARCHAR(255) NULL,
    inscricao_estadual VARCHAR(255) NULL,
    inscricao_municipal VARCHAR(255) NULL,
    endereco VARCHAR(255) NOT NULL,
    numero VARCHAR(255) NOT NULL,
    complemento VARCHAR(255) NULL,
    bairro VARCHAR(255) NOT NULL,
    cidade VARCHAR(255) NOT NULL,
    estado VARCHAR(255) NOT NULL,
    cep VARCHAR(255) NOT NULL,
    telefone VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    site VARCHAR(255) NULL,
    ramo_atividade VARCHAR(255) NULL,
    data_constituicao DATE NULL,
    ativo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);

-- Foreign key para pessoa_juridica_usuario
DO $$
BEGIN
    IF NOT EXISTS (SELECT 1 FROM pg_constraint WHERE conname = 'pessoa_juridica_usuario_user_id_foreign') THEN
        ALTER TABLE pessoa_juridica_usuario ADD CONSTRAINT pessoa_juridica_usuario_user_id_foreign
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE;
    END IF;
END $$;

CREATE UNIQUE INDEX IF NOT EXISTS pessoa_juridica_usuario_cnpj_unique ON pessoa_juridica_usuario(cnpj);
CREATE INDEX IF NOT EXISTS pessoa_juridica_usuario_user_id_idx ON pessoa_juridica_usuario(user_id);
CREATE INDEX IF NOT EXISTS pessoa_juridica_usuario_cnpj_idx ON pessoa_juridica_usuario(cnpj);

-- Alterar tipo da coluna estado se necessário
DO $$
BEGIN
    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='pessoa_juridica_usuario' AND column_name='estado'
               AND character_maximum_length < 255) THEN
        ALTER TABLE pessoa_juridica_usuario ALTER COLUMN estado TYPE VARCHAR(255);
    END IF;

    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='pessoa_juridica_usuario' AND column_name='cidade'
               AND character_maximum_length < 255) THEN
        ALTER TABLE pessoa_juridica_usuario ALTER COLUMN cidade TYPE VARCHAR(255);
    END IF;
END $$;

-- =====================================================
-- FIM DO SCRIPT
-- =====================================================

-- Mensagem de confirmação
DO $$
BEGIN
    RAISE NOTICE '========================================';
    RAISE NOTICE '✓ SCRIPT EXECUTADO COM SUCESSO!';
    RAISE NOTICE '========================================';
    RAISE NOTICE 'Todas as tabelas foram criadas ou atualizadas.';
    RAISE NOTICE 'Total de tabelas: 30';
    RAISE NOTICE '========================================';
END $$;
