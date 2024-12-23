document.addEventListener('DOMContentLoaded', function () {

    let selectedGroup = null;
    let currentFilter = 'todas';

    
    if (typeuser === 'admin') {
        const adminSection = document.getElementById('adminSection');
        const enterpriseSelect = document.getElementById('enterprise');
        const cfg = document.getElementById('cfg');
        const addVpnBtn = document.getElementById('addVpnBtn');
        const addGroupBtn = document.getElementById('addGroupBtn');
        const showUsersBtn = document.getElementById('showUsersBtn')
   
        adminSection.style.display = 'block';
        cfg.style.display = 'block';
        addVpnBtn.style.display = '';
        addGroupBtn.style.display = '';
        showUsersBtn.style.display = '';

        tables.forEach(table => {
            const option = document.createElement('option');
            option.value = table;
            option.textContent = table.toUpperCase(); 

            if (table === selectedEnterprise) {
                option.selected = true;
            }

            enterpriseSelect.appendChild(option);
        });
    }


     window.submitEnterprise = function() {
        const enterpriseSelect = document.getElementById('enterprise');
        const selectedEnterprise = enterpriseSelect.value;
    
        fetch('server.php?action=updateEnterprise', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ enterprise: selectedEnterprise })
        })
        .then(response => {
            if (!response.ok) {
                console.error(`Erro HTTP: ${response.status}`);
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                window.location.href = 'vpn'; 
            } else {
                console.error('Erro ao atualizar empresa:', data.error);
                alert('Erro ao atualizar empresa: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro ao atualizar empresa:', error);
            alert('Erro ao atualizar empresa. Verifique o console para mais detalhes.');
        });
    };

    const groupCheckboxes = document.querySelectorAll('input[name="group"]');
    if (groupCheckboxes.length > 0) {
        groupCheckboxes[0].checked = true;
        selectedGroup = groupCheckboxes[0].value;
    }


    window.filterByGroup = function(checkbox) {
        const checkboxes = document.querySelectorAll('input[name="group"]');
        
        // Desmarca todas as outras checkboxes
        checkboxes.forEach(cb => {
            if (cb !== checkbox) {
                cb.checked = false;
            }
        });
    
        // Se desmarcar a única checkbox marcada, a reverte para marcada
        const anyChecked = [...checkboxes].some(cb => cb.checked);
        if (!anyChecked) {
            checkbox.checked = true;
        }
    
        // Seu código para filtrar as VPNs
        const groupName = checkbox.value;
        if (checkbox.checked) {

            fetch('server.php?action=filterByGroup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ group: groupName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    renderVPNList(data.vpns);  // Renderiza a lista de VPNs filtrada
                } else {
                    alert('Erro ao filtrar VPNs: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro ao filtrar VPNs:', error);
                alert('Erro ao filtrar VPNs. Verifique o console para mais detalhes.');
            });
        }
    };


    
    
    const addVpnBtn = document.getElementById('addVpnBtn');
    const fileInput = document.getElementById('fileInput');
    const vpnList = document.getElementById('vpnList');
    const deleteModal = document.getElementById('deleteModal');
    const deleteForm = document.getElementById('deleteForm');
    const passwordInput = document.getElementById('password');
    const vpnIdToDelete = document.getElementById('vpnIdToDelete');
    const linkModal = document.getElementById('linkModal');
    const linkForm = document.getElementById('linkForm');
    const userNameInput = document.getElementById('userName');
    const vpnIdToLink = document.getElementById('vpnIdToLink');
    const groupObsModal = document.getElementById('GroupObsModal');
    const groupObsForm = document.getElementById('gpobsForm');
    const closeGroupObsModal = document.querySelector('.close-gpobs');
    const addGroupBtn = document.getElementById('addGroupBtn');
    const obsModal = document.getElementById('ObsModal');
    const obsModalContent = document.getElementById('groupObservationsContent');
    const closeObsModal = document.querySelector('.close-gpobsexibe');
    const searchInput = document.getElementById('searchInput');
    const isAdmin = typeuser === 'admin';

    const filterButtons = {
        todas: document.getElementById('todas'),
        disponiveis: document.getElementById('disponiveis'),
        usando: document.getElementById('usando'),
        encerradas: document.getElementById('encerradas')
    };

    let vpnData = [];
    
    searchInput.addEventListener('input', function () {
        const searchTerm = searchInput.value.toLowerCase();
        renderVPNList(searchTerm);
    });

    function showDeleteGroupModal(groupName) {
        const deleteModal = document.getElementById('deleteGroupModal');
        document.getElementById('groupNameToDelete').value = groupName;
    
        deleteModal.style.display = 'block'; // Exibe o modal de confirmação
    
        // Adiciona o evento de confirmação da exclusão
        document.getElementById('confirmDeleteBtn').addEventListener('click', confirmDeleteGroup);
    
        // Fechar o modal ao clicar no botão cancelar
        document.getElementById('cancelDeleteBtn').addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
        
        // Fechar modal ao clicar no "x"
        document.querySelector('.close-delete-modal').addEventListener('click', () => {
            deleteModal.style.display = 'none';
        });
    }

    function confirmDeleteGroup() {
        const groupName = document.getElementById('groupNameToDelete').value;
    
        fetch('server.php?action=deleteGroup', {
            method: 'POST',
            body: JSON.stringify({ groupName }),
            headers: { 'Content-Type': 'application/json' }
        })
        .then(response => response.text())  // Mude para .text() para capturar tudo
.then(data => {
    try {
        let jsonData = JSON.parse(data);  // Tenta converter para JSON
        if (jsonData.success) {
            alert('Grupo excluído com sucesso.');
                location.reload(); // Recarrega a página após a exclusão
            } else {
                alert('Erro ao excluir o grupo: ' + jsonData.error);
            }
        } catch (e) {
            console.error('Erro ao processar resposta: ', data);  // Mostra resposta completa
            alert('Erro inesperado, verifique o console.');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        alert('Erro ao excluir o grupo. Verifique o console para mais detalhes.');
    });
}
    
    
    
    function showGroupObservations(groupName) {
        fetch(`server.php?action=getGroupDetails&group=${encodeURIComponent(groupName)}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const groupObservation = data.groupObservation || 'Sem observações';
    
                    obsModalContent.innerHTML = `<p>Grupo: ${groupName}</p>`;
                        
                    // Cria um campo <textarea> para exibir a observação com quebras de linha
                    const observationTextarea = document.createElement('textarea');
                    observationTextarea.value = groupObservation;
                    observationTextarea.style.width = '100%';
                    observationTextarea.style.height = '275px';
                    observationTextarea.readOnly = true;
                    observationTextarea.style.resize = 'none';
                    obsModalContent.appendChild(observationTextarea);
                    
                    // Verificar se o usuário é admin
                     if (isAdmin) {
                        const editButton = document.createElement('button');
                        editButton.textContent = "Editar ✎";
                        editButton.style.marginTop = "10px";
                        editButton.addEventListener('click', () => {
                            showEditGroupModal(groupName, groupObservation);
                            obsModal.style.display = 'none';
                        });

                        const deleteButton = document.createElement('button');
                        deleteButton.textContent = "Excluir ⌫";
                        deleteButton.style.marginTop = "10px";
                        deleteButton.style.backgroundColor = "red";
                        deleteButton.style.color = "white";
                        deleteButton.style.marginLeft = "270px"; 
                        deleteButton.addEventListener('click', () => {
                            showDeleteGroupModal(groupName);
                            obsModal.style.display = 'none';
                        });
    
                        obsModalContent.appendChild(editButton);
                        obsModalContent.appendChild(deleteButton);

                    }
    
                    obsModal.style.display = 'block'; 
                } else {
                    alert('Erro ao buscar detalhes do grupo: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro ao buscar detalhes do grupo:', error);
                alert('Erro ao buscar detalhes do grupo. Verifique o console para mais detalhes.');
            });
    }
    
    closeObsModal.addEventListener('click', () => {
        obsModal.style.display = 'none';
    });

    window.onclick = function (event) {
        if (event.target === obsModal) {
            obsModal.style.display = 'none';
        }
    };

    
    
    // Atualizar o listener dos botões de observação
    const groupObsButtons = document.querySelectorAll('#GroupObs');
    groupObsButtons.forEach(button => {
        button.addEventListener('click', function() {
            const groupName = this.getAttribute('data-group');
            showGroupObservations(groupName);
        });
    });

    addVpnBtn.addEventListener('click', () => {
        fileInput.click();
    });

    window.showGroupObsModal = function() {
        groupObsModal.style.display = 'block';
    };

    closeGroupObsModal.addEventListener('click', () => {
        groupObsModal.style.display = 'none';
    });

    
        // Captura o evento de submit do modal
    groupObsForm.addEventListener('submit', function(event) {
        event.preventDefault();
    
        const groupName = document.getElementById('NameGroup').value;
        const observation = document.getElementById('ObsCampo').value;

        if (!groupName || !observation) {
            alert('Por favor, preencha todos os campos.');
            return;
        }

    
            // Envia as informações do grupo para o servidor
            fetch('server.php?action=addGroup', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    group: groupName,
                    observation: observation
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Grupo adicionado com sucesso.');
                    window.location.reload(); // Recarrega a página para atualizar a lista de grupos
                } else {
                    alert('Erro ao adicionar grupo: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro ao adicionar grupo:', error);
                alert('Erro ao adicionar grupo. Verifique o console para mais detalhes.');
            });
    
            groupObsModal.style.display = 'none'; // Fecha o modal
        });
    
        // Listener para abrir o modal quando um novo grupo for criado
        addGroupBtn.addEventListener('click', () => {
            groupObsModal.style.display = 'block';
        });

        fileInput.addEventListener('change', () => {
            const files = fileInput.files;
            if (files.length > 0) {
                const selectedGroupElement = document.querySelector('input[name="group"]:checked');
        
                if (!selectedGroupElement) {
                    alert('Por favor, selecione um grupo antes de adicionar a VPN.');
                    return;
                }
        
                const selectedGroup = selectedGroupElement.value;
        
                fetch(`server.php?action=getGroupDetails&group=${encodeURIComponent(selectedGroup)}`)
                    .then(response => response.text()) // Capture como texto primeiro
                    .then(text => {
                        try {
                            const data = JSON.parse(text); // Tente analisar o JSON
                            if (data.success) {
                                const groupObservation = data.groupObservation;
        
                                if (!groupObservation) {
                                    alert('As informações de expiração e observação do grupo não foram encontradas.');
                                    return;
                                }
        
                                const uploadPromises = [];
        
                                Array.from(files).forEach(file => {
                                    const formData = new FormData();
                                    formData.append('vpnFile', file);
                                    formData.append('group', selectedGroup);
                                    formData.append('GroupObservation', groupObservation);
        
                                    const uploadPromise = fetch('server.php?action=addVPN', {
                                        method: 'POST',
                                        body: formData
                                    })
                                    .then(response => response.text()) // Capture como texto
                                    .then(text => {
                                        try {
                                            return JSON.parse(text); // Tente analisar o JSON
                                        } catch (jsonError) {
                                            console.error('Erro de análise JSON:', text);
                                            throw new Error('Erro ao processar resposta do servidor. Consulte o console para detalhes.');
                                        }
                                    });
        
                                    uploadPromises.push(uploadPromise);
                                });
        
                                Promise.all(uploadPromises)
                                .then(results => {
                                    const errors = results.filter(result => !result.success);
                            
                                    if (errors.length === 0) {
                                        alert('Todas as VPNs foram adicionadas com sucesso.');
                                    } else {
                                        const uniqueErrors = new Set(errors.map(e => e.error));
                                        alert(`Algumas VPNs não foram adicionadas: ${Array.from(uniqueErrors).join(', ')}`);
                                    }
                            
                                    fetchVPNs();
                                })
                                .catch(error => {
                                    console.error('Erro ao adicionar VPNs:', error);
                                    alert('Erro ao adicionar VPNs. Verifique o console para mais detalhes.');
                                });
                            } else {
                                alert('Erro ao recuperar informações do grupo: ' + data.error);
                            }
                        } catch (jsonError) {
                            console.error('Resposta inesperada do servidor:', text);
                            alert('Erro inesperado do servidor. Verifique o console para mais detalhes.');
                        }
                    })
                    .catch(error => {
                        console.error('Erro ao recuperar informações do grupo:', error);
                        alert('Erro ao recuperar informações do grupo. Verifique o console para mais detalhes.');
                    });
            }
        });

        function showEditGroupModal(groupName, groupObservation) {
            const editModal = document.getElementById('EditGroupObsModal');
            const groupNameInput = document.getElementById('editNameGroup');
            const observationInput = document.getElementById('editObsCampo');
            const originalGroupInput = document.getElementById('originalGroupName'); 

            groupNameInput.value = groupName;
            observationInput.value = groupObservation;
            originalGroupInput.value = groupName; 
        
            editModal.style.display = 'block'; 

        }

        document.querySelector('.close-edit-gpobs').addEventListener('click', () => {
            EditGroupObsModal.style.display = 'none';
        });  

        function submitEditGroupForm(event) {
            event.preventDefault(); 
        

            const updatedGroupName = document.getElementById('editNameGroup').value;
            const observation = document.getElementById('editObsCampo').value;
            const originalGroupName = document.getElementById('originalGroupName').value;
        
            if (!updatedGroupName || !observation) {
                alert('Preencha todos os campos antes de salvar.');
                return;
            }

            fetch('server.php?action=updateGroupObservation', {
                method: 'POST',
                body: JSON.stringify({
                    action: 'updateGroupObservation', 
                    originalGroupName, 
                    updatedGroupName, 
                    observation
                }),
                headers: { 'Content-Type': 'application/json' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Observação atualizada com sucesso.');
                    location.reload(); // Recarrega a página para refletir as alterações
                } else {
                    alert('Erro ao atualizar observação: ' + data.error);
                }
            })
            .catch(error => {
                console.error('Erro ao atualizar observação:', error);
                alert('Erro ao atualizar observação. Verifique o console para mais detalhes.');
            });
        }

        document.getElementById('editGpobsForm').addEventListener('submit', submitEditGroupForm);
        
            
        
    currentFilter = 'todas';

    fetchVPNs();   

   
    function fetchVPNs() {
        fetch('server.php?action=listVPNs')
            .then(response => response.json())
            .then(data => {
                // Filtra e remove VPNs com nomes nulos ou vazios
                vpnData = data.filter(vpn => vpn.filename && vpn.filename.trim() !== '').sort((a, b) => {
                    const nameA = a.filename.toLowerCase();
                    const nameB = b.filename.toLowerCase();
                    return nameA < nameB ? -1 : (nameA > nameB ? 1 : 0);
                });
                renderVPNList();
            })
            .catch(error => {
                console.error('Erro ao buscar VPNs:', error);
                alert('Erro ao buscar VPNs. Verifique o console para mais detalhes.');
            });
    }
    
    window.filterByGroup = function(checkbox) {
        const checkboxes = document.querySelectorAll('input[name="group"]');

        // Desmarca todas as outras checkboxes
        checkboxes.forEach(cb => {
            if (cb !== checkbox) {
                cb.checked = false;
            }
        });

        if (checkbox.checked) {
            // Armazena o grupo selecionado
            selectedGroup = checkbox.value;

            // Sempre define o filtro como 'todas' ao selecionar um grupo
            currentFilter = 'todas';

            // Renderiza as VPNs filtradas pelo grupo selecionado
            renderVPNList();
        }
    };

    function formatDate(inputDate) {    
        const months = {
            'Jan': '01', 'Feb': '02', 'Mar': '03', 'Apr': '04',
            'May': '05', 'Jun': '06', 'Jul': '07', 'Aug': '08',
            'Sep': '09', 'Oct': '10', 'Nov': '11', 'Dec': '12'
        };
    
        // Remove espaços extras e divide a string
        const parts = inputDate.trim().split(/\s+/); // Usando regex para dividir por múltiplos espaços
    
        if (parts.length !== 4) {
            throw new Error('Formato de data inválido');
        }
    
        const [month, day, time, year] = parts;
        const [hour, minute] = time.split(':');
    
        // Verificações adicionais
        if (!months[month] || isNaN(day) || isNaN(hour) || isNaN(minute) || !year) {
            throw new Error('Formato de data inválido');
        }
    
        const formattedMonth = months[month];
        const formattedDay = day.padStart(2, '0'); // Garante que o dia tenha dois dígitos
        const formattedYear = year;
    
        return `${formattedDay}-${formattedMonth}-${formattedYear} ${hour}:${minute}`;
    }
    
    
    function renderVPNList(searchTerm = '') {
        vpnList.innerHTML = '';
        
        const dataToRender = vpnData.filter(vpn => {
            const isInGroup = vpn.group === selectedGroup;
            if (!isInGroup) return false;
    
            const filename = vpn.filename.toLowerCase();
            const username = vpn.user_name ? vpn.user_name.toLowerCase() : '';
    
            // Verifica se o termo de busca está presente no nome da chave ou no nome do usuário
            const matchesSearch = filename.includes(searchTerm) || username.includes(searchTerm);
    
            // Aplica filtros de status
            const statusFilter = (currentFilter === 'todas') ||
                                 (currentFilter === 'disponiveis' && vpn.status === 'disponivel') ||
                                 (currentFilter === 'usando' && vpn.status === 'em_uso') ||
                                 (currentFilter === 'encerradas' && vpn.status === 'desativado');
    
            return matchesSearch && statusFilter;
        });
    
        let total = dataToRender.length;
        document.getElementById('quantidadeFiltro').textContent = total;
    
        dataToRender.forEach(vpn => {
            const filenameWithoutExtension = vpn.filename.replace(/\.[^/.]+$/, "");
            const vpnItem = document.createElement('div');
            vpnItem.className = 'vpn-item';
    
            const expirationDate = vpn.ExpirationDate && vpn.ExpirationDate.trim() !== '' 
                ? formatDate(vpn.ExpirationDate) 
                : 'Não disponível';
    
                vpnItem.innerHTML = `
                <span id="chavename">${filenameWithoutExtension}</span>
                <span>Expira em: <span id="fileexpiration">${expirationDate}</span></span>
                ${vpn.status === 'disponivel' ? `<span id="dispo">Disponível</span>` : ''}
                ${vpn.status === 'em_uso' ? `<span id="emuso">Em uso</span>` : ''}
                ${vpn.status === 'desativado' ? `<span id="desatv">Desativado</span>` : ''}
                ${vpn.status === 'em_uso' || vpn.status === 'desativado' ? `<span id="usernome">${vpn.user_name}</span>` : `<span id="usernome">&nbsp;</span>`}
            `;
    
            const buttonContainer = document.createElement('div');
            buttonContainer.className = 'button-container';
    
            if (vpn.status === 'disponivel') {
                buttonContainer.innerHTML += `<button id="down" title="Download VPN" onclick="openLinkModal(${vpn.id}, '${vpn.filename}')"></button>`;
            }
    
            if (vpn.status === 'em_uso') {
                buttonContainer.innerHTML += `<button id="down2" title="Download VPN" onclick="openVpnObsModal(${vpn.id}, '${vpn.filename}')"></button>`;
                buttonContainer.innerHTML += `<button id="desactive" title="Desativar VPN" onclick="deactivateVPN(${vpn.id})"></button>`;
            }
    
            buttonContainer.innerHTML += `<button id="obsvpn" title="Observações da Chave" onclick="showObservationModal(${vpn.id})"></button>`;
    
            if (typeuser === 'admin') {
                buttonContainer.innerHTML += `<button id="x" title="Excluir VPN" onclick="showDeleteModal(${vpn.id})">X</button>`;
            }
    
            vpnItem.appendChild(buttonContainer);
            vpnList.appendChild(vpnItem);
        });
    }
    
    window.showObservationModal = function (vpnId) {
        const obsModal = document.getElementById('ObsModal');
        const groupObservationsContent = document.getElementById('groupObservationsContent');
    
        // Limpa o conteúdo anterior
        groupObservationsContent.innerHTML = 'Carregando...';
    
        // Exibe o modal
        obsModal.style.display = 'block';
    
        // Faz uma requisição para buscar as informações da chave
        fetch(`server.php?action=getVpnDetails&vpnId=${vpnId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Prepara o conteúdo com as informações recebidas
                    const content = `
                        <p><strong style="color: #b44d27;">EMPRESA:</strong> ${data.empresa || ''}</p>
                        <p><strong style="color: #b44d27;">NOME DO ARQUIVO:</strong> ${data.nome || ''}</p>
                        <p><strong style="color: #b44d27;">EXPIRAÇÃO:</strong> ${data.expiracao || ''}</p>
                        <p><strong style="color: #b44d27;">GRUPO DA CHAVE:</strong> ${data.grupo || ''}</p>
                        <p><strong style="color: #b44d27;">STATUS:</strong> ${data.status || ''}</p>
                        <p><strong style="color: #b44d27;">USUÁRIO VINCULADO:</strong> ${data.usuario || ''}</p>
                        <p><strong style="color: #b44d27;">DATA DO VINCULO:</strong> ${data.primeiro_download || ''}</p>
                        <p><strong style="color: #b44d27;">DATA DO ÚLTIMO DOWNLOAD:</strong> ${data.ultimo_download || ''}</p>
                        <p><strong style="color: #b44d27;">MOTIVO PARA RE-DOWNLOAD:</strong> ${data.motivo_redownload || ''}</p>
                        <p><strong style="color: #b44d27;">DATA DA DESATIVAÇÃO:</strong> ${data.data_desativacao || ''}</p>
                    `;
                    groupObservationsContent.innerHTML = content;
                } else {
                    groupObservationsContent.innerHTML = 'Nenhuma informação disponível.';
                }
            })
            .catch(error => {
                console.error('Erro ao buscar informações da chave:', error);
                groupObservationsContent.innerHTML = 'Erro ao carregar informações.';
            });
    };
    
    // Gerencia o fechamento do modal de observações
    document.querySelector('.close-gpobsexibe').addEventListener('click', () => {
        document.getElementById('ObsModal').style.display = 'none';
    });
    
