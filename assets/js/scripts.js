/**
 * scripts.js (reescrito)
 * - Corrige "card is not defined"
 * - Remove duplicidades e trechos soltos
 * - Dashboard: layout dinâmico + editor + salvar
 * - Cards stat funcionando
 * - Gráficos: opcionais (desligável) e com proteção anti-loop
 *
 * Baseado no seu arquivo enviado. :contentReference[oaicite:0]{index=0}
 */

(() => {
  'use strict';

  // ===============================
  // CONFIG
  // ===============================
  const ENABLE_CHARTS = false; // <-- deixe false pra debug. quando estiver tudo ok, mude pra true.
  const CHART_JS_SRC = 'https://cdn.jsdelivr.net/npm/chart.js';
  const SORTABLE_SRC = 'https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js';

  // ===============================
  // STATE
  // ===============================
  let clienteOriginal = {};
  let sugestoesAtivas = false;

  let currentDashboardLayout = [];
  let currentLayoutId = null;

  // Chart.js
  const chartInstances = new Map();
  let chartJsLoadingPromise = null;

  // ===============================
  // HELPERS
  // ===============================
  function $(sel, root = document) {
    return root.querySelector(sel);
  }
  function $all(sel, root = document) {
    return Array.from(root.querySelectorAll(sel));
  }

  function safeJsonParse(text) {
    try { return JSON.parse(text); } catch { return null; }
  }

  function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text ?? '';
    return div.innerHTML;
  }

  function isIconHtml(icon) {
    return typeof icon === 'string' && icon.includes('<');
  }

  function iconToHtml(icon) {
    if (!icon) return '<i class="fas fa-chart-bar"></i>';
    if (isIconHtml(icon)) return icon;

    // icon vindo como "fa-solid fa-sack-dollar"
    return `<i class="${escapeHtml(icon)}"></i>`;
  }

  function moneyBR(value) {
    return 'R$ ' + Number(value || 0).toLocaleString('pt-BR', {
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    });
  }

  function formatarDataParaInput(dateObj) {
    const dia = String(dateObj.getDate()).padStart(2, '0');
    const mes = String(dateObj.getMonth() + 1).padStart(2, '0');
    const ano = dateObj.getFullYear();
    return `${dia}/${mes}/${ano}`;
  }

  function validarData(dataStr) {
    if (!dataStr) return false;
    const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    const m = dataStr.match(regex);
    if (!m) return false;

    const diaNum = parseInt(m[1], 10);
    const mesNum = parseInt(m[2], 10);
    const anoNum = parseInt(m[3], 10);

    if (anoNum < 2000 || anoNum > 2100) return false;
    if (mesNum < 1 || mesNum > 12) return false;

    const diasNoMes = new Date(anoNum, mesNum, 0).getDate();
    if (diaNum < 1 || diaNum > diasNoMes) return false;

    return true;
  }

  function formatarMoeda(input) {
    let valor = String(input.value || '').replace(/\D/g, '');
    valor = (Number(valor) / 100).toFixed(2);
    valor = valor.replace('.', ',');
    valor = valor.replace(/(\d)(?=(\d{3})+,)/g, '$1.');
    input.value = 'R$ ' + valor;
  }

  function mostrarToast(mensagem, tipo = 'info') {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = `toast ${tipo}`;
    toast.innerHTML = `
      <i class="fas ${tipo === 'success' ? 'fa-check-circle' : tipo === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i>
      <span>${escapeHtml(mensagem)}</span>
    `;
    container.appendChild(toast);

    setTimeout(() => {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(-20px)';
      setTimeout(() => toast.remove(), 300);
    }, 5000);
  }

  async function loadScriptOnce(src) {
    // evita carregar 20x
    if ([...document.scripts].some(s => s.src === src)) return;

    await new Promise((resolve, reject) => {
      const s = document.createElement('script');
      s.src = src;
      s.async = true;
      s.onload = resolve;
      s.onerror = reject;
      document.body.appendChild(s);
    });
  }

  async function ensureChartJs() {
    if (!ENABLE_CHARTS) return false;
    if (typeof window.Chart !== 'undefined') return true;

    if (!chartJsLoadingPromise) {
      chartJsLoadingPromise = loadScriptOnce(CHART_JS_SRC)
        .then(() => true)
        .catch(() => false);
    }
    return chartJsLoadingPromise;
  }

  // ===============================
  // MODAIS (static)
  // ===============================
  function fecharModal(tipo) {
    const modal = document.getElementById(`modal-${tipo}`);
    if (modal) {
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    }

    if (tipo === 'venda-rapida') {
      const sug = document.getElementById('clientes-sugestoes');
      if (sug) {
        sug.innerHTML = '';
        sug.style.display = 'none';
      }
      sugestoesAtivas = false;
    }
  }

  function abrirModalCliente(clienteId = null) {
    const modal = document.getElementById('modal-cliente');
    const form = document.getElementById('form-cliente');
    const btnSalvar = document.getElementById('btn-salvar-cliente');
    if (!modal || !form) return;

    form.reset();
    if (btnSalvar) btnSalvar.style.display = 'none';

    const camposAuto = document.querySelector('.campos-automaticos');
    if (clienteId) {
      const title = document.getElementById('modal-cliente-title');
      if (title) title.textContent = 'Editar Cliente';
      if (camposAuto) camposAuto.style.display = 'block';
      if (typeof window.carregarClienteParaEdicao === 'function') {
        window.carregarClienteParaEdicao(clienteId);
      } else {
        carregarClienteParaEdicao(clienteId);
      }
    } else {
      const title = document.getElementById('modal-cliente-title');
      if (title) title.textContent = 'Novo Cliente';
      if (camposAuto) camposAuto.style.display = 'none';
      const idField = document.getElementById('cliente-id');
      if (idField) idField.value = '';
    }

    clienteOriginal = {
      nome: (document.getElementById('cliente-nome')?.value) || '',
      telefone: (document.getElementById('cliente-telefone')?.value) || '',
      email: (document.getElementById('cliente-email')?.value) || '',
      observacoes: (document.getElementById('cliente-observacoes')?.value) || ''
    };

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function abrirModalVenda(clienteId = null, clienteNome = '') {
    const modal = document.getElementById('modal-venda');
    const form = document.getElementById('form-venda');
    if (!modal || !form) return;

    form.reset();

    if (clienteId) {
      const id = document.getElementById('venda-cliente-id');
      const nome = document.getElementById('venda-cliente-nome');
      if (id) id.value = clienteId;
      if (nome) nome.value = clienteNome || 'Cliente não encontrado';
    }

    const hoje = new Date();
    const vendaData = document.getElementById('venda-data');
    const vendaStatus = document.getElementById('venda-status');
    if (vendaData) vendaData.value = formatarDataParaInput(hoje);
    if (vendaStatus) vendaStatus.value = 'concluida';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
  }

  function abrirModalVendaRapida() {
    const modal = document.getElementById('modal-venda-rapida');
    const form = document.getElementById('form-venda-rapida');
    if (!modal || !form) return;

    form.reset();

    const sug = document.getElementById('clientes-sugestoes');
    const clienteId = document.getElementById('venda-rapida-cliente-id');
    const data = document.getElementById('venda-rapida-data');

    if (sug) {
      sug.innerHTML = '';
      sug.style.display = 'none';
    }
    if (clienteId) clienteId.value = '';

    const hoje = new Date();
    if (data) data.value = formatarDataParaInput(hoje);

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    setTimeout(() => document.getElementById('venda-rapida-cliente')?.focus(), 100);
  }

  // Fechar modal com ESC
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    $all('.modal').forEach(modal => {
      if (modal.style.display === 'flex') {
        const tipo = modal.id.replace('modal-', '');
        fecharModal(tipo);
      }
    });
  });

  // Fechar modal clicando fora
  document.addEventListener('click', (e) => {
    if (e.target && e.target.classList && e.target.classList.contains('modal')) {
      const tipo = e.target.id.replace('modal-', '');
      fecharModal(tipo);
    }
  });

  // Expor globals (pra onclick no HTML)
  window.abrirModalCliente = abrirModalCliente;
  window.abrirModalVenda = abrirModalVenda;
  window.abrirModalVendaRapida = abrirModalVendaRapida;
  window.fecharModal = fecharModal;
  window.mostrarToast = mostrarToast;
  window.formatarMoeda = formatarMoeda;

  // ===============================
  // MODAL REMOTO (abrirModal(url))
  // ===============================
  window.abrirModal = async function (url) {
    try {
      const res = await fetch(url, { credentials: 'same-origin' });
      if (!res.ok) throw new Error('Falha ao carregar modal');
      const text = await res.text();

      const parser = new DOMParser();
      const doc = parser.parseFromString(text, 'text/html');

      const modalElem = doc.querySelector('.modal');
      if (!modalElem) {
        console.error('Modal não encontrado no conteúdo retornado:', url);
        console.log('Preview (até 1000 chars):', text.slice(0, 1000));
        mostrarToast('Conteúdo do modal inválido (ver console)', 'error');
        return;
      }

      const previous = document.getElementById('modal-remote-container');
      if (previous) previous.remove();

      const container = document.createElement('div');
      container.id = 'modal-remote-container';
      container.appendChild(modalElem);
      document.body.appendChild(container);

      // executa scripts do modal remoto
      doc.querySelectorAll('script').forEach(s => {
        const ns = document.createElement('script');
        if (s.src) {
          ns.src = s.src;
          ns.async = false;
        }
        ns.textContent = s.textContent || '';
        document.body.appendChild(ns);
      });

      const inserted = container.querySelector('.modal');
      inserted.style.display = 'flex';
      document.body.style.overflow = 'hidden';

      setTimeout(() => {
        const first = inserted.querySelector('input, select, textarea');
        first?.focus();
      }, 10);

    } catch (err) {
      console.error(err);
      mostrarToast('Erro ao abrir modal', 'error');
    }
  };

  window.closeModal = function (identifier) {
    if (identifier) {
      const id = identifier.startsWith('modal-') ? identifier : `modal-${identifier}`;
      const modal = document.getElementById(id);
      if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
        return;
      }
    }
    const remote = document.getElementById('modal-remote-container');
    if (remote) remote.remove();
    document.body.style.overflow = 'auto';
  };

  // =============================
  // DASHBOARD: layout + render
  // =============================
  async function loadDashboardLayout() {
    try {
      const res = await fetch('api/dashboard_layout.php', { credentials: 'same-origin' });
      const text = await res.text();
      const json = safeJsonParse(text);

      if (!json || !json.success) {
        console.error('Erro ao carregar layout:', text?.slice(0, 1000));
        return;
      }

      currentLayoutId = json.data?.id ?? null;

      let layout = json.data?.layout_json ?? [];
      if (typeof layout === 'string') layout = safeJsonParse(layout) || [];
      if (!Array.isArray(layout)) layout = [];

      currentDashboardLayout = layout;
      renderDashboardFromLayout(currentDashboardLayout);

    } catch (err) {
      console.error('Erro ao carregar layout:', err);
    }
  }

  function renderDashboardFromLayout(layout) {
    const container = document.getElementById('stats-container');
    if (!container) return;

    if (!Array.isArray(layout)) layout = [];
    container.innerHTML = '';

    layout.forEach((card) => {
      const el = document.createElement('div');
      el.className = 'stat-card';
      el.dataset.instanceId = card.instance_id || card.id || '';

      const icon = document.createElement('div');
      icon.className = 'stat-icon';
      icon.style.background = card.color || '#be1616';
      icon.innerHTML = iconToHtml(card.icon);

      const content = document.createElement('div');
      content.className = 'stat-content';

      const h3 = document.createElement('h3');
      h3.textContent = card.title || '';

      const p = document.createElement('p');
      p.className = 'stat-value';
      p.textContent = '—';

      content.appendChild(h3);

      if (card.type === 'chart') {
        const canvas = document.createElement('canvas');
        canvas.className = 'stat-chart';
        canvas.height = 80;
        canvas.style.width = '100%';
        content.appendChild(canvas);
      } else {
        content.appendChild(p);
      }

      el.appendChild(icon);
      el.appendChild(content);
      container.appendChild(el);

      // carregar dados do card
      if (card.type === 'stat' && card.metric) {
        fetch('api/dashboard_stats.php?metric=' + encodeURIComponent(card.metric), { credentials: 'same-origin' })
          .then(r => r.json())
          .then(data => {
            if (!data || data.success !== true) return;

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
              p.textContent = moneyBR(data.value);
            } else if (percentMetrics.has(card.metric)) {
              p.textContent = Number(data.value || 0).toFixed(1) + '%';
            } else {
              p.textContent = String(data.value ?? '0');
            }
          })
          .catch(() => {});
      }

      if (card.type === 'chart' && card.metric) {
        if (!ENABLE_CHARTS) return;
        const canvas = content.querySelector('canvas');
        if (canvas) renderChart(canvas, card.metric);
      }
    });
  }

  async function renderChart(canvas, metric) {
    const ok = await ensureChartJs();
    if (!ok || typeof window.Chart === 'undefined') return;

    // destrói instância antiga desse canvas
    if (chartInstances.has(canvas)) {
      try { chartInstances.get(canvas).destroy(); } catch {}
      chartInstances.delete(canvas);
    }

    try {
      const r = await fetch('api/dashboard_stats.php?metric=' + encodeURIComponent(metric), { credentials: 'same-origin' });
      const data = await r.json();

      if (!data || data.success !== true) {
        console.error('Dados inválidos do gráfico:', metric, data);
        return;
      }

      const ctx = canvas.getContext('2d');
      const chart = new window.Chart(ctx, {
        type: 'line',
        data: {
          labels: data.labels || [],
          datasets: [{
            data: data.values || [],
            borderWidth: 2,
            tension: 0.35,
            fill: false
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false,
          animation: false,
          plugins: { legend: { display: false } }
        }
      });

      chartInstances.set(canvas, chart);
    } catch (err) {
      console.error('Erro ao renderizar gráfico:', metric, err);
    }
  }

  // Expor (se você quiser chamar manualmente)
  window.loadDashboardLayout = loadDashboardLayout;

  // =============================
  // DASHBOARD EDITOR
  // =============================
  function generateInstanceId() {
    return 'c_' + Date.now().toString(36) + Math.random().toString(36).slice(2, 8);
  }

  function createCardById(id) {
    const map = {
      card_faturamento_mes: { id: 'card_faturamento_mes', type: 'stat', title: 'Faturamento do Mês', icon: 'fa-solid fa-sack-dollar', metric: 'faturamento_mes' },
      card_qtd_vendas_mes: { id: 'card_qtd_vendas_mes', type: 'stat', title: 'Vendas do Mês', icon: 'fa-solid fa-cart-shopping', metric: 'qtd_vendas_mes' },
      card_ticket_medio_mes: { id: 'card_ticket_medio_mes', type: 'stat', title: 'Ticket Médio (Mês)', icon: 'fa-solid fa-receipt', metric: 'ticket_medio_mes' },
      card_clientes_perdidos_60d: { id: 'card_clientes_perdidos_60d', type: 'stat', title: 'Clientes Perdidos (60d)', icon: 'fa-solid fa-user-slash', metric: 'clientes_perdidos_60d' },
      card_projecao_mes: { id: 'card_projecao_mes', type: 'stat', title: 'Projeção do Mês', icon: 'fa-solid fa-chart-line', metric: 'projecao_mes' },
      card_meta_atingida_percent: { id: 'card_meta_atingida_percent', type: 'stat', title: '% da Meta (Mês)', icon: 'fa-solid fa-bullseye', metric: 'meta_atingida_percent' },
      card_necessario_por_dia: { id: 'card_necessario_por_dia', type: 'stat', title: 'Necessário por Dia', icon: 'fa-solid fa-calendar-day', metric: 'necessario_por_dia' },

      card_total_clientes: { id: 'card_total_clientes', type: 'stat', title: 'Total de Clientes', icon: 'fa-solid fa-users', metric: 'total_clients' },
      card_vendas_mes: { id: 'card_vendas_mes', type: 'stat', title: 'Vendas do Mês', icon: 'fa-solid fa-cart-shopping', metric: 'vendas_mes' },
      card_valor_mes: { id: 'card_valor_mes', type: 'stat', title: 'Valor do Mês', icon: 'fa-solid fa-coins', metric: 'valor_mes' },
      card_taxa_fechamento: { id: 'card_taxa_fechamento', type: 'stat', title: 'Taxa de Fechamento', icon: 'fa-solid fa-percent', metric: 'taxa_fechamento' },
      card_clientes_inativos: { id: 'card_clientes_inativos', type: 'stat', title: 'Clientes Inativos', icon: 'fa-solid fa-user-clock', metric: 'clientes_inativos' },
      card_total_negociacoes: { id: 'card_total_negociacoes', type: 'stat', title: 'Total de Negociações', icon: 'fa-solid fa-handshake', metric: 'total_negociacoes' },

      // gráfico (opcional)
      card_grafico_exemplo: { id: 'card_grafico_exemplo', type: 'chart', title: 'Vendas (6 meses)', icon: 'fa-solid fa-chart-line', metric: 'vendas_6meses' },
    };

    const base = structuredClone(map[id] || { id, type: 'stat', title: id, icon: 'fa-solid fa-chart-bar', metric: '' });
    base.instance_id = generateInstanceId();
    return base;
  }

  async function openDashboardEditor() {
    const modal = document.getElementById('modal-dashboard-editor');
    if (!modal) return;

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    // puxa layout atual pra editar
    try {
      const res = await fetch('api/dashboard_layout.php', { credentials: 'same-origin' });
      const text = await res.text();
      const json = safeJsonParse(text);

      if (!json || !json.success) {
        console.error('Falha ao abrir editor:', text?.slice(0, 800));
        mostrarToast('Erro ao carregar editor', 'error');
        return;
      }

      currentLayoutId = json.data?.id ?? null;

      let layout = json.data?.layout_json ?? [];
      if (typeof layout === 'string') layout = safeJsonParse(layout) || [];
      if (!Array.isArray(layout)) layout = [];

      currentDashboardLayout = layout;

      const nameInput = document.getElementById('editor-layout-name');
      const sharedInput = document.getElementById('editor-layout-shared');

      if (nameInput) nameInput.value = json.data?.name || 'Layout do usuário';
      if (sharedInput) sharedInput.checked = String(json.data?.is_shared) === '1';

      await renderEditorLayout(currentDashboardLayout);
    } catch (err) {
      console.error(err);
      mostrarToast('Erro ao abrir editor', 'error');
    }
  }

  async function renderEditorLayout(layout) {
    const list = document.getElementById('editor-layout-list');
    if (!list) return;

    list.innerHTML = '';

    layout.forEach(card => {
      const item = document.createElement('div');
      item.className = 'editor-item';
      item.dataset.instanceId = card.instance_id || card.id;

      item.innerHTML = `
        <div class="editor-handle">☰</div>
        <div class="editor-title">${escapeHtml(card.title || card.id)}</div>
        <button class="btn btn-link btn-remove" type="button">Remover</button>
      `;
      list.appendChild(item);
    });

    // remover
    $all('.btn-remove', list).forEach(btn => {
      btn.onclick = (e) => {
        const item = e.target.closest('.editor-item');
        const iid = item?.dataset?.instanceId;
        if (!iid) return;

        const idx = currentDashboardLayout.findIndex(x => (x.instance_id || x.id) === iid);
        if (idx !== -1) currentDashboardLayout.splice(idx, 1);

        renderEditorLayout(currentDashboardLayout);
      };
    });

    // sortable (somente 1x)
    await loadSortableAndInit(list);

    // botões de adicionar
    $all('#editor-available-cards button').forEach(btn => {
      btn.onclick = () => {
        const id = btn.dataset.cardId;
        if (!id) return;

        currentDashboardLayout.push(createCardById(id));
        renderEditorLayout(currentDashboardLayout);
      };
    });
  }

  async function loadSortableAndInit(list) {
    if (typeof window.Sortable === 'undefined') {
      try {
        await loadScriptOnce(SORTABLE_SRC);
      } catch (e) {
        console.error('Sortable falhou:', e);
        return;
      }
    }

    // destrói instância anterior se existir
    if (list._sortableInstance && typeof list._sortableInstance.destroy === 'function') {
      try { list._sortableInstance.destroy(); } catch {}
    }

    list._sortableInstance = window.Sortable.create(list, {
      handle: '.editor-handle',
      animation: 150,
      onEnd: () => {
        const newOrder = [...list.children].map(ch => ch.dataset.instanceId);
        currentDashboardLayout = newOrder
          .map(iid => currentDashboardLayout.find(c => (c.instance_id || c.id) === iid))
          .filter(Boolean);
      }
    });
  }

  async function saveDashboardLayout() {
    const list = document.getElementById('editor-layout-list');
    if (!list) return;

    // layout na ordem do DOM
    const items = [...list.children];
    const layout = items
      .map(it => {
        const iid = it.dataset.instanceId;
        return currentDashboardLayout.find(c => (c.instance_id || c.id) === iid);
      })
      .filter(Boolean);

    const nameInput = document.getElementById('editor-layout-name');
    const sharedInput = document.getElementById('editor-layout-shared');

    const payload = {
      name: nameInput ? nameInput.value : 'Layout do usuário',
      layout_json: layout
    };
    if (currentLayoutId) payload.id = currentLayoutId;
    if (sharedInput && sharedInput.checked) payload.is_shared = true;

    try {
      const resp = await fetch('api/dashboard_layout.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });

      const text = await resp.text();
      const j = safeJsonParse(text);

      if (!j || !j.success) {
        console.error('Erro ao salvar layout:', text?.slice(0, 800));
        mostrarToast('Erro ao salvar layout', 'error');
        return;
      }

      if (j.id) currentLayoutId = j.id;

      mostrarToast('Layout salvo com sucesso', 'success');
      window.closeModal('modal-dashboard-editor');

      currentDashboardLayout = layout;
      renderDashboardFromLayout(currentDashboardLayout);
    } catch (err) {
      console.error(err);
      mostrarToast('Erro ao salvar layout', 'error');
    }
  }

  // expor pro HTML (onclick)
  window.openDashboardEditor = openDashboardEditor;
  window.saveDashboardLayout = saveDashboardLayout;

  // =============================
  // CLIENTES / VENDAS (API)
  // =============================
  async function carregarClienteParaEdicao(clienteId) {
    try {
      const response = await fetch(`api/clientes.php?id=${clienteId}`, { credentials: 'same-origin' });
      const data = await response.json();

      if (!data.success || !data.data) {
        mostrarToast('Cliente não encontrado', 'error');
        fecharModal('cliente');
        return;
      }

      const cliente = data.data;

      document.getElementById('cliente-id').value = cliente.id;
      document.getElementById('cliente-nome').value = cliente.nome || '';
      document.getElementById('cliente-telefone').value = cliente.telefone || '';
      document.getElementById('cliente-email').value = cliente.email || '';
      document.getElementById('cliente-observacoes').value = cliente.observacoes || '';

      clienteOriginal = {
        nome: cliente.nome || '',
        telefone: cliente.telefone || '',
        email: cliente.email || '',
        observacoes: cliente.observacoes || ''
      };

      // campos automáticos (se existirem)
      const ultimaVenda = document.getElementById('cliente-ultima-venda');
      const media = document.getElementById('cliente-media-gastos');
      const total = document.getElementById('cliente-total-gasto');
      const taxa = document.getElementById('cliente-taxa-f')
      if (ultimaVenda) ultimaVenda.value = cliente.ultima_venda ? cliente.ultima_venda : 'Nunca';
      if (media) media.value = moneyBR(cliente.media_gastos || 0);
      if (total) total.value = moneyBR(cliente.total_gasto || 0);

      const taxaField = document.getElementById('cliente-taxa-fechamento');
      if (taxaField) taxaField.value = cliente.taxa_fechamento ? `${Number(cliente.taxa_fechamento).toFixed(1)}%` : '0%';

    } catch (error) {
      console.error('Erro:', error);
      mostrarToast('Erro ao carregar cliente', 'error');
    }
  }

  async function salvarCliente(event) {
    event.preventDefault();

    const form = document.getElementById('form-cliente');
    if (!form) return;

    const data = Object.fromEntries(new FormData(form).entries());

    if (!data.nome || !data.nome.trim()) {
      mostrarToast('Nome é obrigatório', 'error');
      return;
    }

    const btnSalvar = document.getElementById('btn-salvar-cliente');
    if (btnSalvar) {
      btnSalvar.disabled = true;
      btnSalvar.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    }

    try {
      const response = await fetch('api/clientes.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (result.success) {
        mostrarToast(result.message || 'Salvo!', 'success');
        fecharModal('cliente');
        setTimeout(() => window.location.reload(), 600);
      } else {
        mostrarToast(result.message || 'Erro ao salvar', 'error');
      }
    } catch (error) {
      console.error('Erro:', error);
      mostrarToast('Erro de conexão', 'error');
    } finally {
      if (btnSalvar) {
        btnSalvar.disabled = false;
        btnSalvar.innerHTML = '<i class="fas fa-save"></i> Salvar Cliente';
      }
    }
  }

  async function registrarVenda(event) {
    event.preventDefault();

    const form = document.getElementById('form-venda');
    if (!form) return;

    const data = Object.fromEntries(new FormData(form).entries());

    if (!data.cliente_id) return mostrarToast('Cliente é obrigatório', 'error');
    if (!data.valor || data.valor === 'R$ 0,00') return mostrarToast('Valor é obrigatório', 'error');

    if (data.data_venda && !validarData(data.data_venda)) {
      return mostrarToast('Data inválida. Use dd/mm/aaaa', 'error');
    }

    const btnSubmit = form.querySelector('button[type="submit"]');
    if (btnSubmit) {
      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
    }

    try {
      const response = await fetch('api/vendas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(data)
      });

      const result = await response.json();

      if (result.success) {
        mostrarToast(result.message || 'Venda registrada!', 'success');
        fecharModal('venda');
        setTimeout(() => window.location.reload(), 800);
      } else {
        mostrarToast(result.message || 'Erro ao registrar venda', 'error');
      }
    } catch (error) {
      console.error('Erro:', error);
      mostrarToast('Erro de conexão', 'error');
    } finally {
      if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-check"></i> Registrar Venda';
      }
    }
  }

  async function buscarClientesSugestoes(termo) {
    const container = document.getElementById('clientes-sugestoes');
    if (!container) return;

    if (!termo || termo.length < 2) {
      container.innerHTML = '';
      container.style.display = 'none';
      sugestoesAtivas = false;
      return;
    }

    try {
      const response = await fetch('api/clientes.php', { credentials: 'same-origin' });
      const data = await response.json();

      if (!data.success || !Array.isArray(data.data)) return;

      const clientesFiltrados = data.data
        .filter(c =>
          (c.nome || '').toLowerCase().includes(termo.toLowerCase()) ||
          (c.telefone || '').includes(termo) ||
          (c.email || '').toLowerCase().includes(termo.toLowerCase())
        )
        .slice(0, 8);

      if (clientesFiltrados.length) {
        container.innerHTML = clientesFiltrados.map(c => `
          <div class="suggestion-item" onclick="selecionarClienteSugestao(${Number(c.id)}, '${escapeHtml(String(c.nome || '')).replace(/'/g, "\\'")}')">
            <strong>${escapeHtml(c.nome || '')}</strong>
            ${c.telefone ? `<br><small>${escapeHtml(c.telefone)}</small>` : ''}
          </div>
        `).join('');
      } else {
        container.innerHTML = '<div class="suggestion-item">Nenhum cliente encontrado</div>';
      }

      container.style.display = 'block';
      sugestoesAtivas = true;
    } catch (error) {
      console.error('Erro:', error);
    }
  }

  function selecionarClienteSugestao(clienteId, clienteNome) {
    document.getElementById('venda-rapida-cliente').value = clienteNome;
    document.getElementById('venda-rapida-cliente-id').value = clienteId;

    const sug = document.getElementById('clientes-sugestoes');
    if (sug) {
      sug.innerHTML = '';
      sug.style.display = 'none';
    }
    sugestoesAtivas = false;

    setTimeout(() => document.getElementById('venda-rapida-valor')?.focus(), 50);
  }

  async function registrarVendaRapida(event) {
    event.preventDefault();

    const clienteId = document.getElementById('venda-rapida-cliente-id')?.value;
    const valor = document.getElementById('venda-rapida-valor')?.value;
    const dataVenda = document.getElementById('venda-rapida-data')?.value;

    if (!clienteId) return mostrarToast('Selecione um cliente', 'error');
    if (!valor || valor === 'R$ 0,00') return mostrarToast('Valor é obrigatório', 'error');
    if (dataVenda && !validarData(dataVenda)) return mostrarToast('Data inválida. Use dd/mm/aaaa', 'error');

    const payload = {
      cliente_id: clienteId,
      valor: valor,
      data_venda: dataVenda,
      status: 'concluida',
      observacoes: 'Venda rápida registrada pelo sistema'
    };

    const btnSubmit = document.querySelector('#form-venda-rapida button[type="submit"]');
    if (btnSubmit) {
      btnSubmit.disabled = true;
      btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Registrando...';
    }

    try {
      const response = await fetch('api/vendas.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify(payload)
      });

      const result = await response.json();

      if (result.success) {
        mostrarToast(result.message || 'Venda rápida registrada!', 'success');
        fecharModal('venda-rapida');
        setTimeout(() => window.location.reload(), 800);
      } else {
        mostrarToast(result.message || 'Erro ao registrar', 'error');
      }
    } catch (error) {
      console.error('Erro:', error);
      mostrarToast('Erro de conexão', 'error');
    } finally {
      if (btnSubmit) {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-bolt"></i> Registrar Venda Rápida';
      }
    }
  }

  // expor pro HTML
  window.carregarClienteParaEdicao = carregarClienteParaEdicao;
  window.salvarCliente = salvarCliente;
  window.registrarVenda = registrarVenda;
  window.buscarClientesSugestoes = buscarClientesSugestoes;
  window.selecionarClienteSugestao = selecionarClienteSugestao;
  window.registrarVendaRapida = registrarVendaRapida;
  window.validarData = validarData;

  // ===============================
  // CLICK fora das sugestões
  // ===============================
  document.addEventListener('click', (e) => {
    if (!sugestoesAtivas) return;
    if (e.target.closest('#clientes-sugestoes')) return;
    if (e.target.closest('#venda-rapida-cliente')) return;

    const sug = document.getElementById('clientes-sugestoes');
    if (sug) {
      sug.innerHTML = '';
      sug.style.display = 'none';
    }
    sugestoesAtivas = false;
  });

  // ===============================
  // INIT
  // ===============================
  document.addEventListener('DOMContentLoaded', () => {
    // máscara dinheiro
    $all('.money-input').forEach(input => {
      if (input.value) formatarMoeda(input);
      input.addEventListener('input', () => formatarMoeda(input));
    });

    // Botões topo
    $('#btn-novo-cliente-topo')?.addEventListener('click', (e) => { e.preventDefault(); abrirModalCliente(); });
    $('#btn-venda-rapida-topo')?.addEventListener('click', (e) => { e.preventDefault(); abrirModalVendaRapida(); });

    // Botões actions
    $('#btn-novo-cliente-action')?.addEventListener('click', (e) => { e.preventDefault(); abrirModalCliente(); });
    $('#btn-venda-rapida-action')?.addEventListener('click', (e) => { e.preventDefault(); abrirModalVendaRapida(); });

    // Carrega dashboard
    loadDashboardLayout();
  });

})();
