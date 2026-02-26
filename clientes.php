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
<div class="filter-trigger">
        <button type="button" class="btn-filter" onclick="abrirModalFiltro()">
            <i class="fas fa-filter"></i> Filtrar e Ordenar
        </button>
    </div>

<!-- Modal de Filtro -->
<div id="modal-filtro" class="modal" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-sliders-h"></i> Refinar Resultados</h3>
            <button class="modal-close" onclick="fecharModal('filtro')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label class="label-bold">Status Operacional</label>
                <div class="filtro-opcoes">
                    <label class="checkbox-item"><input type="checkbox" name="filtro-status" value="ativo" checked> Ativo</label>
                    <label class="checkbox-item"><input type="checkbox" name="filtro-status" value="inativo" checked> Inativo</label>
                    <label class="checkbox-item"><input type="checkbox" name="filtro-status" value="bloqueado" checked> Bloqueado</label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="label-bold">Critério de Ordenação</label>
                <select class="form-control" id="filtro-ordenacao">
                    <option value="nome_asc">Nome (A-Z)</option>
                    <option value="nome_desc">Nome (Z-A)</option>
                    <option value="valor_desc">Maior Faturamento (LTV)</option>
                    <option value="valor_asc">Menor Faturamento</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="label-bold">Volume de Compras (Mínimo)</label>
                <select class="form-control" id="filtro-valor">
                    <option value="0">Todos os volumes</option>
                    <option value="1000">Acima de R$ 1.000</option>
                    <option value="5000">Acima de R$ 5.000</option>
                    <option value="10000">Acima de R$ 10.000</option>
                </select>
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn-secondary" onclick="fecharModal('filtro')">Cancelar</button>
            <button type="button" class="btn-primary" onclick="processarFiltros()">Aplicar Filtros</button>
        </div>
    </div>
</div>

<!-- Tabela de Clientes com cabeçalho fixo -->
<div class="table-container">
    <table class="data-table sortable" id="tabela-clientes">
        <thead>
            <tr>
                <th class="sortable-header" data-sort="nome" data-order="">
                    Nome <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="telefone" data-order="">
                    Contato <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="status_cliente" data-order="">
                    Status <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="ultima_venda" data-order="desc">
                    Última Venda <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="total_gasto" data-order="desc">
                    Total Gasto <i class="fas fa-sort"></i>
                </th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody id="clientes-body">
            <tr>
                <td colspan="6" class="text-center">Carregando clientes...</td>
            </tr>
        </tbody>
    </table>
</div>

<div id="loading-clientes" class="text-center" style="display: none;">
    <p><i class="fas fa-spinner fa-spin"></i> Carregando...</p>
</div>

<style>
/* Estilos para o modal de filtro */
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.4);
    align-items: center;
    justify-content: center;
}

.modal-content {
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    animation: modalFadeIn 0.3s;
}

@keyframes modalFadeIn {
    from { opacity: 0; transform: translateY(-20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-body {
    padding: 20px;
}

.modal-footer {
    padding: 15px 20px;
    border-top: 1px solid #eee;
    text-align: right;
}

.filtro-opcoes {
    display: flex;
    gap: 15px;
    margin-top: 8px;
}

.filtro-option {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}
.filter-trigger .btn-filter{
    background-color: #17a2b8;
    color: white;
    border: none;
    margin-bottom: 15px;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-info {
    background-color: #17a2b8;
    color: white;
    border: none;
    padding: 8px 15px;
    border-radius: 4px;
    cursor: pointer;
}

.btn-info:hover {
    background-color: #138496;
}

/* Estilos existentes para a tabela */
.table-container {
    position: relative;
    max-height: 300px;
    overflow-y: auto;
    border: 1px solid #d1d5db;
    border-radius: 8px;
}

.table-container thead th {
    position: sticky;
    top: 0;
    background: #b80000;
    z-index: 10;
    color: white;
}

.search-box {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 2px 2px;
    width: 260px;
    margin-bottom: 15px;
}

.search-box input {
    flex: 1;
    border: none;
    background: transparent;
    margin-left: 30px;
    outline: none;
    font-size: 13px;
}

.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    color: white;
}
/* Dropdown sai do overflow da tabela */
.actions-dropdown {
    display: none;
    position: fixed; /* FIXED em vez de absolute */
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    z-index: 9999;
    min-width: 160px;
    padding: 4px 0;
}

.actions-dropdown.show {
    display: block;
}

.actions-dropdown button {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 15px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 13px;
    text-align: left;
    color: #374151;
}

.actions-dropdown button:hover {
    background: #f3f4f6;
}

.actions-dropdown button.danger {
    color: #dc2626;
}

.actions-dropdown button.danger:hover {
    background: #fef2f2;
}
/* Estilo dos botões dentro do dropdown flutuante */
#floating-dropdown button {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 9px 15px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 13px;
    text-align: left;
    color: #374151;
    white-space: nowrap;
}

#floating-dropdown button:hover {
    background: #f3f4f6;
}

#floating-dropdown button.danger {
    color: #dc2626;
}

#floating-dropdown button.danger:hover {
    background: #fef2f2;
}

