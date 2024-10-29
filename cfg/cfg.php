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
    <button type="button" id="back-button">Voltar ↩︎</button>
<body>
<div class="config-wrapper">
    <div class="company-config">
    <div class="wrapper">
        <div class="content">
            <h2>Configurações de Empresas</h2>
            <form id="config-form">
            <button style='font-weight: 800;'type="button" id="add-button">+ ADICIONAR EMPRESA</button>
                <ul id="config-list">
                    <!-- Lista de empresas carregadas via JavaScript -->
                </ul>

            </form>
        </div>
    </div>

    <!-- Modal para adicionar empresa -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2>CRIAR NOVA EMPRESA</h2>
            <form id="add-company-form">
                <p style="color: orange;"> O deve conter letras ⚠️ </p>
                <label for="company_name">Nome da Empresa:</label>
                
                <input maxlength="45" type="text" id="company_name" name="company_name" required>
                <button type="submit">Confirmar</button>
            </form>
        </div>
    </div>

<!-- Modal para editar a empresa -->
<div id="editCompanyModal" class="modal">
    <div class="modal-content">
        <span class="close-button" id="closeModal">&times;</span> 
        <h2>Editar Empresa</h2>
        <!--<p style="color: orange;">Ao trocar o nome de uma empresa, lembre-se de atualizar o usuário ao qual acessa tal empresa ⚠️</p>-->
        <form id="editCompanyForm">
            <label for="companyName">Nome da Empresa:</label>
            <input maxlength="45" type="text" id="companyName" name="companyName" required>            
            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>


<!-- Modal de confirmação para excluir empresa -->
<div id="delete-company-modal" class="modal">
    <div class="modal-content">
        <span class="close-delete-modal">&times;</span>
        <h2>EXCLUIR EMPRESA</h2>
        <p style="color: orange;">Está ação excluirá todos os registros relacionados ⚠️</p>
        <p>Tem certeza de que deseja excluir esta empresa?</p>
        <button id="confirm-delete-button" style="background-color: green;
        width: 80px;" type="button">Sim</button>
        <button id="cancel-delete-button" style="background-color: red;" type="button">Cancelar</button>
    </div>
</div>
</div>




    <script>
document.addEventListener("DOMContentLoaded", function() {

    document.getElementById("back-button").addEventListener("click", function() {
            window.location.href = "/server.php?action=backToVpn"; 
            });

    const modal = document.getElementById("modal");
    const addButton = document.getElementById("add-button");
    const span = document.getElementsByClassName("close")[0];

    addButton.onclick = function() {
        modal.style.display = "block";
    }

    span.onclick = function() {
        modal.style.display = "none";
    }

    window.addEventListener("click", function(event) {
        if (event.target == modal) {
            modal.style.display = "none";
        }
    });


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
                location.reload();  
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erro:', error));
    });

    function loadCompanies() {
    fetch('list_companies.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok ' + response.statusText);
            }
            return response.json();
        })
        .then(data => {
            if (data.error) {
                throw new Error(data.error);
            }
            const list = document.getElementById('config-list');
            list.innerHTML = '';
            data.forEach(company => {
                const li = document.createElement('li');
                li.textContent = company;

                // Botão de excluir
                const deleteButton = document.createElement('button');
                deleteButton.textContent = "Excluir ⌫";
                deleteButton.style.marginLeft = '10px';
                deleteButton.type = "button"; 
                deleteButton.addEventListener('click', function () {
                    openDeleteModal(company);
                });

                // Botão de editar
                const editButton = document.createElement('button');
                editButton.textContent = "Editar ✎";
                editButton.style.marginLeft = '10px';
                editButton.type = "button";
                editButton.addEventListener('click', function () {
                    openEditModal(company);
                });

                li.appendChild(editButton); 
                li.appendChild(deleteButton);
                list.appendChild(li);
            });
        })
        .catch(error => console.error('Erro ao carregar as empresas:', error));
}



