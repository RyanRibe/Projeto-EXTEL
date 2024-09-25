<?php
header('Content-Type: application/json');
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection using PDO
$dbHost = 'localhost';
$dbPort = '3306';
$dbName = 'vpns';
$dbUser = 'root';
$dbPass = 'admin';

try {
    $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Retrieve the user data from the POST request
    $username = $_POST['username'];
    $password = $_POST['password'];
    $typeuser = $_POST['typeuser'];
    $enterprise = $_POST['enterprise'];
    $resetpassword = $_POST['resetpassword'];

    // Insert the new user
    $query = "INSERT INTO users (username, password, typeuser, enterprise, resetpassword) VALUES (:username, :password, :typeuser, :enterprise, :resetpassword)";
    $stmt = $pdo->prepare($query);

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':typeuser', $typeuser);
    $stmt->bindParam(':enterprise', $enterprise);
    $stmt->bindParam(':resetpassword', $resetpassword);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuário criado com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao criar o usuário.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
}
?>
