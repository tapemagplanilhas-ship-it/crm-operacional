<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
requerirPermissao('vendedor');
?>

<!-- KPIs -->
<div class="kpis-wrapper">
    <div class="kpi-card">
        <span class="kpi-icon"><i class="fas fa-tasks"></i></span>
        <div><span class="kpi-valor" id="kpi-total">0</span><span class="kpi-label">Total</span></div>
    </div>
    <div class="kpi-card amarelo">
        <span class="kpi-icon"><i class="fas fa-clock"></i></span>
        <div><span class="kpi-valor" id="kpi-pendentes">0</span><span class="kpi-label">Pendentes</span></div>
    </div>
    <div class="kpi-card vermelho">
        <span class="kpi-icon"><i class="fas fa-exclamation-circle"></i></span>
        <div><span class="kpi-valor" id="kpi-atrasadas">0</span><span class="kpi-label">Atrasadas</span></div>
    </div>
    <div class="kpi-card verde">
        <span class="kpi-icon"><i class="fas fa-check-circle"></i></span>
        <div><span class="kpi-valor" id="kpi-concluidas">0</span><span class="kpi-label">Concluídas Hoje</span></div>
    </div>
</div>

<!-- Header -->
<div class="page-header">
    <h2><i class="fas fa-tasks"></i> Agendador de Tarefas</h2>
    <div class="page-actions">
        <button class="btn-primary" onclick="abrirModalNovaTarefa()">
            <i class="fas fa-plus"></i> Nova Tarefa
        </button>
        <button class="btn-filter" onclick="abrirModalFiltroTarefas()">
            <i class="fas fa-filter"></i> Filtrar
        </button>
    </div>
</div>

<!-- Busca -->
<div class="search-box" style="max-width:400px; margin-bottom:15px;">
    <i class="fas fa-search"></i>
    <input type="text" id="search-tarefa" placeholder="Buscar por cliente ou título...">
</div>

<!-- Modal Filtro -->
<div id="modal-filtro-tarefas" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:460px;">
        <div class="modal-header">
            <h3><i class="fas fa-filter"></i> Filtrar Tarefas</h3>
            <button class="modal-close" onclick="fecharModalTarefa('filtro-tarefas')">&times;</button>
        </div>
        <div class="modal-body">
            <div class="form-group">
                <label>Status</label>
                <div class="filtro-opcoes">
                    <label><input type="checkbox" name="ft-status" value="pendente" checked> Pendente</label>
                    <label><input type="checkbox" name="ft-status" value="concluida" checked> Concluída</label>
                    <label><input type="checkbox" name="ft-status" value="cancelada" checked> Cancelada</label>
                </div>
            </div>
            <div class="form-group">
                <label>Prioridade</label>
                <div class="filtro-opcoes">
                    <label><input type="checkbox" name="ft-prioridade" value="alta" checked> Alta</label>
                    <label><input type="checkbox" name="ft-prioridade" value="media" checked> Média</label>
                    <label><input type="checkbox" name="ft-prioridade" value="baixa" checked> Baixa</label>
                </div>
            </div>
            <div class="form-group">
                <label>Tipo de Contato</label>
                <div class="filtro-opcoes">
                    <label><input type="checkbox" name="ft-tipo" value="whatsapp" checked> WhatsApp</label>
                    <label><input type="checkbox" name="ft-tipo" value="ligacao" checked> Ligação</label>
                    <label><input type="checkbox" name="ft-tipo" value="email" checked> E-mail</label>
                    <label><input type="checkbox" name="ft-tipo" value="visita" checked> Visita</label>
                </div>
            </div>
            <div class="form-group" style="display:flex;gap:10px;">
                <div style="flex:1;">
                    <label>Data Início</label>
                    <input type="date" class="form-control" id="ft-data-inicio">
                </div>
                <div style="flex:1;">
                    <label>Data Fim</label>
                    <input type="date" class="form-control" id="ft-data-fim">
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="fecharModalTarefa('filtro-tarefas')">Cancelar</button>
            <button class="btn-primary" onclick="executarFiltroTarefas()">Aplicar</button>
        </div>
    </div>
</div>

