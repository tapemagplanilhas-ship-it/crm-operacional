<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
requerirPermissao('vendedor');
?>

<!-- KPIs do Dia -->
<div class="kpis-wrapper" id="kpis-container">
    <div class="kpi-card">
        <span class="kpi-icon"><i class="fas fa-box"></i></span>
        <div><span class="kpi-valor" id="kpi-total">0</span><span class="kpi-label">Total do Dia</span></div>
    </div>
    <div class="kpi-card verde">
        <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
        <div><span class="kpi-valor" id="kpi-entregues">0</span><span class="kpi-label">Entregues</span></div>
    </div>
    <div class="kpi-card amarelo">
        <span class="kpi-icon"><i class="fas fa-clock"></i></span>
        <div><span class="kpi-valor" id="kpi-pendentes">0</span><span class="kpi-label">Pendentes</span></div>
    </div>
    <div class="kpi-card azul">
        <span class="kpi-icon"><i class="fas fa-dollar-sign"></i></span>
        <div><span class="kpi-valor" id="kpi-valor">R$ 0,00</span><span class="kpi-label">Valor Total</span></div>
    </div>
</div>

<!-- Header da Página -->
<div class="page-header">
    <h2><i class="fas fa-truck"></i> Entregas</h2>
    <div class="page-actions">
        <button type="button" class="btn-primary" onclick="abrirModalNovaEntrega()">
            <i class="fas fa-plus"></i> Nova Entrega
        </button>
        <button type="button" class="btn-info" onclick="abrirModalFiltroEntregas()">
            <i class="fas fa-filter"></i> Filtrar
        </button>
    </div>
</div>

<!-- Busca -->
<div class="search-box" style="max-width:400px; margin-bottom:15px;">
    <i class="fas fa-search"></i>
    <input type="text" id="search-entrega" placeholder="Buscar por cliente, pedido ou entregador...">
</div>

<!-- Modal Filtro -->
<div id="modal-filtro-entregas" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:480px;">
        <div class="modal-header">
            <h3><i class="fas fa-filter"></i> Filtrar Entregas</h3>
            <button class="modal-close" onclick="fecharModalEntrega('filtro-entregas')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Status</label>
                <div class="filtro-opcoes">
                    <label><input type="checkbox" name="fe-status" value="entregue" checked> Entregue</label>
                    <label><input type="checkbox" name="fe-status" value="pendente" checked> Pendente</label>
                    <label><input type="checkbox" name="fe-status" value="cancelado" checked> Cancelado</label>
                </div>
            </div>
            <div class="form-group">
                <label>Entregador</label>
                <select class="form-control" id="fe-entregador">
                    <option value="">Todos</option>
                </select>
            </div>
            <div class="form-group" style="display:flex; gap:10px;">
                <div style="flex:1;">
                    <label>Data Início</label>
                    <input type="date" class="form-control" id="fe-data-inicio">
                </div>
                <div style="flex:1;">
                    <label>Data Fim</label>
                    <input type="date" class="form-control" id="fe-data-fim">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="fecharModalEntrega('filtro-entregas')">Cancelar</button>
            <button class="btn-primary" onclick="executarFiltroEntregas()">Aplicar Filtros</button>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Entrega -->
<div id="modal-entrega" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:600px;">
        <div class="modal-header">
            <h3 id="modal-entrega-titulo"><i class="fas fa-motorcycle"></i> Nova Entrega</h3>
            <button class="modal-close" onclick="fecharModalEntrega('entrega')">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="entrega-id">
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group">
                    <label>Número do Pedido *</label>
                    <input type="text" class="form-control" id="entrega-pedido" placeholder="Ex: 1118811">
                </div>
                <div class="form-group">
                    <label>Valor (R$) *</label>
                    <input type="number" step="0.01" class="form-control" id="entrega-valor" placeholder="0,00">
                </div>
                <div class="form-group">
                    <label>Cliente / Destino *</label>
                    <input type="text" class="form-control" id="entrega-cliente" placeholder="Nome ou empresa">
                </div>
                <div class="form-group">
                    <label>Cidade</label>
                    <input type="text" class="form-control" id="entrega-cidade" placeholder="Ex: TATUI">
                </div>
                <div class="form-group">
                    <label>Entregador *</label>
                    <select class="form-control" id="entrega-entregador">
                        <option value="">Selecione...</option>
                        <option>FELIPE</option>
                        <option>TIAGO HENRIQUE</option>
                        <option>NICOLLY</option>
                        <option>MATHEUS</option>
                        <option>ELISEU</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Veículo</label>
                    <input type="text" class="form-control" id="entrega-veiculo" placeholder="Ex: MOTO BRANCA">
                </div>
                <div class="form-group">
                    <label>Hora Saída</label>
                    <input type="time" class="form-control" id="entrega-hora-saida">
                </div>
                <div class="form-group">
                    <label>Hora Retorno</label>
                    <input type="time" class="form-control" id="entrega-hora-retorno">
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select class="form-control" id="entrega-status">
                        <option value="pendente">Pendente</option>
                        <option value="entregue">Entregue</option>
                        <option value="cancelado">Cancelado</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Observações <small style="color:#f59e0b;">(urgente será destacado em amarelo)</small></label>
                <textarea class="form-control" id="entrega-obs" rows="2" placeholder="Ex: ENTREGAR ANTES DAS 16:00"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="fecharModalEntrega('entrega')">Cancelar</button>
            <button class="btn-primary" onclick="salvarEntrega()">Salvar Entrega</button>
        </div>
    </div>