// Variável de controle para evitar múltiplas submissões
let isSubmitting = false;

window.openLinkModal = function (vpnId, filename) {
    // Abre o modal de vinculação
    linkModal.style.display = 'block';
    vpnIdToLink.value = vpnId;
    userNameInput.value = '';

    // Reseta o controle de submissão quando o modal é aberto
    isSubmitting = false;

    // Define o comportamento do form para realizar o vínculo e depois o download
    linkForm.onsubmit = function (event) {
        event.preventDefault();

        // Evita múltiplas submissões
        if (isSubmitting) return;
        isSubmitting = true;

        const formData = new FormData(linkForm);

        fetch('server.php?action=linkVPN', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('VPN vinculada com sucesso.');
                linkModal.style.display = 'none';

                // Atualiza a lista de VPNs com as informações mais recentes
                fetchVPNs();

                // Realiza o download após o vínculo ser bem-sucedido
                initiateVPNDownload(filename);
            } else {
                alert('Erro ao vincular VPN: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro ao vincular VPN:', error);
            alert('Erro ao vincular VPN. Verifique o console para mais detalhes.');
        })
        .finally(() => {
            // Reseta o controle de submissão após o processo completo
            isSubmitting = false;
        });
    };
};

window.openVpnObsModal = function (vpnId, filename) {
    const vpnObsModal = document.getElementById('VpnObsModal');
    const vpnObsForm = document.getElementById('vpnobsForm');
    const vpnToObs = document.getElementById('vpnToObs');
    const vpnObsCampo = document.getElementById('VpnObsCampo');
    
    // Limpa o campo de observação
    vpnObsCampo.value = '';

    // Limpa o campo hidden com o ID da VPN
    vpnToObs.value = vpnId;

    // Abre o modal de observações
    vpnObsModal.style.display = 'block';

    // Remove o listener existente se houver
    vpnObsForm.removeEventListener('submit', handleFormSubmit);

    // Adiciona um novo listener para o submit
    vpnObsForm.addEventListener('submit', handleFormSubmit, { once: true });

    function handleFormSubmit(event) {
        event.preventDefault();

        // Captura o valor da observação
        const observation = vpnObsCampo.value;

        if (!observation) {
            alert('Por favor, preencha a observação.');
            return;
        }

        // Envia a observação e a data de download para o servidor
        fetch('server.php?action=saveObservation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                vpnId: vpnId,
                observation: observation
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('Observação salva com sucesso.');
                vpnObsModal.style.display = 'none';
                // Realiza o download após salvar a observação e a data de download
                initiateVPNDownload(filename);
            } else {
                alert('Erro ao salvar a observação: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro ao salvar a observação:', error);
            alert('Erro ao salvar a observação. Verifique o console para mais detalhes.');
        });
    }
};

