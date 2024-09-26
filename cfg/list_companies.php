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

    $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'vpns' AND table_name <> 'users'");
    $companies = [];

    while ($row = $stmt->fetch(PDO::FETCH_NUM)) {
        $companies[] = $row[0];
    }

    echo json_encode($companies);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao listar empresas: ' . $e->getMessage()]);
}