<!-- Modal Nova/Editar Tarefa -->
<div id="modal-tarefa" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:580px;">
        <div class="modal-header">
            <h3 id="modal-tarefa-titulo"><i class="fas fa-plus"></i> Nova Tarefa</h3>
            <button class="modal-close" onclick="fecharModalTarefa('tarefa')">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="tarefa-id">
            <input type="hidden" id="tarefa-cliente-id">

            <!-- Busca de cliente com autocomplete -->
            <div class="form-group" style="position:relative;">
                <label>Cliente *</label>
                <input type="text" class="form-control" id="tarefa-cliente-busca"
                       placeholder="Digite o nome do cliente..." autocomplete="off">
                <div id="autocomplete-lista" style="
                    display:none; position:absolute; top:100%; left:0; right:0;
                    background:white; border:1px solid #d1d5db; border-radius:6px;
                    box-shadow:0 4px 12px rgba(0,0,0,0.1); z-index:9999; max-height:200px; overflow-y:auto;">
                </div>
            </div>

            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                <div class="form-group" style="grid-column:span 2;">
                    <label>Título da Tarefa *</label>
                    <input type="text" class="form-control" id="tarefa-titulo"
                           placeholder="Ex: Ligar para oferecer promoção">
                </div>
                <div class="form-group">
                    <label>Tipo de Contato</label>
                    <select class="form-control" id="tarefa-tipo">
                        <option value="whatsapp">💬 WhatsApp</option>
                        <option value="ligacao">📞 Ligação</option>
                        <option value="email">📧 E-mail</option>
                        <option value="visita">🤝 Visita</option>
                        <option value="outro">📌 Outro</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Prioridade</label>
                    <select class="form-control" id="tarefa-prioridade">
                        <option value="media">Média</option>
                        <option value="alta">🔴 Alta</option>
                        <option value="baixa">Baixa</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column:span 2;">
                    <label>Data e Hora *</label>
                    <input type="datetime-local" class="form-control" id="tarefa-data">
                </div>
            </div>
            <div class="form-group">
                <label>Observações</label>
                <textarea class="form-control" id="tarefa-descricao" rows="2"
                          placeholder="Detalhes sobre o contato..."></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="fecharModalTarefa('tarefa')">Cancelar</button>
            <button class="btn-primary" onclick="salvarTarefa()">Salvar Tarefa</button>
        </div>
    </div>
</div>

<!-- Modal Registrar Contato -->
<div id="modal-contato" class="modal" style="display:none;">
    <div class="modal-content" style="max-width:460px;">
        <div class="modal-header">
            <h3><i class="fas fa-phone-alt"></i> Registrar Contato</h3>
            <button class="modal-close" onclick="fecharModalTarefa('contato')">&times;</button>
        </div>
        <div class="modal-body">
            <input type="hidden" id="contato-tarefa-id">
            <input type="hidden" id="contato-cliente-id">
            <div class="form-group" style="background:#f9fafb;border-radius:8px;padding:12px;margin-bottom:16px;">
                <div style="font-size:13px;color:#6b7280;">Registrando contato para:</div>
                <div id="contato-cliente-nome" style="font-weight:700;font-size:15px;color:#111;"></div>
            </div>
            <div class="form-group">
                <label>Resultado do Contato *</label>
                <div style="display:flex;gap:10px;margin-top:8px;">
                    <label style="flex:1;border:2px solid #10b981;border-radius:8px;padding:14px;text-align:center;cursor:pointer;transition:all .2s;" id="btn-atendido">
                        <input type="radio" name="status-contato" value="atendido" style="display:none;">
                        <div style="font-size:22px;">✅</div>
                        <div style="font-weight:700;color:#166534;margin-top:4px;">Atendido</div>
                    </label>
                    <label style="flex:1;border:2px solid #dc2626;border-radius:8px;padding:14px;text-align:center;cursor:pointer;transition:all .2s;" id="btn-nao-atendido">
                        <input type="radio" name="status-contato" value="nao_atendido" style="display:none;">
                        <div style="font-size:22px;">❌</div>
                        <div style="font-weight:700;color:#991b1b;margin-top:4px;">Não Atendido</div>
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label>Canal Utilizado</label>
                <select class="form-control" id="contato-tipo">
                    <option value="whatsapp">💬 WhatsApp</option>
                    <option value="ligacao">📞 Ligação</option>
                    <option value="email">📧 E-mail</option>
                    <option value="visita">🤝 Visita</option>
                    <option value="outro">📌 Outro</option>
                </select>
            </div>
            <div class="form-group">
                <label>Observações</label>
                <textarea class="form-control" id="contato-descricao" rows="3"
                          placeholder="O que foi conversado?"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-secondary" onclick="fecharModalTarefa('contato')">Cancelar</button>
            <button class="btn-primary" onclick="salvarContato()">Registrar Contato</button>
        </div>
    </div>