document.querySelector('.close-vpnobs').addEventListener('click', () => {
    document.getElementById('VpnObsModal').style.display = 'none';
});

    window.downloadVPN = function (filename) {
        window.location.href = 'server.php?action=downloadVPN&filename=' + encodeURIComponent(filename);
    };
    

    window.initiateVPNDownload = function (filename) {
        window.location.href = 'server.php?action=downloadVPN&filename=' + encodeURIComponent(filename);
    };
    
    

    window.showDeleteModal = function (vpnId) {
        deleteModal.style.display = 'block';
        vpnIdToDelete.value = vpnId;
        passwordInput.value = '';
    };

    deleteForm.addEventListener('submit', function (event) {
        event.preventDefault();

        
        console.log('Enviando senha:', passwordInput.value);

        const formData = new FormData(deleteForm);
        formData.append('password', passwordInput.value);

        fetch('server.php?action=deleteVPN', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('VPN excluída com sucesso.');
                fetchVPNs();
                deleteModal.style.display = 'none';
            } else {
                alert('Erro ao excluir VPN: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro ao excluir VPN:', error);
            alert('Erro ao excluir VPN. Verifique o console para mais detalhes.');
        });
    });

    window.showLinkModal = function (vpnId) {
        linkModal.style.display = 'block';
        vpnIdToLink.value = vpnId;
        userNameInput.value = '';
    };

