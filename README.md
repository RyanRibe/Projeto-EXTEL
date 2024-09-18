// READ-ME //

::: STEPS FOR CONFIGURATION :::

1- Extraia pasta onde deseja que fique armazenado o programa.

2- No ambiente de seu servidor, aponte o login.php como página principal de seu projeto no software server http que for utilizar.

3- Crie o banco de dados MySQL, a crie uma tabela de empresa ou quantas quiser, a seguir o script a ser utilizado:

CREATE TABLE `nome_da_empresa` (
  `id` int NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `user_name` varchar(100) DEFAULT NULL,
  `group` varchar(255) NOT NULL,
  `GroupObservation` text NOT NULL,
  `lastdowndate` varchar(250) DEFAULT NULL,
  `ExpirationDate` varchar(60) DEFAULT NULL,
  `downobs` text,
  `deactivateddate` varchar(250) DEFAULT NULL,
  `firstdowndate` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=193 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

Tabela de Usuários:

CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(12) NOT NULL,
  `password` varchar(8) NOT NULL,
  `typeuser` varchar(45) NOT NULL DEFAULT 'user',
  `enterprise` varchar(45) NOT NULL,
  `lastlogindate` datetime DEFAULT NULL,
  `lastip` varchar(45) DEFAULT NULL,
  `useragent` varchar(255) DEFAULT NULL,
  `last_activity` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `status1` enum('online','offline') DEFAULT 'offline',
  UNIQUE KEY `username_UNIQUE` (`username`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci

(Ao criar um usuário pelo banco se atente em colocar seu tipo, como "admin" ou "user", sendo que o admin pode navegar pelas "enterprise" e o do tipo user apenas na enterprise pré-setada em seu usuário. Pré-set uma enterprise para TODOS os usuários ou gerará erro no programa.)

4- Instale a biblioteca PHPMailer para usar todas as funções do programa (Arquivos necessários para instalação no Windows se encontra na pasta "Send Mail" do projeto, caso necessário pesquise como pode ser instalado de acordo com o seu cenário escolhido).

5- No arquivo "server.php", logo no cabeçalho configure a conexão com o banco de dados MySQL. 

E na função handleDeactivateVPN do mesmo arquivo, configure de acordo com o email que será usado para enviar a mensagem e se quiser altere o destinatário e mensagem
(Atenção para conta do gmail, é necessário ativar a autenticação em duas etapas e utilizar a chamada "senha de app", a senha comum não funcionará.)


6- Inicialize o servidor, teste a conexão, faça login e experimente todas as funções.

!! Lembrando que o programa não está completo, faltando configurar itens de alta importância para o funcionamento correto da página.!!

// Qualquer dúvidas a disposição....


:::: Ryan Ribeiro / 6298400-3988 ::::