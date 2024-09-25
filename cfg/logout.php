<?php
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
} catch (PDOException $e) {
    die("Erro ao conectar ao banco de dados: " . $e->getMessage());
}

if (isset($_SESSION['id'])) {
    $userId = $_SESSION['id'];

    try {
        $updateStmt = $pdo->prepare("UPDATE users SET status1 = 'offline' WHERE id = :id");
        $updateStmt->bindParam(':id', $userId);
        $updateStmt->execute();
    } catch (PDOException $e) {
        echo 'Erro ao marcar como offline: ' . $e->getMessage();
    }

    session_destroy();
    header('Location: /login'); // Redireciona para a página de login ou página inicial
    exit();
} else {
    echo 'Usuário não está autenticado.';
}

$url = $_SERVER['REQUEST_URI'];

if ($url == '/login') {
    include 'login.php';
    exit();
}

exit();
?>
