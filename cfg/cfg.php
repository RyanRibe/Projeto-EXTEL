<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações</title>
    <link rel="stylesheet" href="./stylescfg.css">
    <link rel="icon" type="image/x-icon" href="./assets/favico.png">
</head>
    <a id="logout" href="logout.php" title="Sair"></a>
<body>
    <div class="wrapper">
        <div class="content">
            <h2>Configurações de Empresas</h2>
            <form id="config-form">
                <ul id="config-list">
                    <!-- Lista de empresas carregadas via JavaScript -->
                </ul>
                <button type="button" id="add-button">Adicionar Empresa</button>
            </form>
        </div>
    </div>

    <!-- Modal para adicionar empresa -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>CRIAR NOVA EMPRESA</h2>
            <form id="add-company-form">
                <p style="color: orange;"> O nome deve conter letras e números ⚠️ </p>
                <label for="company_name">Nome da Empresa:</label>
                
                <input type="text" id="company_name" name="company_name" required>
                <button type="submit">Confirmar</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            // Abrir e fechar o modal
            const modal = document.getElementById("modal");
            const addButton = document.getElementById("add-button");
            const span = document.getElementsByClassName("close")[0];

            addButton.onclick = function() {
                modal.style.display = "block";
            }

            span.onclick = function() {
                modal.style.display = "none";
            }

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            }

            // Manipulação do formulário de adicionar empresa
            document.getElementById("add-company-form").addEventListener("submit", function(e) {
                e.preventDefault();
                const companyName = document.getElementById("company_name").value;

                fetch('create_company.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded'
                    },
                    body: new URLSearchParams({
                        'company_name': companyName
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert(data.message);
                        modal.style.display = "none";
                        location.reload();  // Recarrega a página para atualizar a lista de empresas
                    } else {
                        alert(data.message);
                    }
                })
                .catch(error => console.error('Erro:', error));
            });

            // Função para carregar a lista de empresas
            function loadCompanies() {
                fetch('list_companies.php')
                    .then(response => response.json())
                    .then(data => {
                        const list = document.getElementById('config-list');
                        list.innerHTML = '';
                        data.forEach(company => {
                            const li = document.createElement('li');
                            li.textContent = company;
                            list.appendChild(li);
                        });
                    })
                    .catch(error => console.error('Erro ao carregar as empresas:', error));
            }

            // Carrega as empresas quando a página é carregada
            loadCompanies();
        });
    </script>
            <!-- Botão de Voltar -->
            <button type="button" id="back-button">Voltar</button>
        </div>
    </div>

    <script>
    // Função para redirecionar ao clicar no botão de voltar
    document.getElementById("back-button").addEventListener("click", function() {
        // Redireciona para a página VPN com a ação de atualizar as tabelas
        window.location.href = "/server.php?action=backToVpn"; 
    });
</script>

<h2>Configurações de Usuários</h2>
<ul id="users-list">
    <!-- Lista de usuários carregados via JavaScript -->
