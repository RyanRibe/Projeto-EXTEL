<?php
// update_company.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentCompanyName = $_POST['current_company_name'];
    $newCompanyName = $_POST['new_company_name'];

    // Conexão com o banco de dados
    $dbHost = 'localhost';
    $dbPort = '3306';
    $dbName = 'vpns';
    $dbUser = 'root';
    $dbPass = 'admin';

    try {
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Atualiza o nome da tabela da empresa
        $renameTableSQL = "RENAME TABLE `$currentCompanyName` TO `$newCompanyName`";
        $pdo->exec($renameTableSQL);

        echo json_encode(['success' => true, 'message' => 'Nome da empresa atualizado com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o nome da empresa: ' . $e->getMessage()]);
    }
}
