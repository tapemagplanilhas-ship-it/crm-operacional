<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
?>

<div class="page-header">
    <h2><i class="fas fa-user-friends"></i> Clientes</h2>
    <div class="page-actions">
        <button class="btn-primary" onclick="abrirModalCliente()">
            <i class="fas fa-user-plus"></i> Novo Cliente
        </button>
        <button class="btn-success" onclick="abrirModalVendaRapida()">
            <i class="fas fa-bolt"></i> Venda Rápida
        </button>
    </div>
</div>

<div class="filters">
    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="search-cliente" placeholder="Buscar cliente por nome..." 
               onkeyup="buscarClientes()">
    </div>
</div>

<!-- Tabela de Clientes com ordenação -->
<div class="table-responsive">
    <table class="data-table sortable" id="tabela-clientes">
        <thead>
            <tr>
                <th class="sortable-header" data-sort="nome" data-order="">
                    Nome <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="telefone" data-order="">
                    Contato <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="ultima_venda" data-order="desc">
                    Última Venda <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="total_gasto" data-order="desc">
                    Total Gasto <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="media_gastos" data-order="desc">
                    Média <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="taxa_fechamento" data-order="desc">
                    Taxa Fechamento <i class="fas fa-sort"></i>
                </th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody id="clientes-body">
            <!-- Conteúdo será carregado via JavaScript -->
            <tr>
                <td colspan="7" class="text-center">Carregando clientes...</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="loading-clientes" class="text-center" style="display: none;">
    <p><i class="fas fa-spinner fa-spin"></i> Carregando...</p>
</div>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Configurar busca
    const searchInput = document.getElementById('search-cliente');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            buscarClientes(this.value);
        });
    }
    
    // Função para buscar clientes
    async function buscarClientes(termo) {
        try {
            const response = await fetch('api/clientes.php');
            const data = await response.json();
            
            if (data.success) {
                const clientesFiltrados = data.data.filter(cliente => 
                    !termo || termo.length < 2 || 
                    cliente.nome.toLowerCase().includes(termo.toLowerCase()) ||
                    (cliente.telefone && cliente.telefone.includes(termo)) ||
                    (cliente.email && cliente.email.toLowerCase().includes(termo.toLowerCase()))
                );
                
                atualizarTabelaClientes(clientesFiltrados);
            }
        } catch (error) {
            console.error('Erro:', error);
        }
    }
    
    // Função para atualizar tabela
    function atualizarTabelaClientes(clientes) {
        const tbody = document.getElementById('clientes-body');
        if (!tbody) return;
        
        if (!clientes || clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nenhum cliente encontrado</td></tr>';
            return;
        }
        
        tbody.innerHTML = clientes.map(cliente => `
            <tr>
                <td>
                    <strong>${escapeHtml(cliente.nome)}</strong>
                    ${cliente.observacoes ? `<div class="text-muted">${escapeHtml(cliente.observacoes.substring(0, 50))}...</div>` : ''}
                </td>
                <td>
                    ${cliente.telefone ? `<div><i class="fas fa-phone"></i> ${escapeHtml(cliente.telefone)}</div>` : ''}
                    ${cliente.email ? `<div><i class="fas fa-envelope"></i> ${escapeHtml(cliente.email)}</div>` : ''}
                </td>
                <td>${cliente.ultima_venda && cliente.ultima_venda != '0000-00-00' ? formatarData(cliente.ultima_venda) : 'Nunca'}</td>
                <td><strong>${formatarMoeda(cliente.total_gasto)}</strong></td>
                <td>${formatarMoeda(cliente.media_gastos)}</td>
                <td>
                    <span class="badge ${cliente.taxa_fechamento >= 50 ? 'badge-success' : cliente.taxa_fechamento >= 25 ? 'badge-warning' : 'badge-danger'}">
                        ${cliente.taxa_fechamento ? parseFloat(cliente.taxa_fechamento).toFixed(1) + '%' : '0%'}
                    </span>
                </td>
                <td class="text-center">
                    <div class="actions-menu">
                        <button class="actions-toggle" onclick="toggleActionsMenu(this)">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                        <div class="actions-dropdown">
                            <button onclick="abrirModalCliente(${cliente.id})">
                                <i class="fas fa-edit"></i> Editar
                            </button>
                            <button onclick="abrirModalVenda(${cliente.id}, '${escapeHtml(cliente.nome).replace(/'/g, "\\'")}')">
                                <i class="fas fa-shopping-cart"></i> Nova Venda
                            </button>
                            <button class="danger" onclick="excluirCliente(${cliente.id})">
                                <i class="fas fa-trash"></i> Excluir
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
        `).join('');
    }
    
    function formatarData(dateString) {
        if (!dateString || dateString === '0000-00-00') return '';
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    }
    
    function formatarMoeda(valor) {
        return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }
    
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    // Função para menu de ações
    window.toggleActionsMenu = function(button) {
        const dropdown = button.nextElementSibling;
        const isShowing = dropdown.classList.contains('show');
        
        // Fechar todos
        document.querySelectorAll('.actions-dropdown.show').forEach(d => {
            d.classList.remove('show');
        });
        
        // Abrir/fechar atual
        if (!isShowing) {
            dropdown.classList.add('show');
            
            setTimeout(() => {
                const closeDropdown = (e) => {
                    if (!dropdown.contains(e.target) && e.target !== button) {
                        dropdown.classList.remove('show');
                        document.removeEventListener('click', closeDropdown);
                    }
                };
                document.addEventListener('click', closeDropdown);
            });
        }
    };
    
    // Função para excluir cliente
    window.excluirCliente = async function(clienteId) {
        if (!confirm('Tem certeza que deseja excluir este cliente?')) {
            return;
        }
        
        try {
            const response = await fetch(`api/clientes.php?id=${clienteId}`, {
                method: 'DELETE'
            });
            
            const result = await response.json();
            
            if (result.success) {
                mostrarToast(result.message, 'success');
                buscarClientes('');
            } else {
                mostrarToast(result.message, 'error');
            }
        } catch (error) {
            console.error('Erro:', error);
            mostrarToast('Erro de conexão', 'error');
        }
    };
    
    // Carregar clientes inicialmente
    buscarClientes('');
});

