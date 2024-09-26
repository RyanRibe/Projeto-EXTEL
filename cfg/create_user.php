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

    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $typeuser = $_POST['typeuser'];
    $enterprise = $_POST['enterprise'];
    $resetpassword = $_POST['resetpassword'];

    if (empty($username) || empty($password) || empty($typeuser) || empty($enterprise)) {
        echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
        exit;
    }

    $checkQuery = "SELECT COUNT(*) FROM users WHERE username = :username";
    $checkStmt = $pdo->prepare($checkQuery);
    $checkStmt->bindParam(':username', $username, PDO::PARAM_STR);
    $checkStmt->execute();
    $count = $checkStmt->fetchColumn();

    if ($count > 0) {
        echo json_encode(['success' => false, 'message' => 'O nome de usuário já existe. Escolha outro.']);
        exit;
    }

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $query = "INSERT INTO users (username, password, typeuser, enterprise, resetpassword) VALUES (:username, :password, :typeuser, :enterprise, :resetpassword)";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->bindParam(':password', $password, PDO::PARAM_STR);
    $stmt->bindParam(':typeuser', $typeuser, PDO::PARAM_STR);
    $stmt->bindParam(':enterprise', $enterprise, PDO::PARAM_STR);
    $stmt->bindParam(':resetpassword', $resetpassword, PDO::PARAM_INT);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar o usuário.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
}
?>
