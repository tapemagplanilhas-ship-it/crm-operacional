<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

requerirPermissao('vendedor');
?>
<div class="page-header">
    
    <h2><i class="fas fa-user-friends"></i> Clientes</h2>
    <div class="page-actions">
        <button type="button" class="btn-primary" data-action="novo-cliente">
            <i class="fas fa-user-plus"></i> Novo Cliente
        </button>
        <button type="button" class="btn-success" data-action="venda-rapida">
            <i class="fas fa-bolt"></i> Venda Rápida
        </button>
    </div>
</div>

    <div class="search-box">
        <i class="fas fa-search"></i>
        <input type="text" id="search-cliente" placeholder="Buscar cliente por nome..." 
               onkeyup="buscarClientes()">
    </div>

<!-- Tabela de Clientes com ordenação -->
<div class="table-responsive table-responsive-clientes">
    <table class="data-table sortable" id="tabela-clientes">
        <thead>
            <tr>
                <th class="sortable-header" data-sort="nome" data-order="">
                    Nome <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="telefone" data-order="">
                    Contato <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="status" data-order="">
                    Status <i class="fas fa-sort"></i>
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
<style>
.search-box {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 2px 2px;
    width: 260px;
}

.search-box input {
    flex: 1;
    border: none;
    background: transparent;
    margin-left: 30px;
    outline: none;
    font-size: 13px;
    border: none;
}

.search-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #ffffff;
    padding: 6px;
}
</style>

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
                <td>
                <span class="badge ${getStatusClass(cliente.status_cliente)}">
                    ${escapeHtml(cliente.status_cliente)}</span>
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
                            <button onclick="abrirModalClienteDetalhes(${cliente.id})">
                                <i class="fas fa-eye"></i> Ver Cliente
                            </button>
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

    // Adicione esta função antes da função atualizarTabelaClientes
function getStatusClass(status) {
    if (!status) return 'badge-secondary';
    
    status = status.toLowerCase();
    switch(status) {
        case 'ativo': return 'badge-success';
        case 'inativo': return 'badge-warning';
        case 'bloqueado': return 'badge-danger';
        default: return 'badge-secondary';
    }
}
    
    function carregarVendasCliente(clienteId) {
    fetch(`/api/vendas.php?cliente_id=${clienteId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.data) {
                // Atualiza a lista de vendas na página
                const tbody = document.querySelector('#vendas-cliente tbody');
                tbody.innerHTML = '';
                
                data.data.forEach(venda => {
                    const row = document.createElement('tr');
                    row.innerHTML = `
                        <td>${formatarDataExibicao(venda.data_venda)}</td>
                        <td>${venda.status}</td>
                        <td>R$ ${venda.valor.toFixed(2).replace('.', ',')}</td>
                        <td>${venda.forma_pagamento}</td>
                        <td>${venda.observacoes || '-'}</td>
                    `;
                    tbody.appendChild(row);
                });
                
                // Atualiza os totais do cliente
                if (data.totais) {
                    document.getElementById('total-vendas').textContent = data.totais.total_vendas;
                    document.getElementById('valor-total-vendas').textContent = 
                        'R$ ' + data.totais.valor_total_vendas.toFixed(2).replace('.', ',');
                }
            }
        })
        .catch(error => console.error('Erro ao carregar vendas:', error));
}

function formatarDataExibicao(data) {
    // Converte de yyyy-mm-dd para dd/mm/yyyy
    const parts = data.split('-');
    return `${parts[2]}/${parts[1]}/${parts[0]}`;
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
            d.classList.remove('drop-up');
        });
        
        // Abrir/fechar atual
        if (!isShowing) {
            dropdown.classList.add('show');

            requestAnimationFrame(() => {
                const rect = dropdown.getBoundingClientRect();
                const footer = document.querySelector('.main-footer');
                const footerTop = footer ? footer.getBoundingClientRect().top : window.innerHeight;
                const viewportPadding = 8;
                const limit = Math.min(window.innerHeight, footerTop);
                const spaceBelow = limit - rect.top;
                const spaceAbove = rect.top;
                const needed = dropdown.offsetHeight + viewportPadding;

                if (needed > spaceBelow && spaceAbove > spaceBelow) {
                    dropdown.classList.add('drop-up');
                } else {
                    dropdown.classList.remove('drop-up');
                }
            });

            setTimeout(() => {
                const closeDropdown = (e) => {
                    if (!dropdown.contains(e.target) && e.target !== button) {
                        dropdown.classList.remove('show');
                        dropdown.classList.remove('drop-up');
                        document.removeEventListener('click', closeDropdown);
                    }
                };
                document.addEventListener('click', closeDropdown);
            });
        }
    };
    
    // Função para excluir cliente
    window.excluirCliente = async function(clienteId) {
        if (typeof window.confirmarExclusao !== 'function') return;
        const confirmado = await window.confirmarExclusao('Tem certeza que deseja excluir este cliente?');
        if (!confirmado) return;
        
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
