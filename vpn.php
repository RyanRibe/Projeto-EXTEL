<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['id'])) {
    // Se não estiver logado, redireciona para a página de login
    header('Location: /login');  // Substitua 'login.php' pelo caminho correto para sua página de login
    exit();
  
}

$url = $_SERVER['REQUEST_URI'];

if ($url == '/login') {
    include 'login.php';
    exit();
}

$url = $_SERVER['REQUEST_URI'];

if ($url == '/cfg') {
    include 'cfg.php';
    exit();
}

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

function sanitizeTableName($enterprise) {
    if (preg_match('/^[a-zA-Z0-9_]+$/', $enterprise)) {
        return $enterprise;
    } else {
        throw new Exception('Nome de tabela inválido.');
    }
}

function getGroups($enterprise) {
    global $pdo;  // Declaração para acessar a variável global $pdo

    try {
        $enterprise = sanitizeTableName($enterprise);
        $stmt = $pdo->prepare("SELECT DISTINCT `group` FROM {$enterprise}");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao buscar grupos: ' . $e->getMessage()]);
        return [];
    }
}

$enterprise = $_SESSION['enterprise'];
$groups = getGroups($enterprise);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <title>VPN - <?php echo htmlspecialchars(strtoupper(str_replace('_', ' ', $enterprise))); ?></title>
    <link rel="icon" type="image/x-icon" href="./assets/favico.png">
    <link rel="stylesheet" href="styles.css">
</head>
    <a id="logout" href="logout.php" title="Sair"></a>

    <nav>
        <a id="cfg"  style="display: none;"   href="cfg/cfg.php" title="Configurar">Configurações</a>
        <button style="display: none;" id="showUsersBtn">Usuários Conectados</button>
      </nav>

    <nav>
    <a id="resetpw" href="reset_password.php" title="Trocar senha"></a>
</nav>

<body>

   
    <div class="select" id="adminSection" style="display: none;">
        <label for="enterprise"></label>
        <select id="enterprise" name="enterprise" onchange="submitEnterprise()">
            
        </select>
    </div>

  
    <div class="container">
        <h1>GERENCIAMENTO VPN's</h1>
    
        <input type="file" id="fileInput" multiple accept=".ovpn" style="display: none;">
    

        <div class="grupos">
          <p> GRUPOS </p>
            <div class="labels-container">
                <?php foreach ($groups as $index => $group): ?>
                    <label>
                        <input type="radio" name="group" value="<?= htmlspecialchars($group) ?>" 
                        onchange="filterByGroup(this)" 
                        <?= $index === 0 ? 'checked' : '' ?>>
                        <?= htmlspecialchars($group) ?>
                        <button id="GroupObs" data-group="<?= htmlspecialchars($group) ?>" title="Ver observações do grupo"></button>
                    </label>
                <?php endforeach; ?>
            </div>
                <button id="addGroupBtn" style="display: none;" title="Adicionar Grupo"></button>
        </div>

        
        <div class="filtro">
            <button id="addVpnBtn" style="display: none;" title="Adicionar VPN"></button>
            <button id="todas">Todas</button> 
            <button id="disponiveis">Disponíveis</button>                         
            <button id="usando">Em Uso</button>
            <button id="encerradas">Desativadas</button>
            <input type="text" id="searchInput" autocomplete="off" placeholder="Pesquisar">    
            <span id="quantidadeFiltro"></span><span id="quantidadeText">Quantidade:</span>                            
        </div>
        
        <!--<div class="cabecalho"> 
            <span>Chave</span>
            <span id="stts">Status</span>
            <span>Usuário</span>
        </div>-->

        <div id="vpnList">
            <div class="vpn-item">
                <span id="chavename">Nome da VPN</span>
                <span id="dispo">Disponível</span>
                <span id="usernome">Nome do Usuário</span>
                <button id="vincula">Vincular</button>
                <button id="desactive">Desativar</button>
                <button id="down">Download</button>
                <button id="x">X</button>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content">
            <span class="close" style="cursor: pointer;
            color: red;
            font-weight: 800;
            ">&times;</span>
            <h2>CONFIRMAR EXCLUSÃO ⚠️</h2>
            <form id="deleteForm">
                <p>Digite a senha para confirmar a exclusão:</p>
                <input type="password" id="password" name="password" required>
                <input type="hidden" id="vpnIdToDelete" name="vpnId">
                <button type="submit">Confirmar Exclusão</button>
            </form>
        </div>
    </div>

    <div id="linkModal" class="modal">
        <div class="modal-content">
            <span class="close-link" style="cursor: pointer;
            color: red;
            font-weight: 800;
            ">&times;</span>
            <h2>ATRIBUIR CHAVE VPN PARA:</h2>
            <form id="linkForm">
                <p>Nome do Usuário do AD:</p>
                <input type="text" id="userName" style="padding: 10px;" data-ls-module="charCounter" maxlength="30" placeholder="Máx. 30 caracteres" name="userName" required>
                <input type="hidden" id="vpnIdToLink" name="vpnId">
                <button type="submit">Vincular</button>
            </form>
        </div>
    </div>

    <div id="GroupObsModal" class="modal">
        <div id="GroupObsModalBox"class="modal-content">
            <span class="close-gpobs" style="cursor: pointer;
            color: red;
            font-weight: 800;
            ">&times;</span>
            <h2>NOVO GRUPO DE VPN'S</h2>
            <form id="gpobsForm">
                <p>Digite o nome do grupo:</p>
                <input type="text" id="NameGroup" name="NameGroup" required>
                <p id="obsp">Observação:</p>
                <textarea type="text" rows="10" style="resize: none; height: 215px;" placeholder="Digite a observação referente ao grupo. Máx. 999 Caracteres" maxlength="999" id="ObsCampo" name="ObsCampo" required></textarea>
                <input type="hidden" id="groupToObs" name="vpnId">
                <button type="submit">Gravar</button>
            </form>
        </div>
    </div>