// Função para abrir o modal de desativação
window.deactivateVPN = function (vpnId) {
    const deactivateModal = document.getElementById('deactivateModal');
    const vpnIdToDeactivate = document.getElementById('vpnIdToDeactivate');

    // Armazena o ID da VPN que será desativada
    vpnIdToDeactivate.value = vpnId;

    // Exibe o modal de desativação
    deactivateModal.style.display = 'block';
};

// Gerencia o fechamento do modal de desativação
document.querySelector('.close-deactivate').addEventListener('click', () => {
    document.getElementById('deactivateModal').style.display = 'none';
});

// Quando o botão "Confirmar" é clicado no primeiro modal
document.getElementById('confirmDeactivateBtn').addEventListener('click', function () {
    const vpnId = document.getElementById('vpnIdToDeactivate').value;
    const adDeactivateModal = document.getElementById('adDeactivateModal');
    const vpnIdToAdDeactivate = document.getElementById('vpnIdToAdDeactivate');

    // Fecha o primeiro modal
    document.getElementById('deactivateModal').style.display = 'none';

    // Abre o segundo modal e armazena o ID da VPN
    vpnIdToAdDeactivate.value = vpnId;
    adDeactivateModal.style.display = 'block';
});

// Gerencia o fechamento do segundo modal
document.querySelector('.close-ad-deactivate').addEventListener('click', () => {
    document.getElementById('adDeactivateModal').style.display = 'none';
});