// No clientes.php, substitua a função buscarClientes por:
async function buscarClientes(termo = '') {
    try {
        const response = await fetch('api/clientes.php');
        const data = await response.json();
        
        if (data.success && data.data) {
            window.clientesData = data.data; // Salvar dados globalmente
            
            let clientesFiltrados = data.data;
            
            // Aplicar filtro de busca
            if (termo && termo.length >= 2) {
                clientesFiltrados = data.data.filter(cliente => 
                    cliente.nome.toLowerCase().includes(termo.toLowerCase()) ||
                    (cliente.telefone && cliente.telefone.includes(termo)) ||
                    (cliente.email && cliente.email.toLowerCase().includes(termo.toLowerCase()))
                );
            }
            
            // Aplicar ordenação atual
            const sortHeader = document.querySelector('.sortable-header[data-order]');
            if (sortHeader && sortHeader.dataset.order) {
                const sortBy = sortHeader.dataset.sort;
                const order = sortHeader.dataset.order;
                
                clientesFiltrados.sort((a, b) => {
                    let valA = a[sortBy] || '';
                    let valB = b[sortBy] || '';
                    
                    if (['total_gasto', 'media_gastos', 'taxa_fechamento'].includes(sortBy)) {
                        valA = parseFloat(valA) || 0;
                        valB = parseFloat(valB) || 0;
                    }
                    
                    if (sortBy === 'ultima_venda') {
                        valA = valA ? new Date(valA).getTime() : 0;
                        valB = valB ? new Date(valB).getTime() : 0;
                    }
                    
                    if (order === 'asc') {
                        return valA < valB ? -1 : valA > valB ? 1 : 0;
                    } else {
                        return valA > valB ? -1 : valA < valB ? 1 : 0;
                    }
                });
            }
            
            atualizarTabelaClientes(clientesFiltrados);
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

// Atualizar a função ordenarClientes para usar os dados locais
async function ordenarClientes(sortBy, order) {
    if (!window.clientesData) {
        await buscarClientes('');
    }
    
    let clientesFiltrados = window.clientesData || [];
    const termo = document.getElementById('search-cliente')?.value || '';
    
    // Aplicar filtro de busca
    if (termo && termo.length >= 2) {
        clientesFiltrados = window.clientesData.filter(cliente => 
            cliente.nome.toLowerCase().includes(termo.toLowerCase()) ||
            (cliente.telefone && cliente.telefone.includes(termo)) ||
            (cliente.email && cliente.email.toLowerCase().includes(termo.toLowerCase()))
        );
    }
    
    // Ordenar
    clientesFiltrados.sort((a, b) => {
        let valA = a[sortBy] || '';
        let valB = b[sortBy] || '';
        
        if (['total_gasto', 'media_gastos', 'taxa_fechamento'].includes(sortBy)) {
            valA = parseFloat(valA) || 0;
            valB = parseFloat(valB) || 0;
        }
        
        if (sortBy === 'ultima_venda') {
            valA = valA ? new Date(valA).getTime() : 0;
            valB = valB ? new Date(valB).getTime() : 0;
        }
        
        if (order === 'asc') {
            return valA < valB ? -1 : valA > valB ? 1 : 0;
        } else if (order === 'desc') {
            return valA > valB ? -1 : valA < valB ? 1 : 0;
        }
        
        return 0;
    });
    
    atualizarTabelaClientes(clientesFiltrados);
}
</script>

<?php 
require_once 'includes/footer.php';
?>