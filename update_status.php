// update_status.php
<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require './vendor/autoload.php'; // Autoload do Composer para o PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conexão com o db
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

    $updateStmt = $pdo->prepare("UPDATE users SET last_activity = NOW(), status1 = 'online' WHERE id = :id");
    $updateStmt->bindParam(':id', $userId);
    $updateStmt->execute();
}
?>