// Quando o usuário escolhe desativar o usuário do AD
document.getElementById('confirmAdDeactivateBtn').addEventListener('click', function () {
    const vpnId = document.getElementById('vpnIdToAdDeactivate').value;
    const confirmButton = document.getElementById('confirmAdDeactivateBtn');
    const cancelButton = document.getElementById('cancelAdDeactivateBtn');

    // Desativa ambos os botões para evitar múltiplos cliques
    confirmButton.disabled = true;
    cancelButton.disabled = true;

    // Realiza a requisição de desativação da VPN e do usuário do AD
    fetch('server.php?action=deactivateVPN', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ vpnId: vpnId, deactivateAdUser: true })
    })
    .then(response => response.text()) // Alterado para .text() temporariamente
    .then(text => {
        console.log('Resposta do servidor:', text); // Verifique o conteúdo real
        const data = JSON.parse(text); // Parse manualmente após verificar
        if (data.success) {
            alert('Foi aberto um chamado para desativar a VPN e o usuário do AD. Acompanhe o andamento através do portal de chamados: extel.tomticket.com');
            fetchVPNs(); // Atualiza a lista de VPNs
        } else {
            alert('Erro ao desativar VPN: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro ao desativar VPN:', error);
        alert('Erro ao desativar VPN. Verifique o console para mais detalhes.');
    })    
    .finally(() => {
        // Fecha o modal após a operação e reativa os botões
        document.getElementById('adDeactivateModal').style.display = 'none';
        confirmButton.disabled = false;
        cancelButton.disabled = false;
    });
});

