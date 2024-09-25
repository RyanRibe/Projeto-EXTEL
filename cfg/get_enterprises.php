<?php
session_start();

// Verificar se o usuário é admin
if (isset($_SESSION['typeuser']) && $_SESSION['typeuser'] === 'admin') {
    // Conexão com o banco de dados
    $dbHost = 'localhost';
    $dbPort = '3306';
    $dbName = 'vpns';
    $dbUser = 'root';
    $dbPass = 'admin';

    try {
        $pdo = new PDO("mysql:host=$dbHost;port=$dbPort;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Consulta para obter todas as tabelas (exceto 'users' e 'mediacoes')
        $stmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'vpns' AND table_name <> 'users' AND table_name <> 'mediacoes'");
        $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Retorna as tabelas em formato JSON
        echo json_encode($tables);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Erro ao buscar empresas: ' . $e->getMessage()]);
    }
} else {
    // Se o usuário não for admin, retorna um erro
    echo json_encode(['error' => 'Usuário não autorizado']);
}
?>
