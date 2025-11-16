-- ===============================================
-- Script de Configuração de Bancos de Dados
-- Portal - Desenvolvimento, Staging e Produção
-- ===============================================

-- 1. Criar banco de dados de DESENVOLVIMENTO
CREATE DATABASE portal_dev
  WITH OWNER postgres
  ENCODING 'UTF8'
  LC_COLLATE='pt_BR.UTF-8'
  LC_CTYPE='pt_BR.UTF-8'
  TEMPLATE=template0;

-- 2. Criar banco de dados de STAGING (Validação/Testes)
CREATE DATABASE portal_staging
  WITH OWNER postgres
  ENCODING 'UTF8'
  LC_COLLATE='pt_BR.UTF-8'
  LC_CTYPE='pt_BR.UTF-8'
  TEMPLATE=template0;

-- 3. Criar banco de dados de PRODUÇÃO
CREATE DATABASE portal_prod
  WITH OWNER postgres
  ENCODING 'UTF8'
  LC_COLLATE='pt_BR.UTF-8'
  LC_CTYPE='pt_BR.UTF-8'
  TEMPLATE=template0;

-- ===============================================
-- Verificação de Criação
-- ===============================================
\l portal_dev
\l portal_staging
\l portal_prod