</div>

<!-- Tabela -->
<div class="table-container">
    <table class="data-table" id="tabela-entregas">
        <thead>
            <tr>
                <th>Data/Hora</th>
                <th>Pedido</th>
                <th>Cliente / Destino</th>
                <th>Entregador</th>
                <th>Veículo</th>
                <th>Saída</th>
                <th>Retorno</th>
                <th>Valor</th>
                <th>Status</th>
                <th>Obs</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody id="entregas-body">
            <tr><td colspan="11" class="text-center">Carregando...</td></tr>
        </tbody>
    </table>
</div>

<style>
/* KPIs */
.kpis-wrapper {
    display: flex;
    gap: 12px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.kpi-card {
    flex: 1;
    min-width: 140px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 10px;
    padding: 15px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.06);
}
.kpi-card.verde { border-left: 4px solid #10b981; }
.kpi-card.amarelo { border-left: 4px solid #f59e0b; }
.kpi-card.azul { border-left: 4px solid #3b82f6; }
.kpi-icon { font-size: 22px; color: #9ca3af; }
.kpi-card.verde .kpi-icon { color: #10b981; }
.kpi-card.amarelo .kpi-icon { color: #f59e0b; }
.kpi-card.azul .kpi-icon { color: #3b82f6; }
.kpi-valor { display: block; font-size: 20px; font-weight: bold; color: #111; line-height: 1.2; }
.kpi-label { font-size: 11px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; }

/* Tabela */
.table-container {
    max-height: 520px;
    overflow-y: auto;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
}
.data-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.data-table thead th {
    position: sticky;
    top: 0;
    background: #b80000;
    color: white;
    padding: 11px 12px;
    text-align: left;
    z-index: 10;
    white-space: nowrap;
}
.data-table tbody td { padding: 10px 12px; border-bottom: 1px solid #f3f4f6; }
.data-table tbody tr:hover { background: #fafafa; }

/* Badges de Status */
.badge-entregue  { background:#dcfce7; color:#166534; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-pendente  { background:#fef9c3; color:#854d0e; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-cancelado { background:#fee2e2; color:#991b1b; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }

/* Badge Observação */
.badge-obs {
    display:inline-block;
    background:#fef08a;
    color:#713f12;
    border:1px solid #fde047;
    padding:2px 7px;
    border-radius:4px;
    font-size:11px;
    max-width:160px;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
    vertical-align:middle;
}

/* Modal */
.modal {
    display: none;
    position: fixed;
    z-index: 9999;
    inset: 0;
    background: rgba(0,0,0,0.5);
    align-items: center;
    justify-content: center;
}
.modal-content { background: white; border-radius: 10px; overflow: hidden; width: 90%; }
.modal-header { padding: 16px 20px; border-bottom: 1px solid #f3f4f6; display: flex; justify-content: space-between; align-items: center; }
.modal-body { padding: 20px; }
.modal-footer { padding: 14px 20px; background: #f9fafb; display: flex; justify-content: flex-end; gap: 10px; }
.filtro-opcoes { display: flex; gap: 15px; margin-top: 6px; flex-wrap: wrap; }
.modal-close { background: none; border: none; font-size: 20px; cursor: pointer; color: #9ca3af; }

/* Search */
.search-box { display: flex; align-items: center; background: #fff; border: 1px solid #d1d5db; border-radius: 8px; padding: 9px 14px; }
.search-box input { border: none; outline: none; margin-left: 10px; width: 100%; font-size: 13px; }

/* Dropdown flutuante */
#floating-entrega-dropdown {
    display: none;
    position: fixed;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    z-index: 99999;
    min-width: 175px;
    padding: 4px 0;
}
#floating-entrega-dropdown button {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 9px 15px;
    border: none;
    background: none;
    cursor: pointer;
    font-size: 13px;
    color: #374151;
    text-align: left;
}
#floating-entrega-dropdown button:hover { background: #f3f4f6; }
#floating-entrega-dropdown button.danger { color: #dc2626; }
#floating-entrega-dropdown button.danger:hover { background: #fef2f2; }
#floating-entrega-dropdown button.success { color: #16a34a; }
#floating-entrega-dropdown button.success:hover { background: #f0fdf4; }

/* btn-info */
.btn-info { background:#17a2b8; color:white; border:none; padding:8px 15px; border-radius:4px; cursor:pointer; }
.btn-info:hover { background:#138496; }
</style>

<div id="floating-entrega-dropdown"></div>

<script>
// 
// ESTADO GLOBAL
// 
let entregasMaster = [];

// 
// INICIALIZAÇÃO
// 
document.addEventListener('DOMContentLoaded', () => {
    carregarEntregas();

    let timer;
    document.getElementById('search-entrega').addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(() => renderEntregas(filtrarEntregas()), 250);
    });

    document.addEventListener('click', (e) => {
        const dd = document.getElementById('floating-entrega-dropdown');
        if (dd && !dd.contains(e.target) && !e.target.classList.contains('actions-toggle')) {
            dd.style.display = 'none';
        }
    });
});

// 
// CARREGAR DA API
// 
async function carregarEntregas() {
    try {
        const res  = await fetch('api/entregas.php');
        const json = await res.json();
        if (json.success) {
            entregasMaster = json.data;
            popularSelectEntregadores();
            renderEntregas(entregasMaster);
            atualizarKPIs(entregasMaster);
        }
    } catch (e) {
        console.error('Erro ao carregar entregas:', e);
        document.getElementById('entregas-body').innerHTML =
            '<tr><td colspan="11" class="text-center">Erro ao carregar dados.</td></tr>';
    }
}

// 
// POPULAR SELECT DE ENTREGADORES NO FILTRO
// 
function popularSelectEntregadores() {
    const sel = document.getElementById('fe-entregador');
    const unicos = [...new Set(entregasMaster.map(e => e.entregador).filter(Boolean))];
    sel.innerHTML = '<option value="">Todos</option>';
    unicos.forEach(e => sel.innerHTML += `<option value="${e}">${e}</option>`);
}

// 
// FILTRAR DADOS
// 
function filtrarEntregas() {
    const busca      = document.getElementById('search-entrega').value.toLowerCase();
    const statusSel  = [...document.querySelectorAll('[name="fe-status"]:checked')].map(c => c.value);
    const entregador = document.getElementById('fe-entregador').value;
    const dataIni    = document.getElementById('fe-data-inicio').value;
    const dataFim    = document.getElementById('fe-data-fim').value;

    return entregasMaster.filter(e => {
        const mBusca = !busca ||
            (e.cliente_destino || '').toLowerCase().includes(busca) ||
            (e.numero_pedido   || '').toLowerCase().includes(busca) ||
            (e.entregador      || '').toLowerCase().includes(busca);

        const mStatus     = statusSel.length === 0 || statusSel.includes(e.status);
        const mEntregador = !entregador || e.entregador === entregador;

        const dataEntrega = (e.data_criacao || '').substring(0, 10);
        const mDataIni    = !dataIni || dataEntrega >= dataIni;
        const mDataFim    = !dataFim || dataEntrega &lt;= dataFim;

        return mBusca && mStatus && mEntregador && mDataIni && mDataFim;
    });
}

// 
// EXECUTAR FILTRO DO MODAL
// 
function executarFiltroEntregas() {
    const resultado = filtrarEntregas();
    renderEntregas(resultado);
    atualizarKPIs(resultado);
    fecharModalEntrega('filtro-entregas');
}

// 
// RENDERIZAR TABELA
// 
function renderEntregas(lista) {
    const tbody = document.getElementById('entregas-body');

    if (!lista || lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="11" class="text-center">Nenhuma entrega encontrada.</td></tr>';
        return;
    }

    tbody.innerHTML = lista.map(e => {
        const dataFmt = formatarDataHora(e.data_criacao);
        const valorFmt = formatMoeda(e.valor);
        const statusBadge = `<span class="badge-${e.status || 'pendente'}">${(e.status || 'pendente').toUpperCase()}</span>`;
        const obsBadge = e.observacoes
            ? `<span class="badge-obs" title="${escHtml(e.observacoes)}">⚠ ${escHtml(e.observacoes)}</span>`
            : '<span style="color:#9ca3af; font-size:12px;">—</span>';

        return `
        <tr>
            <td style="white-space:nowrap; font-size:12px;">${dataFmt}</td>
            <td><strong>${escHtml(e.numero_pedido || '—')}</strong></td>
            <td>${escHtml(e.cliente_destino || '—')}<br><small style="color:#9ca3af;">${escHtml(e.cidade || '')}</small></td>
            <td>${escHtml(e.entregador || '—')}</td>
            <td style="font-size:12px;">${escHtml(e.veiculo || '—')}</td>
            <td style="font-size:12px;">${e.hora_saida  || '—'}</td>
            <td style="font-size:12px;">${e.hora_retorno || '—'}</td>
            <td><strong>${valorFmt}</strong></td>
            <td>${statusBadge}</td>
            <td>${obsBadge}</td>
            <td class="text-center">
                <button class="actions-toggle" onclick="abrirMenuEntrega(this, ${e.id})">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

// 
// ATUALIZAR KPIs
// 
function atualizarKPIs(lista) {
    const total     = lista.length;
    const entregues = lista.filter(e => e.status === 'entregue').length;
    const pendentes = lista.filter(e => e.status === 'pendente').length;
    const valor     = lista.reduce((s, e) => s + parseFloat(e.valor || 0), 0);

    document.getElementById('kpi-total').textContent    = total;
    document.getElementById('kpi-entregues').textContent = entregues;
    document.getElementById('kpi-pendentes').textContent = pendentes;
    document.getElementById('kpi-valor').textContent    = formatMoeda(valor);
}

// 
// MENU DE AÇÕES (DROPDOWN FLUTUANTE — SEM CORTE)
// 
window.abrirMenuEntrega = function(btn, id) {
    const dd = document.getElementById('floating-entrega-dropdown');

    if (dd.style.display === 'block' && dd.dataset.id == id) {
        dd.style.display = 'none';
        return;
    }

    dd.dataset.id = id;
    dd.innerHTML = `
        <button onclick="verDetalhesEntrega(${id})"><i class="fas fa-eye"></i> Ver Detalhes</button>
        <button onclick="editarEntrega(${id})"><i class="fas fa-edit"></i> Editar</button>
        <button class="success" onclick="marcarEntregue(${id})"><i class="fas fa-check"></i> Marcar Entregue</button>
        <button class="danger" onclick="excluirEntrega(${id})"><i class="fas fa-trash"></i> Excluir</button>
    `;

    dd.style.display = 'block';

    const rect        = btn.getBoundingClientRect();
    const ddH         = 160;
    const spaceBelow  = window.innerHeight - rect.bottom;
    const left        = Math.max(0, rect.right - 175);

    dd.style.left = left + 'px';
    dd.style.top  = (spaceBelow &lt; ddH ? rect.top - ddH : rect.bottom + 4) + 'px';
};

// 
// AÇÕES CRUD
// 
window.verDetalhesEntrega = function(id) {
    const e = entregasMaster.find(x => x.id == id);
    if (!e) return;
    alert(`Pedido: ${e.numero_pedido}\nCliente: ${e.cliente_destino}\nEntregador: ${e.entregador}\nValor: ${formatMoeda(e.valor)}\nStatus: ${e.status}\nObs: ${e.observacoes || '—'}`);
    document.getElementById('floating-entrega-dropdown').style.display = 'none';
};

window.editarEntrega = function(id) {
    const e = entregasMaster.find(x => x.id == id);
    if (!e) return;

    document.getElementById('entrega-id').value           = e.id;
    document.getElementById('entrega-pedido').value       = e.numero_pedido  || '';
    document.getElementById('entrega-valor').value        = e.valor          || '';
    document.getElementById('entrega-cliente').value      = e.cliente_destino|| '';
    document.getElementById('entrega-cidade').value       = e.cidade         || '';
    document.getElementById('entrega-entregador').value   = e.entregador     || '';
    document.getElementById('entrega-veiculo').value      = e.veiculo        || '';
    document.getElementById('entrega-hora-saida').value   = e.hora_saida     || '';
    document.getElementById('entrega-hora-retorno').value = e.hora_retorno   || '';
    document.getElementById('entrega-status').value       = e.status         || 'pendente';
    document.getElementById('entrega-obs').value          = e.observacoes    || '';

    document.getElementById('modal-entrega-titulo').innerHTML = '<i class="fas fa-edit"></i> Editar Entrega';
    abrirModalEntrega();
    document.getElementById('floating-entrega-dropdown').style.display = 'none';
};

window.marcarEntregue = async function(id) {
    try {
        const res  = await fetch(`api/entregas.php?id=${id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ status: 'entregue' })
        });
        const json = await res.json();
        if (json.success) {
            mostrarToast('Marcado como entregue!', 'success');
            carregarEntregas();
        }
    } catch (e) {
        mostrarToast('Erro ao atualizar', 'error');
    }
    document.getElementById('floating-entrega-dropdown').style.display = 'none';
};

window.excluirEntrega = async function(id) {
    if (typeof window.confirmarExclusao === 'function') {
        const ok = await window.confirmarExclusao('Excluir esta entrega?');
        if (!ok) return;
    } else {
        if (!confirm('Excluir esta entrega?')) return;
    }
    try {
        const res  = await fetch(`api/entregas.php?id=${id}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.success) {
            mostrarToast('Entrega excluída', 'success');
            carregarEntregas();
        }
    } catch (e) {
        mostrarToast('Erro ao excluir', 'error');
    }
    document.getElementById('floating-entrega-dropdown').style.display = 'none';
};

// 
// SALVAR NOVA / EDITAR ENTREGA
// 
window.salvarEntrega = async function() {
    const id = document.getElementById('entrega-id').value;
    const payload = {
        numero_pedido:   document.getElementById('entrega-pedido').value,
        valor:           document.getElementById('entrega-valor').value,
        cliente_destino: document.getElementById('entrega-cliente').value,
        cidade:          document.getElementById('entrega-cidade').value,
        entregador:      document.getElementById('entrega-entregador').value,
        veiculo:         document.getElementById('entrega-veiculo').value,
        hora_saida:      document.getElementById('entrega-hora-saida').value,
        hora_retorno:    document.getElementById('entrega-hora-retorno').value,
        status:          document.getElementById('entrega-status').value,
        observacoes:     document.getElementById('entrega-obs').value,
    };

    if (!payload.numero_pedido || !payload.cliente_destino || !payload.entregador) {
        mostrarToast('Preencha os campos obrigatórios', 'error');
        return;
    }

    const method = id ? 'PUT' : 'POST';
    const url    = id ? `api/entregas.php?id=${id}` : 'api/entregas.php';

    try {
        const res  = await fetch(url, {
            method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            mostrarToast(id ? 'Entrega atualizada!' : 'Entrega criada!', 'success');
            fecharModalEntrega('entrega');
            carregarEntregas();
        } else {
            mostrarToast(json.message || 'Erro ao salvar', 'error');
        }
    } catch (e) {
        mostrarToast('Erro de conexão', 'error');
    }
};

// 
// CONTROLE DE MODAIS
// 
function abrirModalFiltroEntregas() {
    document.getElementById('modal-filtro-entregas').style.display = 'flex';
}

function abrirModalNovaEntrega() {
    document.getElementById('entrega-id').value = '';
    document.getElementById('modal-entrega-titulo').innerHTML = '<i class="fas fa-plus"></i> Nova Entrega';
    document.getElementById('entrega-pedido').value       = '';
    document.getElementById('entrega-valor').value        = '';
    document.getElementById('entrega-cliente').value      = '';
    document.getElementById('entrega-cidade').value       = '';
    document.getElementById('entrega-entregador').value   = '';
    document.getElementById('entrega-veiculo').value      = '';
    document.getElementById('entrega-hora-saida').value   = '';
    document.getElementById('entrega-hora-retorno').value = '';
    document.getElementById('entrega-status').value       = 'pendente';
    document.getElementById('entrega-obs').value          = '';
    abrirModalEntrega();
}

function abrirModalEntrega() {
    document.getElementById('modal-entrega').style.display = 'flex';
}

function fecharModalEntrega(tipo) {
    document.getElementById(`modal-${tipo}`).style.display = 'none';
}

// 
// UTILITÁRIOS
// 
function formatMoeda(v) {
    return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(parseFloat(v) || 0);
}

function formatarDataHora(d) {
    if (!d) return '—';
    const dt = new Date(d.replace(' ', 'T'));
    return isNaN(dt) ? d : dt.toLocaleString('pt-BR');
}

function escHtml(t) {
    if (!t) return '';
    const div = document.createElement('div');
    div.textContent = String(t);
    return div.innerHTML;
}
</script>

<?php require_once 'includes/footer.php'; ?>