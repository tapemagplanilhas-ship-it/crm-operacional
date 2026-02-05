-- Banco de dados: `crm_tapemag`
CREATE DATABASE IF NOT EXISTS `crm_tapemag` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `crm_tapemag`;

-- --------------------------------------------------------
-- Estrutura da tabela `usuarios`
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `perfil` enum('admin','gerencia','vendedor','estoque','rh','financeiro','caixa','recebimento') NOT NULL DEFAULT 'vendedor',
  `status` enum('ativo','inativo','bloqueado') NOT NULL DEFAULT 'ativo',
  `foto_perfil` varchar(255) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `ultimo_login` datetime DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario` (`usuario`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `clientes`
CREATE TABLE `clientes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL COMMENT 'Responsável pelo cliente',
  `nome` varchar(100) NOT NULL,
  `empresa` varchar(150) DEFAULT NULL,
  `documento` varchar(30) DEFAULT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `endereco` varchar(200) DEFAULT NULL,
  `cidade` varchar(50) DEFAULT NULL,
  `estado` char(2) DEFAULT NULL,
  `cep` varchar(10) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `ultima_venda` date DEFAULT NULL,
  `media_gastos` decimal(10,2) DEFAULT 0.00,
  `total_gasto` decimal(10,2) DEFAULT 0.00,
  `taxa_fechamento` decimal(5,2) DEFAULT 0.00,
  `categoria_abc` enum('A','B','C') DEFAULT NULL,
  `data_primeira_compra` date DEFAULT NULL,
  `status_cliente` enum('ativo','recorrente','novo','perdido','inativo') DEFAULT 'novo',
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `status_cliente` (`status_cliente`),
  KEY `categoria_abc` (`categoria_abc`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `vendas`
CREATE TABLE `vendas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_venda` date NOT NULL,
  `hora_venda` time DEFAULT NULL,
  `status` enum('concluida','perdida','orcamento') NOT NULL DEFAULT 'concluida',
  `observacoes` text DEFAULT NULL,
  `forma_pagamento` enum('dinheiro','cartao','boleto','transferencia','pix','outro') DEFAULT NULL,
  `motivo_perda_id` int(11) DEFAULT NULL,
  `motivo_perda_outro` varchar(255) DEFAULT NULL,
  `codigo_orcamento` varchar(30) DEFAULT NULL,
  `canal_venda` enum('loja','online','telefone','whatsapp','email') DEFAULT 'loja',
  `produto_principal` varchar(100) DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `cliente_id` (`cliente_id`),
  KEY `data_venda` (`data_venda`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `produtos`
CREATE TABLE `produtos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(20) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `descricao` text DEFAULT NULL,
  `preco_custo` decimal(10,2) DEFAULT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `estoque` int(11) NOT NULL DEFAULT 0,
  `estoque_minimo` int(11) DEFAULT 5,
  `categoria` varchar(50) DEFAULT NULL,
  `fornecedor` varchar(100) DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `codigo` (`codigo`),
  KEY `categoria` (`categoria`),
  KEY `ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `venda_itens`
CREATE TABLE `venda_itens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `venda_id` int(11) NOT NULL,
  `produto_id` int(11) NOT NULL,
  `quantidade` int(11) NOT NULL,
  `preco_unitario` decimal(10,2) NOT NULL,
  `desconto` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) GENERATED ALWAYS AS (`quantidade` * (`preco_unitario` - `desconto`)) STORED,
  PRIMARY KEY (`id`),
  KEY `venda_id` (`venda_id`),
  KEY `produto_id` (`produto_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `motivos_perda`
CREATE TABLE `motivos_perda` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) NOT NULL,
  `permite_outro` tinyint(1) NOT NULL DEFAULT 0,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `metas_vendedores`
CREATE TABLE `metas_vendedores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `periodo` enum('diaria','semanal','mensal','trimestral','anual') NOT NULL,
  `valor_meta` decimal(12,2) NOT NULL,
  `tipo_meta` enum('faturamento','quantidade','ticket_medio','novos_clientes') NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `periodo` (`periodo`),
  KEY `ativo` (`ativo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `logs_acesso`
CREATE TABLE `logs_acesso` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `navegador` varchar(200) DEFAULT NULL,
  `sistema_operacional` varchar(100) DEFAULT NULL,
  `acao` varchar(50) DEFAULT NULL,
  `sucesso` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `data_hora` (`data_hora`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Estrutura da tabela `configuracoes_sistema`
CREATE TABLE `configuracoes_sistema` (
  `chave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL DEFAULT '',
  `atualizado_por` int(11) DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`chave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Chaves estrangeiras
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `vendas`
  ADD CONSTRAINT `vendas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `vendas_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `vendas_ibfk_3` FOREIGN KEY (`motivo_perda_id`) REFERENCES `motivos_perda` (`id`);

ALTER TABLE `venda_itens`
  ADD CONSTRAINT `venda_itens_ibfk_1` FOREIGN KEY (`venda_id`) REFERENCES `vendas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `venda_itens_ibfk_2` FOREIGN KEY (`produto_id`) REFERENCES `produtos` (`id`);

ALTER TABLE `metas_vendedores`
  ADD CONSTRAINT `metas_vendedores_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

ALTER TABLE `logs_acesso`
  ADD CONSTRAINT `logs_acesso_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

-- --------------------------------------------------------
-- Triggers para atualização de dados de clientes
DELIMITER $$
CREATE TRIGGER `trg_vendas_after_insert_clientes` AFTER INSERT ON `vendas`
FOR EACH ROW BEGIN
  UPDATE clientes
  SET 
    ultima_venda = NEW.data_venda,
    total_gasto = (SELECT COALESCE(SUM(valor), 0) FROM vendas WHERE cliente_id = NEW.cliente_id AND status = 'concluida'),
    media_gastos = (SELECT COALESCE(AVG(valor), 0) FROM vendas WHERE cliente_id = NEW.cliente_id AND status = 'concluida'),
    taxa_fechamento = (
      SELECT CASE 
        WHEN COUNT(*) = 0 THEN 0
        ELSE (SUM(status = 'concluida') / COUNT(*)) * 100
      END
      FROM vendas
      WHERE cliente_id = NEW.cliente_id
    ),
    status_cliente = CASE
      WHEN NEW.status = 'concluida' THEN 'ativo'
      ELSE status_cliente
    END,
    data_primeira_compra = COALESCE(
      (SELECT MIN(data_venda) FROM vendas WHERE cliente_id = NEW.cliente_id AND status = 'concluida'),
      data_primeira_compra
    )
  WHERE id = NEW.cliente_id;
END$$
DELIMITER ;

DELIMITER $$
CREATE TRIGGER `trg_vendas_after_update_clientes` AFTER UPDATE ON `vendas`
FOR EACH ROW BEGIN
  IF OLD.cliente_id != NEW.cliente_id OR OLD.status != NEW.status OR OLD.data_venda != NEW.data_venda THEN
    -- Atualiza cliente novo
    UPDATE clientes
    SET 
      ultima_venda = (SELECT MAX(data_venda) FROM vendas WHERE cliente_id = NEW.cliente_id AND status = 'concluida'),
      total_gasto = (SELECT COALESCE(SUM(valor), 0) FROM vendas WHERE cliente_id = NEW.cliente_id AND status = 'concluida'),
      media_gastos = (SELECT COALESCE(AVG(valor), 0) FROM vendas WHERE cliente_id = NEW.cliente_id AND status = 'concluida'),
      taxa_fechamento = (
        SELECT CASE 
          WHEN COUNT(*) = 0 THEN 0
          ELSE (SUM(status = 'concluida') / COUNT(*)) * 100
        END
        FROM vendas
        WHERE cliente_id = NEW.cliente_id
      ),
      status_cliente = CASE
        WHEN NEW.status = 'concluida' THEN 'ativo'
        ELSE status_cliente
      END,
      data_primeira_compra = COALESCE(
        (SELECT MIN(data_venda) FROM vendas WHERE cliente_id = NEW.cliente_id AND status = 'concluida'),
        data_primeira_compra
      )
    WHERE id = NEW.cliente_id;
    
    -- Atualiza cliente antigo se houve mudança
    IF OLD.cliente_id != NEW.cliente_id THEN
      UPDATE clientes
      SET 
        ultima_venda = (SELECT MAX(data_venda) FROM vendas WHERE cliente_id = OLD.cliente_id AND status = 'concluida'),
        total_gasto = (SELECT COALESCE(SUM(valor), 0) FROM vendas WHERE cliente_id = OLD.cliente_id AND status = 'concluida'),
        media_gastos = (SELECT COALESCE(AVG(valor), 0) FROM vendas WHERE cliente_id = OLD.cliente_id AND status = 'concluida'),
        taxa_fechamento = (
          SELECT CASE 
            WHEN COUNT(*) = 0 THEN 0
            ELSE (SUM(status = 'concluida') / COUNT(*)) * 100
          END
          FROM vendas
          WHERE cliente_id = OLD.cliente_id
        )
      WHERE id = OLD.cliente_id;
    END IF;
  END IF;
END$$
DELIMITER ;

-- --------------------------------------------------------
-- Inserção de dados iniciais
INSERT INTO `motivos_perda` (`id`, `nome`, `permite_outro`, `ordem`, `ativo`) VALUES
(1, 'Preço fora do orçamento', 0, 1, 1),
(2, 'Prazo de entrega', 0, 2, 1),
(3, 'Sem estoque', 0, 3, 1),
(4, 'Pagamento incompatível', 0, 4, 1),
(5, 'Outro', 1, 99, 1);

INSERT INTO `configuracoes_sistema` (`chave`, `valor`) VALUES
('contar_sabado', '0'),
('taxa_comissao_padrao', '5.00'),
('dias_para_cliente_inativo', '90'),
('limite_estoque_minimo', '5');

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
-- Host: localhost
-- Tempo de geração: 20/01/2026 às 16:16
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `crm_operacional`
--

-- --------------------------------------------------------

--
-- Inserindo dados de exemplo
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `foto`, `usuario`, `senha`, `foto_perfil`, `perfil`, `status`, `ultimo_login`, `data_cadastro`, `atualizado_em`, `ativo`) VALUES
(1, 'Administrador', 'admin@tapemag.com', NULL, 'admin', '$2y$10$hashhash', NULL, 'admin', 'ativo', NULL, NOW(), NOW(), 1),
(2, 'Gerente Comercial', 'gerente@tapemag.com', NULL, 'gerente', '$2y$10$hashhash', NULL, 'gerencia', 'ativo', NULL, NOW(), NOW(), 1),
(3, 'Vendedor 1', 'vendedor1@tapemag.com', NULL, 'vendedor1', '$2y$10$hashhash', NULL, 'vendedor', 'ativo', NULL, NOW(), NOW(), 1),
(4, 'Estoque', 'estoque@tapemag.com', NULL, 'estoque', '$2y$10$hashhash', NULL, 'estoque', 'ativo', NULL, NOW(), NOW(), 1),
(5, 'RH', 'rh@tapemag.com', NULL, 'rh', '$2y$10$hashhash', NULL, 'rh', 'ativo', NULL, NOW(), NOW(), 1),
(6, 'Financeiro', 'financeiro@tapemag.com', NULL, 'financeiro', '$2y$10$hashhash', NULL, 'financeiro', 'ativo', NULL, NOW(), NOW(), 1),
(7, 'Caixa', 'caixa@tapemag.com', NULL, 'caixa', '$2y$10$hashhash', NULL, 'caixa', 'ativo', NULL, NOW(), NOW(), 1),
(8, 'Recebimento', 'recebimento@tapemag.com', NULL, 'recebimento', '$2y$10$hashhash', NULL, 'recebimento', 'ativo', NULL, NOW(), NOW(), 1);

-- --------------------------------------------------------

--
-- Estrutura da tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL,
  `perfil` varchar(50) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `acesso` enum('leitura','escrita','total','nenhum') NOT NULL DEFAULT 'leitura'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Permissões básicas
--

INSERT INTO `permissoes` (`perfil`, `modulo`, `acesso`) VALUES
('admin', 'todos', 'total'),
('gerencia', 'vendas', 'total'),
('gerencia', 'clientes', 'total'),
('gerencia', 'relatorios', 'total'),
('vendedor', 'vendas', 'escrita'),
('vendedor', 'clientes', 'escrita'),
('vendedor', 'relatorios', 'leitura'),
('estoque', 'produtos', 'total'),
('estoque', 'estoque', 'total'),
('rh', 'colaboradores', 'total'),
('financeiro', 'financeiro', 'total'),
('caixa', 'vendas', 'escrita'),
('caixa', 'caixa', 'total'),
('recebimento', 'financeiro', 'escrita'),
('recebimento', 'contas', 'escrita');

-- --------------------------------------------------------

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Índices para tabela `permissoes`
--
ALTER TABLE `permissoes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `perfil_modulo` (`perfil`,`modulo`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
  
ALTER TABLE `usuarios`
MODIFY COLUMN perfil ENUM('admin','gerencia','vendedor','estoque','rh','financeiro','caixa','recebimento') NOT NULL DEFAULT 'vendedor';
--
-- AUTO_INCREMENT de tabela `permissoes`
--
ALTER TABLE `permissoes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
COMMIT;

-- Primeiro, verifique se a tabela já tem o campo 'perfil'
ALTER TABLE usuarios 
MODIFY COLUMN perfil ENUM('admin','gerencia','vendedor','estoque','rh','financeiro','caixa','recebimento') NOT NULL DEFAULT 'vendedor';

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;