-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 06/02/2026 às 14:44
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

DELIMITER $$
--
-- Procedimentos
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `atualizar_churn_cliente` (IN `p_cliente_id` INT)   BEGIN
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `atualizar_comportamento_clientes` (IN `p_cliente_id` INT)   BEGIN
  -- Stub: evita erro no CRM.
  -- Depois você implementa a lógica real aqui.
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `atualizar_metricas_cliente` (IN `p_cliente_id` INT)   BEGIN
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura para tabela `atendimentos`
--

CREATE TABLE `atendimentos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `tipo_atendimento` enum('presencial','telefone','whatsapp','email') NOT NULL,
  `data_hora_inicio` datetime NOT NULL,
  `data_hora_fim` datetime DEFAULT NULL,
  `duracao_minutos` int(11) DEFAULT NULL,
  `resultado` enum('venda','orcamento','nao_comprou','retornar') NOT NULL,
  `observacoes` text DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `calendario_uteis`
--

CREATE TABLE `calendario_uteis` (
  `data` date NOT NULL,
  `dia_semana` varchar(20) NOT NULL,
  `dia_util` tinyint(1) DEFAULT 1,
  `feriado` tinyint(1) DEFAULT 0,
  `descricao` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `calendario_uteis`
--

INSERT INTO `calendario_uteis` (`data`, `dia_semana`, `dia_util`, `feriado`, `descricao`) VALUES
('2025-01-01', 'Wednesday', 0, 1, 'Ano Novo'),
('2025-01-02', 'Thursday', 1, 0, NULL),
('2025-01-03', 'Friday', 1, 0, NULL),
('2025-01-04', 'Saturday', 0, 0, NULL),
('2025-01-05', 'Sunday', 0, 0, NULL),
('2025-01-06', 'Monday', 1, 0, NULL),
('2025-01-07', 'Tuesday', 1, 0, NULL),
('2025-01-08', 'Wednesday', 1, 0, NULL),
('2025-01-09', 'Thursday', 1, 0, NULL),
('2025-01-10', 'Friday', 1, 0, NULL),
('2025-01-11', 'Saturday', 0, 0, NULL),
('2025-01-12', 'Sunday', 0, 0, NULL),
('2025-01-13', 'Monday', 1, 0, NULL),
('2025-01-14', 'Tuesday', 1, 0, NULL),
('2025-01-15', 'Wednesday', 1, 0, NULL),
('2025-01-16', 'Thursday', 1, 0, NULL),
('2025-01-17', 'Friday', 1, 0, NULL),
('2025-01-18', 'Saturday', 0, 0, NULL),
('2025-01-19', 'Sunday', 0, 0, NULL),
('2025-01-20', 'Monday', 1, 0, NULL),
('2025-01-21', 'Tuesday', 1, 0, NULL),
('2025-01-22', 'Wednesday', 1, 0, NULL),
('2025-01-23', 'Thursday', 1, 0, NULL),
('2025-01-24', 'Friday', 1, 0, NULL),
('2025-01-25', 'Saturday', 0, 0, NULL),
('2025-01-26', 'Sunday', 0, 0, NULL),
('2025-01-27', 'Monday', 1, 0, NULL),
('2025-01-28', 'Tuesday', 1, 0, NULL),
('2025-01-29', 'Wednesday', 1, 0, NULL),
('2025-01-30', 'Thursday', 1, 0, NULL),
('2025-01-31', 'Friday', 1, 0, NULL),
('2025-02-01', 'Saturday', 0, 0, NULL),
('2025-02-02', 'Sunday', 0, 0, NULL),
('2025-02-03', 'Monday', 1, 0, NULL),
('2025-02-04', 'Tuesday', 1, 0, NULL),
('2025-02-05', 'Wednesday', 1, 0, NULL),
('2025-02-06', 'Thursday', 1, 0, NULL),
('2025-02-07', 'Friday', 1, 0, NULL),
('2025-02-08', 'Saturday', 0, 0, NULL),
('2025-02-09', 'Sunday', 0, 0, NULL),
('2025-02-10', 'Monday', 1, 0, NULL),
('2025-02-11', 'Tuesday', 1, 0, NULL),
('2025-02-12', 'Wednesday', 1, 0, NULL),
('2025-02-13', 'Thursday', 1, 0, NULL),
('2025-02-14', 'Friday', 1, 0, NULL),
('2025-02-15', 'Saturday', 0, 0, NULL),
('2025-02-16', 'Sunday', 0, 0, NULL),
('2025-02-17', 'Monday', 1, 0, NULL),
('2025-02-18', 'Tuesday', 1, 0, NULL),
('2025-02-19', 'Wednesday', 1, 0, NULL),
('2025-02-20', 'Thursday', 1, 0, NULL),
('2025-02-21', 'Friday', 1, 0, NULL),
('2025-02-22', 'Saturday', 0, 0, NULL),
('2025-02-23', 'Sunday', 0, 0, NULL),
('2025-02-24', 'Monday', 1, 0, NULL),
('2025-02-25', 'Tuesday', 0, 1, 'Carnaval'),
('2025-02-26', 'Wednesday', 0, 1, 'Carnaval'),
('2025-02-27', 'Thursday', 1, 0, NULL),
('2025-02-28', 'Friday', 1, 0, NULL),
('2025-03-01', 'Saturday', 0, 0, NULL),
('2025-03-02', 'Sunday', 0, 0, NULL),
('2025-03-03', 'Monday', 1, 0, NULL),
('2025-03-04', 'Tuesday', 1, 0, NULL),
('2025-03-05', 'Wednesday', 1, 0, NULL),
('2025-03-06', 'Thursday', 1, 0, NULL),
('2025-03-07', 'Friday', 1, 0, NULL),
('2025-03-08', 'Saturday', 0, 0, NULL),
('2025-03-09', 'Sunday', 0, 0, NULL),
('2025-03-10', 'Monday', 1, 0, NULL),
('2025-03-11', 'Tuesday', 1, 0, NULL),
('2025-03-12', 'Wednesday', 1, 0, NULL),
('2025-03-13', 'Thursday', 1, 0, NULL),
('2025-03-14', 'Friday', 1, 0, NULL),
('2025-03-15', 'Saturday', 0, 0, NULL),
('2025-03-16', 'Sunday', 0, 0, NULL),
('2025-03-17', 'Monday', 1, 0, NULL),
('2025-03-18', 'Tuesday', 1, 0, NULL),
('2025-03-19', 'Wednesday', 1, 0, NULL),
('2025-03-20', 'Thursday', 1, 0, NULL),
('2025-03-21', 'Friday', 1, 0, NULL),
('2025-03-22', 'Saturday', 0, 0, NULL),
('2025-03-23', 'Sunday', 0, 0, NULL),
('2025-03-24', 'Monday', 1, 0, NULL),
('2025-03-25', 'Tuesday', 1, 0, NULL),
('2025-03-26', 'Wednesday', 1, 0, NULL),
('2025-03-27', 'Thursday', 1, 0, NULL),
('2025-03-28', 'Friday', 1, 0, NULL),
('2025-03-29', 'Saturday', 0, 0, NULL),
('2025-03-30', 'Sunday', 0, 0, NULL),
('2025-03-31', 'Monday', 1, 0, NULL),
('2025-04-01', 'Tuesday', 1, 0, NULL),
('2025-04-02', 'Wednesday', 1, 0, NULL),
('2025-04-03', 'Thursday', 1, 0, NULL),
('2025-04-04', 'Friday', 1, 0, NULL),
('2025-04-05', 'Saturday', 0, 0, NULL),
('2025-04-06', 'Sunday', 0, 0, NULL),
('2025-04-07', 'Monday', 1, 0, NULL),
('2025-04-08', 'Tuesday', 1, 0, NULL),
('2025-04-09', 'Wednesday', 1, 0, NULL),
('2025-04-10', 'Thursday', 1, 0, NULL),
('2025-04-11', 'Friday', 1, 0, NULL),
('2025-04-12', 'Saturday', 0, 0, NULL),
('2025-04-13', 'Sunday', 0, 0, NULL),
('2025-04-14', 'Monday', 1, 0, NULL),
('2025-04-15', 'Tuesday', 1, 0, NULL),
('2025-04-16', 'Wednesday', 1, 0, NULL),
('2025-04-17', 'Thursday', 1, 0, NULL),
('2025-04-18', 'Friday', 0, 1, 'Páscoa'),
('2025-04-19', 'Saturday', 0, 0, NULL),
('2025-04-20', 'Sunday', 0, 0, NULL),
('2025-04-21', 'Monday', 0, 1, 'Tiradentes'),
('2025-04-22', 'Tuesday', 1, 0, NULL),
('2025-04-23', 'Wednesday', 1, 0, NULL),
('2025-04-24', 'Thursday', 1, 0, NULL),
('2025-04-25', 'Friday', 1, 0, NULL),
('2025-04-26', 'Saturday', 0, 0, NULL),
('2025-04-27', 'Sunday', 0, 0, NULL),
('2025-04-28', 'Monday', 1, 0, NULL),
('2025-04-29', 'Tuesday', 1, 0, NULL),
('2025-04-30', 'Wednesday', 1, 0, NULL),
('2025-05-01', 'Thursday', 0, 1, 'Dia do Trabalho'),
('2025-05-02', 'Friday', 1, 0, NULL),
('2025-05-03', 'Saturday', 0, 0, NULL),
('2025-05-04', 'Sunday', 0, 0, NULL),
('2025-05-05', 'Monday', 1, 0, NULL),
('2025-05-06', 'Tuesday', 1, 0, NULL),
('2025-05-07', 'Wednesday', 1, 0, NULL),
('2025-05-08', 'Thursday', 1, 0, NULL),
('2025-05-09', 'Friday', 1, 0, NULL),
('2025-05-10', 'Saturday', 0, 0, NULL),
('2025-05-11', 'Sunday', 0, 0, NULL),
('2025-05-12', 'Monday', 1, 0, NULL),
('2025-05-13', 'Tuesday', 1, 0, NULL),
('2025-05-14', 'Wednesday', 1, 0, NULL),
('2025-05-15', 'Thursday', 1, 0, NULL),
('2025-05-16', 'Friday', 1, 0, NULL),
('2025-05-17', 'Saturday', 0, 0, NULL),
('2025-05-18', 'Sunday', 0, 0, NULL),
('2025-05-19', 'Monday', 1, 0, NULL),
('2025-05-20', 'Tuesday', 1, 0, NULL),
('2025-05-21', 'Wednesday', 1, 0, NULL),
('2025-05-22', 'Thursday', 1, 0, NULL),
('2025-05-23', 'Friday', 1, 0, NULL),
('2025-05-24', 'Saturday', 0, 0, NULL),
('2025-05-25', 'Sunday', 0, 0, NULL),
('2025-05-26', 'Monday', 1, 0, NULL),
('2025-05-27', 'Tuesday', 1, 0, NULL),
('2025-05-28', 'Wednesday', 1, 0, NULL),
('2025-05-29', 'Thursday', 1, 0, NULL),
('2025-05-30', 'Friday', 1, 0, NULL),
('2025-05-31', 'Saturday', 0, 0, NULL),
('2025-06-01', 'Sunday', 0, 0, NULL),
('2025-06-02', 'Monday', 1, 0, NULL),
('2025-06-03', 'Tuesday', 1, 0, NULL),
('2025-06-04', 'Wednesday', 1, 0, NULL),
('2025-06-05', 'Thursday', 1, 0, NULL),
('2025-06-06', 'Friday', 1, 0, NULL),
('2025-06-07', 'Saturday', 0, 0, NULL),
('2025-06-08', 'Sunday', 0, 0, NULL),
('2025-06-09', 'Monday', 1, 0, NULL),
('2025-06-10', 'Tuesday', 1, 0, NULL),
('2025-06-11', 'Wednesday', 1, 0, NULL),
('2025-06-12', 'Thursday', 1, 0, NULL),
('2025-06-13', 'Friday', 1, 0, NULL),
('2025-06-14', 'Saturday', 0, 0, NULL),
('2025-06-15', 'Sunday', 0, 0, NULL),
('2025-06-16', 'Monday', 1, 0, NULL),
('2025-06-17', 'Tuesday', 1, 0, NULL),
('2025-06-18', 'Wednesday', 1, 0, NULL),
('2025-06-19', 'Thursday', 0, 1, 'Corpus Christi'),
('2025-06-20', 'Friday', 1, 0, NULL),
('2025-06-21', 'Saturday', 0, 0, NULL),
('2025-06-22', 'Sunday', 0, 0, NULL),
('2025-06-23', 'Monday', 1, 0, NULL),
('2025-06-24', 'Tuesday', 1, 0, NULL),
('2025-06-25', 'Wednesday', 1, 0, NULL),
('2025-06-26', 'Thursday', 1, 0, NULL),
('2025-06-27', 'Friday', 1, 0, NULL),
('2025-06-28', 'Saturday', 0, 0, NULL),
('2025-06-29', 'Sunday', 0, 0, NULL),
('2025-06-30', 'Monday', 1, 0, NULL),
('2025-07-01', 'Tuesday', 1, 0, NULL),
('2025-07-02', 'Wednesday', 1, 0, NULL),
('2025-07-03', 'Thursday', 1, 0, NULL),
('2025-07-04', 'Friday', 1, 0, NULL),
('2025-07-05', 'Saturday', 0, 0, NULL),
('2025-07-06', 'Sunday', 0, 0, NULL),
('2025-07-07', 'Monday', 1, 0, NULL),
('2025-07-08', 'Tuesday', 1, 0, NULL),
('2025-07-09', 'Wednesday', 1, 0, NULL),
('2025-07-10', 'Thursday', 1, 0, NULL),
('2025-07-11', 'Friday', 1, 0, NULL),
('2025-07-12', 'Saturday', 0, 0, NULL),
('2025-07-13', 'Sunday', 0, 0, NULL),
('2025-07-14', 'Monday', 1, 0, NULL),
('2025-07-15', 'Tuesday', 1, 0, NULL),
('2025-07-16', 'Wednesday', 1, 0, NULL),
('2025-07-17', 'Thursday', 1, 0, NULL),
('2025-07-18', 'Friday', 1, 0, NULL),
('2025-07-19', 'Saturday', 0, 0, NULL),
('2025-07-20', 'Sunday', 0, 0, NULL),
('2025-07-21', 'Monday', 1, 0, NULL),
('2025-07-22', 'Tuesday', 1, 0, NULL),
('2025-07-23', 'Wednesday', 1, 0, NULL),
('2025-07-24', 'Thursday', 1, 0, NULL),
('2025-07-25', 'Friday', 1, 0, NULL),
('2025-07-26', 'Saturday', 0, 0, NULL),
('2025-07-27', 'Sunday', 0, 0, NULL),
('2025-07-28', 'Monday', 1, 0, NULL),
('2025-07-29', 'Tuesday', 1, 0, NULL),
('2025-07-30', 'Wednesday', 1, 0, NULL),
('2025-07-31', 'Thursday', 1, 0, NULL),
('2025-08-01', 'Friday', 1, 0, NULL),
('2025-08-02', 'Saturday', 0, 0, NULL),
('2025-08-03', 'Sunday', 0, 0, NULL),
('2025-08-04', 'Monday', 1, 0, NULL),
('2025-08-05', 'Tuesday', 1, 0, NULL),
('2025-08-06', 'Wednesday', 1, 0, NULL),
('2025-08-07', 'Thursday', 1, 0, NULL),
('2025-08-08', 'Friday', 1, 0, NULL),
('2025-08-09', 'Saturday', 0, 0, NULL),
('2025-08-10', 'Sunday', 0, 0, NULL),
('2025-08-11', 'Monday', 1, 0, NULL),
('2025-08-12', 'Tuesday', 1, 0, NULL),
('2025-08-13', 'Wednesday', 1, 0, NULL),
('2025-08-14', 'Thursday', 1, 0, NULL),
('2025-08-15', 'Friday', 1, 0, NULL),
('2025-08-16', 'Saturday', 0, 0, NULL),
('2025-08-17', 'Sunday', 0, 0, NULL),
('2025-08-18', 'Monday', 1, 0, NULL),
('2025-08-19', 'Tuesday', 1, 0, NULL),
('2025-08-20', 'Wednesday', 1, 0, NULL),
('2025-08-21', 'Thursday', 1, 0, NULL),
('2025-08-22', 'Friday', 1, 0, NULL),
('2025-08-23', 'Saturday', 0, 0, NULL),
('2025-08-24', 'Sunday', 0, 0, NULL),
('2025-08-25', 'Monday', 1, 0, NULL),
('2025-08-26', 'Tuesday', 1, 0, NULL),
('2025-08-27', 'Wednesday', 1, 0, NULL),
('2025-08-28', 'Thursday', 1, 0, NULL),
('2025-08-29', 'Friday', 1, 0, NULL),
('2025-08-30', 'Saturday', 0, 0, NULL),
('2025-08-31', 'Sunday', 0, 0, NULL),
('2025-09-01', 'Monday', 1, 0, NULL),
('2025-09-02', 'Tuesday', 1, 0, NULL),
('2025-09-03', 'Wednesday', 1, 0, NULL),
('2025-09-04', 'Thursday', 1, 0, NULL),
('2025-09-05', 'Friday', 1, 0, NULL),
('2025-09-06', 'Saturday', 0, 0, NULL),
('2025-09-07', 'Sunday', 0, 1, 'Independência'),
('2025-09-08', 'Monday', 1, 0, NULL),
('2025-09-09', 'Tuesday', 1, 0, NULL),
('2025-09-10', 'Wednesday', 1, 0, NULL),
('2025-09-11', 'Thursday', 1, 0, NULL),
('2025-09-12', 'Friday', 1, 0, NULL),
('2025-09-13', 'Saturday', 0, 0, NULL),
('2025-09-14', 'Sunday', 0, 0, NULL),
('2025-09-15', 'Monday', 1, 0, NULL),
('2025-09-16', 'Tuesday', 1, 0, NULL),
('2025-09-17', 'Wednesday', 1, 0, NULL),
('2025-09-18', 'Thursday', 1, 0, NULL),
('2025-09-19', 'Friday', 1, 0, NULL),
('2025-09-20', 'Saturday', 0, 0, NULL),
('2025-09-21', 'Sunday', 0, 0, NULL),
('2025-09-22', 'Monday', 1, 0, NULL),
('2025-09-23', 'Tuesday', 1, 0, NULL),
('2025-09-24', 'Wednesday', 1, 0, NULL),
('2025-09-25', 'Thursday', 1, 0, NULL),
('2025-09-26', 'Friday', 1, 0, NULL),
('2025-09-27', 'Saturday', 0, 0, NULL),
('2025-09-28', 'Sunday', 0, 0, NULL),
('2025-09-29', 'Monday', 1, 0, NULL),
('2025-09-30', 'Tuesday', 1, 0, NULL),
('2025-10-01', 'Wednesday', 1, 0, NULL),
('2025-10-02', 'Thursday', 1, 0, NULL),
('2025-10-03', 'Friday', 1, 0, NULL),
('2025-10-04', 'Saturday', 0, 0, NULL),
('2025-10-05', 'Sunday', 0, 0, NULL),
('2025-10-06', 'Monday', 1, 0, NULL),
('2025-10-07', 'Tuesday', 1, 0, NULL),
('2025-10-08', 'Wednesday', 1, 0, NULL),
('2025-10-09', 'Thursday', 1, 0, NULL),
('2025-10-10', 'Friday', 1, 0, NULL),
('2025-10-11', 'Saturday', 0, 0, NULL),
('2025-10-12', 'Sunday', 0, 1, 'Nossa Senhora Aparecida'),
('2025-10-13', 'Monday', 1, 0, NULL),
('2025-10-14', 'Tuesday', 1, 0, NULL),
('2025-10-15', 'Wednesday', 1, 0, NULL),
('2025-10-16', 'Thursday', 1, 0, NULL),
('2025-10-17', 'Friday', 1, 0, NULL),
('2025-10-18', 'Saturday', 0, 0, NULL),
('2025-10-19', 'Sunday', 0, 0, NULL),
('2025-10-20', 'Monday', 1, 0, NULL),
('2025-10-21', 'Tuesday', 1, 0, NULL),
('2025-10-22', 'Wednesday', 1, 0, NULL),
('2025-10-23', 'Thursday', 1, 0, NULL),
('2025-10-24', 'Friday', 1, 0, NULL),
('2025-10-25', 'Saturday', 0, 0, NULL),
('2025-10-26', 'Sunday', 0, 0, NULL),
('2025-10-27', 'Monday', 1, 0, NULL),
('2025-10-28', 'Tuesday', 1, 0, NULL),
('2025-10-29', 'Wednesday', 1, 0, NULL),
('2025-10-30', 'Thursday', 1, 0, NULL),
('2025-10-31', 'Friday', 1, 0, NULL),
('2025-11-01', 'Saturday', 0, 0, NULL),
('2025-11-02', 'Sunday', 0, 1, 'Finados'),
('2025-11-03', 'Monday', 1, 0, NULL),
('2025-11-04', 'Tuesday', 1, 0, NULL),
('2025-11-05', 'Wednesday', 1, 0, NULL),
('2025-11-06', 'Thursday', 1, 0, NULL),
('2025-11-07', 'Friday', 1, 0, NULL),
('2025-11-08', 'Saturday', 0, 0, NULL),
('2025-11-09', 'Sunday', 0, 0, NULL),
('2025-11-10', 'Monday', 1, 0, NULL),
('2025-11-11', 'Tuesday', 1, 0, NULL),
('2025-11-12', 'Wednesday', 1, 0, NULL),
('2025-11-13', 'Thursday', 1, 0, NULL),
('2025-11-14', 'Friday', 1, 0, NULL),
('2025-11-15', 'Saturday', 0, 1, 'Proclamação da República'),
('2025-11-16', 'Sunday', 0, 0, NULL),
('2025-11-17', 'Monday', 1, 0, NULL),
('2025-11-18', 'Tuesday', 1, 0, NULL),
('2025-11-19', 'Wednesday', 1, 0, NULL),
('2025-11-20', 'Thursday', 1, 0, NULL),
('2025-11-21', 'Friday', 1, 0, NULL),
('2025-11-22', 'Saturday', 0, 0, NULL),
('2025-11-23', 'Sunday', 0, 0, NULL),
('2025-11-24', 'Monday', 1, 0, NULL),
('2025-11-25', 'Tuesday', 1, 0, NULL),
('2025-11-26', 'Wednesday', 1, 0, NULL),
('2025-11-27', 'Thursday', 1, 0, NULL),
('2025-11-28', 'Friday', 1, 0, NULL),
('2025-11-29', 'Saturday', 0, 0, NULL),
('2025-11-30', 'Sunday', 0, 0, NULL),
('2025-12-01', 'Monday', 1, 0, NULL),
('2025-12-02', 'Tuesday', 1, 0, NULL),
('2025-12-03', 'Wednesday', 1, 0, NULL),
('2025-12-04', 'Thursday', 1, 0, NULL),
('2025-12-05', 'Friday', 1, 0, NULL),
('2025-12-06', 'Saturday', 0, 0, NULL),
('2025-12-07', 'Sunday', 0, 0, NULL),
('2025-12-08', 'Monday', 1, 0, NULL),
('2025-12-09', 'Tuesday', 1, 0, NULL),
('2025-12-10', 'Wednesday', 1, 0, NULL),
('2025-12-11', 'Thursday', 1, 0, NULL),
('2025-12-12', 'Friday', 1, 0, NULL),
('2025-12-13', 'Saturday', 0, 0, NULL),
('2025-12-14', 'Sunday', 0, 0, NULL),
('2025-12-15', 'Monday', 1, 0, NULL),
('2025-12-16', 'Tuesday', 1, 0, NULL),
('2025-12-17', 'Wednesday', 1, 0, NULL),
('2025-12-18', 'Thursday', 1, 0, NULL),
('2025-12-19', 'Friday', 1, 0, NULL),
('2025-12-20', 'Saturday', 0, 0, NULL),
('2025-12-21', 'Sunday', 0, 0, NULL),
('2025-12-22', 'Monday', 1, 0, NULL),
('2025-12-23', 'Tuesday', 1, 0, NULL),
('2025-12-24', 'Wednesday', 1, 0, NULL),
('2025-12-25', 'Thursday', 0, 1, 'Natal'),
('2025-12-26', 'Friday', 1, 0, NULL),
('2025-12-27', 'Saturday', 0, 0, NULL),
('2025-12-28', 'Sunday', 0, 0, NULL),
('2025-12-29', 'Monday', 1, 0, NULL),
('2025-12-30', 'Tuesday', 1, 0, NULL),
('2025-12-31', 'Wednesday', 1, 0, NULL),
('2026-01-01', 'Thursday', 0, 1, 'Ano Novo'),
('2026-01-02', 'Friday', 1, 0, NULL),
('2026-01-03', 'Saturday', 0, 0, NULL),
('2026-01-04', 'Sunday', 0, 0, NULL),
('2026-01-05', 'Monday', 1, 0, NULL),
('2026-01-06', 'Tuesday', 1, 0, NULL),
('2026-01-07', 'Wednesday', 1, 0, NULL),
('2026-01-08', 'Thursday', 1, 0, NULL),
('2026-01-09', 'Friday', 1, 0, NULL),
('2026-01-10', 'Saturday', 0, 0, NULL),
('2026-01-11', 'Sunday', 0, 0, NULL),
('2026-01-12', 'Monday', 1, 0, NULL),
('2026-01-13', 'Tuesday', 1, 0, NULL),
('2026-01-14', 'Wednesday', 1, 0, NULL),
('2026-01-15', 'Thursday', 1, 0, NULL),
('2026-01-16', 'Friday', 1, 0, NULL),
('2026-01-17', 'Saturday', 0, 0, NULL),
('2026-01-18', 'Sunday', 0, 0, NULL),
('2026-01-19', 'Monday', 1, 0, NULL),
('2026-01-20', 'Tuesday', 1, 0, NULL),
('2026-01-21', 'Wednesday', 1, 0, NULL),
('2026-01-22', 'Thursday', 1, 0, NULL),
('2026-01-23', 'Friday', 1, 0, NULL),
('2026-01-24', 'Saturday', 0, 0, NULL),
('2026-01-25', 'Sunday', 0, 0, NULL),
('2026-01-26', 'Monday', 1, 0, NULL),
('2026-01-27', 'Tuesday', 1, 0, NULL),
('2026-01-28', 'Wednesday', 1, 0, NULL),
('2026-01-29', 'Thursday', 1, 0, NULL),
('2026-01-30', 'Friday', 1, 0, NULL),
('2026-01-31', 'Saturday', 0, 0, NULL),
('2026-02-01', 'Sunday', 0, 0, NULL),
('2026-02-02', 'Monday', 1, 0, NULL),
('2026-02-03', 'Tuesday', 1, 0, NULL),
('2026-02-04', 'Wednesday', 1, 0, NULL),
('2026-02-05', 'Thursday', 1, 0, NULL),
('2026-02-06', 'Friday', 1, 0, NULL),
('2026-02-07', 'Saturday', 0, 0, NULL),
('2026-02-08', 'Sunday', 0, 0, NULL),
('2026-02-09', 'Monday', 1, 0, NULL),
('2026-02-10', 'Tuesday', 1, 0, NULL),
('2026-02-11', 'Wednesday', 1, 0, NULL),
('2026-02-12', 'Thursday', 1, 0, NULL),
('2026-02-13', 'Friday', 1, 0, NULL),
('2026-02-14', 'Saturday', 0, 0, NULL),
('2026-02-15', 'Sunday', 0, 0, NULL),
('2026-02-16', 'Monday', 1, 0, NULL),
('2026-02-17', 'Tuesday', 0, 1, 'Carnaval'),
('2026-02-18', 'Wednesday', 0, 1, 'Carnaval'),
('2026-02-19', 'Thursday', 1, 0, NULL),
('2026-02-20', 'Friday', 1, 0, NULL),
('2026-02-21', 'Saturday', 0, 0, NULL),
('2026-02-22', 'Sunday', 0, 0, NULL),
('2026-02-23', 'Monday', 1, 0, NULL),
('2026-02-24', 'Tuesday', 1, 0, NULL),
('2026-02-25', 'Wednesday', 1, 0, NULL),
('2026-02-26', 'Thursday', 1, 0, NULL),
('2026-02-27', 'Friday', 1, 0, NULL),
('2026-02-28', 'Saturday', 0, 0, NULL),
('2026-03-01', 'Sunday', 0, 0, NULL),
('2026-03-02', 'Monday', 1, 0, NULL),
('2026-03-03', 'Tuesday', 1, 0, NULL),
('2026-03-04', 'Wednesday', 1, 0, NULL),
('2026-03-05', 'Thursday', 1, 0, NULL),
('2026-03-06', 'Friday', 1, 0, NULL),
('2026-03-07', 'Saturday', 0, 0, NULL),
('2026-03-08', 'Sunday', 0, 0, NULL),
('2026-03-09', 'Monday', 1, 0, NULL),
('2026-03-10', 'Tuesday', 1, 0, NULL),
('2026-03-11', 'Wednesday', 1, 0, NULL),
('2026-03-12', 'Thursday', 1, 0, NULL),
('2026-03-13', 'Friday', 1, 0, NULL),
('2026-03-14', 'Saturday', 0, 0, NULL),
('2026-03-15', 'Sunday', 0, 0, NULL),
('2026-03-16', 'Monday', 1, 0, NULL),
('2026-03-17', 'Tuesday', 1, 0, NULL),
('2026-03-18', 'Wednesday', 1, 0, NULL),
('2026-03-19', 'Thursday', 1, 0, NULL),
('2026-03-20', 'Friday', 1, 0, NULL),
('2026-03-21', 'Saturday', 0, 0, NULL),
('2026-03-22', 'Sunday', 0, 0, NULL),
('2026-03-23', 'Monday', 1, 0, NULL),
('2026-03-24', 'Tuesday', 1, 0, NULL),
('2026-03-25', 'Wednesday', 1, 0, NULL),
('2026-03-26', 'Thursday', 1, 0, NULL),
('2026-03-27', 'Friday', 1, 0, NULL),
('2026-03-28', 'Saturday', 0, 0, NULL),
('2026-03-29', 'Sunday', 0, 0, NULL),
('2026-03-30', 'Monday', 1, 0, NULL),
('2026-03-31', 'Tuesday', 1, 0, NULL),
('2026-04-01', 'Wednesday', 1, 0, NULL),
('2026-04-02', 'Thursday', 1, 0, NULL),
('2026-04-03', 'Friday', 0, 1, 'Páscoa'),
('2026-04-04', 'Saturday', 0, 0, NULL),
('2026-04-05', 'Sunday', 0, 0, NULL),
('2026-04-06', 'Monday', 1, 0, NULL),
('2026-04-07', 'Tuesday', 1, 0, NULL),
('2026-04-08', 'Wednesday', 1, 0, NULL),
('2026-04-09', 'Thursday', 1, 0, NULL),
('2026-04-10', 'Friday', 1, 0, NULL),
('2026-04-11', 'Saturday', 0, 0, NULL),
('2026-04-12', 'Sunday', 0, 0, NULL),
('2026-04-13', 'Monday', 1, 0, NULL),
('2026-04-14', 'Tuesday', 1, 0, NULL),
('2026-04-15', 'Wednesday', 1, 0, NULL),
('2026-04-16', 'Thursday', 1, 0, NULL),
('2026-04-17', 'Friday', 1, 0, NULL),
('2026-04-18', 'Saturday', 0, 0, NULL),
('2026-04-19', 'Sunday', 0, 0, NULL),
('2026-04-20', 'Monday', 1, 0, NULL),
('2026-04-21', 'Tuesday', 0, 1, 'Tiradentes'),
('2026-04-22', 'Wednesday', 1, 0, NULL),
('2026-04-23', 'Thursday', 1, 0, NULL),
('2026-04-24', 'Friday', 1, 0, NULL),
('2026-04-25', 'Saturday', 0, 0, NULL),
('2026-04-26', 'Sunday', 0, 0, NULL),
('2026-04-27', 'Monday', 1, 0, NULL),
('2026-04-28', 'Tuesday', 1, 0, NULL),
('2026-04-29', 'Wednesday', 1, 0, NULL),
('2026-04-30', 'Thursday', 1, 0, NULL),
('2026-05-01', 'Friday', 0, 1, 'Dia do Trabalho'),
('2026-05-02', 'Saturday', 0, 0, NULL),
('2026-05-03', 'Sunday', 0, 0, NULL),
('2026-05-04', 'Monday', 1, 0, NULL),
('2026-05-05', 'Tuesday', 1, 0, NULL),
('2026-05-06', 'Wednesday', 1, 0, NULL),
('2026-05-07', 'Thursday', 1, 0, NULL),
('2026-05-08', 'Friday', 1, 0, NULL),
('2026-05-09', 'Saturday', 0, 0, NULL),
('2026-05-10', 'Sunday', 0, 0, NULL),
('2026-05-11', 'Monday', 1, 0, NULL),
('2026-05-12', 'Tuesday', 1, 0, NULL),
('2026-05-13', 'Wednesday', 1, 0, NULL),
('2026-05-14', 'Thursday', 1, 0, NULL),
('2026-05-15', 'Friday', 1, 0, NULL),
('2026-05-16', 'Saturday', 0, 0, NULL),
('2026-05-17', 'Sunday', 0, 0, NULL),
('2026-05-18', 'Monday', 1, 0, NULL),
('2026-05-19', 'Tuesday', 1, 0, NULL),
('2026-05-20', 'Wednesday', 1, 0, NULL),
('2026-05-21', 'Thursday', 1, 0, NULL),
('2026-05-22', 'Friday', 1, 0, NULL),
('2026-05-23', 'Saturday', 0, 0, NULL),
('2026-05-24', 'Sunday', 0, 0, NULL),
('2026-05-25', 'Monday', 1, 0, NULL),
('2026-05-26', 'Tuesday', 1, 0, NULL),
('2026-05-27', 'Wednesday', 1, 0, NULL),
('2026-05-28', 'Thursday', 1, 0, NULL),
('2026-05-29', 'Friday', 1, 0, NULL),
('2026-05-30', 'Saturday', 0, 0, NULL),
('2026-05-31', 'Sunday', 0, 0, NULL),
('2026-06-01', 'Monday', 1, 0, NULL),
('2026-06-02', 'Tuesday', 1, 0, NULL),
('2026-06-03', 'Wednesday', 1, 0, NULL),
('2026-06-04', 'Thursday', 0, 1, 'Corpus Christi'),
('2026-06-05', 'Friday', 1, 0, NULL),
('2026-06-06', 'Saturday', 0, 0, NULL),
('2026-06-07', 'Sunday', 0, 0, NULL),
('2026-06-08', 'Monday', 1, 0, NULL),
('2026-06-09', 'Tuesday', 1, 0, NULL),
('2026-06-10', 'Wednesday', 1, 0, NULL),
('2026-06-11', 'Thursday', 1, 0, NULL),
('2026-06-12', 'Friday', 1, 0, NULL),
('2026-06-13', 'Saturday', 0, 0, NULL),
('2026-06-14', 'Sunday', 0, 0, NULL),
('2026-06-15', 'Monday', 1, 0, NULL),
('2026-06-16', 'Tuesday', 1, 0, NULL),
('2026-06-17', 'Wednesday', 1, 0, NULL),
('2026-06-18', 'Thursday', 1, 0, NULL),
('2026-06-19', 'Friday', 1, 0, NULL),
('2026-06-20', 'Saturday', 0, 0, NULL),
('2026-06-21', 'Sunday', 0, 0, NULL),
('2026-06-22', 'Monday', 1, 0, NULL),
('2026-06-23', 'Tuesday', 1, 0, NULL),
('2026-06-24', 'Wednesday', 1, 0, NULL),
('2026-06-25', 'Thursday', 1, 0, NULL),
('2026-06-26', 'Friday', 1, 0, NULL),
('2026-06-27', 'Saturday', 0, 0, NULL),
('2026-06-28', 'Sunday', 0, 0, NULL),
('2026-06-29', 'Monday', 1, 0, NULL),
('2026-06-30', 'Tuesday', 1, 0, NULL),
('2026-07-01', 'Wednesday', 1, 0, NULL),
('2026-07-02', 'Thursday', 1, 0, NULL),
('2026-07-03', 'Friday', 1, 0, NULL),
('2026-07-04', 'Saturday', 0, 0, NULL),
('2026-07-05', 'Sunday', 0, 0, NULL),
('2026-07-06', 'Monday', 1, 0, NULL),
('2026-07-07', 'Tuesday', 1, 0, NULL),
('2026-07-08', 'Wednesday', 1, 0, NULL),
('2026-07-09', 'Thursday', 1, 0, NULL),
('2026-07-10', 'Friday', 1, 0, NULL),
('2026-07-11', 'Saturday', 0, 0, NULL),
('2026-07-12', 'Sunday', 0, 0, NULL),
('2026-07-13', 'Monday', 1, 0, NULL),
('2026-07-14', 'Tuesday', 1, 0, NULL),
('2026-07-15', 'Wednesday', 1, 0, NULL),
('2026-07-16', 'Thursday', 1, 0, NULL),
('2026-07-17', 'Friday', 1, 0, NULL),
('2026-07-18', 'Saturday', 0, 0, NULL),
('2026-07-19', 'Sunday', 0, 0, NULL),
('2026-07-20', 'Monday', 1, 0, NULL),
('2026-07-21', 'Tuesday', 1, 0, NULL),
('2026-07-22', 'Wednesday', 1, 0, NULL),
('2026-07-23', 'Thursday', 1, 0, NULL),
('2026-07-24', 'Friday', 1, 0, NULL),
('2026-07-25', 'Saturday', 0, 0, NULL),
('2026-07-26', 'Sunday', 0, 0, NULL),
('2026-07-27', 'Monday', 1, 0, NULL),
('2026-07-28', 'Tuesday', 1, 0, NULL),
('2026-07-29', 'Wednesday', 1, 0, NULL),
('2026-07-30', 'Thursday', 1, 0, NULL),
('2026-07-31', 'Friday', 1, 0, NULL),
('2026-08-01', 'Saturday', 0, 0, NULL),
('2026-08-02', 'Sunday', 0, 0, NULL),
('2026-08-03', 'Monday', 1, 0, NULL),
('2026-08-04', 'Tuesday', 1, 0, NULL),
('2026-08-05', 'Wednesday', 1, 0, NULL),
('2026-08-06', 'Thursday', 1, 0, NULL),
('2026-08-07', 'Friday', 1, 0, NULL),
('2026-08-08', 'Saturday', 0, 0, NULL),
('2026-08-09', 'Sunday', 0, 0, NULL),
('2026-08-10', 'Monday', 1, 0, NULL),
('2026-08-11', 'Tuesday', 1, 0, NULL),
('2026-08-12', 'Wednesday', 1, 0, NULL),
('2026-08-13', 'Thursday', 1, 0, NULL),
('2026-08-14', 'Friday', 1, 0, NULL),
('2026-08-15', 'Saturday', 0, 0, NULL),
('2026-08-16', 'Sunday', 0, 0, NULL),
('2026-08-17', 'Monday', 1, 0, NULL),
('2026-08-18', 'Tuesday', 1, 0, NULL),
('2026-08-19', 'Wednesday', 1, 0, NULL),
('2026-08-20', 'Thursday', 1, 0, NULL),
('2026-08-21', 'Friday', 1, 0, NULL),
('2026-08-22', 'Saturday', 0, 0, NULL),
('2026-08-23', 'Sunday', 0, 0, NULL),
('2026-08-24', 'Monday', 1, 0, NULL),
('2026-08-25', 'Tuesday', 1, 0, NULL),
('2026-08-26', 'Wednesday', 1, 0, NULL),
('2026-08-27', 'Thursday', 1, 0, NULL),
('2026-08-28', 'Friday', 1, 0, NULL),
('2026-08-29', 'Saturday', 0, 0, NULL),
('2026-08-30', 'Sunday', 0, 0, NULL),
('2026-08-31', 'Monday', 1, 0, NULL),
('2026-09-01', 'Tuesday', 1, 0, NULL),
('2026-09-02', 'Wednesday', 1, 0, NULL),
('2026-09-03', 'Thursday', 1, 0, NULL),
('2026-09-04', 'Friday', 1, 0, NULL),
('2026-09-05', 'Saturday', 0, 0, NULL),
('2026-09-06', 'Sunday', 0, 0, NULL),
('2026-09-07', 'Monday', 0, 1, 'Independência'),
('2026-09-08', 'Tuesday', 1, 0, NULL),
('2026-09-09', 'Wednesday', 1, 0, NULL),
('2026-09-10', 'Thursday', 1, 0, NULL),
('2026-09-11', 'Friday', 1, 0, NULL),
('2026-09-12', 'Saturday', 0, 0, NULL),
('2026-09-13', 'Sunday', 0, 0, NULL),
('2026-09-14', 'Monday', 1, 0, NULL),
('2026-09-15', 'Tuesday', 1, 0, NULL),
('2026-09-16', 'Wednesday', 1, 0, NULL),
('2026-09-17', 'Thursday', 1, 0, NULL),
('2026-09-18', 'Friday', 1, 0, NULL),
('2026-09-19', 'Saturday', 0, 0, NULL),
('2026-09-20', 'Sunday', 0, 0, NULL),
('2026-09-21', 'Monday', 1, 0, NULL),
('2026-09-22', 'Tuesday', 1, 0, NULL),
('2026-09-23', 'Wednesday', 1, 0, NULL),
('2026-09-24', 'Thursday', 1, 0, NULL),
('2026-09-25', 'Friday', 1, 0, NULL),
('2026-09-26', 'Saturday', 0, 0, NULL),
('2026-09-27', 'Sunday', 0, 0, NULL),
('2026-09-28', 'Monday', 1, 0, NULL),
('2026-09-29', 'Tuesday', 1, 0, NULL),
('2026-09-30', 'Wednesday', 1, 0, NULL),
('2026-10-01', 'Thursday', 1, 0, NULL),
('2026-10-02', 'Friday', 1, 0, NULL),
('2026-10-03', 'Saturday', 0, 0, NULL),
('2026-10-04', 'Sunday', 0, 0, NULL),
('2026-10-05', 'Monday', 1, 0, NULL),
('2026-10-06', 'Tuesday', 1, 0, NULL),
('2026-10-07', 'Wednesday', 1, 0, NULL),
('2026-10-08', 'Thursday', 1, 0, NULL),
('2026-10-09', 'Friday', 1, 0, NULL),
('2026-10-10', 'Saturday', 0, 0, NULL),
('2026-10-11', 'Sunday', 0, 0, NULL),
('2026-10-12', 'Monday', 0, 1, 'Nossa Senhora Aparecida'),
('2026-10-13', 'Tuesday', 1, 0, NULL),
('2026-10-14', 'Wednesday', 1, 0, NULL),
('2026-10-15', 'Thursday', 1, 0, NULL),
('2026-10-16', 'Friday', 1, 0, NULL),
('2026-10-17', 'Saturday', 0, 0, NULL),
('2026-10-18', 'Sunday', 0, 0, NULL),
('2026-10-19', 'Monday', 1, 0, NULL),
('2026-10-20', 'Tuesday', 1, 0, NULL),
('2026-10-21', 'Wednesday', 1, 0, NULL),
('2026-10-22', 'Thursday', 1, 0, NULL),
('2026-10-23', 'Friday', 1, 0, NULL),
('2026-10-24', 'Saturday', 0, 0, NULL),
('2026-10-25', 'Sunday', 0, 0, NULL),
('2026-10-26', 'Monday', 1, 0, NULL),
('2026-10-27', 'Tuesday', 1, 0, NULL),
('2026-10-28', 'Wednesday', 1, 0, NULL),
('2026-10-29', 'Thursday', 1, 0, NULL),
('2026-10-30', 'Friday', 1, 0, NULL),
('2026-10-31', 'Saturday', 0, 0, NULL),
('2026-11-01', 'Sunday', 0, 0, NULL),
('2026-11-02', 'Monday', 0, 1, 'Finados'),
('2026-11-03', 'Tuesday', 1, 0, NULL),
('2026-11-04', 'Wednesday', 1, 0, NULL),
('2026-11-05', 'Thursday', 1, 0, NULL),
('2026-11-06', 'Friday', 1, 0, NULL),
('2026-11-07', 'Saturday', 0, 0, NULL),
('2026-11-08', 'Sunday', 0, 0, NULL),
('2026-11-09', 'Monday', 1, 0, NULL),
('2026-11-10', 'Tuesday', 1, 0, NULL),
('2026-11-11', 'Wednesday', 1, 0, NULL),
('2026-11-12', 'Thursday', 1, 0, NULL),
('2026-11-13', 'Friday', 1, 0, NULL),
('2026-11-14', 'Saturday', 0, 0, NULL),
('2026-11-15', 'Sunday', 0, 1, 'Proclamação da República'),
('2026-11-16', 'Monday', 1, 0, NULL),
('2026-11-17', 'Tuesday', 1, 0, NULL),
('2026-11-18', 'Wednesday', 1, 0, NULL),
('2026-11-19', 'Thursday', 1, 0, NULL),
('2026-11-20', 'Friday', 1, 0, NULL),
('2026-11-21', 'Saturday', 0, 0, NULL),
('2026-11-22', 'Sunday', 0, 0, NULL),
('2026-11-23', 'Monday', 1, 0, NULL),
('2026-11-24', 'Tuesday', 1, 0, NULL),
('2026-11-25', 'Wednesday', 1, 0, NULL),
('2026-11-26', 'Thursday', 1, 0, NULL),
('2026-11-27', 'Friday', 1, 0, NULL),
('2026-11-28', 'Saturday', 0, 0, NULL),
('2026-11-29', 'Sunday', 0, 0, NULL),
('2026-11-30', 'Monday', 1, 0, NULL),
('2026-12-01', 'Tuesday', 1, 0, NULL),
('2026-12-02', 'Wednesday', 1, 0, NULL),
('2026-12-03', 'Thursday', 1, 0, NULL),
('2026-12-04', 'Friday', 1, 0, NULL),
('2026-12-05', 'Saturday', 0, 0, NULL),
('2026-12-06', 'Sunday', 0, 0, NULL),
('2026-12-07', 'Monday', 1, 0, NULL),
('2026-12-08', 'Tuesday', 1, 0, NULL),
('2026-12-09', 'Wednesday', 1, 0, NULL),
('2026-12-10', 'Thursday', 1, 0, NULL),
('2026-12-11', 'Friday', 1, 0, NULL),
('2026-12-12', 'Saturday', 0, 0, NULL),
('2026-12-13', 'Sunday', 0, 0, NULL),
('2026-12-14', 'Monday', 1, 0, NULL),
('2026-12-15', 'Tuesday', 1, 0, NULL),
('2026-12-16', 'Wednesday', 1, 0, NULL),
('2026-12-17', 'Thursday', 1, 0, NULL),
('2026-12-18', 'Friday', 1, 0, NULL),
('2026-12-19', 'Saturday', 0, 0, NULL),
('2026-12-20', 'Sunday', 0, 0, NULL),
('2026-12-21', 'Monday', 1, 0, NULL),
('2026-12-22', 'Tuesday', 1, 0, NULL),
('2026-12-23', 'Wednesday', 1, 0, NULL),
('2026-12-24', 'Thursday', 1, 0, NULL),
('2026-12-25', 'Friday', 0, 1, 'Natal'),
('2026-12-26', 'Saturday', 0, 0, NULL),
('2026-12-27', 'Sunday', 0, 0, NULL),
('2026-12-28', 'Monday', 1, 0, NULL),
('2026-12-29', 'Tuesday', 1, 0, NULL),
('2026-12-30', 'Wednesday', 1, 0, NULL),
('2026-12-31', 'Thursday', 1, 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL DEFAULT 1,
  `nome` varchar(100) NOT NULL,
  `telefone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `observacoes` text DEFAULT NULL,
  `ultima_venda` date DEFAULT NULL,
  `media_gastos` decimal(10,2) DEFAULT 0.00,
  `total_gasto` decimal(10,2) DEFAULT 0.00,
  `taxa_fechamento` decimal(5,2) DEFAULT 0.00,
  `categoria_abc` enum('A','B','C') DEFAULT NULL,
  `data_primeira_compra` date DEFAULT NULL,
  `status_cliente` enum('ativo','recorrente','novo','perdido','inativo') DEFAULT 'novo',
  `empresa` varchar(150) DEFAULT NULL,
  `documento` varchar(30) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `comportamento_clientes`
