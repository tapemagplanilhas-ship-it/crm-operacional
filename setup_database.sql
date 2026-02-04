-- Script de configuração do banco para XAMPP/MariaDB
-- Execute este script no phpMyAdmin

-- 1. Criar banco de dados
CREATE DATABASE IF NOT EXISTS `crm_operacional` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_general_ci;

USE `crm_operacional`;

-- 2. Tabela de clientes
CREATE TABLE IF NOT EXISTS `clientes` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `nome` VARCHAR(100) NOT NULL,
    `empresa` VARCHAR(150) DEFAULT NULL,
    `documento` VARCHAR(30) DEFAULT NULL,
    `telefone` VARCHAR(20),
    `email` VARCHAR(100),
    `data_cadastro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `observacoes` TEXT,
    -- Campos automáticos (calculados)
    `ultima_venda` DATE NULL,
    `media_gastos` DECIMAL(10,2) DEFAULT 0.00,
    `total_gasto` DECIMAL(10,2) DEFAULT 0.00,
    `taxa_fechamento` DECIMAL(5,2) DEFAULT 0.00,
    INDEX `idx_nome` (`nome`),
    INDEX `idx_ultima_venda` (`ultima_venda` DESC)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2.1 Tabela de motivos da perda
CREATE TABLE IF NOT EXISTS `motivos_perda` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `nome` VARCHAR(100) NOT NULL,
    `permite_outro` TINYINT(1) NOT NULL DEFAULT 0,
    `ordem` INT NOT NULL DEFAULT 0,
    `ativo` TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY `uniq_nome` (`nome`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `motivos_perda` (`nome`, `permite_outro`, `ordem`) VALUES
('PreÃ§o fora do orÃ§amento', 0, 1),
('Prazo de entrega', 0, 2),
('Sem estoque', 0, 3),
('Pagamento incompatÃ­vel', 0, 4),
('Outro', 1, 99);

-- 3. Tabela de vendas
CREATE TABLE IF NOT EXISTS `vendas` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `cliente_id` INT NOT NULL,
    `valor` DECIMAL(10,2) NOT NULL,
    `data_venda` DATE NOT NULL,
    `status` ENUM('concluida', 'cancelada', 'orcamento') NOT NULL DEFAULT 'concluida',
    `observacoes` TEXT,
    `data_registro` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `codigo_orcamento` VARCHAR(30) DEFAULT NULL,
    `motivo_perda_id` INT DEFAULT NULL,
    `motivo_perda_outro` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`motivo_perda_id`) REFERENCES `motivos_perda`(`id`) ON DELETE SET NULL,
    INDEX `idx_cliente_id` (`cliente_id`),
    INDEX `idx_data_venda` (`data_venda` DESC),
    INDEX `idx_status` (`status`),
    INDEX `idx_motivo_perda_id` (`motivo_perda_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Inserir dados de exemplo para testes
INSERT INTO `clientes` (`nome`, `telefone`, `email`, `observacoes`) VALUES
('João Silva', '(11) 99999-8888', 'joao@email.com', 'Cliente preferencial'),
('Maria Santos', '(11) 98888-7777', 'maria@email.com', 'Gosta de promoções'),
('Pedro Oliveira', '(11) 97777-6666', NULL, 'Compra sempre às sextas'),
('Ana Costa', '(11) 96666-5555', 'ana@email.com', 'Novo cliente'),
('Carlos Rodrigues', '(11) 95555-4444', 'carlos@email.com', NULL);

INSERT INTO `vendas` (`cliente_id`, `valor`, `data_venda`, `status`, `observacoes`) VALUES
(1, 150.50, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'concluida', 'Venda de camisetas'),
(1, 89.90, DATE_SUB(CURDATE(), INTERVAL 2 DAY), 'concluida', 'Venda de calça'),
(2, 200.00, DATE_SUB(CURDATE(), INTERVAL 10 DAY), 'concluida', 'Venda completa'),
(2, 75.30, DATE_SUB(CURDATE(), INTERVAL 3 DAY), 'orcamento', 'Orçamento pendente'),
(3, 120.00, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 'concluida', 'Pagamento à vista'),
(4, 50.00, CURDATE(), 'concluida', 'Primeira compra');

-- 5. Atualizar métricas manualmente (sem triggers por enquanto)
UPDATE clientes c SET 
    ultima_venda = (
        SELECT MAX(data_venda) 
        FROM vendas v 
        WHERE v.cliente_id = c.id 
        AND v.status = 'concluida'
    ),
    total_gasto = (
        SELECT COALESCE(SUM(valor), 0) 
        FROM vendas v 
        WHERE v.cliente_id = c.id 
        AND v.status = 'concluida'
    ),
    media_gastos = (
        SELECT COALESCE(AVG(valor), 0) 
        FROM vendas v 
        WHERE v.cliente_id = c.id 
        AND v.status = 'concluida'
    ),
    taxa_fechamento = (
        SELECT 
            CASE 
                WHEN COUNT(*) = 0 THEN 0
                ELSE (COUNT(CASE WHEN status = 'concluida' THEN 1 END) * 100.0 / COUNT(*))
            END
        FROM vendas v 
        WHERE v.cliente_id = c.id
    );

-- 6. Tabela para layouts de dashboard por usuário
CREATE TABLE IF NOT EXISTS `dashboard_layouts` (
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NULL,
    `name` VARCHAR(100) NOT NULL DEFAULT 'Layout',
    `is_shared` TINYINT(1) NOT NULL DEFAULT 0,
    `layout_json` JSON NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Inserir layout padrão compartilhado (exemplo)
INSERT INTO `dashboard_layouts` (`user_id`, `name`, `is_shared`, `layout_json`) VALUES
(NULL, 'Padrão', 1, JSON_ARRAY(
    JSON_OBJECT('id', 'card_total_clientes', 'type', 'stat', 'title', 'Total de Clientes', 'metric', 'total_clients'),
    JSON_OBJECT('id', 'card_vendas_mes', 'type', 'stat', 'title', 'Vendas do Mês', 'metric', 'vendas_mes'),
    JSON_OBJECT('id', 'card_valor_mes', 'type', 'stat', 'title', 'Valor do Mês', 'metric', 'valor_mes'),
    JSON_OBJECT('id', 'card_taxa_fechamento', 'type', 'stat', 'title', 'Taxa de Fechamento', 'metric', 'taxa_fechamento'),
    JSON_OBJECT('id', 'card_clientes_inativos', 'type', 'stat', 'title', 'Clientes Inativos', 'metric', 'clientes_inativos'),
    JSON_OBJECT('id', 'card_total_negociacoes', 'type', 'stat', 'title', 'Total de Negociações', 'metric', 'total_negociacoes'),
    JSON_OBJECT('id', 'card_grafico_exemplo', 'type', 'chart', 'title', 'Vendas (6 meses)', 'metric', 'vendas_6meses')
));

-- Mensagem de sucesso
SELECT 'Banco de dados configurado com sucesso para XAMPP!' as Mensagem;