function openEditModal(companyName) {
    document.getElementById('companyName').value = companyName;
    editCompanyModal.style.display = 'block';
    currentCompanyName = companyName;
}

let currentCompanyName = null; 

editCompanyForm.addEventListener('submit', (event) => {
    event.preventDefault(); 

    // Obtém o novo nome da empresa
    const newCompanyName = document.getElementById('companyName').value;

    // Faz a solicitação para atualizar o nome da empresa
    fetch('update_company.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'current_company_name': currentCompanyName,
            'new_company_name': newCompanyName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            editCompanyModal.style.display = 'none';
            loadCompanies(); 
            location.reload();
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erro ao atualizar a empresa:', error));
});

const editCompanyModal = document.getElementById('editCompanyModal');
const closeModalButton = document.getElementById('closeModal'); 

closeModalButton.addEventListener('click', () => {
    editCompanyModal.style.display = 'none'; 
});

window.addEventListener('click', (event) => {
    if (event.target === editCompanyModal) {
        editCompanyModal.style.display = 'none';
    }
});



    loadCompanies();
 

    const deleteCompanyModal = document.getElementById("delete-company-modal");
    const closeDeleteModal = document.getElementsByClassName("close-delete-modal")[0];
    const confirmDeleteButton = document.getElementById("confirm-delete-button");
    const cancelDeleteButton = document.getElementById("cancel-delete-button");

    let companyToDelete = null;
    function openDeleteModal(company) {
        companyToDelete = company;
        deleteCompanyModal.style.display = "block";
    }


    closeDeleteModal.onclick = function() {
        deleteCompanyModal.style.display = "none";
        companyToDelete = null;
    }

    cancelDeleteButton.onclick = function() {
        deleteCompanyModal.style.display = "none";
        companyToDelete = null;
    }

    confirmDeleteButton.onclick = function() {
        if (companyToDelete) {
            deleteCompany(companyToDelete);
        }
    }


    window.addEventListener("click", function(event) {
        if (event.target == deleteCompanyModal) {
            deleteCompanyModal.style.display = "none";
            companyToDelete = null;
        }
    });

    function deleteCompany(companyName) {
        fetch('delete_company.php', {
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
                deleteCompanyModal.style.display = "none";
                loadCompanies(); 
                location.reload();
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erro ao excluir a empresa:', error));
    }
});

</script>



<!-- SESSÃO DE USUARIOS - SESSÃO DE USUARIOS SESSÃO DE USUARIOS - SESSÃO DE USUARIOS -->
 <!-- SESSÃO DE USUARIOS - SESSÃO DE USUARIOS SESSÃO DE USUARIOS - SESSÃO DE USUARIOS -->
  <!-- SESSÃO DE USUARIOS - SESSÃO DE USUARIOS SESSÃO DE USUARIOS - SESSÃO DE USUARIOS -->
   <!-- SESSÃO DE USUARIOS - SESSÃO DE USUARIOS SESSÃO DE USUARIOS - SESSÃO DE USUARIOS -->
    <!-- SESSÃO DE USUARIOS - SESSÃO DE USUARIOS SESSÃO DE USUARIOS - SESSÃO DE USUARIOS -->
     <!-- SESSÃO DE USUARIOS - SESSÃO DE USUARIOS SESSÃO DE USUARIOS - SESSÃO DE USUARIOS -->
      <!-- SESSÃO DE USUARIOS - SESSÃO DE USUARIOS SESSÃO DE USUARIOS - SESSÃO DE USUARIOS -->



      <div class="user-config">
        <h2>Configurações de Usuários</h2>
            <button style='font-weight: 800;' type="button" id="add-user-button">+ ADICIONAR USUÁRIO</button>
            <ul id="users-list">
            <!-- Lista de usuários carregados via JavaScript -->
            </ul>

<!-- Modal para adicionar usuário -->
<div id="user-modal" class="modal">
    <div id="user-modal-content" class="modal-content">
        <span class="close-user-modal">&times;</span>
        <h2>CRIAR NOVO USUÁRIO</h2>
        <form id="add-user-form">
            <label for="username">Nome de Usuário:</label>
            <input type="text" id="username" maxlength="25" autocomplete="off" name="username" required>
            
            <label for="password">Senha:</label>
            <input type="password" maxlength="25" autocomplete="off" id="password" name="password" required>

            <label for="typeuser">Tipo de Usuário:</label>
            <select id="typeuser" name="typeuser" required>
                <option value="user">Usuário</option>
                <option value="admin">Administrador</option>
            </select>

            <label for="enterprise">Empresa:</label>
            <select id="enterprise" name="enterprise" required></select>

            <label for="resetpassword">
                <input type="checkbox" id="resetpassword" name="resetpassword"> Forçar reset de senha
            </label>

            <button type="submit" id="submit-user-btn">Confirmar</button>
        </form>
    </div>
</div>

<!-- Modal para editar usuário -->
<div id="edit-user-modal" class="modal">
    <div id="edit-user-modal-content"class="modal-content">
        <span class="close-edit-user-modal">&times;</span>
        <h2>EDITAR USUÁRIO</h2>
        <form id="edit-user-form">
            <label for="edit-username">Nome de Usuário:</label>
            <input type="text" id="edit-username" maxlength="25" autocomplete="off" name="edit-username" required>
            
            <label for="edit-password">Senha:</label>
            <input type="password" maxlength="25" autocomplete="off" id="edit-password" name="edit-password">

            <label for="edit-typeuser">Tipo de Usuário:</label>
            <select id="edit-typeuser" name="edit-typeuser" required>
                <option value="user">Usuário</option>
                <option value="admin">Administrador</option>
            </select>

            <label for="edit-enterprise">Empresa:</label>
            <select id="edit-enterprise" name="edit-enterprise" required>
                <!-- Empresas serão carregadas via JavaScript -->
            </select>

            <label for="edit-resetpassword">
                <input type="checkbox" id="edit-resetpassword" name="edit-resetpassword">
                Forçar reset de senha
            </label>

            <button type="submit">Salvar Alterações</button>
        </form>
    </div>
</div>

<!-- Modal de confirmação para excluir usuário -->
<div id="delete-user-modal" class="modal">
    <div class="modal-content">
        <span class="close-delete-user-modal">&times;</span>
        <h2>EXCLUIR USUÁRIO</h2>
        <p style="color: orange;">Está ação excluirá todos os registros relacionados ⚠️</p>
        <p>Tem certeza de que deseja excluir este usuário?</p>
        <button id="confirm-delete-user-button" style="background-color: green;
        width: 80px;" type="button">Sim</button>
        <button style="background-color: red;" id="cancel-delete-user-button" type="button">Cancelar</button>
    </div>
</div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const userModal = document.getElementById("user-modal");
    const addUserButton = document.getElementById("add-user-button");
    const closeUserModal = document.querySelector(".close-user-modal");
    const submitUserButton = document.getElementById("submit-user-btn");

    addUserButton.onclick = function() {
        userModal.style.display = "block";
        loadCompaniesToDropdown();
    };

    closeUserModal.onclick = function() {
        userModal.style.display = "none";
    };

    window.onclick = function(event) {
        if (event.target == userModal) {
            userModal.style.display = "none";
        }
    };

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

    const submitButton = document.getElementById("submit-user-btn");
    submitButton.disabled = true;

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
    .then(response => {
        console.log(response);
        return response.json();
    })
    .then(data => {
        submitButton.disabled = false;
        if (data.success) {
            alert(data.message);
            userModal.style.display = "none";
            loadUsers(); 
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        submitButton.disabled = false;
    });
});

    function loadUsers() {
        fetch('list_users.php')
            .then(response => response.json())
            .then(data => {
                const usersList = document.getElementById('users-list');
                usersList.innerHTML = '';
                if (data.length > 0) {
                    data.forEach(user => {
                        const li = document.createElement('li');
                        li.textContent = `Usuário: ${user.username} | Tipo: ${user.typeuser} | Empresa: ${user.enterprise}`;
                        
                        const editButton = document.createElement('button');
                        editButton.textContent = "Editar ✎";
                        editButton.style.marginLeft = '10px';
                        editButton.onclick = () => openEditModal(user);
                        li.appendChild(editButton);

                        const deleteButton = document.createElement('button');
                        deleteButton.textContent = "Excluir ⌫";
                        deleteButton.style.marginLeft = '10px';
                        deleteButton.type = "button";
                        deleteButton.onclick = () => openDeleteUserModal(user.username);
                        li.appendChild(deleteButton);

                        usersList.appendChild(li);
                    });
                } else {
                    usersList.innerHTML = '<li>Nenhum usuário encontrado.</li>';
                }
            })
            .catch(error => console.error('Erro ao carregar os usuários:', error));
    }

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


