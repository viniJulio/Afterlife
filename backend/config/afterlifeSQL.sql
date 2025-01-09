CREATE DATABASE  IF NOT EXISTS `afterlife` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `afterlife`;
-- MySQL dump 10.13  Distrib 8.0.38, for Win64 (x86_64)
--
-- Host: localhost    Database: afterlife
-- ------------------------------------------------------
-- Server version	8.0.40

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `admin`
--

DROP TABLE IF EXISTS `admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admin` (
  `idAdmin` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `nomeSocial` varchar(120) DEFAULT NULL,
  `senha` varchar(255) NOT NULL,
  `cpf` varchar(11) NOT NULL,
  `email` varchar(80) NOT NULL,
  `telefone` varchar(11) NOT NULL,
  `dataCriacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dataAtualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `cadastroAceito` enum('não','sim') NOT NULL DEFAULT 'não',
  `tokenCadastro` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`idAdmin`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `arquivo`
--

DROP TABLE IF EXISTS `arquivo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `arquivo` (
  `idArquivos` int NOT NULL AUTO_INCREMENT,
  `caminho` longtext,
  `titulo` varchar(80) NOT NULL,
  `descricao` longtext NOT NULL,
  `texto` longtext,
  `senha` varchar(255) DEFAULT NULL,
  `dataCriacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dataAtualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fkIdPasta` int NOT NULL,
  PRIMARY KEY (`idArquivos`),
  KEY `fk_arquivo_pasta1_idx` (`fkIdPasta`),
  CONSTRAINT `fk_arquivo_pasta1` FOREIGN KEY (`fkIdPasta`) REFERENCES `pasta` (`idPasta`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `dependente`
--

DROP TABLE IF EXISTS `dependente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `dependente` (
  `idDependente` int NOT NULL AUTO_INCREMENT,
  `parentesco` varchar(30) NOT NULL,
  `tokenAcesso` longtext,
  `fkIdPessoa` int NOT NULL,
  PRIMARY KEY (`idDependente`),
  KEY `fk_dependente_pessoa1_idx` (`fkIdPessoa`),
  CONSTRAINT `fk_dependente_pessoa1` FOREIGN KEY (`fkIdPessoa`) REFERENCES `pessoa` (`idPessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `empresa`
--

DROP TABLE IF EXISTS `empresa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa` (
  `idEmpresa` int NOT NULL AUTO_INCREMENT,
  `nomeResponsavel` varchar(120) NOT NULL,
  `emailResponsavel` varchar(80) NOT NULL,
  `telefoneResponsavel` varchar(11) NOT NULL,
  `razaoSocial` varchar(120) NOT NULL,
  `cnpj` varchar(14) NOT NULL,
  `inscEstadual` varchar(15) DEFAULT NULL,
  `inscMunicipal` varchar(15) DEFAULT NULL,
  `cep` varchar(8) NOT NULL,
  `endereco` varchar(60) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(80) DEFAULT NULL,
  `bairro` varchar(60) NOT NULL,
  `cidade` varchar(60) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `telefone1` varchar(11) NOT NULL,
  `telefone2` varchar(11) DEFAULT NULL,
  `email` varchar(80) NOT NULL,
  `caminhoLogo` longtext,
  `caminhoLogoPequena` longtext NOT NULL, -- Adicionado
  `nomeFantasia` varchar(120) DEFAULT NULL, -- Adicionado
  `redesSociais` longtext DEFAULT NULL, -- Adicionado
  `dataCriacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dataAtualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `tipoEmpresa` enum('matriz','filial') NOT NULL,
  PRIMARY KEY (`idEmpresa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `logErro`
--

DROP TABLE IF EXISTS `logErro`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `logErro` (
  `idLogErro` int NOT NULL AUTO_INCREMENT,
  `descricao` longtext NOT NULL,
  `codigoErro` varchar(255) NOT NULL,
  `dataCriacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idLogErro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `mensagem`
--

DROP TABLE IF EXISTS `mensagem`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mensagem` (
  `idMensagem` int NOT NULL AUTO_INCREMENT,
  `destinatario` varchar(80) DEFAULT NULL,
  `descricao` longtext NOT NULL,
  `dataEnvio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMensagem`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pasta`
--

DROP TABLE IF EXISTS `pasta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pasta` (
  `idPasta` int NOT NULL AUTO_INCREMENT,
  `titulo` varchar(255) NOT NULL,
  `tipo` enum('imagem','documento','senha','audio','texto','video') NOT NULL,
  `descricao` longtext,
  `dataCriacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dataAtualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `fkIdTitular` int NOT NULL,
  PRIMARY KEY (`idPasta`),
  KEY `fk_pasta_titular1_idx` (`fkIdTitular`),
  CONSTRAINT `fk_pasta_titular1` FOREIGN KEY (`fkIdTitular`) REFERENCES `titular` (`idTitular`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pessoa`
--

DROP TABLE IF EXISTS `pessoa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pessoa` (
  `idPessoa` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) NOT NULL,
  `nomeSocial` varchar(120) DEFAULT NULL,
  `cpf` varchar(11) NOT NULL,
  `email` varchar(80) NOT NULL,
  `senha` varchar(255) DEFAULT NULL,
  `dataNascimento` varchar(10) NOT NULL,
  `cep` varchar(8) NOT NULL,
  `endereco` varchar(60) NOT NULL,
  `numero` varchar(10) NOT NULL,
  `complemento` varchar(80) DEFAULT NULL,
  `bairro` varchar(60) NOT NULL,
  `cidade` varchar(60) NOT NULL,
  `estado` varchar(2) NOT NULL,
  `telefone1` varchar(11) NOT NULL,
  `telefone2` varchar(11) DEFAULT NULL,
  `tokenSenha` int DEFAULT NULL,
  `tokenSenhaValidade` datetime DEFAULT NULL,
  `dataCriacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dataAtualizacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `numeroContrato` varchar(12) DEFAULT NULL,
  PRIMARY KEY (`idPessoa`),
  UNIQUE KEY `cpf` (`cpf`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `email_UNIQUE` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `titular`
--

DROP TABLE IF EXISTS `titular`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titular` (
  `idTitular` int NOT NULL AUTO_INCREMENT,
  `status` enum('ativo','inativo') NOT NULL DEFAULT 'ativo',
  `fkIdPessoa` int NOT NULL,
  `acesso` enum('bloqueado','desbloqueado') NOT NULL DEFAULT 'desbloqueado',
  PRIMARY KEY (`idTitular`),
  KEY `fk_titular_pessoa1_idx` (`fkIdPessoa`),
  CONSTRAINT `fk_titular_pessoa1` FOREIGN KEY (`fkIdPessoa`) REFERENCES `pessoa` (`idPessoa`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `titularDependente`
--

DROP TABLE IF EXISTS `titularDependente`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `titularDependente` (
  `idTitularDependente` int NOT NULL AUTO_INCREMENT,
  `fkIdTitular` int NOT NULL,
  `fkIdDependente` int NOT NULL,
  PRIMARY KEY (`idTitularDependente`),
  KEY `fkIdDependente` (`fkIdDependente`) /*!80000 INVISIBLE */,
  KEY `fkIdTitular` (`fkIdTitular`),
  CONSTRAINT `fkIdDependente` FOREIGN KEY (`fkIdDependente`) REFERENCES `dependente` (`idDependente`),
  CONSTRAINT `fkIdTitular` FOREIGN KEY (`fkIdTitular`) REFERENCES `titular` (`idTitular`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping events for database 'afterlife'
--

--
-- Dumping routines for database 'afterlife'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-11-12 16:46:54
