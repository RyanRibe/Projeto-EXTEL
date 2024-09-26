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


    $originalUsername = $_POST['original_username'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $typeuser = $_POST['typeuser'];
    $enterprise = $_POST['enterprise'];
    $resetpassword = $_POST['resetpassword'];


    if (!empty($password)) {
        $query = "UPDATE users SET username = :username, password = :password, typeuser = :typeuser, enterprise = :enterprise, resetpassword = :resetpassword WHERE username = :original_username";
        $stmt = $pdo->prepare($query);
        $stmt->bindParam(':password', $password);
    } else {
        $query = "UPDATE users SET username = :username, typeuser = :typeuser, enterprise = :enterprise, resetpassword = :resetpassword WHERE username = :original_username";
        $stmt = $pdo->prepare($query);
    }

    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':typeuser', $typeuser);
    $stmt->bindParam(':enterprise', $enterprise);
    $stmt->bindParam(':resetpassword', $resetpassword);
    $stmt->bindParam(':original_username', $originalUsername);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o usuário.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao conectar ao banco de dados: ' . $e->getMessage()]);
}
?>
