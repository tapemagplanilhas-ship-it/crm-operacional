window.renderChart = function () {
    console.warn('🚫 renderChart bloqueado para debug');
};


// ===============================
// CONTROLE DE GRÁFICOS (Chart.js)
// ===============================
const chartInstances = new Map();
// ==============================================
// VARIÁVEIS GLOBAIS
// ==============================================
let clienteOriginal = {};
let sugestoesAtivas = false;

// ==============================================
// FUNÇÕES DE MODAL
// ==============================================

// Abrir modal cliente
function abrirModalCliente(clienteId = null) {
    const modal = document.getElementById('modal-cliente');
    const form = document.getElementById('form-cliente');
    const btnSalvar = document.getElementById('btn-salvar-cliente');

    // Resetar form
    form.reset();
    btnSalvar.style.display = 'none';

    // Mostrar/ocultar campos automáticos
    const camposAuto = document.querySelector('.campos-automaticos');
    if (clienteId) {
        // Modo edição
        document.getElementById('modal-cliente-title').textContent = 'Editar Cliente';
        camposAuto.style.display = 'block';
        carregarClienteParaEdicao(clienteId);
    } else {
        // Modo novo
        document.getElementById('modal-cliente-title').textContent = 'Novo Cliente';
        camposAuto.style.display = 'none';
        document.getElementById('cliente-id').value = '';
    }

    // Salvar estado original
    clienteOriginal = {
        nome: document.getElementById('cliente-nome').value,
        telefone: document.getElementById('cliente-telefone').value,
        email: document.getElementById('cliente-email').value,
        observacoes: document.getElementById('cliente-observacoes').value
    };

    // Mostrar modal
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Abrir modal venda
function abrirModalVenda(clienteId = null, clienteNome = '') {
    const modal = document.getElementById('modal-venda');
    const form = document.getElementById('form-venda');

    form.reset();

    if (clienteId) {
        document.getElementById('venda-cliente-id').value = clienteId;
        document.getElementById('venda-cliente-nome').value = clienteNome || 'Cliente não encontrado';
    }

    // Data atual
    const hoje = new Date();
    document.getElementById('venda-data').value = formatarDataParaInput(hoje);
    document.getElementById('venda-status').value = 'concluida';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Abrir modal venda rápida
function abrirModalVendaRapida() {
    const modal = document.getElementById('modal-venda-rapida');
    const form = document.getElementById('form-venda-rapida');

    form.reset();

    // Limpar sugestões
    document.getElementById('clientes-sugestoes').innerHTML = '';
    document.getElementById('venda-rapida-cliente-id').value = '';

    // Data atual
    const hoje = new Date();
    document.getElementById('venda-rapida-data').value = formatarDataParaInput(hoje);

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // Focar no campo de busca
    setTimeout(() => {
        document.getElementById('venda-rapida-cliente').focus();
    }, 100);
}

// Fechar modal
function fecharModal(tipo) {
    const modal = document.getElementById(`modal-${tipo}`);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    // Limpar sugestões se for venda rápida
    if (tipo === 'venda-rapida') {
        document.getElementById('clientes-sugestoes').innerHTML = '';
        sugestoesAtivas = false;
    }
}

// Fechar modal com ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal');
        modals.forEach(modal => {
            if (modal.style.display === 'flex') {
                const tipo = modal.id.replace('modal-', '');
                fecharModal(tipo);
            }
        });
    }
});

// Fechar modal clicando fora
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        const tipo = e.target.id.replace('modal-', '');
        fecharModal(tipo);
    }
});