// Quando o usuário escolhe não desativar o usuário do AD
document.getElementById('cancelAdDeactivateBtn').addEventListener('click', function () {
    const vpnId = document.getElementById('vpnIdToAdDeactivate').value;
    const confirmButton = document.getElementById('confirmAdDeactivateBtn');
    const cancelButton = document.getElementById('cancelAdDeactivateBtn');

    // Desativa ambos os botões para evitar múltiplos cliques
    confirmButton.disabled = true;
    cancelButton.disabled = true;

    // Realiza a requisição de desativação da VPN apenas
    fetch('server.php?action=deactivateVPN', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ vpnId: vpnId, deactivateAdUser: false })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Foi criado um chamado para desativação da chave, acompanhe o andamento através do portal de chamados: extel.tomticket.com');
            fetchVPNs(); // Atualiza a lista de VPNs
        } else {
            alert('Erro ao desativar VPN: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Erro ao desativar VPN:', error);
        alert('Erro ao desativar VPN. Verifique o console para mais detalhes.');
    })
    .finally(() => {
        // Fecha o modal após a operação e reativa os botões
        document.getElementById('adDeactivateModal').style.display = 'none';
        confirmButton.disabled = false;
        cancelButton.disabled = false;
    });
});

    
    // Quando o botão "Cancelar" é clicado
    document.getElementById('cancelDeactivateBtn').addEventListener('click', function () {
        document.getElementById('deactivateModal').style.display = 'none';
    });

    filterButtons.todas.addEventListener('click', () => {
        currentFilter = 'todas';
        renderVPNList();
    });

    filterButtons.disponiveis.addEventListener('click', () => {
        currentFilter = 'disponiveis';
        renderVPNList();
    });

    filterButtons.usando.addEventListener('click', () => {
        currentFilter = 'usando';
        renderVPNList();
    });

    filterButtons.encerradas.addEventListener('click', () => {
        currentFilter = 'encerradas';
        renderVPNList();
    });

    

    document.querySelector('.close').addEventListener('click', () => {
        deleteModal.style.display = 'none';
        passwordInput.value = '';
    });

    document.querySelector('.close-link').addEventListener('click', () => {
        linkModal.style.display = 'none';
        userNameInput.value = '';
    });

    window.onclick = function (event) {
        if (event.target === deleteModal) {
            deleteModal.style.display = 'none';
            passwordInput.value = '';
        }
        if (event.target === linkModal) {
            linkModal.style.display = 'none';
            userNameInput.value = '';
        }
    };

    console.log('ExpirationDate:', vpn.ExpirationDate);

});

