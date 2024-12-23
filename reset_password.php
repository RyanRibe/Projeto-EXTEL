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

if (!isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['current_password'], $_POST['new_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $userId = $_SESSION['id'];

    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = :id");
        $stmt->bindParam(':id', $userId);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($currentPassword === $user['password']) {
                $updateStmt = $pdo->prepare("UPDATE users SET password = :newPassword, resetpassword = 0 WHERE id = :id");
                $updateStmt->bindParam(':newPassword', $newPassword);
                $updateStmt->bindParam(':id', $userId);
                $updateStmt->execute();

                $success = "Senha atualizada com sucesso. Redirecionando em 3 segundos...";
                header('refresh:1;url=/vpn');
                exit();
            } else {
                $error = "A senha atual está incorreta.";
            }
        } else {
            $error = "Usuário não encontrado.";
        }
    } catch (PDOException $e) {
        $error = 'Erro ao atualizar a senha: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css">
    <link rel="icon" type="image/x-icon" href="./assets/favico.png">
    <title>Redefinir Senha</title>
    <a type="button" id="back-button" href="javascript:history.back()" style='
    SSdisplay: inline-block;
    /*margin: 0px 0px 20px;*/
    padding: 10px;
    text-decoration: none;
    font-size: 16px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    background-color: #b44d27;
    color: #fff;
    transition: background-color 0.3s ease;'>Voltar ↩︎</a>
</head>
<body>
    <h2 class="modal">Redefinir Senha</h2>

    <form method="POST" class="modal-content">
        
        <?php if ($error) { echo "<p style='color:red; text-align:center;'>$error</p>"; } ?>
        <?php if ($success) { echo "<p style='color:green; text-align:center;'>$success</p>"; } ?>

        <label style='display: flex;' for="current_password">Senha Atual:</label>
        <div id="icon1" onclick="showHide()"></div>
        <input style='margin-bottom: 30px;' autocomplete="off" maxlength="25" type="password" id="current_password" name="current_password" required>
        
        <label style='display: flex;' for="new_password">Nova Senha:</label>
        <div id="icon" onclick="showHide()"></div>
        <input style='margin-right: 120px;' autocomplete="off" maxlength="25" type="password" id="new_password" name="new_password" required>

        <button type="submit">Atualizar Senha</button>
    </form>

    <script src="icoeye-reset.js"></script>
</body>
</html>