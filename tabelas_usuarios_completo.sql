-- =====================================================
-- SCRIPT COMPLETO PARA TABELAS DE USUÁRIOS
-- Execute este script no pgAdmin
-- =====================================================
-- Este script cria todas as 5 tabelas relacionadas a usuários:
-- 1. users (principal)
-- 2. password_reset_tokens
-- 3. sessions
-- 4. pessoa_juridica_usuario
-- 5. pagamento_usuario
-- =====================================================

-- =====================================================
-- TABELA 1: users (principal)
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id BIGSERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Campos customizados
    data_nasc VARCHAR(255) NULL,
    papel VARCHAR(255) NOT NULL,
    cgc VARCHAR(255) NULL,
    celular VARCHAR(255) NULL,
    valor_hora VARCHAR(255) NULL,
    valor_desloc VARCHAR(255) NULL,
    valor_km VARCHAR(255) NULL,
    salario_base VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT true,
    codigo VARCHAR(255) NULL UNIQUE
);

-- Criar índices para users
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_papel ON users(papel);
CREATE INDEX IF NOT EXISTS idx_users_ativo ON users(ativo);
CREATE INDEX IF NOT EXISTS idx_users_codigo ON users(codigo);

-- =====================================================
-- TABELA 2: password_reset_tokens
-- =====================================================
CREATE TABLE IF NOT EXISTS password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
);

-- =====================================================
-- TABELA 3: sessions
-- =====================================================
CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INTEGER NOT NULL
);

-- Criar índices para sessions
CREATE INDEX IF NOT EXISTS idx_sessions_user_id ON sessions(user_id);
CREATE INDEX IF NOT EXISTS idx_sessions_last_activity ON sessions(last_activity);

