// check_status.php
<?php
session_start();
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

    $statusStmt = $pdo->prepare("SELECT status1 FROM users WHERE id = :id");
    $statusStmt->bindParam(':id', $userId);
    $statusStmt->execute();
    $status = $statusStmt->fetch(PDO::FETCH_ASSOC);

    echo json_encode(['status1' => $status['status1']]);
}
?>
