<?php
ob_start();
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require './vendor/autoload.php'; // Autoload do Composer para o PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Conexão com o banco de dados
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

// Função para analisar o User-Agent
function parseUserAgent($userAgent) {
    $browser = 'Desconhecido';
    $device = 'Desconhecido';

    // Verificar dispositivo
    if (strpos($userAgent, 'iPhone') !== false) {
        $device = 'iPhone';
    } elseif (strpos($userAgent, 'iPad') !== false) {
        $device = 'iPad';
    } elseif (strpos($userAgent, 'Android') !== false) {
        $device = 'Android';
    } elseif (strpos($userAgent, 'Windows') !== false) {
        $device = 'Windows';
    } elseif (strpos($userAgent, 'Macintosh') !== false) {
        $device = 'Macintosh';
    }

    // Verificar navegador
    if (strpos($userAgent, 'Edg/') !== false) {
        $browser = 'Edge';
    } elseif (strpos($userAgent, 'Chrome/') !== false) {
        $browser = 'Chrome';
    } elseif (strpos($userAgent, 'Firefox/') !== false) {
        $browser = 'Firefox';
    } elseif (strpos($userAgent, 'Safari/') !== false) {
        $browser = 'Safari';
    } elseif (strpos($userAgent, 'Opera/') !== false || strpos($userAgent, 'OPR/') !== false) {
        $browser = 'Opera';
    } elseif (strpos($userAgent, 'MSIE') !== false || strpos($userAgent, 'Trident/') !== false) {
        $browser = 'Internet Explorer';
    }

    return $device . ' - ' . $browser;
}

function updateTablesList($pdo) {
    $tablesStmt = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema = 'vpns' AND table_name <> 'users' AND table_name <> 'mediacoes'");
    $tables = $tablesStmt->fetchAll(PDO::FETCH_COLUMN);
    $_SESSION['tables'] = $tables; 
}

if (isset($_GET['action']) && $_GET['action'] === 'backToVpn') {
    // Executar a função para atualizar a lista de tabelas
    updateTablesList($pdo);

    // Redirecionar para a página VPN após atualizar a lista de tabelas
    header('Location: /vpn');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $pdo->prepare("SELECT id, typeuser, enterprise, resetpassword, password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $username);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && $password === $user['password']) {
            // Definir variáveis de sessão
            $_SESSION['id'] = $user['id']; 
            $_SESSION['typeuser'] = $user['typeuser'];
            $_SESSION['enterprise'] = $user['enterprise'];
            $_SESSION['username'] = $username;

                  // Checa resetpassword é igual a 1
        if ($user['resetpassword'] == 1) {
                               
            $_SESSION['id'] = $user['id'];
            $_SESSION['username'] = $username;
            echo "Redirecionando para a redefinição de senha..."; 
            header('Location: /reset_password.php');
            exit();
            }

            // Obter IP e User-Agent do usuário
            $userIp = $_SERVER['REMOTE_ADDR'];
            $userAgent = $_SERVER['HTTP_USER_AGENT']; 
            $parsedUserAgent = parseUserAgent($userAgent);

            // Atualizar lastlogindate, lastip, useragent e status
            $updateStmt = $pdo->prepare("UPDATE users SET lastlogindate = NOW(), lastip = :lastip, useragent = :useragent, status1 = 'online' WHERE id = :id");
            $updateStmt->bindParam(':lastip', $userIp);
            $updateStmt->bindParam(':useragent', $parsedUserAgent);
            $updateStmt->bindParam(':id', $user['id']);
            $updateStmt->execute();

            if ($user['typeuser'] === 'admin') {
                updateTablesList($pdo);
            }
            

            header('Location: /vpn');
            exit();
        } else {
            $_SESSION['login_error'] = 'Usuário ou senha inválidos.';
            header('Location: login.php'); 
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = 'Erro ao verificar login: ' . $e->getMessage();
        header('Location: login.php'); 
        exit();
    }
}

$url = $_SERVER['REQUEST_URI'];