-- =====================================================
-- TABELA 4: pessoa_juridica_usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS pessoa_juridica_usuario (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    cnpj VARCHAR(255) NULL,
    razao_social VARCHAR(255) NULL,
    nome_fantasia VARCHAR(255) NULL,
    inscricao_estadual VARCHAR(255) NULL,
    inscricao_municipal VARCHAR(255) NULL,
    endereco VARCHAR(255) NULL,
    numero VARCHAR(255) NULL,
    complemento VARCHAR(255) NULL,
    bairro VARCHAR(255) NULL,
    cidade VARCHAR(255) NULL,
    estado VARCHAR(255) NULL,
    cep VARCHAR(255) NULL,
    telefone VARCHAR(255) NULL,
    email VARCHAR(255) NULL,
    site VARCHAR(255) NULL,
    ramo_atividade VARCHAR(255) NULL,
    data_constituicao DATE NULL,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Criar índices para pessoa_juridica_usuario
CREATE INDEX IF NOT EXISTS idx_pessoa_juridica_user_id ON pessoa_juridica_usuario(user_id);
CREATE INDEX IF NOT EXISTS idx_pessoa_juridica_cnpj ON pessoa_juridica_usuario(cnpj);

-- =====================================================
-- TABELA 5: pagamento_usuario
-- =====================================================
CREATE TABLE IF NOT EXISTS pagamento_usuario (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    titular_conta VARCHAR(255) NULL,
    cpf_cnpj_titular VARCHAR(255) NULL,
    banco VARCHAR(255) NULL,
    agencia VARCHAR(255) NULL,
    conta VARCHAR(255) NULL,
    tipo_conta VARCHAR(255) DEFAULT 'corrente',
    pix_key VARCHAR(255) NULL,
    ativo BOOLEAN DEFAULT true,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Criar índices para pagamento_usuario
CREATE INDEX IF NOT EXISTS idx_pagamento_user_id ON pagamento_usuario(user_id);

-- =====================================================
-- ADICIONAR COLUNAS SE NÃO EXISTIREM (IDEMPOTENTE)
-- =====================================================

DO $$
BEGIN
    -- Adicionar colunas na tabela users se não existirem

    -- data_nasc
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='data_nasc') THEN
        ALTER TABLE users ADD COLUMN data_nasc VARCHAR(255) NULL;
    END IF;

    -- papel
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='papel') THEN
        ALTER TABLE users ADD COLUMN papel VARCHAR(255) NOT NULL DEFAULT 'consultor';
    END IF;

    -- cgc
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='cgc') THEN
        ALTER TABLE users ADD COLUMN cgc VARCHAR(255) NULL;
    END IF;

    -- celular
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='celular') THEN
        ALTER TABLE users ADD COLUMN celular VARCHAR(255) NULL;
    END IF;

    -- valor_hora
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='valor_hora') THEN
        ALTER TABLE users ADD COLUMN valor_hora VARCHAR(255) NULL;
    END IF;

    -- valor_desloc
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='valor_desloc') THEN
        ALTER TABLE users ADD COLUMN valor_desloc VARCHAR(255) NULL;
    END IF;

    -- valor_km
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='valor_km') THEN
        ALTER TABLE users ADD COLUMN valor_km VARCHAR(255) NULL;
    END IF;

    -- salario_base
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='salario_base') THEN
        ALTER TABLE users ADD COLUMN salario_base VARCHAR(255) NULL;
    END IF;

    -- ativo
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='ativo') THEN
        ALTER TABLE users ADD COLUMN ativo BOOLEAN DEFAULT true;
    END IF;

    -- codigo
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns
                   WHERE table_name='users' AND column_name='codigo') THEN
        ALTER TABLE users ADD COLUMN codigo VARCHAR(255) NULL UNIQUE;
    END IF;

    -- Atualizar coluna estado em pessoa_juridica_usuario para VARCHAR(255)
    IF EXISTS (SELECT 1 FROM information_schema.columns
               WHERE table_name='pessoa_juridica_usuario' AND column_name='estado'
               AND character_maximum_length < 255) THEN
        ALTER TABLE pessoa_juridica_usuario ALTER COLUMN estado TYPE VARCHAR(255);
    END IF;

    -- Remover constraint UNIQUE do CNPJ se existir (para permitir NULL duplicados)
    IF EXISTS (SELECT 1 FROM information_schema.table_constraints
               WHERE table_name='pessoa_juridica_usuario'
               AND constraint_type='UNIQUE'
               AND constraint_name LIKE '%cnpj%') THEN
        -- Encontrar o nome da constraint
        DECLARE
            constraint_name_var VARCHAR(255);
        BEGIN
            SELECT constraint_name INTO constraint_name_var
            FROM information_schema.table_constraints
            WHERE table_name='pessoa_juridica_usuario'
            AND constraint_type='UNIQUE'
            AND constraint_name LIKE '%cnpj%'
            LIMIT 1;

            IF constraint_name_var IS NOT NULL THEN
                EXECUTE 'ALTER TABLE pessoa_juridica_usuario DROP CONSTRAINT IF EXISTS ' || constraint_name_var;
            END IF;
        END;
    END IF;

    -- Remover constraint UNIQUE de user_id+conta em pagamento_usuario se existir
    IF EXISTS (SELECT 1 FROM information_schema.table_constraints
               WHERE table_name='pagamento_usuario'
               AND constraint_type='UNIQUE'
               AND constraint_name LIKE '%user_id%conta%') THEN
        DECLARE
            constraint_name_var VARCHAR(255);
        BEGIN
            SELECT constraint_name INTO constraint_name_var
            FROM information_schema.table_constraints
            WHERE table_name='pagamento_usuario'
            AND constraint_type='UNIQUE'
            AND constraint_name LIKE '%user_id%conta%'
            LIMIT 1;

            IF constraint_name_var IS NOT NULL THEN
                EXECUTE 'ALTER TABLE pagamento_usuario DROP CONSTRAINT IF EXISTS ' || constraint_name_var;
            END IF;
        END;
    END IF;

END $$;

-- =====================================================
-- VERIFICAÇÃO FINAL
-- =====================================================
SELECT
    'Tabelas de usuários criadas/atualizadas com sucesso!' as status,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'users') as users_existe,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'password_reset_tokens') as password_reset_existe,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'sessions') as sessions_existe,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'pessoa_juridica_usuario') as pessoa_juridica_existe,
    (SELECT COUNT(*) FROM information_schema.tables WHERE table_name = 'pagamento_usuario') as pagamento_existe,
    (SELECT COUNT(*) FROM users) as total_usuarios;
