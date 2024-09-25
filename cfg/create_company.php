<?php
// Conexão com o banco de dados
$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'vpns';
$dbUser = 'root';
$dbPass = 'admin';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Recebe o nome da empresa e substitui espaços por "_"
        $companyName = str_replace(' ', '_', $_POST['company_name']);
        
        // Cria a tabela com o nome da empresa
        $sql = "CREATE TABLE `$companyName` (
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci";

        $pdo->exec($sql);
        echo json_encode(['success' => true, 'message' => 'Empresa criada com sucesso!']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao criar a empresa: ' . $e->getMessage()]);
}