if ($url == '/vpn') {
    include 'vpn.php';
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : '';
$enterprise = isset($_SESSION['enterprise']) ? $_SESSION['enterprise'] : null;
$typeuser = isset($_SESSION['typeuser']) ? $_SESSION['typeuser'] : null;

if ($typeuser === 'admin' && isset($_POST['enterprise'])) {
    
    $enterprise = sanitizeTableName($_POST['enterprise']);
    $_SESSION['enterprise'] = $enterprise;
}

if ($_GET['action'] === 'getUserLogs') {
    try {
        // Consulta para buscar os usuários e calcular o tempo online para os offline
        $stmt = $pdo->query("
            SELECT 
     username, 
    useragent, 
    lastlogindate, 
    lastip, 
    status1,
    CASE 
        WHEN status1 = 'offline' THEN TIMEDIFF(last_activity, lastlogindate)
        ELSE NULL 
    END AS time_online
FROM users
ORDER BY 
    CASE 
        WHEN status1 = 'online' THEN 0 
        ELSE 1 
    END,
    lastlogindate DESC;

        ");

        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Retorne os dados como JSON
        echo json_encode(['success' => true, 'users' => $users]);

        // Pare a execução após a resposta JSON
        exit();

    } catch (PDOException $e) {
        // Capture o erro e retorne-o como JSON
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit(); // Certifique-se de parar a execução após o erro também
    }
}

function handleUpdateEnterprise() {
    global $pdo;

    $input = json_decode(file_get_contents('php://input'), true);
    $enterprise = isset($input['enterprise']) ? $input['enterprise'] : null;

    if (!$enterprise) {
        http_response_code(400);
        echo json_encode(['error' => 'Nenhuma empresa selecionada']);
        return;
    }

    try {
       
        if (!isset($_SESSION['id'])) {
            http_response_code(412); 
            echo json_encode(['error' => 'Sessão expirada ou inválida.']);
            return;
        }

        
        $enterprise = sanitizeTableName($enterprise);
        $_SESSION['enterprise'] = $enterprise;

        $stmt = $pdo->prepare("UPDATE users SET enterprise = :enterprise WHERE id = :id");
        $stmt->bindParam(':enterprise', $enterprise);
        $stmt->bindParam(':id', $_SESSION['id']); 
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar empresa: ' . $e->getMessage()]);
    }
}

function getGroups($enterprise) {
    global $pdo;

    try {
        $enterprise = sanitizeTableName($enterprise);  // Certifique-se de que sanitizeTableName() está definida
        $stmt = $pdo->prepare("SELECT DISTINCT `group` FROM {$enterprise}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar grupos: ' . $e->getMessage()]);
        return [];
    }
}


function handleFilterByGroup($enterprise) {
    global $pdo;

    $input = json_decode(file_get_contents('php://input'), true);
    $group = isset($input['group']) ? $input['group'] : null;

    if (!$group) {
        http_response_code(400);
        echo json_encode(['error' => 'Nenhum grupo especificado']);
        return;
    }

    try {
        $enterprise = sanitizeTableName($enterprise);
        $stmt = $pdo->prepare("SELECT id, filename, status, user_name, `group`, ExpirationDate FROM {$enterprise} WHERE `group` = :group");
        $stmt->bindParam(':group', $group);
        $stmt->execute();
        $vpns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode(['success' => true, 'vpns' => $vpns]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao filtrar VPNs: ' . $e->getMessage()]);
    }
}

function handleAddGroup($enterprise) {
    global $pdo;

    $input = json_decode(file_get_contents('php://input'), true);
    $group = isset($input['group']) ? $input['group'] : null;
    $observation = isset($input['observation']) ? $input['observation'] : null;

    if (!$group || !$observation) {
        http_response_code(400);
        echo json_encode(['error' => 'Grupo, data de expiração ou observação não especificados']);
        return;
    }

    try {
        $enterprise = sanitizeTableName($enterprise);
        $stmt = $pdo->prepare("INSERT INTO {$enterprise} (`group`, GroupObservation) VALUES (:group, :observation)");
        $stmt->bindParam(':group', $group);
        $stmt->bindParam(':observation', $observation);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao adicionar grupo: ' . $e->getMessage()]);
    }
}

function handleGetGroupDetails($enterprise) {
    global $pdo;

    $group = isset($_GET['group']) ? $_GET['group'] : null;

    if (!$group) {
        http_response_code(400);
        echo json_encode(['error' => 'Grupo não especificado']);
        return;
    }

    try {
        $enterprise = sanitizeTableName($enterprise);
        $stmt = $pdo->prepare("SELECT GroupObservation FROM {$enterprise} WHERE `group` = :group LIMIT 1");
        $stmt->bindParam(':group', $group);
        $stmt->execute();

        $groupDetails = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($groupDetails) {
            echo json_encode([
                'success' => true,
                'groupObservation' => $groupDetails['GroupObservation']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Grupo não encontrado']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar informações do grupo: ' . $e->getMessage()]);
    }
}

function updateGroupObservation($originalGroupName, $updatedGroupName, $observation) {
    global $pdo;

    $enterprise = $_SESSION['enterprise'];

    try {

        $tableName = "`" . preg_replace('/[^a-zA-Z0-9_]/', '', $enterprise) . "`";

        $stmt = $pdo->prepare("SELECT COUNT(*) FROM $tableName WHERE `group` = :updatedGroupName");
        $stmt->execute([':updatedGroupName' => $updatedGroupName]);

        if ($stmt->fetchColumn() > 0 && $originalGroupName !== $updatedGroupName) {

            return ['success' => false, 'error' => 'O nome do grupo já existe.'];
        }

        $stmt = $pdo->prepare("UPDATE $tableName SET `group` = :updatedGroupName, `GroupObservation` = :observation 
                               WHERE `group` = :originalGroupName");
        $stmt->execute([
            ':updatedGroupName' => $updatedGroupName,
            ':observation' => $observation,
            ':originalGroupName' => $originalGroupName
        ]);

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    if (isset($data['action']) && $data['action'] === 'updateGroupObservation') {
        $originalGroupName = $data['originalGroupName'];
        $updatedGroupName = $data['updatedGroupName'];
        $observation = $data['observation'];

        $response = updateGroupObservation($originalGroupName, $updatedGroupName, $observation);

        echo json_encode($response);
        exit;
    }
}

function deleteGroup($groupName) {
    global $pdo;

    // Pegue a variável enterprise da sessão
    $enterprise = $_SESSION['enterprise'];

    try {
        // Saneia o nome da tabela da empresa
        $tableName = "`" . preg_replace('/[^a-zA-Z0-9_]/', '', $enterprise) . "`";

        // Consulta para obter todas as VPNs associadas ao grupo
        $stmt = $pdo->prepare("SELECT filename FROM $tableName WHERE `group` = :groupName");
        $stmt->execute([':groupName' => $groupName]);
        $vpnFiles = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Se o grupo não tiver VPNs associadas, retorna erro
        if (empty($vpnFiles)) {
            return ['success' => false, 'error' => 'Grupo não encontrado ou não contém VPNs.'];
        }

        // Caminho do diretório de upload baseado na empresa
        $uploadDir = 'uploads/' . $enterprise . '/';

        // Exclui todos os arquivos VPN associados ao grupo
        foreach ($vpnFiles as $vpn) {
            $fileName = $vpn['filename'];

            // Verifica se o nome do arquivo não está vazio
            if (!empty($fileName)) {
                $filePath = $uploadDir . $fileName;

                // Verifica se o caminho é um arquivo antes de tentar excluir
                if (is_file($filePath)) {  // Verifica se é um arquivo válido
                    if (!unlink($filePath)) {
                        return ['success' => false, 'error' => 'Erro ao excluir arquivo VPN: ' . $fileName];
                    }
                } else {
                    return ['success' => false, 'error' => 'Erro ao excluir: ' . $fileName . ' não é um arquivo válido.'];
                }
            } else {
                // Ignorar arquivos com nomes vazios ou inválidos
                continue;
            }
        }

        // Exclui as entradas do grupo no banco de dados
        $stmt = $pdo->prepare("DELETE FROM $tableName WHERE `group` = :groupName");
        $stmt->execute([':groupName' => $groupName]);

        return ['success' => true];
    } catch (PDOException $e) {
        return ['success' => false, 'error' => $e->getMessage()];
    }
}



// Verifica se a requisição é POST e contém a ação de exclusão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'deleteGroup') {
    $data = json_decode(file_get_contents('php://input'), true);
    $groupName = $data['groupName'];

    // Chama a função de exclusão
    $response = deleteGroup($groupName);

    echo json_encode($response);
    exit;
}


switch ($action) {
    case 'addVPN':
        handleAddVPN($enterprise);
        break;
    case 'listVPNs':
        handleListVPNs($enterprise);
        break;
    case 'deleteVPN':
        handleDeleteVPN($enterprise);
        break;
    case 'downloadVPN':
        handleDownloadVPN();
        break;
    case 'linkVPN': 
        handleLinkVPN($enterprise);
        break;
    case 'deactivateVPN':
        handleDeactivateVPN($enterprise);
        break;
    case 'updateEnterprise':
        handleUpdateEnterprise();
        break;
    case 'filterByGroup':
        handleFilterByGroup($enterprise);
    break;
    case 'addGroup':
        handleAddGroup($enterprise);
        break;
    case 'getGroupDetails':
        handleGetGroupDetails($enterprise);
        break;
    case 'saveObservation':
        handleSaveObservation($enterprise);
        break;
    case 'getVpnDetails':
        getVpnDetails($_SESSION['enterprise']);
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Ação não suportada']);
}

function sanitizeTableName($enterprise) {
    
    if (preg_match('/^[a-zA-Z0-9_]+$/', $enterprise)) {
        return $enterprise;
    } else {
        throw new Exception('Nome de tabela inválido.');
    }
}

function getVpnDetails($enterprise) {
    global $pdo;

    $vpnId = isset($_GET['vpnId']) ? $_GET['vpnId'] : null;

    if (!$vpnId) {
        echo json_encode(['success' => false, 'error' => 'ID da VPN não especificado.']);
        return;
    }

    try {
        $enterprise = sanitizeTableName($enterprise);

        // Busca as informações da chave no banco de dados
        $stmt = $pdo->prepare("SELECT * FROM {$enterprise} WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $vpnId);
        $stmt->execute();
        $vpn = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vpn) {
            echo json_encode([
                'success' => true,
                'empresa' => $enterprise,
                'nome' => $vpn['filename'],
                'expiracao' => $vpn['ExpirationDate'],
                'grupo' => $vpn['group'],
                'status' => $vpn['status'],
                'usuario' => $vpn['user_name'],
                'primeiro_download' => $vpn['firstdowndate'],
                'ultimo_download' => $vpn['lastdowndate'],
                'motivo_redownload' => $vpn['downobs'],
                'data_desativacao' => $vpn['deactivateddate']
            ]);
        } else {
            echo json_encode(['success' => false, 'error' => 'VPN não encontrada no banco de dados.']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Erro ao buscar informações: ' . $e->getMessage()]);
    }
}


function handleSaveObservation($enterprise) {
    global $pdo;

    // Decodifica o corpo da requisição JSON
    $input = json_decode(file_get_contents('php://input'), true);
    $vpnId = isset($input['vpnId']) ? $input['vpnId'] : null;
    $observation = isset($input['observation']) ? $input['observation'] : null;

    if (!$vpnId || !$observation) {
        http_response_code(400);
        echo json_encode(['error' => 'ID da VPN ou observação não especificados']);
        return;
    }

    try {
        $enterprise = sanitizeTableName($enterprise);

        // Obtenha a data e hora atual considerando o fuso horário correto
        date_default_timezone_set('America/Sao_Paulo'); // Defina o fuso horário correto
        $now = new DateTime(); // Cria um novo objeto DateTime
        $nowFormatted = $now->format('Y-m-d H:i:s'); // Formata a data no padrão Y-m-d H:i:s

        $lastdowndateWithUser = $nowFormatted . ' por ' . $_SESSION['username'];

        // Atualiza o campo downobs e lastdowndate da VPN no banco de dados
        $stmt = $pdo->prepare("UPDATE {$enterprise} SET downobs = :observation, lastdowndate = :lastdowndate WHERE id = :id");
        $stmt->bindParam(':observation', $observation);
        $stmt->bindParam(':lastdowndate', $lastdowndateWithUser); // Usa a data e hora formatada
        $stmt->bindParam(':id', $vpnId);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao salvar a observação e a data de download: ' . $e->getMessage()]);
    }
}


function handleAddVPN($enterprise) {
    global $pdo;

    if (!isset($_FILES['vpnFile']) || $_FILES['vpnFile']['error'] !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['error' => 'Erro ao receber o arquivo VPN']);
        return;
    }

    // Obtém o nome do arquivo e o caminho temporário
    $fileName = $_FILES['vpnFile']['name'];
    $filePath = $_FILES['vpnFile']['tmp_name'];

    // Lê o conteúdo do arquivo
    $fileContents = file($filePath);

    if ($fileContents === false || count($fileContents) < 43) {
        http_response_code(400);
        echo json_encode(['error' => 'Erro ao ler o arquivo VPN ou o arquivo é muito curto.']);
        return;
    }

    // Extrai a linha 43 (considerando o índice 42, pois arrays em PHP são baseados em zero)
    $line43 = $fileContents[42];

    // Extrai os caracteres das colunas 25 a 45 da linha 43
    $expirationDate = substr($line43, 24, 21); // Coluna 25 a 45 (índice de 24 a 44)

    // Obtém as informações do grupo do POST
    $group = isset($_POST['group']) ? $_POST['group'] : null;
    $groupObservation = isset($_POST['GroupObservation']) ? $_POST['GroupObservation'] : null;

    if (!$group) {
        http_response_code(400);
        echo json_encode(['error' => 'Grupo não especificado']);
        return;
    }

   
    $uploadDir = 'uploads/' . $enterprise . '/';

    if (!is_dir($uploadDir)) {
        if (!mkdir($uploadDir, 0777, true)) {
            http_response_code(500);
            echo json_encode(['error' => 'Erro ao criar diretório da empresa']);
            return;
        }
    }

    $destPath = $uploadDir . basename($fileName);

    if (!move_uploaded_file($filePath, $destPath)) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao salvar arquivo VPN']);
        return;
    }

    ob_end_clean();

    try {
        $enterprise = sanitizeTableName($enterprise);
        $stmt = $pdo->prepare("INSERT INTO {$enterprise} (filename, status, user_name, `group`, GroupObservation, ExpirationDate) VALUES (:filename, 'disponivel', NULL, :group, :GroupObservation, :ExpirationDate)");
        $stmt->bindParam(':filename', $fileName);
        $stmt->bindParam(':group', $group);
        $stmt->bindParam(':GroupObservation', $groupObservation);
        $stmt->bindParam(':ExpirationDate', $expirationDate);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao adicionar VPN: ' . $e->getMessage()]);
    }
}


function handleListVPNs($enterprise) {
    global $pdo;

    try {
        $enterprise = sanitizeTableName($enterprise);
        // Inclua `ExpirationDate` na consulta
        $stmt = $pdo->query("SELECT id, filename, status, user_name, `group`, ExpirationDate FROM {$enterprise}");
        $vpns = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($vpns);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao listar VPNs: ' . $e->getMessage()]);
    }
}

function getAdminPassword() {
    global $pdo;
    
    if (!isset($_SESSION['username'])) {
        throw new Exception('Nenhum usuário está logado na sessão.');
    }

    $loggedInUsername = $_SESSION['username'];

    try {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE username = :username");
        $stmt->bindParam(':username', $loggedInUsername);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user) {
            return $user['password'];
        } else {
            throw new Exception('Usuário não encontrado na base de dados.');
        }
    } catch (PDOException $e) {
        throw new Exception('Erro ao buscar a senha do usuário: ' . $e->getMessage());
    } catch (Exception $e) {
        throw new Exception($e->getMessage());
    }
}

function handleDeleteVPN($enterprise) {
    global $pdo;

    $vpnId = isset($_POST['vpnId']) ? $_POST['vpnId'] : null;
    $password = isset($_POST['password']) ? $_POST['password'] : null;

    if (!$vpnId || !$password) {
        http_response_code(400);
        echo json_encode(['error' => 'ID da VPN ou senha não especificados']);
        return;
    }

    try {
        // Obtém a senha do administrador
        $adminPassword = getAdminPassword();

        // Verifica se a senha fornecida é válida
        if ($password !== $adminPassword) {
            http_response_code(403); // Forbidden
            echo json_encode(['error' => 'Senha inválida']);
            return;
        }

        // Saneia o nome da tabela da empresa
        $enterprise = sanitizeTableName($enterprise);

        // Consulta o arquivo a ser excluído
        $stmt = $pdo->prepare("SELECT filename FROM {$enterprise} WHERE id = :id");
        $stmt->bindParam(':id', $vpnId);
        $stmt->execute();
        $vpn = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$vpn) {
            http_response_code(404); // VPN não encontrada
            echo json_encode(['error' => 'VPN não encontrada']);
            return;
        }

        // Caminho do arquivo VPN
        $filePath = 'uploads/' . $enterprise . '/' . $vpn['filename'];

        // Exclui o arquivo do diretório, se existir
        if (file_exists($filePath)) {
            if (!unlink($filePath)) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao excluir o arquivo VPN']);
                return;
            }
        }

        // Exclui o registro da VPN no banco de dados
        $stmt = $pdo->prepare("DELETE FROM {$enterprise} WHERE id = :id");
        $stmt->bindParam(':id', $vpnId);
        $stmt->execute();

        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao excluir VPN: ' . $e->getMessage()]);
    }
}


function handleDownloadVPN() {
    global $pdo;

    $fileName = isset($_GET['filename']) ? $_GET['filename'] : null;

    if (!$fileName) {
        http_response_code(400);
        echo json_encode(['error' => 'Nome do arquivo não especificado']);
        return;
    }

    if (!isset($_SESSION['enterprise'])) {
        http_response_code(403);
        echo json_encode(['error' => 'Acesso negado. Sessão expirada ou inválida.']);
        return;
    }

    $enterprise = sanitizeTableName($_SESSION['enterprise']);
    $filePath = 'uploads/' . $enterprise . '/' . basename($fileName);

    if (!file_exists($filePath)) {
        http_response_code(404);
        echo json_encode(['error' => 'Arquivo não encontrado: ' . $filePath]);
        return;
    }

    try {
        $stmt = $pdo->prepare("SELECT id, user_name FROM {$enterprise} WHERE filename = :filename LIMIT 1");
        $stmt->bindParam(':filename', $fileName);
        $stmt->execute();
        $vpn = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($vpn) {
            $vpnId = $vpn['id'];
            $userName = !empty($vpn['user_name']) ? $vpn['user_name'] : 'usuario';
            date_default_timezone_set('America/Sao_Paulo'); 
            $now = new DateTime(); 
            $nowFormatted = $now->format('Y-m-d H:i:s'); 
            $lastdowndateWithUser = $nowFormatted . ' por ' . $_SESSION['username'];

            $stmt = $pdo->prepare("UPDATE {$enterprise} SET lastdowndate = :lastdowndate WHERE id = :id");
            $stmt->bindParam(':lastdowndate', $lastdowndateWithUser);
            $stmt->bindParam(':id', $vpnId);
            $stmt->execute();

            $fileInfo = pathinfo($fileName);
            $newFileName = $fileInfo['filename'] . '_' . $userName . '.' . $fileInfo['extension'];
            $newFilePath = 'uploads/' . $enterprise . '/' . $newFileName;

            if (!rename($filePath, $newFilePath)) {
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao renomear o arquivo.']);
                return;
            }

            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename="' . basename($newFilePath) . '"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($newFilePath));
            flush();
            readfile($newFilePath);

            rename($newFilePath, $filePath); // Renomeia de volta após o download
            exit;
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'VPN não encontrada no banco de dados.']);
            return;
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao atualizar a data de download: ' . $e->getMessage()]);
        return;
    }
}


function handleDeactivateVPN($enterprise) {
    global $pdo;

    $input = json_decode(file_get_contents('php://input'), true);
    error_log('Input recebido: ' . print_r($input, true));  // Verifique o que está sendo recebido

    $vpnId = isset($input['vpnId']) ? $input['vpnId'] : null;
    $deactivateAdUser = isset($input['deactivateAdUser']) ? $input['deactivateAdUser'] : false;

    if (!$vpnId) {
        http_response_code(400);
        echo json_encode(['error' => 'ID da VPN não especificado']);
        error_log('Erro: ID da VPN não especificado');
        return;
    }

    try {
        $enterprise = sanitizeTableName($enterprise);
        date_default_timezone_set('America/Sao_Paulo');
        $now = new DateTime();
        $nowFormatted = $now->format('Y-m-d H:i:s');

        if (!isset($_SESSION['username'])) {
            echo json_encode(['error' => 'Usuário não está logado.']);
            error_log('Erro: Usuário não está logado');
            exit();
        }

        $deactivatedDateWithUser = $nowFormatted . ' por ' . $_SESSION['username'];
        error_log('Data de desativação: ' . $deactivatedDateWithUser);

        // Atualizar o status da VPN
        $stmt = $pdo->prepare("UPDATE {$enterprise} SET status = 'desativado', deactivateddate = :deactivatedDate WHERE id = :id");
        $stmt->bindParam(':deactivatedDate', $deactivatedDateWithUser);
        $stmt->bindParam(':id', $vpnId);
        $stmt->execute();
        error_log('Status atualizado para desativado');

        $stmt = $pdo->prepare("SELECT filename, user_name FROM {$enterprise} WHERE id = :id LIMIT 1");
        $stmt->bindParam(':id', $vpnId);
        $stmt->execute();
        $vpn = $stmt->fetch(PDO::FETCH_ASSOC);
        error_log('VPN obtida: ' . print_r($vpn, true));

        if ($vpn) {
            $filenameWithoutExtension = pathinfo($vpn['filename'], PATHINFO_FILENAME);
            $enterprise = ucfirst(strtolower($enterprise));
            $to = 'adryanrybeiro123@gmail.com'; 
            $subject = 'Desativar VPN ' . $filenameWithoutExtension . ' - ' . $enterprise;

            $message = $deactivateAdUser ?
                "Foi solicitado a desativação da VPN da empresa \"$enterprise\" chave \"" . $filenameWithoutExtension . "\" vinculada ao usuário \"" . $vpn['user_name'] . "\". Também foi solicitado desativar o usuário do AD." :
                "Foi solicitado a desativação da VPN da empresa \"$enterprise\" chave \"" . $filenameWithoutExtension . "\" vinculada ao usuário \"" . $vpn['user_name'] . "\".";

            error_log('Mensagem de e-mail: ' . $message);

            // Tente enviar o e-mail usando PHPMailer
            $mail = new PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'ryanrybeiro123@gmail.com';
                $mail->Password = 'paof elvc dgne czrz';
                $mail->SMTPSecure = 'ssl'; 
                $mail->Port = 465;

                $mail->CharSet = 'UTF-8';
                $mail->setFrom('no-reply@extel.com', 'Extel-Bot');
                $mail->addAddress($to);
                $mail->Subject = $subject;
                $mail->Body = $message;

                $mail->send();
                error_log('E-mail enviado com sucesso');
                echo json_encode(['success' => true]);
                exit();
            } catch (Exception $e) {
                error_log('Erro ao enviar o e-mail: ' . $mail->ErrorInfo);
                http_response_code(500);
                echo json_encode(['error' => 'Erro ao enviar o e-mail: ' . $mail->ErrorInfo]);
                exit();
            }
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'VPN não encontrada no banco de dados.']);
            error_log('VPN não encontrada no banco de dados');
            exit();
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao desativar VPN: ' . $e->getMessage()]);
        error_log('Erro ao desativar VPN: ' . $e->getMessage());
        exit();
    }
}



function handleLinkVPN($enterprise) {
    global $pdo;

    $vpnId = isset($_POST['vpnId']) ? $_POST['vpnId'] : null;
    $userName = isset($_POST['userName']) ? $_POST['userName'] : '';

    if (!$vpnId || empty($userName)) {
        http_response_code(400);
        echo json_encode(['error' => 'ID da VPN ou nome de usuário não especificados']);
        return;
    }

    try {
        date_default_timezone_set('America/Sao_Paulo');
        $now = new DateTime();
        $nowFormatted = $now->format('Y-m-d H:i:s');
        $firstdowndateWithUser = $nowFormatted . ' por ' . $_SESSION['username'];

        $enterprise = sanitizeTableName($enterprise);
        $stmt = $pdo->prepare("UPDATE {$enterprise} SET user_name = :userName, status = 'em_uso', firstdowndate = :firstdowndate WHERE id = :id");
        $stmt->bindParam(':userName', $userName);
        $stmt->bindParam(':firstdowndate', $firstdowndateWithUser);
        $stmt->bindParam(':id', $vpnId);
        $stmt->execute();

        echo json_encode(['success' => true]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao vincular VPN: ' . $e->getMessage()]);
    }
}