const editUserModal = document.getElementById("edit-user-modal");
const closeEditUserModal = document.getElementsByClassName("close-edit-user-modal")[0];


function openEditModal(user) {
    editUserModal.style.display = "block";

    
    document.getElementById("edit-username").value = user.username;
    document.getElementById("edit-password").value = "";
    document.getElementById("edit-typeuser").value = user.typeuser;
    
    fetch('list_companies.php')
        .then(response => response.json())
        .then(companies => {
            const enterpriseSelect = document.getElementById('edit-enterprise');
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


    document.getElementById("edit-resetpassword").checked = false;

    document.getElementById("edit-user-form").onsubmit = function(e) {
        e.preventDefault();
        updateUser(user.username);
    };
}


closeEditUserModal.onclick = function() {
    editUserModal.style.display = "none";
}

window.onclick = function(event) {
    if (event.target == editUserModal) {
        editUserModal.style.display = "none";
    }
}


function updateUser(originalUsername) {
    const username = document.getElementById("edit-username").value;
    const password = document.getElementById("edit-password").value;
    const typeuser = document.getElementById("edit-typeuser").value;
    const enterprise = document.getElementById("edit-enterprise").value;
    const resetpassword = document.getElementById("edit-resetpassword").checked ? 1 : 0;

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
            editUserModal.style.display = "none";
            loadUsers();
        } else {
            alert(data.message);
        }
    })
    .catch(error => console.error('Erro ao atualizar o usuário:', error));
}