--

CREATE TABLE `comportamento_clientes` (
  `cliente_id` int(11) NOT NULL,
  `status` enum('ativo','recorrente','novo','perdido','inativo') NOT NULL,
  `ultima_compra` date DEFAULT NULL,
  `primeira_compra` date DEFAULT NULL,
  `total_compras` int(11) DEFAULT 0,
  `total_gasto` decimal(12,2) DEFAULT 0.00,
  `frequencia_media_dias` int(11) DEFAULT NULL,
  `tempo_medio_entre_compras` int(11) DEFAULT NULL,
  `categoria_abc` enum('A','B','C') DEFAULT NULL,
  `vendedores_que_atendeu` text DEFAULT NULL,
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `configuracoes_sistema`
--

CREATE TABLE `configuracoes_sistema` (
  `chave` varchar(50) NOT NULL,
  `valor` varchar(255) NOT NULL DEFAULT '',
  `atualizado_por` int(11) DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `configuracoes_sistema`
--

INSERT INTO `configuracoes_sistema` (`chave`, `valor`, `atualizado_por`, `atualizado_em`) VALUES
('campo_codigo_orcamento_habilitado', '1', NULL, '2026-02-05 11:01:23'),
('campo_email_obrigatorio', '1', NULL, '2026-02-05 11:01:23'),
('campo_observacoes_habilitado', '1', NULL, '2026-02-05 11:01:23'),
('campo_telefone_obrigatorio', '1', NULL, '2026-02-05 11:01:23'),
('contar_sabado', '0', NULL, '2026-01-22 11:10:03'),
('cor_primaria', '#f00000', NULL, '2026-02-05 14:18:25'),
('cor_secundaria', '#10b981', NULL, '2026-02-05 11:01:23'),
('formato_data', 'd/m/Y', NULL, '2026-02-05 11:01:23'),
('formato_moeda', 'R$', NULL, '2026-02-05 11:01:23'),
('fuso_horario', 'America/Sao_Paulo', NULL, '2026-02-05 11:01:23'),
('idioma', 'pt_BR', NULL, '2026-02-05 11:01:23'),
('notificacao_email', '1', NULL, '2026-02-05 11:01:23'),
('notificacao_sistema', '1', NULL, '2026-02-05 11:01:23'),
('notificar_novas_vendas', 'todos', NULL, '2026-02-05 11:01:23'),
('notificar_vendas_perdidas', 'gerentes', NULL, '2026-02-05 11:01:23'),
('tema', 'claro', NULL, '2026-02-05 14:18:25');

-- --------------------------------------------------------

--
-- Estrutura para tabela `dashboard_layouts`
--

CREATE TABLE `dashboard_layouts` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL DEFAULT 'Layout do usuário',
  `is_shared` tinyint(1) NOT NULL DEFAULT 0,
  `layout_json` longtext NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `dashboard_layouts`
--

INSERT INTO `dashboard_layouts` (`id`, `user_id`, `name`, `is_shared`, `layout_json`, `created_at`, `updated_at`) VALUES
(1, 4, 'Layout do usuário', 0, '[{\"id\":\"card_total_clientes\",\"type\":\"stat\",\"title\":\"Total de Clientes\",\"icon\":\"fa-solid fa-users\",\"metric\":\"total_clients\",\"instance_id\":\"c_ml6jdiwwm1m86l\"},{\"id\":\"card_vendas_mes\",\"type\":\"stat\",\"title\":\"Vendas do Mês\",\"icon\":\"fa-solid fa-cart-shopping\",\"metric\":\"vendas_mes\",\"instance_id\":\"c_ml6jdjmo0h62ly\"},{\"id\":\"card_valor_mes\",\"type\":\"stat\",\"title\":\"Valor do Mês\",\"icon\":\"fa-solid fa-coins\",\"metric\":\"valor_mes\",\"instance_id\":\"c_ml6jdkhkkxqvxm\"},{\"id\":\"card_taxa_fechamento\",\"type\":\"stat\",\"title\":\"Taxa de Fechamento\",\"icon\":\"fa-solid fa-percent\",\"metric\":\"taxa_fechamento\",\"instance_id\":\"c_ml6jdkyw20ba75\"},{\"id\":\"card_clientes_inativos\",\"type\":\"stat\",\"title\":\"Clientes Inativos\",\"icon\":\"fa-solid fa-user-clock\",\"metric\":\"clientes_inativos\",\"instance_id\":\"c_ml6jdlggh3qugb\"},{\"id\":\"card_total_negociacoes\",\"type\":\"stat\",\"title\":\"Total de Negociações\",\"icon\":\"fa-solid fa-handshake\",\"metric\":\"total_negociacoes\",\"instance_id\":\"c_ml6jdm54jecpz6\"},{\"id\":\"card_faturamento_mes\",\"type\":\"stat\",\"title\":\"Faturamento do Mês\",\"icon\":\"fa-solid fa-sack-dollar\",\"metric\":\"faturamento_mes\",\"instance_id\":\"c_ml6jdn94vjommg\"},{\"id\":\"card_qtd_vendas_mes\",\"type\":\"stat\",\"title\":\"Vendas do Mês\",\"icon\":\"fa-solid fa-cart-shopping\",\"metric\":\"qtd_vendas_mes\",\"instance_id\":\"c_ml6jdnnc5ahftp\"},{\"id\":\"card_ticket_medio_mes\",\"type\":\"stat\",\"title\":\"Ticket Médio (Mês)\",\"icon\":\"fa-solid fa-receipt\",\"metric\":\"ticket_medio_mes\",\"instance_id\":\"c_ml6jdo0o53ai3h\"},{\"id\":\"card_faturamento_semana\",\"type\":\"stat\",\"title\":\"Faturamento da Semana\",\"icon\":\"fa-solid fa-sack-dollar\",\"metric\":\"faturamento_semana\",\"instance_id\":\"c_ml6jdocwz52aqm\"}]', '2025-12-30 15:26:44', '2026-02-03 11:49:47'),
(2, NULL, 'Layout Padrão CRM', 1, '[\r\n    {\"x\":0,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_total_clientes\"},\r\n    {\"x\":3,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_vendas_mes\"},\r\n    {\"x\":6,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_valor_mes\"},\r\n    {\"x\":9,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_taxa_fechamento\"},\r\n    {\"x\":0,\"y\":2,\"w\":3,\"h\":2,\"id\":\"card_clientes_inativos\"},\r\n    {\"x\":3,\"y\":2,\"w\":3,\"h\":2,\"id\":\"card_total_negociacoes\"}\r\n]', '2026-01-09 14:55:33', '2026-01-09 14:55:33'),
(3, 5, 'Layout do usuário', 0, '[{\"id\":\"card_faturamento_mes\",\"type\":\"stat\",\"title\":\"Faturamento do Mês\",\"icon\":\"fa-solid fa-sack-dollar\",\"metric\":\"faturamento_mes\",\"instance_id\":\"c_mkidr7qwvu0aol\"},{\"id\":\"card_total_negociacoes\",\"type\":\"stat\",\"title\":\"Total de Negociações\",\"icon\":\"fa-solid fa-handshake\",\"metric\":\"total_negociacoes\",\"instance_id\":\"c_mkidraxjbld5nr\"},{\"id\":\"card_meta_atingida_percent\",\"type\":\"stat\",\"title\":\"% da Meta (Mês)\",\"icon\":\"fa-solid fa-bullseye\",\"metric\":\"meta_atingida_percent\",\"instance_id\":\"c_mkidrdmsibg0ro\"},{\"id\":\"card_faturamento_dia\",\"type\":\"stat\",\"title\":\"Faturamento do Dia\",\"icon\":\"fa-solid fa-sack-dollar\",\"metric\":\"faturamento_dia\",\"instance_id\":\"c_mkidrk85lpp052\"},{\"id\":\"card_ticket_medio_dia\",\"type\":\"stat\",\"title\":\"Ticket Médio (Diario)\",\"icon\":\"fa-solid fa-receipt\",\"metric\":\"ticket_medio_dia\",\"instance_id\":\"c_mkidrx0xy5ectq\"},{\"id\":\"card_ticket_medio_mes\",\"type\":\"stat\",\"title\":\"Ticket Médio (Mês)\",\"icon\":\"fa-solid fa-receipt\",\"metric\":\"ticket_medio_mes\",\"instance_id\":\"c_mkids26ikdurou\"},{\"id\":\"card_taxa_fechamento\",\"type\":\"stat\",\"title\":\"Taxa de Fechamento\",\"icon\":\"fa-solid fa-percent\",\"metric\":\"taxa_fechamento\",\"instance_id\":\"c_mkidsamha4zyrg\"},{\"id\":\"card_meta_mes\",\"type\":\"stat\",\"title\":\"Meta do Mês\",\"icon\":\"fa-solid fa-flag-checkered\",\"metric\":\"meta_mes\",\"instance_id\":\"c_mkidyd6zhbejbi\"},{\"id\":\"card_meta_atingida_percent_dia\",\"type\":\"stat\",\"title\":\"% da Meta (Dia)\",\"icon\":\"fa-solid fa-percent\",\"metric\":\"meta_atingida_percent_dia\",\"instance_id\":\"c_mkie41m1bg1kur\"}]', '2026-01-15 13:05:36', '2026-01-23 12:06:30'),
(4, 8, '0', 0, '[{\"x\":0,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_total_clientes\"},{\"x\":3,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_vendas_mes\"},{\"x\":6,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_valor_mes\"},{\"x\":9,\"y\":0,\"w\":3,\"h\":2,\"id\":\"card_taxa_fechamento\"},{\"x\":0,\"y\":2,\"w\":3,\"h\":2,\"id\":\"card_clientes_inativos\"},{\"x\":3,\"y\":2,\"w\":3,\"h\":2,\"id\":\"card_total_negociacoes\"},{\"id\":\"card_faturamento_dia\",\"type\":\"stat\",\"title\":\"Faturamento do Dia\",\"icon\":\"fa-solid fa-sack-dollar\",\"metric\":\"faturamento_dia\",\"instance_id\":\"c_mkml60a6fpo1f6\"}]', '2026-01-20 12:44:25', '2026-01-20 12:44:25'),
(5, 11, 'Layout do usuário', 0, '[{\"id\":\"card_faturamento_mes\",\"type\":\"stat\",\"title\":\"Faturamento do Mês\",\"icon\":\"fa-solid fa-sack-dollar\",\"metric\":\"faturamento_mes\",\"instance_id\":\"c_mkmmxhp3thfsk5\"},{\"id\":\"card_taxa_fechamento\",\"type\":\"stat\",\"title\":\"Taxa de Fechamento\",\"icon\":\"fa-solid fa-percent\",\"metric\":\"taxa_fechamento\",\"instance_id\":\"c_mkmmxcbrbj44l5\"},{\"id\":\"card_necessario_por_dia\",\"type\":\"stat\",\"title\":\"Meta Ticket Medio (Dia)\",\"icon\":\"fa-solid fa-calendar-day\",\"metric\":\"necessario_por_dia\",\"instance_id\":\"c_mkmmxotkb5u6jg\"},{\"id\":\"card_meta_mes\",\"type\":\"stat\",\"title\":\"Meta do Mês\",\"icon\":\"fa-solid fa-flag-checkered\",\"metric\":\"meta_mes\",\"instance_id\":\"c_mkmmxrfv065cig\"},{\"id\":\"card_meta_atingida_percent_dia\",\"type\":\"stat\",\"title\":\"% da Meta (Dia)\",\"icon\":\"fa-solid fa-percent\",\"metric\":\"meta_atingida_percent_dia\",\"instance_id\":\"c_mkmmxtvh9xdhsi\"},{\"id\":\"card_ticket_medio_dia\",\"type\":\"stat\",\"title\":\"Ticket Médio (Diario)\",\"icon\":\"fa-solid fa-receipt\",\"metric\":\"ticket_medio_dia\",\"instance_id\":\"c_mkmmxv3vrksti9\"},{\"id\":\"card_meta_atingida_percent\",\"type\":\"stat\",\"title\":\"% da Meta (Mês)\",\"icon\":\"fa-solid fa-bullseye\",\"metric\":\"meta_atingida_percent\",\"instance_id\":\"c_mkmn6engweg4xs\"}]', '2026-01-20 13:34:05', '2026-01-20 13:40:44');

-- --------------------------------------------------------

--
-- Estrutura para tabela `logs_acesso`
--

CREATE TABLE `logs_acesso` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_hora` datetime DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `navegador` varchar(200) DEFAULT NULL,
  `sistema_operacional` varchar(100) DEFAULT NULL,
  `acao` varchar(50) DEFAULT NULL,
  `sucesso` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `logs_acesso`
--

INSERT INTO `logs_acesso` (`id`, `usuario_id`, `data_hora`, `ip`, `navegador`, `sistema_operacional`, `acao`, `sucesso`) VALUES
(2, 4, '2025-12-23 16:03:27', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(3, 4, '2025-12-23 16:23:27', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(4, 4, '2025-12-23 16:49:16', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(5, 4, '2025-12-23 16:49:37', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(6, 4, '2025-12-23 17:22:14', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(7, 4, '2025-12-26 07:37:56', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(8, 4, '2025-12-26 09:06:29', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(9, 4, '2025-12-27 08:33:35', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(10, 4, '2025-12-27 09:41:16', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(11, 4, '2025-12-27 09:50:01', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(12, 4, '2025-12-27 11:06:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(13, 4, '2025-12-29 08:06:27', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(14, 4, '2025-12-29 09:08:22', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(15, 4, '2025-12-29 09:35:31', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', 'Windows', 'login', 1),
(16, 4, '2025-12-29 09:43:56', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', 'Windows', 'login', 1),
(17, 4, '2025-12-29 11:28:30', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(18, 4, '2025-12-30 07:56:21', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(19, 4, '2025-12-30 07:59:36', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0', 'Windows', 'login', 1),
(20, 4, '2025-12-30 08:17:31', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(21, 4, '2025-12-30 08:40:16', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(22, 4, '2025-12-30 09:40:17', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(23, 4, '2026-01-03 08:19:56', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(25, 4, '2026-01-03 10:33:31', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(29, 4, '2026-01-03 11:03:38', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(31, 4, '2026-01-03 11:12:57', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(32, 4, '2026-01-09 10:53:07', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(33, 4, '2026-01-13 08:22:10', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(35, 4, '2026-01-13 11:35:16', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(37, 4, '2026-01-13 11:38:54', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(40, 4, '2026-01-13 11:49:51', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(42, 4, '2026-01-13 11:52:46', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(43, 4, '2026-01-15 07:49:19', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(45, 4, '2026-01-15 09:45:44', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(47, 4, '2026-01-16 09:27:25', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(49, 4, '2026-01-16 09:37:50', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(50, 4, '2026-01-17 10:02:15', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(52, 4, '2026-01-17 10:57:05', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(54, 4, '2026-01-17 11:35:15', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(55, 4, '2026-01-17 11:36:54', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(58, 4, '2026-01-17 11:52:06', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(60, 4, '2026-01-20 08:36:08', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(62, 4, '2026-01-20 09:43:41', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(64, 4, '2026-01-20 09:44:07', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(73, 4, '2026-01-20 10:23:39', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(80, 4, '2026-01-20 10:26:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(82, 4, '2026-01-20 10:27:11', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(86, 4, '2026-01-20 10:28:28', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(90, 4, '2026-01-20 10:30:43', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(95, 4, '2026-01-20 10:38:00', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(102, 4, '2026-01-20 10:55:38', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(105, 4, '2026-01-20 11:17:06', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(108, 4, '2026-01-20 11:40:52', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(109, 4, '2026-01-20 12:12:40', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(112, 4, '2026-01-22 08:02:05', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(116, 4, '2026-01-23 09:06:42', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(128, 4, '2026-01-23 10:33:36', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(134, 4, '2026-01-23 10:42:18', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(137, 4, '2026-01-23 10:42:45', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(138, 12, '2026-01-23 10:44:06', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(139, 4, '2026-01-23 11:21:31', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(140, 12, '2026-01-23 11:21:39', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(141, 4, '2026-01-23 11:21:48', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(142, 12, '2026-01-23 11:23:10', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(143, 14, '2026-01-23 11:26:22', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', NULL, 'login_falha', 0),
(144, 14, '2026-01-23 11:26:29', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(145, 4, '2026-01-23 11:27:20', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(146, 4, '2026-01-29 13:29:34', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(149, 4, '2026-01-31 10:20:16', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(152, 4, '2026-01-31 11:01:24', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(153, 4, '2026-02-02 08:12:47', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(154, 4, '2026-02-02 08:14:31', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(156, 4, '2026-02-02 08:21:50', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(157, 4, '2026-02-02 08:31:20', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(158, 18, '2026-02-02 10:04:10', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(159, 4, '2026-02-02 10:04:22', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(160, 18, '2026-02-02 10:04:41', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(161, 4, '2026-02-02 10:06:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(162, 4, '2026-02-03 08:46:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(163, 4, '2026-02-03 08:49:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(164, 19, '2026-02-03 11:20:49', '192.168.0.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(165, 4, '2026-02-04 07:56:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(166, 4, '2026-02-04 09:44:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1),
(167, 4, '2026-02-04 11:18:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Windows', 'login', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `metas_vendedores`
--

CREATE TABLE `metas_vendedores` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `periodo` enum('diaria','semanal','mensal') NOT NULL,
  `valor_meta` decimal(12,2) NOT NULL,
  `data_inicio` date NOT NULL,
  `data_fim` date DEFAULT NULL,
  `tipo_meta` enum('faturamento','quantidade','ticket_medio') NOT NULL,
  `ativo` tinyint(1) DEFAULT 1,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `metas_vendedor_mensal`
--

CREATE TABLE `metas_vendedor_mensal` (
  `id` int(11) NOT NULL,
  `vendedor_id` int(11) NOT NULL,
  `ano` int(11) NOT NULL,
  `mes` int(11) NOT NULL,
  `meta_valor` decimal(12,2) NOT NULL DEFAULT 0.00,
  `atualizado_por` int(11) DEFAULT NULL,
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `metas_vendedor_mensal`
--

INSERT INTO `metas_vendedor_mensal` (`id`, `vendedor_id`, `ano`, `mes`, `meta_valor`, `atualizado_por`, `atualizado_em`) VALUES
(13, 12, 2026, 1, 0.00, 4, '2026-01-31 14:46:21');

-- --------------------------------------------------------

--
-- Estrutura para tabela `motivos_perda`
--

CREATE TABLE `motivos_perda` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `permite_outro` tinyint(1) NOT NULL DEFAULT 0,
  `ordem` int(11) NOT NULL DEFAULT 0,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `motivos_perda`
--

INSERT INTO `motivos_perda` (`id`, `nome`, `permite_outro`, `ordem`, `ativo`) VALUES
(1, 'Preço fora do orçamento', 0, 1, 1),
(2, 'Prazo de entrega', 0, 2, 1),
(3, 'Sem estoque', 0, 3, 1),
(4, 'Pagamento incompatível', 0, 4, 1),
(5, 'Outro', 1, 99, 1),
(6, 'Preço fora do orçamento', 0, 1, 1),
(7, 'Prazo de entrega', 0, 2, 1),
(8, 'Sem estoque', 0, 3, 1),
(9, 'Pagamento incompatível', 0, 4, 1),
(10, 'Outro', 1, 99, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `permissoes`
--

CREATE TABLE `permissoes` (
  `id` int(11) NOT NULL,
  `perfil` varchar(50) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `acesso` enum('leitura','escrita','total','nenhum') NOT NULL DEFAULT 'leitura'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `permissoes`
--

INSERT INTO `permissoes` (`id`, `perfil`, `modulo`, `acesso`) VALUES
(0, 'admin', 'todos', 'total'),
(0, 'gerencia', 'vendas', 'total'),
(0, 'gerencia', 'clientes', 'total'),
(0, 'gerencia', 'relatorios', 'total'),
(0, 'vendedor', 'vendas', 'escrita'),
(0, 'vendedor', 'clientes', 'escrita'),
(0, 'vendedor', 'relatorios', 'leitura'),
(0, 'estoque', 'produtos', 'total'),
(0, 'estoque', 'estoque', 'total'),
(0, 'rh', 'colaboradores', 'total'),
(0, 'financeiro', 'financeiro', 'total'),
(0, 'caixa', 'vendas', 'escrita'),
(0, 'caixa', 'caixa', 'total'),
(0, 'recebimento', 'financeiro', 'escrita'),
(0, 'recebimento', 'contas', 'escrita');

-- --------------------------------------------------------

--
-- Estrutura para tabela `sessoes_ativas`
--

CREATE TABLE `sessoes_ativas` (
  `id` varchar(128) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `data_inicio` datetime DEFAULT current_timestamp(),
  `data_ultima_atividade` datetime DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `sessoes_ativas`
--

INSERT INTO `sessoes_ativas` (`id`, `usuario_id`, `data_inicio`, `data_ultima_atividade`, `ip`, `user_agent`) VALUES
('071gpjfteksbl200c91qkkvfkm', 4, '2026-02-04 11:18:36', '2026-02-04 11:18:36', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('0ebop7ng8ifbd4i5vnfgbmrund', 4, '2025-12-30 07:59:36', '2025-12-30 07:59:36', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0'),
('3u5vnt27ajd5ijk427gq926vj3', 4, '2026-01-20 10:26:14', '2026-01-20 10:26:14', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('88clvocgearv01mmi15ran63g8', 4, '2025-12-30 08:17:31', '2025-12-30 08:17:31', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('8ru053irgdc0sec42mm7kg19a1', 4, '2025-12-30 09:40:17', '2025-12-30 09:40:17', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('9nr2qbppk7o80pn1nlkklk3o0p', 4, '2026-02-04 07:56:27', '2026-02-04 07:56:27', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('ah9q398vc0h7ndojq0f9l72m8u', 4, '2025-12-29 11:28:30', '2025-12-29 11:28:30', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('bccbe475v5icguj6p09pnd7iul', 4, '2026-01-16 09:37:50', '2026-01-16 09:37:50', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('bh6db9patmov5li626e46rek91', 4, '2026-01-03 11:12:57', '2026-01-03 11:12:57', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('cg5haf6trg2jk98i2fbue00ppo', 4, '2026-02-04 09:44:15', '2026-02-04 09:44:15', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('etpeidb633eo2uhvcbtj3d67jt', 4, '2025-12-26 07:37:56', '2025-12-26 07:37:56', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('evro2t0oi2nsqioss77o5ijtig', 4, '2026-02-02 10:06:59', '2026-02-02 10:06:59', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('f3nugsq2nlsems0n60l5trgrgb', 4, '2025-12-30 07:56:21', '2025-12-30 07:56:21', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('fmlre4usshqivslc7ea0filkvq', 4, '2026-02-03 08:49:33', '2026-02-03 08:49:33', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('fmvulo5an9j0kgovrmgciroo6d', 4, '2025-12-27 09:41:16', '2025-12-27 09:41:16', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('gduatkk9j3c51230gqb528bmgr', 4, '2025-12-27 08:33:35', '2025-12-27 08:33:35', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('hp6dsge8jsvv8n14lcep7i9036', 19, '2026-02-03 11:20:49', '2026-02-03 11:20:49', '192.168.0.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('ivt18e5seqakve6lsemn40ck24', 4, '2025-12-23 17:22:14', '2025-12-23 17:22:14', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('jolbjup14mnrjm1i3hpdboikk2', 4, '2026-01-23 11:27:20', '2026-01-23 11:27:20', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('kgr5apmdjhisvt9a288hjapc61', 4, '2026-01-09 10:53:07', '2026-01-09 10:53:07', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('mpd2fl81lo118fchf22k0oies1', 4, '2025-12-29 08:06:27', '2025-12-29 09:08:22', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('ptv3c25250uid92cgljla15rj6', 4, '2025-12-29 09:43:56', '2025-12-29 09:43:56', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:146.0) Gecko/20100101 Firefox/146.0'),
('q613fjv4qulqek6d00bihg61c7', 4, '2025-12-27 11:06:52', '2025-12-27 11:06:52', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36'),
('spcfquicbk61soq0occeggbh0k', 4, '2026-02-03 08:46:46', '2026-02-03 08:46:46', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36'),
('vu3j4p8eihoe93l565gtko82gi', 4, '2026-01-13 11:52:46', '2026-01-13 11:52:46', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `teste`
--

CREATE TABLE `teste` (
  `id` int(11) NOT NULL,
  `nome` varchar(50) DEFAULT NULL,
  `data` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `teste`
--

INSERT INTO `teste` (`id`, `nome`, `data`) VALUES
(1, 'Teste XAMPP', '2025-12-23 12:55:19'),
(2, 'Teste XAMPP', '2025-12-23 12:55:20'),
(3, 'Teste XAMPP', '2025-12-23 12:55:20'),
(4, 'Teste XAMPP', '2025-12-23 12:55:21'),
(5, 'Teste XAMPP', '2025-12-23 12:55:22'),
(6, 'Teste XAMPP', '2025-12-23 12:55:22'),
(7, 'Teste XAMPP', '2025-12-23 12:55:22'),
(8, 'Teste XAMPP', '2025-12-23 12:55:22'),
(9, 'Teste XAMPP', '2025-12-23 12:55:22'),
(10, 'Teste XAMPP', '2025-12-23 12:55:23'),
(11, 'Teste XAMPP', '2025-12-23 12:55:23'),
(12, 'Teste XAMPP', '2025-12-23 12:55:24'),
(13, 'Teste XAMPP', '2025-12-23 12:55:27'),
(14, 'Teste XAMPP', '2025-12-23 12:55:28'),
(15, 'Teste XAMPP', '2025-12-23 12:55:28'),
(16, 'Teste XAMPP', '2025-12-23 12:55:28'),
(17, 'Teste XAMPP', '2025-12-23 12:55:28'),
(18, 'Teste XAMPP', '2025-12-23 12:55:28'),
(19, 'Teste XAMPP', '2025-12-23 12:55:29'),
(20, 'Teste XAMPP', '2025-12-23 12:55:29'),
(21, 'Teste XAMPP', '2025-12-23 12:55:29'),
(22, 'Teste XAMPP', '2025-12-23 12:55:29'),
(23, 'Teste XAMPP', '2025-12-23 12:55:29'),
(24, 'Teste XAMPP', '2025-12-23 12:55:29'),
(25, 'Teste XAMPP', '2025-12-23 12:55:29'),
(26, 'Teste XAMPP', '2025-12-23 12:55:30'),
(27, 'Teste XAMPP', '2025-12-23 12:55:30'),
(28, 'Teste XAMPP', '2025-12-23 12:55:30'),
(29, 'Teste XAMPP', '2025-12-23 12:55:30'),
(30, 'Teste XAMPP', '2025-12-23 12:58:34'),
(31, 'Teste XAMPP', '2025-12-23 13:18:21'),
(32, 'Teste XAMPP', '2025-12-23 13:18:22');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nome` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `usuario` varchar(50) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `perfil` enum('admin','gerencia','vendedor','estoque','rh','caixa','financeiro','recebimento') NOT NULL DEFAULT 'vendedor',
  `status` enum('ativo','bloqueado') NOT NULL DEFAULT 'ativo',
  `ultimo_login` datetime DEFAULT NULL,
  `data_cadastro` timestamp NOT NULL DEFAULT current_timestamp(),
  `atualizado_em` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ativo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios`
--

INSERT INTO `usuarios` (`id`, `nome`, `email`, `foto`, `usuario`, `senha`, `foto_perfil`, `perfil`, `status`, `ultimo_login`, `data_cadastro`, `atualizado_em`, `ativo`) VALUES
(4, 'Suporte', 'suporte@tapemag.com', 'uploads/perfis/perfil_4_1767449552.jfif', 'Tecsup@9873', '$2y$10$HFD02E8XzmuIQfuLKihpheZi6k1mkCr6OI4Jao9pYBQUu6HmBM2BS', 'uploads/perfis/perfil_4_1767449552.jfif', 'admin', 'ativo', '2026-02-04 11:18:36', '2025-12-23 19:03:03', '2026-02-04 14:18:36', 1),
(12, 'Tiago Henrique', 'vendas5@tapemag.com.br', NULL, 'Tiago', '$2y$10$gV6rhuCDNmkybBJZ9C2H.OrNFy71LEyKMnqPiMYVJEfITGsybkfWO', NULL, 'vendedor', 'ativo', '2026-01-23 11:23:10', '2026-01-23 13:43:45', '2026-02-02 11:20:33', 1),
(14, 'Cesar Antunes', 'cesar.antunes@tapemag.com.br', NULL, 'Cesar', '$2y$10$jkxq/oKZ8A4jqGYcPg7rM.ltJmFlfGxiDXz5DXR0TdPYYCGB9YKUK', NULL, 'gerencia', 'ativo', '2026-01-23 11:26:29', '2026-01-23 14:22:52', '2026-01-23 14:26:29', 1),
(18, 'teste1', 'teste1@gmail.com', NULL, 'teste1', '$2y$10$d/Hy.sZ389itItwL0iVKBej8/9P7RNMNe8vUXvJfL/EwIYKxNoQa.', NULL, 'caixa', 'ativo', '2026-02-02 10:04:41', '2026-02-02 13:04:00', '2026-02-06 13:29:39', 1),
(19, 'Victor da Silva', 'Victor.araujo@tapemag.com.br', NULL, 'Victor Araujo', '$2y$10$ZUWK2ONCCgP/UXBxi2GPWeIpnRtqe7ln1QPrhWiAgFYmlb.Ypuw3q', NULL, 'admin', 'ativo', '2026-02-03 11:20:49', '2026-02-03 14:20:40', '2026-02-05 10:29:38', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_logs`
--

CREATE TABLE `usuarios_logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `tipo` enum('login','logout','troca_senha','atualizacao_perfil') NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `detalhes` text DEFAULT NULL,
  `criado_em` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `usuarios_logs`
--

INSERT INTO `usuarios_logs` (`id`, `usuario_id`, `tipo`, `ip_address`, `user_agent`, `detalhes`, `criado_em`) VALUES
(2, 4, 'atualizacao_perfil', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Foto de perfil atualizada', '2026-01-03 14:04:04'),
(3, 4, 'atualizacao_perfil', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Foto de perfil atualizada', '2026-01-03 14:12:17'),
(4, 4, 'atualizacao_perfil', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Foto de perfil atualizada', '2026-01-03 14:12:32'),
(5, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-03 14:12:45'),
(7, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-09 13:53:04'),
(8, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-13 13:07:20'),
(10, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-13 14:38:31'),
(12, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-13 14:39:25'),
(15, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-13 14:52:38'),
(17, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-15 12:39:59'),
(19, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-15 13:05:29'),
(20, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-16 12:37:35'),
(22, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-17 13:55:45'),
(24, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-17 14:05:32'),
(26, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-17 14:36:42'),
(27, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-17 14:41:41'),
(30, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-17 14:58:22'),
(31, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 11:44:49'),
(33, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 12:43:52'),
(35, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 12:44:14'),
(40, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 13:24:50'),
(41, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 13:27:45'),
(42, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 13:29:03'),
(45, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 13:32:35'),
(50, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 13:38:04'),
(57, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 14:02:21'),
(60, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 14:29:51'),
(62, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 14:49:37'),
(63, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-20 15:13:07'),
(66, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-22 11:11:25'),
(68, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 13:01:33'),
(80, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 13:33:43'),
(86, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 13:42:27'),
(89, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 13:43:56'),
(90, 12, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 14:21:29'),
(91, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 14:21:34'),
(92, 12, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 14:21:43'),
(93, 4, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 14:23:07'),
(94, 12, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 14:26:12'),
(95, 14, 'logout', '192.168.0.6', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-23 14:27:18'),
(96, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-29 16:33:00'),
(98, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-31 13:21:07'),
(100, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-01-31 14:46:41'),
(101, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-02 11:12:54'),
(102, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-02 11:21:02'),
(104, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-02 11:30:53'),
(105, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-02 12:56:39'),
(106, 18, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-02 13:04:17'),
(107, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-02 13:04:34'),
(108, 18, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-02 13:06:43'),
(109, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-04 12:42:46'),
(110, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-04 13:38:36'),
(111, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-04 14:18:40'),
(112, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 12:25:47'),
(113, 18, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 12:30:03'),
(114, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 13:05:56'),
(115, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 13:24:25'),
(117, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 13:25:51'),
(118, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 13:28:04'),
(119, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 13:29:47'),
(120, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-05 13:47:45'),
(121, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-06 12:57:00'),
(122, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-06 13:32:00'),
(123, 18, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-06 13:32:22'),
(124, 4, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-06 13:37:39'),
(125, 18, 'logout', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36', 'Logout realizado', '2026-02-06 13:38:10');

-- --------------------------------------------------------

--
-- Estrutura para tabela `usuarios_sessoes`
--

CREATE TABLE `usuarios_sessoes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `session_id` varchar(128) NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `user_agent` text DEFAULT NULL,
  `ultima_atividade` datetime NOT NULL,
  `criado_em` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `vendas`
--

CREATE TABLE `vendas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL DEFAULT 1,
  `cliente_id` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `data_venda` date NOT NULL,
  `status` enum('concluida','perdida','orcamento') NOT NULL DEFAULT 'concluida',
  `observacoes` text DEFAULT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `forma_pagamento` varchar(20) DEFAULT 'na',
  `motivo_perda` text DEFAULT NULL,
  `canal_venda` enum('loja','online','telefone','whatsapp') DEFAULT 'loja',
  `hora_venda` time DEFAULT NULL,
  `produto_principal` varchar(100) DEFAULT NULL,
  `codigo_orcamento` varchar(30) DEFAULT NULL,
  `motivo_perda_id` int(11) DEFAULT NULL,
  `motivo_perda_outro` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Acionadores `vendas`
--
DELIMITER $$
CREATE TRIGGER `trg_clientes_after_delete_vendas` AFTER DELETE ON `vendas` FOR EACH ROW BEGIN
    UPDATE clientes
    SET 
        ultima_venda = (
            SELECT MAX(data_venda)
            FROM vendas
            WHERE cliente_id = OLD.cliente_id AND status = 'concluida'
        ),
        total_gasto = (
            SELECT COALESCE(SUM(valor), 0)
            FROM vendas
            WHERE cliente_id = OLD.cliente_id AND status = 'concluida'
        ),
        media_gastos = (
            SELECT COALESCE(AVG(valor), 0)
            FROM vendas
            WHERE cliente_id = OLD.cliente_id AND status = 'concluida'
        ),
        taxa_fechamento = (
            SELECT
                CASE 
                    WHEN COUNT(*) = 0 THEN 0
                    ELSE (SUM(status = 'concluida') / COUNT(*)) * 100
                END
            FROM vendas
            WHERE cliente_id = OLD.cliente_id
        )
    WHERE id = OLD.cliente_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_clientes_after_insert_vendas` AFTER INSERT ON `vendas` FOR EACH ROW BEGIN
    UPDATE clientes
    SET 
        ultima_venda = (
            SELECT MAX(data_venda)
            FROM vendas
            WHERE cliente_id = NEW.cliente_id AND status = 'concluida'
        ),
        total_gasto = (
            SELECT COALESCE(SUM(valor), 0)
            FROM vendas
            WHERE cliente_id = NEW.cliente_id AND status = 'concluida'
        ),
        media_gastos = (
            SELECT COALESCE(AVG(valor), 0)
            FROM vendas
            WHERE cliente_id = NEW.cliente_id AND status = 'concluida'
        ),
        taxa_fechamento = (
            SELECT
                CASE 
                    WHEN COUNT(*) = 0 THEN 0
                    ELSE (SUM(status = 'concluida') / COUNT(*)) * 100
                END
            FROM vendas
            WHERE cliente_id = NEW.cliente_id
        )
    WHERE id = NEW.cliente_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_clientes_after_update_vendas` AFTER UPDATE ON `vendas` FOR EACH ROW BEGIN
    IF OLD.cliente_id != NEW.cliente_id THEN
        UPDATE clientes
        SET 
            ultima_venda = (
                SELECT MAX(data_venda)
                FROM vendas
                WHERE cliente_id = OLD.cliente_id AND status = 'concluida'
            ),
            total_gasto = (
                SELECT COALESCE(SUM(valor), 0)
                FROM vendas
                WHERE cliente_id = OLD.cliente_id AND status = 'concluida'
            ),
            media_gastos = (
                SELECT COALESCE(AVG(valor), 0)
                FROM vendas
                WHERE cliente_id = OLD.cliente_id AND status = 'concluida'
            ),
            taxa_fechamento = (
                SELECT
                    CASE 
                        WHEN COUNT(*) = 0 THEN 0
                        ELSE (SUM(status = 'concluida') / COUNT(*)) * 100
                    END
                FROM vendas
                WHERE cliente_id = OLD.cliente_id
            )
        WHERE id = OLD.cliente_id;
    END IF;

    UPDATE clientes
    SET 
        ultima_venda = (
            SELECT MAX(data_venda)
            FROM vendas
            WHERE cliente_id = NEW.cliente_id AND status = 'concluida'
        ),
        total_gasto = (
            SELECT COALESCE(SUM(valor), 0)
            FROM vendas
            WHERE cliente_id = NEW.cliente_id AND status = 'concluida'
        ),
        media_gastos = (
            SELECT COALESCE(AVG(valor), 0)
            FROM vendas
            WHERE cliente_id = NEW.cliente_id AND status = 'concluida'
        ),
        taxa_fechamento = (
            SELECT
                CASE 
                    WHEN COUNT(*) = 0 THEN 0
                    ELSE (SUM(status = 'concluida') / COUNT(*)) * 100
                END
            FROM vendas
            WHERE cliente_id = NEW.cliente_id
        )
    WHERE id = NEW.cliente_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_vendas_after_delete_comportamento` AFTER DELETE ON `vendas` FOR EACH ROW BEGIN
  CALL atualizar_comportamento_clientes(OLD.cliente_id);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_vendas_after_insert_comportamento` AFTER INSERT ON `vendas` FOR EACH ROW BEGIN
  IF NEW.cliente_id IS NOT NULL AND NEW.cliente_id > 0 THEN
    CALL atualizar_comportamento_clientes(NEW.cliente_id);
  END IF;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `trg_vendas_after_update_comportamento` AFTER UPDATE ON `vendas` FOR EACH ROW BEGIN
  -- Recalcula pro cliente atual
  IF NEW.cliente_id IS NOT NULL AND NEW.cliente_id > 0 THEN
    CALL atualizar_comportamento_clientes(NEW.cliente_id);
  END IF;

  -- Se mudou o cliente_id, recalcula também pro cliente antigo
  IF OLD.cliente_id <> NEW.cliente_id AND OLD.cliente_id IS NOT NULL AND OLD.cliente_id > 0 THEN
    CALL atualizar_comportamento_clientes(OLD.cliente_id);
  END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_analise_churn`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_analise_churn` (
`ano` int(4)
,`mes` int(2)
,`clientes_perdidos` bigint(21)
,`valor_perdido` decimal(32,2)
,`valor_medio_perdido` decimal(14,6)
,`lista_clientes` mediumtext
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_analise_diaria_metas`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_analise_diaria_metas` (
`usuario_id` int(11)
,`vendedor` varchar(100)
,`data_venda` date
,`vendas_dia` bigint(21)
,`faturamento_dia` decimal(32,2)
,`ticket_medio_dia` decimal(14,6)
,`meta_diaria` decimal(12,2)
,`percentual_meta_dia` decimal(41,6)
,`atendimentos_dia` bigint(21)
,`atendimentos_venda_dia` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_clientes_status`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_clientes_status` (
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_crescimento_carteira`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_crescimento_carteira` (
`ano` int(4)
,`mes` int(2)
,`novos_clientes` bigint(21)
,`clientes_que_compraram` decimal(22,0)
,`faturamento_novos_clientes` decimal(32,2)
,`clientes_mes_anterior` bigint(21)
,`crescimento_percentual` decimal(28,4)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_curva_abc_clientes`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_curva_abc_clientes` (
`categoria_abc` varchar(1)
,`quantidade_clientes` bigint(21)
,`total_faturamento` decimal(32,2)
,`media_por_cliente` decimal(14,6)
,`clientes` mediumtext
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_dependencia_vendedor`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_dependencia_vendedor` (
`cliente_id` int(11)
,`cliente` varchar(100)
,`total_vendedores` bigint(21)
,`vendedores_que_atendeu` mediumtext
,`nivel_dependencia` varchar(17)
,`ultima_compra` date
,`total_gasto` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_metricas_operacionais`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_metricas_operacionais` (
`data_venda` date
,`dia_semana` varchar(9)
,`hora` int(2)
,`total_vendas` bigint(21)
,`faturamento_total` decimal(32,2)
,`ticket_medio` decimal(14,6)
,`vendedores_ativos` bigint(21)
,`clientes_atendidos` bigint(21)
,`total_atendimentos` bigint(21)
,`atendimentos_com_venda` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_projecao_metas`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_projecao_metas` (
`usuario_id` int(11)
,`vendedor` varchar(100)
,`meta_mensal` decimal(12,2)
,`faturamento_atual` decimal(32,2)
,`percentual_atingido` decimal(41,6)
,`dias_restantes_mes` int(3)
,`media_diaria_atual` decimal(36,6)
,`necessidade_diaria_restante` decimal(37,6)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_ranking_vendedores_mes`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_ranking_vendedores_mes` (
`id` int(11)
,`nome` varchar(100)
,`perfil` enum('admin','gerencia','vendedor','estoque','rh','caixa','financeiro','recebimento')
,`vendas_mes` bigint(21)
,`faturamento_mes` decimal(32,2)
,`ticket_medio_mes` decimal(36,6)
,`clientes_atendidos_mes` bigint(21)
,`total_clientes_carteira` bigint(21)
,`ranking_faturamento` bigint(21)
,`ranking_vendas` bigint(21)
);

-- --------------------------------------------------------

--
-- Estrutura stand-in para view `vw_vendedor_retencao`
-- (Veja abaixo para a visão atual)
--
CREATE TABLE `vw_vendedor_retencao` (
`vendedor_id` int(11)
,`vendedor` varchar(100)
,`total_clientes_carteira` bigint(21)
,`clientes_retidos_90dias` bigint(21)
,`clientes_retidos_60dias` bigint(21)
,`clientes_retidos_30dias` bigint(21)
,`taxa_retencao_90dias` decimal(27,4)
,`faturamento_carteira` decimal(32,2)
,`ticket_medio_carteira` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Estrutura para view `vw_analise_churn`
--
DROP TABLE IF EXISTS `vw_analise_churn`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_analise_churn`  AS SELECT year(`c`.`ultima_venda`) AS `ano`, month(`c`.`ultima_venda`) AS `mes`, count(0) AS `clientes_perdidos`, sum(`c`.`total_gasto`) AS `valor_perdido`, avg(`c`.`total_gasto`) AS `valor_medio_perdido`, group_concat(`c`.`nome` separator ', ') AS `lista_clientes` FROM `clientes` AS `c` WHERE to_days(curdate()) - to_days(`c`.`ultima_venda`) > 90 AND `c`.`ultima_venda` is not null AND `c`.`total_gasto` > 0 GROUP BY year(`c`.`ultima_venda`), month(`c`.`ultima_venda`) ORDER BY year(`c`.`ultima_venda`) DESC, month(`c`.`ultima_venda`) DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_analise_diaria_metas`
--
DROP TABLE IF EXISTS `vw_analise_diaria_metas`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_analise_diaria_metas`  AS SELECT `u`.`id` AS `usuario_id`, `u`.`nome` AS `vendedor`, cast(`v`.`data_venda` as date) AS `data_venda`, count(`v`.`id`) AS `vendas_dia`, sum(`v`.`valor`) AS `faturamento_dia`, avg(`v`.`valor`) AS `ticket_medio_dia`, `m`.`valor_meta` AS `meta_diaria`, CASE WHEN `m`.`valor_meta` > 0 THEN sum(`v`.`valor`) / `m`.`valor_meta` * 100 ELSE 0 END AS `percentual_meta_dia`, (select count(0) from `atendimentos` `a` where `a`.`usuario_id` = `u`.`id` and cast(`a`.`data_hora_inicio` as date) = cast(`v`.`data_venda` as date)) AS `atendimentos_dia`, (select count(0) from `atendimentos` `a` where `a`.`usuario_id` = `u`.`id` and cast(`a`.`data_hora_inicio` as date) = cast(`v`.`data_venda` as date) and `a`.`resultado` = 'venda') AS `atendimentos_venda_dia` FROM ((`usuarios` `u` left join `vendas` `v` on(`u`.`id` = `v`.`usuario_id` and `v`.`status` = 'concluida' and `v`.`data_venda` >= curdate() - interval 30 day)) left join `metas_vendedores` `m` on(`u`.`id` = `m`.`usuario_id` and `m`.`periodo` = 'diaria' and `m`.`tipo_meta` = 'faturamento' and `m`.`ativo` = 1 and (`m`.`data_fim` is null or `m`.`data_fim` >= curdate()))) WHERE `u`.`perfil` in ('vendedor','gerencia') GROUP BY `u`.`id`, `u`.`nome`, cast(`v`.`data_venda` as date), `m`.`valor_meta` ORDER BY cast(`v`.`data_venda` as date) DESC, sum(`v`.`valor`) DESC ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_clientes_status`
--
DROP TABLE IF EXISTS `vw_clientes_status`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_clientes_status`  AS SELECT `c`.`id` AS `id`, `c`.`nome` AS `nome`, `c`.`email` AS `email`, `c`.`telefone` AS `telefone`, `c`.`ultima_venda` AS `ultima_venda`, `c`.`data_primeira_compra` AS `data_primeira_compra`, `c`.`total_gasto` AS `total_gasto`, `c`.`media_gastos` AS `media_gastos`, to_days(curdate()) - to_days(coalesce(`c`.`ultima_venda`,`c`.`data_cadastro`)) AS `dias_sem_compra`, `obter_status_cliente`(to_days(curdate()) - to_days(coalesce(`c`.`ultima_venda`,`c`.`data_cadastro`)),`c`.`total_gasto`) AS `status_cliente`, `cc`.`categoria_abc` AS `categoria_abc`, `cc`.`frequencia_media_dias` AS `frequencia_media_dias`, `u`.`nome` AS `vendedor_principal` FROM (((`clientes` `c` left join `comportamento_clientes` `cc` on(`c`.`id` = `cc`.`cliente_id`)) left join (select `vendas`.`cliente_id` AS `cliente_id`,`vendas`.`usuario_id` AS `usuario_id` from `vendas` where `vendas`.`status` = 'concluida' group by `vendas`.`cliente_id`,`vendas`.`usuario_id` order by count(0) desc limit 1) `vp` on(`c`.`id` = `vp`.`cliente_id`)) left join `usuarios` `u` on(`vp`.`usuario_id` = `u`.`id`)) ;

-- --------------------------------------------------------

--
-- Estrutura para view `vw_crescimento_carteira`
--
DROP TABLE IF EXISTS `vw_crescimento_carteira`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `vw_crescimento_carteira`  AS SELECT year(`clientes`.`data_cadastro`) AS `ano`, month(`clientes`.`data_cadastro`) AS `mes`, count(0) AS `novos_clientes`, sum(case when `clientes`.`total_gasto` > 0 then 1 else 0 end) AS `clientes_que_compraram`, sum(`clientes`.`total_gasto`) AS `faturamento_novos_clientes`, lag(count(0),1) over ( order by year(`clientes`.`data_cadastro`),month(`clientes`.`data_cadastro`)) AS `clientes_mes_anterior`, CASE END ORDER BY year(`clientes`.`data_cadastro`) ASC, month(`clientes`.`data_cadastro`) AS `ASCorder` ASCorder by year(`clientes`.`data_cadastro`),month(`clientes`.`data_cadastro`)) * 100 else 0 end AS `crescimento_percentual` from `clientes` group by year(`clientes`.`data_cadastro`),month(`clientes`.`data_cadastro`) order by year(`clientes`.`data_cadastro`) desc,month(`clientes`.`data_cadastro`) desc  ;
<div class="alert alert-danger" role="alert"><img src="themes/dot.gif" title="" alt="" class="icon ic_s_error"> RuntimeException: No statement inside WITH</div></body></html>