</div>

<!-- Tabela -->
<div class="table-container">
    <table class="data-table" id="tabela-tarefas">
        <thead>
            <tr>
                <th>Cliente</th>
                <th>Tarefa</th>
                <th>Tipo</th>
                <th>Agendado Para</th>
                <th>Prioridade</th>
                <th>Status</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody id="tarefas-body">
            <tr><td colspan="7" class="text-center">Carregando...</td></tr>
        </tbody>
    </table>
</div>

<!-- Dropdown Flutuante -->
<div id="floating-tarefa-dropdown" style="
    display:none; position:fixed; background:white;
    border:1px solid #e5e7eb; border-radius:8px;
    box-shadow:0 4px 20px rgba(0,0,0,0.15);
    z-index:99999; min-width:180px; padding:4px 0;">
</div>

<style>
.kpis-wrapper { display:flex; gap:12px; margin-bottom:20px; flex-wrap:wrap; }
.kpi-card { flex:1; min-width:130px; background:white; border:1px solid #e5e7eb; border-radius:10px; padding:15px 18px; display:flex; align-items:center; gap:12px; box-shadow:0 1px 3px rgba(0,0,0,0.06); }
.kpi-card.verde  { border-left:4px solid #10b981; }
.kpi-card.amarelo{ border-left:4px solid #f59e0b; }
.kpi-card.vermelho{ border-left:4px solid #dc2626; }
.kpi-icon { font-size:22px; color:#9ca3af; }
.kpi-card.verde   .kpi-icon { color:#10b981; }
.kpi-card.amarelo .kpi-icon { color:#f59e0b; }
.kpi-card.vermelho.kpi-icon { color:#dc2626; }
.kpi-valor { display:block; font-size:22px; font-weight:700; color:#111; line-height:1.2; }
.kpi-label { font-size:11px; color:#6b7280; text-transform:uppercase; letter-spacing:.5px; }

.table-container { max-height:520px; overflow-y:auto; border:1px solid #e5e7eb; border-radius:8px; }
.data-table { width:100%; border-collapse:collapse; font-size:13px; }
.data-table thead th { position:sticky; top:0; background:#b80000; color:white; padding:11px 12px; text-align:left; z-index:10; white-space:nowrap; }
.data-table tbody td { padding:10px 12px; border-bottom:1px solid #f3f4f6; vertical-align:middle; }
.data-table tbody tr:hover { background:#fafafa; }

/* Badges */
.bp-alta    { background:#fee2e2; color:#991b1b; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.bp-media   { background:#fef9c3; color:#854d0e; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.bp-baixa   { background:#dcfce7; color:#166534; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.bs-pendente  { background:#fef9c3; color:#854d0e; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.bs-concluida { background:#dcfce7; color:#166534; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.bs-cancelada { background:#f3f4f6; color:#6b7280; padding:3px 9px; border-radius:20px; font-size:11px; font-weight:700; }
.badge-atrasada { background:#dc2626; color:white; padding:2px 7px; border-radius:4px; font-size:10px; font-weight:700; margin-left:6px; animation:piscar 1.5s infinite; }
@keyframes piscar { 0%,100%{opacity:1;} 50%{opacity:.5;} }

/* Dropdown flutuante */
#floating-tarefa-dropdown button {
    display:flex; align-items:center; gap:8px; width:100%; padding:9px 15px;
    border:none; background:none; cursor:pointer; font-size:13px; color:#374151; text-align:left;
}
#floating-tarefa-dropdown button:hover { background:#f3f4f6; }
#floating-tarefa-dropdown button.success { color:#16a34a; }
#floating-tarefa-dropdown button.success:hover { background:#f0fdf4; }
#floating-tarefa-dropdown button.danger { color:#dc2626; }
#floating-tarefa-dropdown button.danger:hover { background:#fef2f2; }

/* Modal */
.modal { display:none; position:fixed; z-index:9998; inset:0; background:rgba(0,0,0,.5); align-items:center; justify-content:center; }
.modal-content { background:white; border-radius:10px; overflow:hidden; width:90%; }
.modal-header { padding:16px 20px; border-bottom:1px solid #f3f4f6; display:flex; justify-content:space-between; align-items:center; }
.modal-body { padding:20px; }
.modal-footer { padding:14px 20px; background:#f9fafb; display:flex; justify-content:flex-end; gap:10px; border-top:1px solid #f3f4f6; }
.modal-close { background:none; border:none; font-size:20px; cursor:pointer; color:#9ca3af; }
.filtro-opcoes { display:flex; gap:12px; margin-top:6px; flex-wrap:wrap; }
.search-box { display:flex; align-items:center; background:#fff; border:1px solid #d1d5db; border-radius:8px; padding:9px 14px; }
.search-box input { border:none; outline:none; margin-left:10px; width:100%; font-size:13px; }

/* Autocomplete */
#autocomplete-lista div { padding:10px 14px; cursor:pointer; font-size:13px; border-bottom:1px solid #f3f4f6; }
#autocomplete-lista div:hover { background:#f3f4f6; }
#autocomplete-lista div small { color:#9ca3af; font-size:11px; margin-left:6px; }

/* Botão ação inline */
.btn-acao { background:none; border:1px solid #e5e7eb; border-radius:6px; padding:5px 10px; cursor:pointer; font-size:13px; color:#374151; }
.btn-acao:hover { background:#f3f4f6; }

/* Radio buttons de atendido/não atendido */
#btn-atendido.selecionado     { background:#dcfce7; }
#btn-nao-atendido.selecionado { background:#fee2e2; }
</style>

<script>
// ─── ESTADO GLOBAL ──────────────────────────────────────────
let tarefasMaster = [];
let currentTarefaId = null;
let searchTimer;

// ─── INICIALIZAÇÃO ──────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    carregarTarefas();

    document.getElementById('search-tarefa').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => renderTarefas(filtrarTarefas()), 250);
    });

    // Autocomplete de cliente
    document.getElementById('tarefa-cliente-busca').addEventListener('input', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => buscarClienteAutocomplete(this.value), 300);
    });

    // Fechar autocomplete ao clicar fora
    document.addEventListener('click', (e) => {
        if (!e.target.closest('#tarefa-cliente-busca') && !e.target.closest('#autocomplete-lista')) {
            document.getElementById('autocomplete-lista').style.display = 'none';
        }
        const dd = document.getElementById('floating-tarefa-dropdown');
        if (dd && !dd.contains(e.target) && !e.target.classList.contains('btn-acao')) {
            dd.style.display = 'none';
        }
    });

    // Radio buttons visual
    document.querySelectorAll('[name="status-contato"]').forEach(radio => {
        radio.parentElement.addEventListener('click', function() {
            document.getElementById('btn-atendido').classList.remove('selecionado');
            document.getElementById('btn-nao-atendido').classList.remove('selecionado');
            this.classList.add('selecionado');
            this.querySelector('input').checked = true;
        });
    });
});

// ─── CARREGAR TAREFAS ────────────────────────────────────────
async function carregarTarefas() {
    try {
        const res  = await fetch('api/tarefas.php');
        const json = await res.json();
        if (json.success) {
            tarefasMaster = json.data;
            renderTarefas(tarefasMaster);
            atualizarKPIs(tarefasMaster);
        }
    } catch(e) {
        document.getElementById('tarefas-body').innerHTML =
            '<tr><td colspan="7" class="text-center">Erro ao carregar tarefas.</td></tr>';
    }
}

// ─── FILTRAR ─────────────────────────────────────────────────
function filtrarTarefas() {
    const busca      = document.getElementById('search-tarefa').value.toLowerCase();
    const statusSel  = [...document.querySelectorAll('[name="ft-status"]:checked')].map(c => c.value);
    const priSel     = [...document.querySelectorAll('[name="ft-prioridade"]:checked')].map(c => c.value);
    const tipoSel    = [...document.querySelectorAll('[name="ft-tipo"]:checked')].map(c => c.value);
    const dataIni    = document.getElementById('ft-data-inicio').value;
    const dataFim    = document.getElementById('ft-data-fim').value;

    return tarefasMaster.filter(t => {
        const mBusca  = !busca || t.cliente_nome.toLowerCase().includes(busca) || t.titulo.toLowerCase().includes(busca);
        const mStatus = !statusSel.length || statusSel.includes(t.status);
        const mPri    = !priSel.length    || priSel.includes(t.prioridade);
        const mTipo   = !tipoSel.length   || tipoSel.includes(t.tipo);
        const data    = (t.data_agendada || '').substring(0, 10);
        const mIni    = !dataIni || data >= dataIni;
        const mFim    = !dataFim || data <= dataFim;
        return mBusca && mStatus && mPri && mTipo && mIni && mFim;
    });
}

function executarFiltroTarefas() {
    const res = filtrarTarefas();
    renderTarefas(res);
    atualizarKPIs(res);
    fecharModalTarefa('filtro-tarefas');
}

// ─── RENDERIZAR TABELA ───────────────────────────────────────
function renderTarefas(lista) {
    const tbody = document.getElementById('tarefas-body');
    if (!lista || lista.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="text-center">Nenhuma tarefa encontrada.</td></tr>';
        return;
    }

    const tipoIcone = { whatsapp:'💬', ligacao:'📞', email:'📧', visita:'🤝', outro:'📌' };

    tbody.innerHTML = lista.map(t => {
        const atrasada = t.atrasada == 1 && t.status === 'pendente';
        const dataFmt  = formatarDataHora(t.data_agendada);

        return `
        <tr style="${atrasada ? 'background:#fff5f5;' : ''}">
            <td>
                <strong>${escHtml(t.cliente_nome)}</strong>
                ${t.cliente_telefone ? `<div style="font-size:11px;color:#9ca3af;">📞 ${escHtml(t.cliente_telefone)}</div>` : ''}
            </td>
            <td>
                ${escHtml(t.titulo)}
                ${t.descricao ? `<div style="font-size:11px;color:#9ca3af;">${escHtml(t.descricao.substring(0,60))}...</div>` : ''}
            </td>
            <td style="font-size:18px;" title="${t.tipo}">${tipoIcone[t.tipo] || '📌'}</td>
            <td style="white-space:nowrap; font-size:12px;">
                ${dataFmt}
                ${atrasada ? '<br><span class="badge-atrasada">⚠ ATRASADA</span>' : ''}
            </td>
            <td><span class="bp-${t.prioridade}">${t.prioridade.toUpperCase()}</span></td>
            <td><span class="bs-${t.status}">${t.status.toUpperCase()}</span></td>
            <td class="text-center">
                <button class="btn-acao" onclick="abrirMenuTarefa(this, ${t.id})">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
            </td>
        </tr>`;
    }).join('');
}

// ─── KPIs ────────────────────────────────────────────────────
function atualizarKPIs(lista) {
    const hoje = new Date().toISOString().substring(0, 10);
    document.getElementById('kpi-total').textContent     = lista.length;
    document.getElementById('kpi-pendentes').textContent = lista.filter(t => t.status === 'pendente').length;
    document.getElementById('kpi-atrasadas').textContent = lista.filter(t => t.atrasada == 1 && t.status === 'pendente').length;
    document.getElementById('kpi-concluidas').textContent= lista.filter(t => t.status === 'concluida' && (t.data_agendada||'').startsWith(hoje)).length;
}

// ─── DROPDOWN FLUTUANTE (sem corte) ─────────────────────────
window.abrirMenuTarefa = function(btn, id) {
    const dd = document.getElementById('floating-tarefa-dropdown');

    if (dd.style.display === 'block' && currentTarefaId === id) {
        dd.style.display = 'none';
        currentTarefaId = null;
        return;
    }

    currentTarefaId = id;
    const tarefa = tarefasMaster.find(t => t.id == id);

    dd.innerHTML = `
        ${tarefa && tarefa.status === 'pendente' ? `<button class="success" onclick="abrirModalContato(${id})"><i class="fas fa-check"></i> Registrar Contato</button>` : ''}
        <button onclick="editarTarefa(${id})"><i class="fas fa-edit"></i> Editar</button>
        <button class="danger" onclick="excluirTarefa(${id})"><i class="fas fa-trash"></i> Excluir</button>
    `;

    dd.style.display = 'block';

    const rect       = btn.getBoundingClientRect();
    const ddH        = dd.offsetHeight || 120;
    const spaceBelow = window.innerHeight - rect.bottom;
    const left       = Math.max(0, rect.right - 180);

    dd.style.left = left + 'px';
    dd.style.top  = (spaceBelow < ddH ? rect.top - ddH - 4 : rect.bottom + 4) + 'px';
};

// ─── MODAL NOVA TAREFA ───────────────────────────────────────
function abrirModalNovaTarefa() {
    document.getElementById('tarefa-id').value               = '';
    document.getElementById('tarefa-cliente-id').value       = '';
    document.getElementById('tarefa-cliente-busca').value    = '';
    document.getElementById('tarefa-titulo').value           = '';
    document.getElementById('tarefa-tipo').value             = 'whatsapp';
    document.getElementById('tarefa-prioridade').value       = 'media';
    document.getElementById('tarefa-data').value             = '';
    document.getElementById('tarefa-descricao').value        = '';
    document.getElementById('modal-tarefa-titulo').innerHTML = '<i class="fas fa-plus"></i> Nova Tarefa';
    document.getElementById('modal-tarefa').style.display    = 'flex';
}

// ─── AUTOCOMPLETE CLIENTE ────────────────────────────────────
async function buscarClienteAutocomplete(termo) {
    if (termo.length < 2) {
        document.getElementById('autocomplete-lista').style.display = 'none';
        return;
    }
    try {
        const res  = await fetch('api/clientes.php');
        const json = await res.json();

        if (!json.success) return;

        const filtrados = json.data.filter(c =>
            c.nome.toLowerCase().includes(termo.toLowerCase())
        ).slice(0, 8);

        const lista = document.getElementById('autocomplete-lista');
        if (filtrados.length === 0) { lista.style.display = 'none'; return; }

        lista.innerHTML = filtrados.map(c => `
            <div onclick="selecionarCliente(${c.id}, '${escHtml(c.nome)}', '${escHtml(c.telefone || '')}')">
                ${escHtml(c.nome)}
                <small>${c.telefone || ''}</small>
            </div>
        `).join('');
        lista.style.display = 'block';
    } catch(e) {}
}

function selecionarCliente(id, nome, tel) {
    document.getElementById('tarefa-cliente-id').value    = id;
    document.getElementById('tarefa-cliente-busca').value = nome;
    document.getElementById('autocomplete-lista').style.display = 'none';
}

// ─── SALVAR TAREFA ────────────────────────────────────────────
window.salvarTarefa = async function() {
    const clienteId = document.getElementById('tarefa-cliente-id').value;
    const titulo    = document.getElementById('tarefa-titulo').value;
    const data      = document.getElementById('tarefa-data').value;

    if (!clienteId || !titulo || !data) {
        mostrarToast('Preencha: Cliente, Título e Data', 'error');
        return;
    }

    const id = document.getElementById('tarefa-id').value;
    const payload = {
        cliente_id:    clienteId,
        titulo,
        tipo:          document.getElementById('tarefa-tipo').value,
        prioridade:    document.getElementById('tarefa-prioridade').value,
        data_agendada: data.replace('T', ' '),
        descricao:     document.getElementById('tarefa-descricao').value,
    };

    try {
        const res  = await fetch(id ? `api/tarefas.php?id=${id}` : 'api/tarefas.php', {
            method:  id ? 'PUT' : 'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            mostrarToast(id ? 'Tarefa atualizada!' : 'Tarefa criada!', 'success');
            fecharModalTarefa('tarefa');
            carregarTarefas();
        } else {
            mostrarToast(json.message || 'Erro ao salvar', 'error');
        }
    } catch(e) {
        mostrarToast('Erro de conexão', 'error');
    }
};

// ─── EDITAR TAREFA ────────────────────────────────────────────
window.editarTarefa = function(id) {
    const t = tarefasMaster.find(x => x.id == id);
    if (!t) return;

    document.getElementById('tarefa-id').value               = t.id;
    document.getElementById('tarefa-cliente-id').value       = t.cliente_id;
    document.getElementById('tarefa-cliente-busca').value    = t.cliente_nome;
    document.getElementById('tarefa-titulo').value           = t.titulo;
    document.getElementById('tarefa-tipo').value             = t.tipo;
    document.getElementById('tarefa-prioridade').value       = t.prioridade;
    document.getElementById('tarefa-data').value             = (t.data_agendada || '').replace(' ', 'T');
    document.getElementById('tarefa-descricao').value        = t.descricao || '';
    document.getElementById('modal-tarefa-titulo').innerHTML = '<i class="fas fa-edit"></i> Editar Tarefa';
    document.getElementById('modal-tarefa').style.display    = 'flex';
    document.getElementById('floating-tarefa-dropdown').style.display = 'none';
};

// ─── REGISTRAR CONTATO ────────────────────────────────────────
window.abrirModalContato = function(id) {
    const t = tarefasMaster.find(x => x.id == id);
    if (!t) return;

    document.getElementById('contato-tarefa-id').value    = t.id;
    document.getElementById('contato-cliente-id').value   = t.cliente_id;
    document.getElementById('contato-cliente-nome').textContent = t.cliente_nome;
    document.getElementById('contato-tipo').value         = t.tipo;

    // Reset radio buttons
    document.querySelectorAll('[name="status-contato"]').forEach(r => r.checked = false);
    document.getElementById('btn-atendido').classList.remove('selecionado');
    document.getElementById('btn-nao-atendido').classList.remove('selecionado');
    document.getElementById('contato-descricao').value = '';

    document.getElementById('modal-contato').style.display = 'flex';
    document.getElementById('floating-tarefa-dropdown').style.display = 'none';
};

window.salvarContato = async function() {
    const statusRadio = document.querySelector('[name="status-contato"]:checked');
    if (!statusRadio) { mostrarToast('Selecione: Atendido ou Não Atendido', 'error'); return; }

    const payload = {
        cliente_id:     document.getElementById('contato-cliente-id').value,
        tarefa_id:      document.getElementById('contato-tarefa-id').value,
        tipo_contato:   document.getElementById('contato-tipo').value,
        status_contato: statusRadio.value,
        descricao:      document.getElementById('contato-descricao').value,
    };

    try {
        const res  = await fetch('api/historico_contatos.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify(payload)
        });
        const json = await res.json();
        if (json.success) {
            mostrarToast('Contato registrado com sucesso!', 'success');
            fecharModalTarefa('contato');
            carregarTarefas();
        } else {
            mostrarToast(json.message || 'Erro ao registrar', 'error');
        }
    } catch(e) {
        mostrarToast('Erro de conexão', 'error');
    }
};

// ─── EXCLUIR TAREFA ───────────────────────────────────────────
window.excluirTarefa = async function(id) {
    const ok = typeof window.confirmarExclusao === 'function'
        ? await window.confirmarExclusao('Excluir esta tarefa?')
        : confirm('Excluir esta tarefa?');
    if (!ok) return;

    try {
        const res  = await fetch(`api/tarefas.php?id=${id}`, { method: 'DELETE' });
        const json = await res.json();
        if (json.success) {
            mostrarToast('Tarefa excluída!', 'success');
            carregarTarefas();
        }
    } catch(e) {
        mostrarToast('Erro de conexão', 'error');
    }
    document.getElementById('floating-tarefa-dropdown').style.display = 'none';
};

// ─── MODAIS ───────────────────────────────────────────────────
function abrirModalFiltroTarefas() {
    document.getElementById('modal-filtro-tarefas').style.display = 'flex';
}
function fecharModalTarefa(tipo) {
    document.getElementById(`modal-${tipo}`).style.display = 'none';
}

// ─── UTILITÁRIOS ──────────────────────────────────────────────
function formatarDataHora(d) {
    if (!d) return '—';
    const dt = new Date(d.replace(' ', 'T'));
    return isNaN(dt) ? d : dt.toLocaleString('pt-BR', { day:'2-digit', month:'2-digit', year:'numeric', hour:'2-digit', minute:'2-digit' });
}
function escHtml(t) {
    if (!t) return '';
    const div = document.createElement('div');
    div.textContent = String(t);
    return div.innerHTML;
}
</script>

<?php require_once 'includes/footer.php'; ?>