loadUsers();

const deleteUserModal = document.getElementById("delete-user-modal");
    const closeDeleteUserModal = document.getElementsByClassName("close-delete-user-modal")[0];
    const confirmDeleteUserButton = document.getElementById("confirm-delete-user-button");
    const cancelDeleteUserButton = document.getElementById("cancel-delete-user-button");

    let usernameToDelete = null; 

    
    function openDeleteUserModal(username) {
        usernameToDelete = username; 
        deleteUserModal.style.display = "block";
    }

   
    closeDeleteUserModal.onclick = function() {
        deleteUserModal.style.display = "none";
        usernameToDelete = null;
    }

    cancelDeleteUserButton.onclick = function() {
        deleteUserModal.style.display = "none";
        usernameToDelete = null;
    }

    confirmDeleteUserButton.onclick = function() {
        if (usernameToDelete) {
            deleteUser(usernameToDelete);
        }
    }

    window.addEventListener("click", function(event) {
        if (event.target == deleteUserModal) {
            deleteUserModal.style.display = "none";
            usernameToDelete = null;
        }
    });

    function deleteUser(username) {
        fetch('delete_user.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'username': username
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                deleteUserModal.style.display = "none";
                loadUsers(); 
            } else {
                alert(data.message);
            }
        })
        .catch(error => console.error('Erro ao excluir o usuário:', error));
    }

   
    loadUsers();
});

</script>


</body>
</html>
