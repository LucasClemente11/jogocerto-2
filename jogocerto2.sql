-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 30/10/2024 às 15:26
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
-- Banco de dados: `jogocerto2`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `championships`
--

CREATE TABLE `championships` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `capacity` int(11) NOT NULL,
  `type` enum('matamata','pontos_corridos') NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT 'default_championship.jpg',
  `team_count` int(11) DEFAULT 0,
  `status` varchar(20) DEFAULT 'não iniciado',
  `method` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Despejando dados para a tabela `championships`
--

INSERT INTO `championships` (`id`, `name`, `capacity`, `type`, `created_by`, `image`, `team_count`, `status`, `method`) VALUES
(1, 'Versta Style', 8, 'matamata', 1, 'default_championship.jpg', 8, 'não iniciado', ''),
(2, 'Otarios da ETEC', 12, 'pontos_corridos', 1, 'default_championship.jpg', 12, 'não iniciado', ''),
(3, 'Brasileirão do Capeta', 8, 'matamata', 1, 'default_championship.jpg', 0, 'não iniciado', ''),
(4, '1', 8, '', 1, 'default_championship.jpg', 8, 'iniciado', 'mata-mata');

-- --------------------------------------------------------

--
-- Estrutura para tabela `matches`
--

CREATE TABLE `matches` (
  `id` int(11) NOT NULL,
  `championship_id` int(11) DEFAULT NULL,
  `team1_id` int(11) DEFAULT NULL,
  `team2_id` int(11) DEFAULT NULL,
  `score_team1` int(11) DEFAULT NULL,
  `score_team2` int(11) DEFAULT NULL,
  `round` int(11) DEFAULT NULL,
  `status` enum('pending','finished') DEFAULT 'pending',
  `score1` int(11) DEFAULT NULL,
  `score2` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Despejando dados para a tabela `matches`
--

INSERT INTO `matches` (`id`, `championship_id`, `team1_id`, `team2_id`, `score_team1`, `score_team2`, `round`, `status`, `score1`, `score2`) VALUES
(1, 4, 21, 22, NULL, NULL, 1, 'pending', 16, 8),
(2, 4, 23, 24, NULL, NULL, 1, 'pending', 7, 11),
(3, 4, 25, 26, NULL, NULL, 1, 'pending', 20, 2),
(4, 4, 27, 28, NULL, NULL, 1, 'pending', 3, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `players`
--

CREATE TABLE `players` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `number` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Despejando dados para a tabela `players`
--

INSERT INTO `players` (`id`, `name`, `number`, `team_id`) VALUES
(1, 'Phtrem', 7, 1),
(2, 'Luan', 11, 1),
(3, 'Kauan Farinha', 9, 1),
(4, 'Dudu', 99, 1),
(5, 'Chico', 69, 1),
(6, 'Bernardo', 1, 1),
(7, 'asd', 1, 2),
(8, 'af', 2, 2),
(9, 'fas', 3, 2),
(10, 'fasf', 4, 2),
(11, 'fasf', 5, 2),
(12, 'asd', 1, 2),
(13, 'af', 2, 2),
(14, 'fas', 3, 2),
(15, 'fasf', 4, 2),
(16, 'fasf', 5, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `championship_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Despejando dados para a tabela `teams`
--

INSERT INTO `teams` (`id`, `name`, `championship_id`) VALUES
(1, 'Bola 8 perneta', 1),
(2, 'a', 1),
(3, 'b', 1),
(4, 'c', 1),
(5, 'd', 1),
(6, 'e', 1),
(7, 'f', 1),
(8, 'g', 1),
(9, 'a', 2),
(10, 'b', 2),
(11, 'c', 2),
(12, 'd', 2),
(13, 'e', 2),
(14, 'f', 2),
(15, 'g', 2),
(16, 'h', 2),
(17, 'i', 2),
(18, 'j', 2),
(19, 'k', 2),
(20, 'l', 2),
(21, 'Bola 8 perneta', 4),
(22, 'a', 4),
(23, 'b', 4),
(24, 'c', 4),
(25, 'd', 4),
(26, 'e', 4),
(27, 'f', 4),
(28, 'g', 4);

-- --------------------------------------------------------

--
-- Estrutura para tabela `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `profile_picture` varchar(255) DEFAULT 'default_profile.jpg'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Despejando dados para a tabela `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `profile_picture`) VALUES
(1, 'admin', '$2y$10$SlLST0kUb5meCxrs0YtdC.ZlQ8BophYwNtIdpyY4hFwd/mlLdEvEW', 'lucasgclemente11@gmail.com', 'default_profile.jpg');

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `championships`
--
ALTER TABLE `championships`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Índices de tabela `matches`
--
ALTER TABLE `matches`
  ADD PRIMARY KEY (`id`),
  ADD KEY `championship_id` (`championship_id`),
  ADD KEY `team1_id` (`team1_id`),
  ADD KEY `team2_id` (`team2_id`);

--
-- Índices de tabela `players`
--
ALTER TABLE `players`
  ADD PRIMARY KEY (`id`),
  ADD KEY `team_id` (`team_id`);

--
-- Índices de tabela `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD KEY `championship_id` (`championship_id`);

--
-- Índices de tabela `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `championships`
--
ALTER TABLE `championships`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `matches`
--
ALTER TABLE `matches`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de tabela `players`
--
ALTER TABLE `players`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de tabela `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de tabela `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `championships`
--
ALTER TABLE `championships`
  ADD CONSTRAINT `championships_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Restrições para tabelas `matches`
--
ALTER TABLE `matches`
  ADD CONSTRAINT `matches_ibfk_1` FOREIGN KEY (`championship_id`) REFERENCES `championships` (`id`),
  ADD CONSTRAINT `matches_ibfk_2` FOREIGN KEY (`team1_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `matches_ibfk_3` FOREIGN KEY (`team2_id`) REFERENCES `teams` (`id`);

--
-- Restrições para tabelas `players`
--
ALTER TABLE `players`
  ADD CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`);

--
-- Restrições para tabelas `teams`
--
ALTER TABLE `teams`
  ADD CONSTRAINT `teams_ibfk_1` FOREIGN KEY (`championship_id`) REFERENCES `championships` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