</ul>
<button type="button" id="add-user-button">Adicionar Usuário</button>
<!-- Modal para adicionar usuário -->
<div id="user-modal" class="modal">
    <div class="modal-content">
        <span class="close-user-modal">&times;</span>
        <h2>CRIAR NOVO USUÁRIO</h2>
        <form id="add-user-form">
            <label for="username">Nome de Usuário:</label>
            <input type="text" id="username" name="username" required>
            
            <label for="password" maxlength="8">Senha:</label>
            <input type="password" id="password" name="password" required>

            <label for="typeuser">Tipo de Usuário:</label>
            <select id="typeuser" name="typeuser" required>
                <option value="user">Usuário</option>
                <option value="admin">Administrador</option>
            </select>

            <label for="enterprise">Empresa:</label>
            <select id="enterprise" name="enterprise" required>
                <!-- Empresas serão carregadas via JavaScript -->
            </select>

            <label for="resetpassword">
                <input type="checkbox" id="resetpassword" name="resetpassword">
                Forçar reset de senha
            </label>

            <button type="submit">Confirmar</button>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {

    const userModal = document.getElementById("user-modal");
    const addUserButton = document.getElementById("add-user-button");
    document.querySelector('.content').appendChild(addUserButton);

    const closeUserModal = document.getElementsByClassName("close-user-modal")[0];

    addUserButton.onclick = function() {
        userModal.style.display = "block";
        loadCompaniesToDropdown();
    }

    closeUserModal.onclick = function() {
        userModal.style.display = "none";
    }

    window.onclick = function(event) {
        if (event.target == userModal) {
            userModal.style.display = "none";
        }
    }

    
    function loadCompaniesToDropdown() {
        fetch('list_companies.php')
            .then(response => response.json())
            .then(data => {
                const enterpriseSelect = document.getElementById('enterprise');
                enterpriseSelect.innerHTML = '';

                data.forEach(company => {
                    const option = document.createElement('option');
                    option.value = company;
                    option.textContent = company;
                    enterpriseSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Erro ao carregar as empresas:', error));
    }

    document.getElementById("add-user-form").addEventListener("submit", function(e) {
        e.preventDefault();
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;
        const typeuser = document.getElementById("typeuser").value;
        const enterprise = document.getElementById("enterprise").value;
        const resetpassword = document.getElementById("resetpassword").checked ? 1 : 0;

        fetch('create_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'username': username,
                'password': password,
                'typeuser': typeuser,
                'enterprise': enterprise,
                'resetpassword': resetpassword
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                userModal.style.display = "none";
            
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erro:', error));
    });
});

function loadUsers() {
    fetch('list_users.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            const usersList = document.getElementById('users-list');
            usersList.innerHTML = ''; 
            if (data.length > 0) {
                data.forEach(user => {
                    const li = document.createElement('li');
                    li.textContent = `Usuário: ${user.username}, Tipo: ${user.typeuser}, Empresa: ${user.enterprise}`;

             
                    const editButton = document.createElement('button');
                    editButton.textContent = "Editar";
                    editButton.style.marginLeft = '10px';
                    editButton.addEventListener('click', function () {
                        openEditModal(user);
                    });

                    li.appendChild(editButton);
                    usersList.appendChild(li);
                });
            } else {
                usersList.innerHTML = '<li>Nenhum usuário encontrado.</li>';
            }
        })
        .catch(error => {
            console.error('Erro ao carregar os usuários:', error);
            alert('Ocorreu um erro ao carregar a lista de usuários: ' + error.message);
        });
}

    
    loadUsers();

    
    document.getElementById("add-user-form").addEventListener("submit", function(e) {
        e.preventDefault();
        const username = document.getElementById("username").value;
        const password = document.getElementById("password").value;
        const typeuser = document.getElementById("typeuser").value;
        const enterprise = document.getElementById("enterprise").value;
        const resetpassword = document.getElementById("resetpassword").checked ? 1 : 0;

        fetch('create_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'username': username,
                'password': password,
                'typeuser': typeuser,
                'enterprise': enterprise,
                'resetpassword': resetpassword
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                userModal.style.display = "none";
                loadUsers(); 
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erro:', error));
    });

    function openEditModal(user) {

    const userModal = document.getElementById("user-modal");
    userModal.style.display = "block";

    document.getElementById("username").value = user.username;
    document.getElementById("password").value = ""; 
    document.getElementById("typeuser").value = user.typeuser;
    
    
    fetch('list_companies.php')
        .then(response => response.json())
        .then(companies => {
            const enterpriseSelect = document.getElementById('enterprise');
            enterpriseSelect.innerHTML = '';

            companies.forEach(company => {
                const option = document.createElement('option');
                option.value = company;
                option.textContent = company;
                if (company === user.enterprise) {
                    option.selected = true; 
                }
                enterpriseSelect.appendChild(option);
            });
        })
        .catch(error => console.error('Erro ao carregar as empresas:', error));

    document.getElementById("resetpassword").checked = false; 

    document.getElementById("add-user-form").onsubmit = function(e) {
        e.preventDefault();
        updateUser(user.username);
    };
}


function updateUser(originalUsername) {
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;
    const typeuser = document.getElementById("typeuser").value;
    const enterprise = document.getElementById("enterprise").value;
    const resetpassword = document.getElementById("resetpassword").checked ? 1 : 0;

    fetch('update_user.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'original_username': originalUsername,
            'username': username,
            'password': password,
            'typeuser': typeuser,
            'enterprise': enterprise,
            'resetpassword': resetpassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            document.getElementById("user-modal").style.display = "none";
            loadUsers(); 
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erro ao atualizar o usuário:', error));
}



</script>

</body>
</html>
