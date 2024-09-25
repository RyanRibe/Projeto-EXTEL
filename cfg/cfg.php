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

</body>
</html>