function updateStatus() {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', '/update_status.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
    xhr.send();
}

// Atualizar o status a cada 30 segundos
setInterval(updateStatus, 3000);

function checkStatus() {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', '/check_status.php', true);
    xhr.onload = function () {
        if (xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response.status1) {
                console.log('O usuário está online.');
            } else {
                console.log('O usuário está offline.');
            }
        }
    };
    xhr.send();
}

// Verificar o status a cada minuto
setInterval(checkStatus, 6000);

let timeout;
const inactivityTime = 5 * 60 * 1000; // Tempo de inatividade em milissegundos (5 minutos)

// Função que será chamada após o período de inatividade
function logoutUser() {
    window.location.href = 'logout.php'; // Redireciona para o script de logout
}

// Reinicia o temporizador de inatividade sempre que há atividade
function resetTimer() {
    clearTimeout(timeout);
    timeout = setTimeout(logoutUser, inactivityTime);
}

// Configura os eventos para detectar atividade do usuário
window.onload = resetTimer; // Inicia o temporizador quando a página é carregada
document.onmousemove = resetTimer;
document.onkeypress = resetTimer;
document.onscroll = resetTimer;

window.addEventListener('beforeunload', function (e) {
    // Faz uma requisição para marcar o usuário como offline
    navigator.sendBeacon('mark_offline.php');
});

