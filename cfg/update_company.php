<?php
// update_company.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $currentCompanyName = $_POST['current_company_name'];
    $newCompanyName = $_POST['new_company_name'];

    if (!preg_match('/[a-zA-Z]/', $newCompanyName)) {
        echo json_encode(['success' => false, 'message' => 'O nome da empresa deve conter pelo menos uma letra.']);
        exit;
    }

    $newCompanyName = preg_replace('/[^a-zA-Z0-9_]/', '_', $newCompanyName);

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
        
        //Atualiza tanmbém o user da empresa
        $updateUsersSQL = "UPDATE `users` SET `enterprise` = :newCompanyName WHERE `enterprise` = :currentCompanyName";
        $stmt = $pdo->prepare($updateUsersSQL);
        $stmt->execute(['newCompanyName' => $newCompanyName, 'currentCompanyName' => $currentCompanyName]);

        echo json_encode(['success' => true, 'message' => 'Nome da empresa e registros de usuários atualizados com sucesso!']);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar: ' . $e->getMessage()]);
    }
}