// Abrir modal a partir de um URL que retorna o HTML do modal (ex: 'modals/modal_editar_usuario.php?id=...')
window.abrirModal = async function (url) {
    try {
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Falha ao carregar modal');
        const text = await res.text();
        const parser = new DOMParser();
        const doc = parser.parseFromString(text, 'text/html');

        // Procurar o primeiro elemento com classe .modal
        const modalElem = doc.querySelector('.modal');
        if (!modalElem) {
            console.error('Modal não encontrado no conteúdo retornado:', url);

            // Mostrar preview do conteúdo retornado para depuração
            console.log('Preview do conteúdo retornado (até 1000 chars):', text.slice(0, 1000));

            // Detectar possível redirecionamento para login (sessão expirada)
            if (text.includes('login.php') || /name=["']?login["']?/i.test(text) || text.toLowerCase().includes('login')) {
                mostrarToast('Sessão expirada ou não autorizado. Recarregue a página e faça login novamente.', 'error');
            } else {
                mostrarToast('Conteúdo do modal inválido (ver console para detalhes)', 'error');
            }

            return;
        }

        // Remover modal remoto anterior, se existir
        const previous = document.getElementById('modal-remote-container');
        if (previous) previous.remove();

        // Criar container e anexar modal
        const container = document.createElement('div');
        container.id = 'modal-remote-container';
        container.appendChild(modalElem);
        document.body.appendChild(container);

        // Executar scripts presentes no documento retornado
        const scripts = doc.querySelectorAll('script');
        scripts.forEach(s => {
            const newScript = document.createElement('script');
            if (s.src) {
                newScript.src = s.src;
                newScript.async = false;
            }
            newScript.textContent = s.textContent;
            document.body.appendChild(newScript);
        });

        // Mostrar modal recém-inserido
        const modalInserted = container.querySelector('.modal');
        if (modalInserted) {
            modalInserted.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Rodar inicializações pequenas (focar no primeiro campo e validação básica)
            setTimeout(() => {
                const firstField = modalInserted.querySelector('input, select, textarea');
                if (firstField) firstField.focus();

                const inputs = modalInserted.querySelectorAll('input[required]');
                inputs.forEach(input => {
                    input.addEventListener('blur', function () {
                        if (!this.value.trim()) this.classList.add('error'); else this.classList.remove('error');
                    });
                });
            }, 10);
        }

    } catch (err) {
        console.error(err);
        mostrarToast('Erro ao abrir modal', 'error');
    }
};

// Robust closeModal helper (handles both static modals and remote modals)
window.closeModal = function (identifier) {
    // If an identifier is provided, try to close that modal
    if (identifier) {
        const id = identifier.startsWith('modal-') ? identifier : ('modal-' + identifier);
        const modal = document.getElementById(id);
        if (modal) {
            modal.style.display = 'none';
            document.body.style.overflow = 'auto';
            return;
        }
    }

    // Fallback: remove remote modal container
    const remote = document.getElementById('modal-remote-container');
    if (remote) remote.remove();
    document.body.style.overflow = 'auto';
};

// =============================
// Dashboard layout & editor
// =============================
let currentDashboardLayout = null;

async function loadDashboardLayout() {
    try {
        const res = await fetch('api/dashboard_layout.php', { credentials: 'same-origin' });
        if (!res.ok) {
            const txt = await res.text();
            console.error('Erro ao carregar layout: HTTP ' + res.status + ' - ' + txt.slice(0, 1000));
            mostrarToast('Erro ao carregar layout (HTTP ' + res.status + ')', 'error');
            return;
        }

        const text = await res.text();
        let json;
        try {
            json = JSON.parse(text);
        } catch (parseErr) {
            console.error('Erro ao carregar layout: resposta não é JSON (mostrando começo):', text.slice(0, 1000));
            mostrarToast('Erro ao carregar layout: resposta inválida (ver console)', 'error');
            return;
        }

        if (json.success && json.data) {
            // layout_json may be string or JSON
            let layout = json.data.layout_json;
            if (typeof layout === 'string') {
                try { layout = JSON.parse(layout); } catch (e) { layout = []; }
            }
            currentDashboardLayout = layout;
            renderDashboardFromLayout(layout);
        }
    } catch (err) {
        console.error('Erro ao carregar layout:', err);
        mostrarToast('Erro ao carregar layout', 'error');
    }
}

function renderDashboardFromLayout(layout) {
    if (!layout || !Array.isArray(layout)) return;
    const container = document.getElementById('stats-container');
    if (!container) return;
    container.innerHTML = '';

    layout.forEach(card => {
        const el = document.createElement('div');
        el.className = 'stat-card';
        el.dataset.instanceId = card.instance_id || card.id;

        // create icon element
        const icon = document.createElement('div');
        icon.className = 'stat-icon';
        icon.style.background = card.color || '#be1616';
        icon.innerHTML = card.icon || '<i class="fas fa-chart-bar"></i>';

        const content = document.createElement('div');
        content.className = 'stat-content';
        const h3 = document.createElement('h3');
        h3.textContent = card.title || '';
        const p = document.createElement('p');
        p.className = 'stat-value';
        p.textContent = '—';

        content.appendChild(h3);
        content.appendChild(p);

        el.appendChild(icon);
        el.appendChild(content);

        container.appendChild(el);
   
        // load data
        if (card.type === 'stat' && card.metric) {
            fetch('api/dashboard_stats.php?metric=' + encodeURIComponent(card.metric))
                .then(r => r.json())
                .then(data => {
                    if (data.success) {
                        const moneyMetrics = new Set([
                            'valor_mes',
                            'faturamento_mes',
                            'ticket_medio_mes',
                            'projecao_mes',
                            'necessario_por_dia'
                        ]);

                        const percentMetrics = new Set([
                            'taxa_fechamento',
                            'meta_atingida_percent'
                        ]);

                        if (moneyMetrics.has(card.metric)) {
                            p.textContent = 'R$ ' + Number(data.value).toLocaleString('pt-BR', {
                                minimumFractionDigits: 2,
                                maximumFractionDigits: 2
                            });
                        } else if (percentMetrics.has(card.metric)) {
                            p.textContent = Number(data.value).toFixed(1) + '%';
                        } else {
                            p.textContent = data.value;
                        }
                    }

                });
        } else if (card.type === 'chart') {
}



function renderChart(canvas, metric) {

    // Se já existe gráfico nesse canvas, destrói
    if (chartInstances.has(canvas)) {
        chartInstances.get(canvas).destroy();
        chartInstances.delete(canvas);
    }

    fetch('api/dashboard_stats.php?metric=' + encodeURIComponent(metric))
        .then(r => r.json())
        .then(data => {
            if (!data || data.success !== true) {
                console.error('❌ Dados inválidos do gráfico:', metric, data);
                return;
            }

            const chart = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: data.labels || [],
                    datasets: [{
                        data: data.values || [],
                        borderWidth: 2,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // MUITO importante
                    plugins: {
                        legend: { display: false }
                    }
                }
            });

            chartInstances.set(canvas, chart);
        })
        .catch(err => {
            console.error('❌ Erro ao carregar gráfico:', metric, err);
        });
}


// Editor
async function openDashboardEditor() {
    const modal = document.getElementById('modal-dashboard-editor');
    if (!modal) return;
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // load layout
    try {
        const res = await fetch('api/dashboard_layout.php');
        const json = await res.json();
        let layout = [];
        if (json.success && json.data) {
            layout = json.data.layout_json;
            if (typeof layout === 'string') {
                try { layout = JSON.parse(layout); } catch (e) { layout = []; }
            }
        }
        currentDashboardLayout = layout || [];
        // set current layout id and metadata if available
        window.currentLayoutId = json.data && json.data.id ? json.data.id : null;
        // set name and shared flag inputs if present
        const nameInput = document.getElementById('editor-layout-name');
        const sharedInput = document.getElementById('editor-layout-shared');
        if (nameInput) nameInput.value = json.data && json.data.name ? json.data.name : 'Layout do usuário';
        if (sharedInput) sharedInput.checked = json.data && json.data.is_shared == 1 ? true : false;
        renderEditorLayout(currentDashboardLayout);
    } catch (err) {
        console.error(err);
    }
}
window.openDashboardEditor = openDashboardEditor;


function renderEditorLayout(layout) {
    const list = document.getElementById('editor-layout-list');
    list.innerHTML = '';
    layout.forEach(card => {
        const item = document.createElement('div');
        item.className = 'editor-item';
        item.dataset.instanceId = card.instance_id || card.id;
        item.innerHTML = `<div class="editor-handle">☰</div><div class="editor-title">${card.title}</div><button class="btn btn-link btn-remove">Remover</button>`;
        list.appendChild(item);
    });

    // attach remove handlers (by instance id)
    list.querySelectorAll('.btn-remove').forEach((btn) => {
        btn.onclick = (e) => {
            const item = e.target.closest('.editor-item');
            const id = item.dataset.instanceId;
            const idx = currentDashboardLayout.findIndex(x => x.instance_id === id);
            if (idx !== -1) {
                currentDashboardLayout.splice(idx, 1);
                renderEditorLayout(currentDashboardLayout);
            }
        };
    });

    // init sortable
    if (typeof Sortable !== 'undefined') {
        Sortable.create(list, {
            handle: '.editor-handle', animation: 150, onEnd: function (evt) {
                // update currentDashboardLayout order
                const newOrder = [...list.children].map(ch => ch.dataset.instanceId);
                currentDashboardLayout = newOrder.map(id => currentDashboardLayout.find(c => c.instance_id === id) || createCardById(id));
                renderEditorLayout(currentDashboardLayout);
            }
        });
    } else {
        // load Sortable
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
        s.onload = () => {
            Sortable.create(list, {
                handle: '.editor-handle', animation: 150, onEnd: function (evt) {
                    const newOrder = [...list.children].map(ch => ch.dataset.instanceId);
                    currentDashboardLayout = newOrder.map(id => currentDashboardLayout.find(c => c.instance_id === id) || createCardById(id));
                    renderEditorLayout(currentDashboardLayout);
                }
            });
        };
        document.body.appendChild(s);
    }

    // attach add buttons
    document.querySelectorAll('#editor-available-cards button').forEach(btn => {
        btn.onclick = () => {
            const id = btn.dataset.cardId;
            let card = createCardById(id);
            currentDashboardLayout.push(card);
            renderEditorLayout(currentDashboardLayout);
        };
    });

    // init sortable
    if (typeof Sortable !== 'undefined') {
        Sortable.create(list, { handle: '.editor-handle' });
    } else {
        // load Sortable
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';
        s.onload = () => { Sortable.create(list, { handle: '.editor-handle' }); };
        document.body.appendChild(s);
    }
}

function generateInstanceId() {
    return 'c_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
}

function createCardById(id) {
    const map = {
        'card_faturamento_mes': {
            id: 'card_faturamento_mes',
            type: 'stat',
            title: 'Faturamento do Mês',
            icon: 'fa-solid fa-sack-dollar',
            metric: 'faturamento_mes'
        },
        'card_qtd_vendas_mes': {
            id: 'card_qtd_vendas_mes',
            type: 'stat',
            title: 'Vendas do Mês',
            icon: 'fa-solid fa-cart-shopping',
            metric: 'qtd_vendas_mes'
        },
        'card_ticket_medio_mes': {
            id: 'card_ticket_medio_mes',
            type: 'stat',
            title: 'Ticket Médio (Mês)',
            metric: 'ticket_medio_mes'
        },
        'card_clientes_perdidos_60d': {
            id: 'card_clientes_perdidos_60d',
            type: 'stat',
            title: 'Clientes Perdidos (60d)',
            metric: 'clientes_perdidos_60d'
        },
        'card_projecao_mes': {
            id: 'card_projecao_mes',
            type: 'stat',
            title: 'Projeção do Mês',
            metric: 'projecao_mes'
        },
        'card_meta_atingida_percent': {
            id: 'card_meta_atingida_percent',
            type: 'stat',
            title: '% da Meta (Mês)',
            metric: 'meta_atingida_percent'
        },
        'card_necessario_por_dia': {
            id: 'card_necessario_por_dia',
            type: 'stat',
            title: 'Necessário por Dia',
            metric: 'necessario_por_dia'
        },

        'card_total_clientes': {
            id: 'card_total_clientes',
            type: 'stat',
            title: 'Total de Clientes',
            metric: 'total_clients'
        },

        'card_vendas_mes': {
            id: 'card_vendas_mes',
            type: 'stat',
            title: 'Vendas do Mês',
            metric: 'vendas_mes'
        },
        'card_valor_mes': {
            id: 'card_valor_mes',
            type: 'stat',
            title: 'Valor do Mês',
            metric: 'valor_mes'
        },
        'card_taxa_fechamento': {
            id: 'card_taxa_fechamento',
            type: 'stat',
            title: 'Taxa de Fechamento',
            metric: 'taxa_fechamento'
        },
        'card_clientes_inativos': {
            id: 'card_clientes_inativos',
            type: 'stat',
            title: 'Clientes Inativos',
            metric: 'clientes_inativos'
        },
        'card_total_negociacoes': {
            id: 'card_total_negociacoes',
            type: 'stat',
            title: 'Total de Negociações',
            metric: 'total_negociacoes'
        },
    };
    // clone and add unique instance id
    const base = JSON.parse(JSON.stringify(map[id] || { id: id, type: 'stat', title: id, metric: '' }));
    base.instance_id = generateInstanceId();
    return base;
}

async function saveDashboardLayout() {
    // collect order and items
    const list = document.getElementById('editor-layout-list');
    if (!list) return;

    const items = [...list.children];
    // Build layout in the DOM order using instance ids
    const layout = items.map(it => {
        const iid = it.dataset.instanceId;
        const c = currentDashboardLayout.find(x => x.instance_id === iid);
        return c || createCardById(it.dataset.instanceId || it.dataset.cardId);
    });

    const nameInput = document.getElementById('editor-layout-name');
    const sharedInput = document.getElementById('editor-layout-shared');
    const payload = { name: nameInput ? nameInput.value : 'Layout do usuário', layout_json: layout };
    if (window.currentLayoutId) payload.id = window.currentLayoutId;
    if (sharedInput && sharedInput.checked) payload.is_shared = true;

    try {
        const resp = await fetch('api/dashboard_layout.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const j = await resp.json();
        if (j.success) {
            mostrarToast('Layout salvo com sucesso', 'success');
            closeModal('modal-dashboard-editor');
            currentDashboardLayout = layout;
            // if server returned id for new layout, store it
            if (j.id) window.currentLayoutId = j.id;
            renderDashboardFromLayout(layout);
        } else {
            mostrarToast(j.message || 'Erro ao salvar', 'error');
        }
    } catch (err) {
        console.error(err);
        mostrarToast('Erro ao salvar layout', 'error');
    }
}

async function resetToDefault() {
    try {
        const res = await fetch('api/dashboard_layout.php?list=1');
        const j = await res.json();
        if (j.success && Array.isArray(j.data)) {
            const shared = j.data.find(l => parseInt(l.is_shared) === 1);
            if (shared && shared.layout_json) {
                let layout = shared.layout_json;
                if (typeof layout === 'string') { try { layout = JSON.parse(layout); } catch (e) { layout = []; } }
                currentDashboardLayout = layout;
                window.currentLayoutId = null; // reset to new layout when saving
                document.getElementById('editor-layout-name').value = shared.name || 'Padrão';
                document.getElementById('editor-layout-shared').checked = false;
                renderEditorLayout(currentDashboardLayout);
                mostrarToast('Layout padrão carregado', 'success');
                return;
            }
        }
        mostrarToast('Não foi possível carregar layout padrão', 'error');
    } catch (err) {
        console.error(err);
        mostrarToast('Erro ao resetar layout', 'error');
    }
}

// Auto-load layout on page ready
document.addEventListener('DOMContentLoaded', function () {
    loadDashboardLayout();
});

// ==============================================
// FUNÇÕES DE CLIENTE
// ==============================================

// Verificar alterações no cliente
function verificarAlteracoesCliente() {
    const btnSalvar = document.getElementById('btn-salvar-cliente');
    if (!btnSalvar) return;

    const dadosAtuais = {
        nome: document.getElementById('cliente-nome').value,
        telefone: document.getElementById('cliente-telefone').value,
        email: document.getElementById('cliente-email').value,
        observacoes: document.getElementById('cliente-observacoes').value
    };

    // Comparar com original
    const houveAlteracao =
        dadosAtuais.nome !== clienteOriginal.nome ||
        dadosAtuais.telefone !== clienteOriginal.telefone ||
        dadosAtuais.email !== clienteOriginal.email ||
        dadosAtuais.observacoes !== clienteOriginal.observacoes;

    // Mostrar/ocultar botão salvar
    btnSalvar.style.display = houveAlteracao ? 'block' : 'none';
}

// Carregar cliente para edição
async function carregarClienteParaEdicao(clienteId) {
    try {
        const response = await fetch(`api/clientes.php?id=${clienteId}`);
        const data = await response.json();

        if (data.success && data.data) {
            const cliente = data.data;

            // Preencher campos editáveis
            document.getElementById('cliente-id').value = cliente.id;
            document.getElementById('cliente-nome').value = cliente.nome || '';
            document.getElementById('cliente-telefone').value = cliente.telefone || '';
            document.getElementById('cliente-email').value = cliente.email || '';
            document.getElementById('cliente-observacoes').value = cliente.observacoes || '';

            // Salvar estado original
            clienteOriginal = {
                nome: cliente.nome || '',
                telefone: cliente.telefone || '',
                email: cliente.email || '',
                observacoes: cliente.observacoes || ''
            };

            // Preencher campos automáticos
            document.getElementById('cliente-ultima-venda').value =
                cliente.ultima_venda ? formatarDataParaExibicao(cliente.ultima_venda) : 'Nunca';
            document.getElementById('cliente-media-gastos').value =
                formatarMoedaParaExibicao(cliente.media_gastos || 0);
            document.getElementById('cliente-total-gasto').value =
                formatarMoedaParaExibicao(cliente.total_gasto || 0);
            document.getElementById('cliente-taxa-fechamento').value =
                cliente.taxa_fechamento ? `${parseFloat(cliente.taxa_fechamento).toFixed(1)}%` : '0%';
        } else {
            mostrarToast('Cliente não encontrado', 'error');
            fecharModal('cliente');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro ao carregar cliente', 'error');
    }
}

// Salvar cliente
async function salvarCliente(event) {
    event.preventDefault();

    const form = document.getElementById('form-cliente');
    const formData = new FormData(form);

    // Converter para objeto
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    // Validar
    if (!data.nome || data.nome.trim() === '') {
        mostrarToast('Nome é obrigatório', 'error');
        return;
    }

    const btnSalvar = document.getElementById('btn-salvar-cliente');
    btnSalvar.disabled = true;
    btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

    try {
        const response = await fetch('api/clientes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast(result.message, 'success');

            // Fechar modal
            fecharModal('cliente');

            // Recarregar página se estiver na lista de clientes
            if (window.location.pathname.includes('clientes.php')) {
                window.location.reload();
            } else {
                // Atualizar dashboard
                setTimeout(() => {
                    if (typeof atualizarDashboard === 'function') {
                        atualizarDashboard();
                    }
                }, 500);
            }
        } else {
            mostrarToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão', 'error');
    } finally {
        btnSalvar.disabled = false;
        btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar Cliente';
    }
}

// ==============================================
// FUNÇÕES DE VENDA
// ==============================================

// Registrar venda normal
async function registrarVenda(event) {
    event.preventDefault();

    const form = document.getElementById('form-venda');
    const formData = new FormData(form);

    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    // Validar
    if (!data.cliente_id) {
        mostrarToast('Cliente é obrigatório', 'error');
        return;
    }

    if (!data.valor || data.valor === 'R$ 0,00') {
        mostrarToast('Valor é obrigatório', 'error');
        return;
    }

    const btnSubmit = form.querySelector('button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    try {
        const response = await fetch('api/vendas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast(result.message, 'success');
            fecharModal('venda');

            // Recarregar página
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão', 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-check"></i> Registrar Venda';
    }
}

// Buscar sugestões de clientes
async function buscarClientesSugestoes(termo) {
    const container = document.getElementById('clientes-sugestoes');

    if (!termo || termo.length < 2) {
        container.innerHTML = '';
        container.style.display = 'none';
        sugestoesAtivas = false;
        return;
    }

    try {
        const response = await fetch('api/clientes.php');
        const data = await response.json();

        if (data.success) {
            const clientesFiltrados = data.data.filter(cliente =>
                cliente.nome.toLowerCase().includes(termo.toLowerCase()) ||
                (cliente.telefone && cliente.telefone.includes(termo)) ||
                (cliente.email && cliente.email.toLowerCase().includes(termo.toLowerCase()))
            ).slice(0, 8);

            if (clientesFiltrados.length > 0) {
                container.innerHTML = clientesFiltrados.map(cliente => `
                    <div class="suggestion-item" 
                         onclick="selecionarClienteSugestao(${cliente.id}, '${cliente.nome.replace(/'/g, "\\'")}')">
                        <strong>${cliente.nome}</strong>
                        ${cliente.telefone ? `<br><small>${cliente.telefone}</small>` : ''}
                    </div>
                `).join('');
                container.style.display = 'block';
                sugestoesAtivas = true;
            } else {
                container.innerHTML = '<div class="suggestion-item">Nenhum cliente encontrado</div>';
                container.style.display = 'block';
                sugestoesAtivas = true;
            }
        }
    } catch (error) {
        console.error('Erro:', error);
    }
}

// Selecionar cliente da sugestão
function selecionarClienteSugestao(clienteId, clienteNome) {
    document.getElementById('venda-rapida-cliente').value = clienteNome;
    document.getElementById('venda-rapida-cliente-id').value = clienteId;
    document.getElementById('clientes-sugestoes').innerHTML = '';
    document.getElementById('clientes-sugestoes').style.display = 'none';
    sugestoesAtivas = false;

    // Focar no campo de valor
    setTimeout(() => {
        document.getElementById('venda-rapida-valor').focus();
    }, 50);
}

// Registrar venda rápida
async function registrarVendaRapida(event) {
    event.preventDefault();

    const clienteId = document.getElementById('venda-rapida-cliente-id').value;
    const clienteNome = document.getElementById('venda-rapida-cliente').value;
    const valor = document.getElementById('venda-rapida-valor').value;
    const dataVenda = document.getElementById('venda-rapida-data').value;

    // Validar
    if (!clienteId) {
        mostrarToast('Selecione um cliente', 'error');
        return;
    }

    if (!valor || valor === 'R$ 0,00') {
        mostrarToast('Valor é obrigatório', 'error');
        return;
    }

    const data = {
        cliente_id: clienteId,
        valor: valor,
        data_venda: dataVenda,
        status: 'concluida',
        observacoes: 'Venda rápida registrada pelo sistema'
    };

    const btnSubmit = document.querySelector('#form-venda-rapida button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    try {
        const response = await fetch('api/vendas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast(result.message, 'success');
            fecharModal('venda-rapida');

            // Recarregar página
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão', 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-bolt"></i> Registrar Venda Rápida';
    }
}

// ==============================================
// FUNÇÕES UTILITÁRIAS
// ==============================================

// Formatar moeda
function formatarMoeda(input) {
    let valor = input.value.replace(/\D/g, '');
    valor = (valor / 100).toFixed(2);
    valor = valor.replace('.', ',');
    valor = valor.replace(/(\d)(?=(\d{3})+,)/g, "$1.");
    input.value = 'R$ ' + valor;
}

// Formatar moeda para exibição
function formatarMoedaParaExibicao(valor) {
    return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Formatar data no input
function formatarData(input) {
    let valor = input.value.replace(/\D/g, '');

    if (valor.length > 8) {
        valor = valor.substring(0, 8);
    }

    if (valor.length > 4) {
        valor = valor.replace(/(\d{2})(\d{2})(\d{0,4})/, "$1/$2/$3");
    } else if (valor.length > 2) {
        valor = valor.replace(/(\d{2})(\d{0,2})/, "$1/$2");
    }

    input.value = valor;
}

// Formatar data para input (dd/mm/aaaa)
function formatarDataParaInput(data) {
    const dia = String(data.getDate()).padStart(2, '0');
    const mes = String(data.getMonth() + 1).padStart(2, '0');
    const ano = data.getFullYear();
    return `${dia}/${mes}/${ano}`;
}

// Formatar data para exibição
function formatarDataParaExibicao(dateString) {
    if (!dateString || dateString === '0000-00-00') return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('pt-BR');
}

// Mostrar toast
function mostrarToast(mensagem, tipo = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.innerHTML = `
        <i class="fas ${tipo === 'success' ? 'fa-check-circle' : tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
        <span>${mensagem}</span>
    `;

    container.appendChild(toast);

    // Remover após 5 segundos
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateY(-20px)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.parentNode.removeChild(toast);
            }
        }, 300);
    }, 5000);
}

// Esconder sugestões ao clicar fora
document.addEventListener('click', (e) => {
    if (sugestoesAtivas &&
        !e.target.closest('#clientes-sugestoes') &&
        !e.target.closest('#venda-rapida-cliente')) {
        document.getElementById('clientes-sugestoes').innerHTML = '';
        document.getElementById('clientes-sugestoes').style.display = 'none';
        sugestoesAtivas = false;
    }
});

// ==============================================
// INICIALIZAÇÃO
// ==============================================
document.addEventListener('DOMContentLoaded', function () {
    // Configurar máscaras iniciais
    const moneyInputs = document.querySelectorAll('.money-input');
    moneyInputs.forEach(input => {
        if (input.value) {
            formatarMoeda(input);
        }
    });

    // Configurar botões do header
    document.getElementById('btn-novo-cliente-topo')?.addEventListener('click', (e) => {
        e.preventDefault();
        abrirModalCliente();
    });

    document.getElementById('btn-venda-rapida-topo')?.addEventListener('click', (e) => {
        e.preventDefault();
        abrirModalVendaRapida();
    });

    // Configurar botões do dashboard
    document.getElementById('btn-novo-cliente-action')?.addEventListener('click', () => {
        abrirModalCliente();
    });

    document.getElementById('btn-venda-rapida-action')?.addEventListener('click', () => {
        abrirModalVendaRapida();
    });
});

// ==============================================
// FUNÇÕES PARA VENDA RÁPIDA
// ==============================================

// Mostrar/ocultar campo de motivo da perda
function mostrarCampoMotivoPerda() {
    const statusSelect = document.getElementById('venda-rapida-status');
    const motivoCampo = document.getElementById('campo-motivo-perda');
    const motivoInput = document.getElementById('venda-rapida-motivo-perda');
    const formaPagamento = document.getElementById('venda-rapida-forma-pagamento');

    if (statusSelect.value === 'perdida') {
        motivoCampo.style.display = 'block';
        motivoInput.required = true;

        // Auto-selecionar N/A para forma de pagamento se for perdida
        if (formaPagamento.value === '') {
            formaPagamento.value = 'na';
        }
    } else {
        motivoCampo.style.display = 'none';
        motivoInput.required = false;
        motivoInput.value = '';

        // Resetar forma de pagamento se não for perdida
        if (formaPagamento.value === 'na' && statusSelect.value === 'concluida') {
            formaPagamento.value = '';
        }
    }
}

// Validar data (aceita passado e futuro)
function validarData(dataStr) {
    if (!dataStr) return false;

    // Verificar formato dd/mm/aaaa
    const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if (!regex.test(dataStr)) return false;

    const [, dia, mes, ano] = dataStr.match(regex);

    // Validar números
    const diaNum = parseInt(dia, 10);
    const mesNum = parseInt(mes, 10);
    const anoNum = parseInt(ano, 10);

    // Validar mês
    if (mesNum < 1 || mesNum > 12) return false;

    // Validar dia baseado no mês
    const diasNoMes = new Date(anoNum, mesNum, 0).getDate();
    if (diaNum < 1 || diaNum > diasNoMes) return false;

    // Validar ano (aceita de 2000 a 2100)
    if (anoNum < 2000 || anoNum > 2100) return false;

    return true;
}

// Formatar data com validação
function formatarDataComValidacao(input) {
    let valor = input.value.replace(/\D/g, '');

    if (valor.length > 8) {
        valor = valor.substring(0, 8);
    }

    if (valor.length > 4) {
        valor = valor.replace(/(\d{2})(\d{2})(\d{0,4})/, "$1/$2/$3");
    } else if (valor.length > 2) {
        valor = valor.replace(/(\d{2})(\d{0,2})/, "$1/$2");
    }

    input.value = valor;

    // Validar e dar feedback visual
    if (valor.length === 10) {
        if (validarData(valor)) {
            input.classList.remove('error');
            input.classList.add('success');
        } else {
            input.classList.add('error');
            input.classList.remove('success');
        }
    } else {
        input.classList.remove('error', 'success');
    }
}

// Atualizar a função de formatar data existente
function formatarData(input) {
    formatarDataComValidacao(input);
}

// Registrar venda rápida (atualizada)
async function registrarVendaRapida(event) {
    event.preventDefault();

    const clienteId = document.getElementById('venda-rapida-cliente-id').value;
    const clienteNome = document.getElementById('venda-rapida-cliente').value;
    const valor = document.getElementById('venda-rapida-valor').value;
    const dataVenda = document.getElementById('venda-rapida-data').value;
    const status = document.getElementById('venda-rapida-status').value;
    const formaPagamento = document.getElementById('venda-rapida-forma-pagamento').value;
    const motivoPerda = document.getElementById('venda-rapida-motivo-perda').value;
    const observacoes = document.getElementById('venda-rapida-observacoes').value;

    // Validações
    if (!clienteId) {
        mostrarToast('Selecione um cliente', 'error');
        return;
    }

    if (!valor || valor === 'R$ 0,00') {
        mostrarToast('Valor é obrigatório', 'error');
        return;
    }

    if (!validarData(dataVenda)) {
        mostrarToast('Data inválida. Use dd/mm/aaaa', 'error');
        return;
    }

    if (!formaPagamento) {
        mostrarToast('Forma de pagamento é obrigatória', 'error');
        return;
    }

    if (status === 'perdida' && !motivoPerda.trim()) {
        mostrarToast('Motivo da perda é obrigatório', 'error');
        return;
    }

    // Validar forma de pagamento para vendas concluídas
    if (status === 'concluida' && formaPagamento === 'na') {
        if (!confirm('Venda CONCLUÍDA com forma de pagamento N/A. Continuar?')) {
            return;
        }
    }

    const data = {
        cliente_id: clienteId,
        valor: valor,
        data_venda: dataVenda,
        status: status,
        forma_pagamento: formaPagamento,
        motivo_perda: motivoPerda,
        observacoes: observacoes
    };

    const btnSubmit = document.querySelector('#form-venda-rapida button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    try {
        const response = await fetch('api/vendas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast(result.message, 'success');
            fecharModal('venda-rapida');

            // Resetar formulário
            document.getElementById('form-venda-rapida').reset();
            document.getElementById('venda-rapida-cliente-id').value = '';
            document.getElementById('campo-motivo-perda').style.display = 'none';

            // Recarregar página após 1 segundo
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão', 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-bolt"></i> Registrar Negociação';
    }
}

// Auto-selecionar forma de pagamento baseada no status
function configurarFormaPagamentoPorStatus() {
    const statusSelect = document.getElementById('venda-rapida-status');
    const formaPagamento = document.getElementById('venda-rapida-forma-pagamento');

    statusSelect.addEventListener('change', function () {
        if (this.value === 'perdida' || this.value === 'orcamento') {
            if (formaPagamento.value === '') {
                formaPagamento.value = 'na';
            }
        }
    });
}
// Mostrar/ocultar campo de motivo da perda para venda normal
function mostrarCampoMotivoPerdaVenda() {
    const statusSelect = document.getElementById('venda-status');
    const motivoCampo = document.getElementById('campo-motivo-perda-venda');
    const motivoInput = document.getElementById('venda-motivo-perda');
    const formaPagamento = document.getElementById('venda-forma-pagamento');

    if (statusSelect.value === 'perdida') {
        motivoCampo.style.display = 'block';
        motivoInput.required = true;

        // Auto-selecionar N/A para forma de pagamento se for perdida
        if (formaPagamento.value === '') {
            formaPagamento.value = 'na';
        }
    } else {
        motivoCampo.style.display = 'none';
        motivoInput.required = false;
        motivoInput.value = '';

        // Resetar forma de pagamento se não for perdida
        if (formaPagamento.value === 'na' && statusSelect.value === 'concluida') {
            formaPagamento.value = '';
        }
    }
}

// Atualizar função abrirModalVenda
function abrirModalVenda(clienteId = null, clienteNome = '') {
    const modal = document.getElementById('modal-venda');
    const form = document.getElementById('form-venda');

    form.reset();

    if (clienteId) {
        document.getElementById('venda-cliente-id').value = clienteId;
        document.getElementById('venda-cliente-nome').value = clienteNome || 'Cliente não encontrado';
    }

    // Data atual
    const hoje = new Date();
    document.getElementById('venda-data').value = formatarDataParaInput(hoje);
    document.getElementById('venda-status').value = 'concluida';
    document.getElementById('venda-forma-pagamento').value = '';
    document.getElementById('campo-motivo-perda-venda').style.display = 'none';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

// Atualizar função registrarVenda
async function registrarVenda(event) {
    event.preventDefault();

    const form = document.getElementById('form-venda');
    const formData = new FormData(form);

    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });

    // Validações
    if (!data.cliente_id) {
        mostrarToast('Cliente é obrigatório', 'error');
        return;
    }

    if (!data.valor || data.valor === 'R$ 0,00') {
        mostrarToast('Valor é obrigatório', 'error');
        return;
    }

    if (!validarData(data.data_venda)) {
        mostrarToast('Data inválida. Use dd/mm/aaaa', 'error');
        return;
    }

    if (!data.forma_pagamento) {
        mostrarToast('Forma de pagamento é obrigatória', 'error');
        return;
    }

    if (data.status === 'perdida' && !data.motivo_perda) {
        mostrarToast('Motivo da perda é obrigatório', 'error');
        return;
    }

    // Validar forma de pagamento para vendas concluídas
    if (data.status === 'concluida' && data.forma_pagamento === 'na') {
        if (!confirm('Venda CONCLUÍDA com forma de pagamento N/A. Continuar?')) {
            return;
        }
    }

    const btnSubmit = form.querySelector('button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';

    try {
        const response = await fetch('api/vendas.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            mostrarToast(result.message, 'success');
            fecharModal('venda');

            // Recarregar página
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexão', 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-check"></i> Registrar Negociação';
    }
}
})};