function markOffline() {
    navigator.sendBeacon('mark_offline.php');
}

window.addEventListener('beforeunload', markOffline);
window.addEventListener('pagehide', markOffline);
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'hidden') {
        markOffline();
    }
});


function loadUserLogs() {

    fetch('server.php?action=getUserLogs')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const userTableBody = document.querySelector('#userTable tbody');
                userTableBody.innerHTML = ''; 

                data.users.forEach(user => {
                    const row = document.createElement('tr');

                    // Verifica o status e define a bolinha correspondente
                    const statusDisplay = user.status1 === 'offline'
                        ? `<span class="status-icon offline"></span> Offline (Tempo conectado: ${user.time_online || 'N/A'})`
                        : `<span class="status-icon online"></span> Online`;

                    row.innerHTML = `
                        <td>${user.username}</td>
                        <td>${user.useragent}</td>
                        <td>${user.lastlogindate}</td>
                        <td>${user.lastip}</td>
                        <td>${statusDisplay}</td>
                    `;
                    userTableBody.appendChild(row);
                });
            } else {
                alert('Erro ao carregar os logs de usuários: ' + data.error);
            }
        })
        .catch(error => {
            console.error('Erro ao carregar os logs de usuários:', error);
        });
}


document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('userModal');
    const showUsersBtn = document.getElementById('showUsersBtn');
    const closeUsersBtn = document.querySelector('.closeUsersBtn');

    showUsersBtn.onclick = function() {
        modal.style.display = 'block';
        loadUserLogs(); 
    }

    closeUsersBtn.onclick = function() {
        modal.style.display = 'none';
    }

    window.onclick = function(event) {
        if (event.target == modal) {
            modal.style.display = 'none';
        }
    }
});
