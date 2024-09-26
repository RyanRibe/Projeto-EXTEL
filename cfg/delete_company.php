<?php
header('Content-Type: application/json');

session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'vpns';
$dbUser = 'root';
$dbPass = 'admin';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    $companyName = $_POST['company_name'];


    if (preg_match('/^[a-zA-Z0-9_]+$/', $companyName)) {

        $query = "DROP TABLE `vpns`.`$companyName`";
        
        $stmt = $pdo->prepare($query);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true, 'message' => 'Empresa excluída com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir a empresa.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Nome da empresa inválido.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
}
?>
