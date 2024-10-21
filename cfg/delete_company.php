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

        $deleteUsersQuery = "DELETE FROM users WHERE typeuser = 'user' AND enterprise = :company_name";
        $deleteUsersStmt = $pdo->prepare($deleteUsersQuery);
        $deleteUsersStmt->bindParam(':company_name', $companyName);
        $deleteUsersSuccess = $deleteUsersStmt->execute();

        $dropTableQuery = "DROP TABLE IF EXISTS `vpns`.`$companyName`";
        $dropTableStmt = $pdo->prepare($dropTableQuery);
        $dropTableSuccess = $dropTableStmt->execute();

        if ($deleteUsersSuccess && $dropTableSuccess) {
            echo json_encode(['success' => true, 'message' => 'Empresa e usuários vinculados excluídos com sucesso.']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Erro ao excluir a empresa ou os usuários vinculados.']);
        }

    } else {
        echo json_encode(['success' => false, 'message' => 'Nome da empresa inválido.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
}
?>