<div id="EditGroupObsModal" class="modal">
    <div id="EditGroupObsModal-modal" class="modal-content">
        <span class="close-edit-gpobs" style="cursor: pointer; color: red; font-weight: 800;">&times;</span>
        <h2>EDITAR GRUPO</h2>
            <form id="editGpobsForm">
                <input type="hidden" id="originalGroupName" name="originalGroupName"> <!-- Campo oculto -->
                <p>Nome do Grupo:</p>
                <input type="text" id="editNameGroup" name="editNameGroup" required>
                <p>Editar Observação:</p>
                <textarea id="editObsCampo" name="editObsCampo" rows="10" style="resize: none; height: 215px;" maxlength="999" required></textarea>
                <button type="submit">Salvar</button>
            </form>
    </div>
</div>

<div id="deleteGroupModal" class="modal">
    <div class="modal-content">
        <span class="close-delete-modal" style="cursor: pointer; color: red; font-weight: 800;">&times;</span>
        <h2>CONFIRMAR EXCLUSÃO ⚠️</h2>
        <p>Você tem certeza que deseja excluir este grupo?</p>
        <input type="hidden" id="groupNameToDelete" name="groupNameToDelete"> <!-- Campo oculto -->
        <button id="confirmDeleteBtn" style="background-color: green;">Confirmar</button>
        <button id="cancelDeleteBtn" style="background-color: red;">Cancelar</button>
    </div>
</div>



    <div id="ObsModal" class="modal">
    <div id="ObsModalBox" class="modal-content">
        <span class="close-gpobsexibe" style="cursor: pointer; color: red; font-weight: 800;">&times;</span>
        <h2>OBSERVAÇÕES</h2>
        <div id="groupObservationsContent" style="max-width: 500px; max-height: 385px;"></div> <!-- Área para exibir observações -->
    </div>
</div>


<div id="VpnObsModal" class="modal">
        <div id="VpnObsModalBox"class="modal-content">
        <span class="close-vpnobs" style="cursor: pointer;
            color: red;
            font-weight: 800;
            ">&times;</span>
            <h2>ESTA CHAVE VPN JÁ ESTÁ EM USO</h2>
            <form id="vpnobsForm">
                <p id="vpnobsp">Para prosseguir, informe o motivo:</p>
                <textarea type="text" rows="10" style="resize: none; height: 215px;" placeholder="Digite a observação referente ao grupo. Máx. 999 Caracteres" maxlength="999" id="VpnObsCampo" name="VpnObsCampo" required></textarea>
                <input type="hidden" id="vpnToObs" name="vpnId">
                <button type="submit">Gravar</button>
            </form>
        </div>
    </div>

    <div id="deactivateModal" class="modal">
    <div class="modal-content">
        <span class="close-deactivate" style="cursor: pointer; color: red; font-weight: 800;">&times;</span>
        <h2>CONFIRMAR DESATIVAÇÃO ⚠️</h2>
        <p>Tem certeza de que deseja desativar esta VPN?</p>
        <button id="confirmDeactivateBtn" style="background-color: green;">Confirmar</button>
        <button id="cancelDeactivateBtn"style="background-color: red;">Cancelar</button>
        <input type="hidden" id="vpnIdToDeactivate" value="">
    </div>
</div>

<div id="adDeactivateModal" class="modal">
    <div class="modal-content">
        <span class="close-ad-deactivate" style="cursor: pointer; color: red; font-weight: 800;">&times;</span>
        <h2>DESATIVAR USUÁRIO DO AD⚠️</h2>
        <p>Gostaria de desativar também o usuário do AD associado a esta VPN?</p>
        <button id="confirmAdDeactivateBtn"style="background-color: green;">SIM, desativar usuário do AD</button>
        <button id="cancelAdDeactivateBtn" style="background-color: red;">NÃO, apenas desativar VPN</button>
        <input type="hidden" id="vpnIdToAdDeactivate" value="">
    </div>
</div>


<div id="userModal" class="modal">
    <div id="userModal-content" class="modal-content">
        <span class="closeUsersBtn">&times;</span>
        <h2>Usuários Conectados</h2>
        <table style="color: white;" id="userTable">
            <thead>
                <tr>
                    <th>Usuário</th>
                    <th>Navegador</th>
                    <th>Data de Login</th>
                    <th>IP</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                
            </tbody>
        </table>
    </div>
</div>

<div id="resetModal"  class="modal">
        <form method="POST" class="modal-content">
            <label for="current_password">Senha Atual:</label>
            <input type="password" id="current_password" name="current_password" required>
            
            <label for="new_password">Nova Senha:</label>
            <input type="password" id="new_password" name="new_password" required>

            <button type="submit">Atualizar Senha</button>
        </form>
        <?php if (isset($error)) { echo "<p>$error</p>"; } ?>
    </div>


    <script src="config.php"></script>
 
    <script src="script.js"></script>

    <footer>
        <p>&copy; 2024 - Ryan Ribeiro Oliveira.</p>
    </footer>
</body>

</html>