.badge-success { background-color: #28a745; }
.badge-warning { background-color: #ffc107; color: #212529; }
.badge-danger { background-color: #dc3545; }
.badge-secondary { background-color: #6c757d; }
</style>

<script>
// Armazena todos os clientes carregados
let masterData = [];

function abrirModalFiltro() { 
    document.getElementById('modal-filtro').style.display = 'flex'; 
}

function fecharModal(id) { 
    document.getElementById('modal-' + id).style.display = 'none'; 
}

// Função chamada pelo botão "Aplicar Filtros"
function processarFiltros() {
    const termoBusca = document.getElementById('search-cliente').value.toLowerCase();
    const statusPermitidos = Array.from(document.querySelectorAll('[name="filtro-status"]:checked')).map(cb => cb.value);
    const valorMinimo = parseFloat(document.getElementById('filtro-valor').value);
    const ordenacao = document.getElementById('filtro-ordenacao').value;

    // Filtrar
    let resultado = masterData.filter(cliente => {
        const matchBusca = !termoBusca ||
            cliente.nome.toLowerCase().includes(termoBusca) ||
            (cliente.telefone && cliente.telefone.includes(termoBusca)) ||
            (cliente.email && cliente.email.toLowerCase().includes(termoBusca));

        const matchStatus = statusPermitidos.includes(
            (cliente.status_cliente || '').toLowerCase()
        );

        const matchValor = parseFloat(cliente.total_gasto || 0) >= valorMinimo;

        return matchBusca && matchStatus && matchValor;
    });

    // Ordenar
    const [campo, direcao] = ordenacao.split('_');
    resultado.sort((a, b) => {
        if (campo === 'nome') {
            return direcao === 'asc'
                ? a.nome.localeCompare(b.nome)
                : b.nome.localeCompare(a.nome);
        }
        if (campo === 'valor') {
            return direcao === 'asc'
                ? parseFloat(a.total_gasto || 0) - parseFloat(b.total_gasto || 0)
                : parseFloat(b.total_gasto || 0) - parseFloat(a.total_gasto || 0);
        }
        return 0;
    });

    // Usa a função original que já funciona
    window.atualizarTabelaClientes(resultado);
    fecharModal('filtro');
}

// Função de determinar a classe do status
function getStatusClass(status) {
    if (!status) return 'badge-secondary';
    status = status.toLowerCase();
    switch (status) {
        case 'ativo':    return 'badge-success';
        case 'inativo':  return 'badge-warning';
        case 'bloqueado': return 'badge-danger';
        default:         return 'badge-secondary';
    }
}

document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('search-cliente');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            buscarClientes(this.value);
        });
    }

    async function buscarClientes(termo) {
        try {
            const response = await fetch('api/clientes.php');
            const data = await response.json();

            if (data.success) {
                // Salva todos os dados para o filtro usar
                masterData = data.data;

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

    function atualizarTabelaClientes(clientes) {
        const tbody = document.getElementById('clientes-body');
        if (!tbody) return;

        if (!clientes || clientes.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center">Nenhum cliente encontrado</td></tr>';
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
                        ${cliente.status_cliente ? cliente.status_cliente.toUpperCase() : 'N/D'}
                    </span>
                </td>
                <td>${cliente.ultima_venda && cliente.ultima_venda != '0000-00-00' ? formatarData(cliente.ultima_venda) : 'Nunca'}</td>
                <td><strong>${formatarMoeda(cliente.total_gasto)}</strong></td>
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
                            <button onclick="abrirModalVenda(${cliente.id}, '${escapeHtml(cliente.nome).replace(/'/g, "\'")}')">
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

    // Expõe para o processarFiltros() usar
    window.atualizarTabelaClientes = atualizarTabelaClientes;

    function formatarData(dateString) {
        if (!dateString || dateString === '0000-00-00') return 'Nunca';
        const date = new Date(dateString);
        return date.toLocaleDateString('pt-BR');
    }

    function formatarMoeda(valor) {
        return 'R$ ' + parseFloat(valor || 0).toLocaleString('pt-BR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    window.toggleActionsMenu = function(button) {
    // Remove qualquer dropdown flutuante já existente
    const existing = document.getElementById('floating-dropdown');
    if (existing) existing.remove();

    const dropdown = button.nextElementSibling;
    const isOpen = button.getAttribute('data-open') === 'true';

    // Se já estava aberto, fecha e para
    if (isOpen) {
        button.setAttribute('data-open', 'false');
        return;
    }

    // Marca todos os botões como fechados
    document.querySelectorAll('.actions-toggle').forEach(b => {
        b.setAttribute('data-open', 'false');
    });

    // Marca este como aberto
    button.setAttribute('data-open', 'true');

    // Clona o dropdown e coloca diretamente no BODY
    const clone = dropdown.cloneNode(true);
    clone.id = 'floating-dropdown';
    clone.style.cssText = `
        display: block;
        position: fixed;
        background: white;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        z-index: 99999;
        min-width: 160px;
        padding: 4px 0;
    `;

    // Calcula posição baseado no botão
    const rect = button.getBoundingClientRect();
    const dropHeight = 160;
    const spaceBelow = window.innerHeight - rect.bottom;

    if (spaceBelow < dropHeight) {
        // Abre para CIMA
        clone.style.top = (rect.top - dropHeight) + 'px';
    } else {
        // Abre para BAIXO
        clone.style.top = rect.bottom + 'px';
    }

    // Alinha pela direita do botão
    clone.style.left = Math.max(0, rect.right - 160) + 'px';

    document.body.appendChild(clone);

    // Fecha ao clicar fora
    setTimeout(() => {
        function closeHandler(e) {
            if (!clone.contains(e.target) && e.target !== button) {
                clone.remove();
                button.setAttribute('data-open', 'false');
                document.removeEventListener('click', closeHandler);
            }
        }
        document.addEventListener('click', closeHandler);
    });
};

    window.excluirCliente = async function (clienteId) {
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

    buscarClientes('');
});
</script>

<?php 
require_once 'includes/footer.php';
?>