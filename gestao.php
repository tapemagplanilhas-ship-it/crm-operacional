<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
verificarLogin();
// Somente admin/gerencia
requerirPermissao('gerencia');

$isAdmin = (($_SESSION['perfil'] ?? '') === 'admin');

// Mes/ano padrao
$mes = (int)($_GET['mes'] ?? date('n'));
$ano = (int)($_GET['ano'] ?? date('Y'));
if ($mes < 1 || $mes > 12) { $mes = (int)date('n'); }
if ($ano < 2000 || $ano > 2100) { $ano = (int)date('Y'); }

$meses = [
  1 => 'Janeiro',
  2 => 'Fevereiro',
  3 => 'Março',
  4 => 'Abril',
  5 => 'Maio',
  6 => 'Junho',
  7 => 'Julho',
  8 => 'Agosto',
  9 => 'Setembro',
  10 => 'Outubro',
  11 => 'Novembro',
  12 => 'Dezembro'
];

// Lista de vendedores (para render inicial da tabela)
$conn = getConnection();
$vendedores = [];
if ($conn) {
  $sql = "SELECT id, nome, email, ativo FROM usuarios WHERE perfil='vendedor' ORDER BY ativo DESC, nome ASC";
  $res = $conn->query($sql);
  if ($res) {
    while ($row = $res->fetch_assoc()) {
      $vendedores[] = $row;
    }
  }
  $conn->close();
}
?>

<style>
  .row-clickable { cursor: pointer; }
</style>

<div class="dashboard">
  <div class="dashboard-header">
    <h2><i class="fas fa-screwdriver-wrench"></i> Gestão</h2>
    <div class="dashboard-actions">
      <button class="btn-secondary" id="btn-recarregar">
        <i class="fas fa-rotate"></i> Recarregar
      </button>
      <button class="btn-success" id="btn-salvar-tudo">
        <i class="fas fa-floppy-disk"></i> Salvar tudo
      </button>
    </div>
  </div>

  <div class="config-section" style="margin-bottom: 18px;">
    <div class="section-header" style="margin-bottom: 10px; border: none; padding-bottom: 0;">
      <h2 style="font-size: 1.1rem;"><i class="fas fa-bullseye"></i> Metas mensais por vendedor</h2>
    </div>

    <div class="form-row" style="align-items: end;">
      <div class="form-group">
        <label for="filtro-mes">Mês</label>
        <select id="filtro-mes">
          <?php foreach ($meses as $num => $nome): ?>
            <option value="<?= $num ?>" <?= $num === $mes ? 'selected' : '' ?>><?= $nome ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group">
        <label for="filtro-ano">Ano</label>
        <select id="filtro-ano">
          <?php for ($y = (int)date('Y') - 2; $y <= (int)date('Y') + 2; $y++): ?>
            <option value="<?= $y ?>" <?= $y === $ano ? 'selected' : '' ?>><?= $y ?></option>
          <?php endfor; ?>
        </select>
      </div>

      <div class="form-group" style="flex: 0 0 auto;">
        <button class="btn-primary" id="btn-aplicar">
          <i class="fas fa-filter"></i> Aplicar
        </button>
      </div>

      <?php if ($isAdmin): ?>
        <div class="form-group" style="flex: 0 0 auto;">
          <label for="toggle-sabado">Contar sabado</label>
          <div>
            <input type="checkbox" id="toggle-sabado">
            <small class="help-text">Considerar sabado como dia util</small>
          </div>
        </div>
      <?php endif; ?>

      <div class="form-group" style="flex: 1;">
        <small class="help-text" id="status-gestao">Carregando dados...</small>
      </div>
    </div>
  </div>

  <div class="table-responsive">
    <table class="data-table" id="tabela-metas">
      <thead>
        <tr>
          <th>Vendedor</th>
          <th class="text-center">Meta mensal (R$)</th>
          <th class="text-center">Realizado (R$)</th>
          <th class="text-center">% atingido</th>
          <th class="text-center">Projeção</th>
          <th class="text-center">Necessário / dia</th>
          <th class="text-center">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($vendedores)): ?>
          <tr><td colspan="7" class="text-center">Nenhum vendedor cadastrado.</td></tr>
        <?php else: ?>
          <?php foreach ($vendedores as $v): ?>
            <tr data-vendedor-id="<?= (int)$v['id'] ?>" class="row-clickable">
              <td>
                <strong><?= htmlspecialchars($v['nome']) ?></strong>
                <div class="text-muted" style="font-size: 12px; color: #718096;">
                  <?= htmlspecialchars($v['email'] ?? '') ?>
                  <?= ((int)$v['ativo'] === 0) ? ' • <span style="color:#e53e3e;">Inativo</span>' : '' ?>
                </div>
              </td>
              <td class="text-center">
                <input
                  type="text"
                  class="money-input meta-input"
                  placeholder="0,00"
                  inputmode="decimal"
                  data-original=""
                  style="max-width: 160px; margin: 0 auto;"
                />
              </td>
              <td class="text-center" data-col="realizado">—</td>
              <td class="text-center" data-col="pct">—</td>
              <td class="text-center" data-col="proj">—</td>
              <td class="text-center" data-col="nec">—</td>
              <td class="text-center">
                <button class="btn-icon btn-salvar-linha" title="Salvar">
                  <i class="fas fa-floppy-disk"></i>
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
(function () {
  const API_METAS = 'api/metas.php'; // se no seu projeto estiver fora de /api, troque aqui

  const statusEl = document.getElementById('status-gestao');
  const btnAplicar = document.getElementById('btn-aplicar');
  const btnRecarregar = document.getElementById('btn-recarregar');
  const btnSalvarTudo = document.getElementById('btn-salvar-tudo');
  const selMes = document.getElementById('filtro-mes');
  const selAno = document.getElementById('filtro-ano');
  const toggleSabado = document.getElementById('toggle-sabado');

  function setStatus(msg) {
    if (statusEl) statusEl.textContent = msg;
  }

  function parseMoneyBR(str) {
    if (!str) return 0;
    const clean = String(str)
      .replace(/\./g, '')
      .replace(',', '.')
      .replace(/[^0-9.\-]/g, '');
    const n = parseFloat(clean);
    return Number.isFinite(n) ? n : 0;
  }

  function formatMoneyBR(n) {
    const v = Number(n || 0);
    return v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function markDirty(input, dirty) {
    if (!input) return;
    if (dirty) input.classList.add('warning');
    else input.classList.remove('warning');
  }

  function getPeriodo() {
    return {
      mes: parseInt(selMes.value, 10),
      ano: parseInt(selAno.value, 10)
    };
  }

  async function carregarConfiguracao() {
    if (!toggleSabado) return;
    try {
      const res = await fetch(`${API_METAS}?action=config_get`, {
        credentials: 'same-origin'
      });
      const data = await res.json();
      if (data.success) {
        toggleSabado.checked = !!data.contar_sabado;
      }
    } catch (e) {
      console.error(e);
    }
  }

  async function salvarConfiguracao() {
    if (!toggleSabado) return;
    setStatus('Salvando configuracao...');
    try {
      const res = await fetch(`${API_METAS}?action=config_set`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'same-origin',
        body: JSON.stringify({ contar_sabado: toggleSabado.checked ? 1 : 0 })
      });
      const data = await res.json();
      if (!data.success) {
        setStatus(data.message || 'Falha ao salvar configuracao.');
        toggleSabado.checked = !toggleSabado.checked;
        return;
      }
      setStatus('Configuracao salva.');
      await carregarMetas();
    } catch (e) {
      console.error(e);
      setStatus('Erro ao salvar configuracao.');
      toggleSabado.checked = !toggleSabado.checked;
    }
  }

  async function carregarMetas() {
    const { mes, ano } = getPeriodo();
    setStatus(`Carregando metas de ${String(mes).padStart(2,'0')}/${ano}...`);

    try {
      const res = await fetch(`${API_METAS}?action=list&mes=${encodeURIComponent(mes)}&ano=${encodeURIComponent(ano)}`, {
        credentials: 'same-origin'
      });
      const data = await res.json();

      if (!data.success) {
        setStatus(data.message || 'Falha ao carregar metas.');
        return;
      }

      const map = new Map();
      (data.data || []).forEach(row => map.set(String(row.vendedor_id), row));

      document.querySelectorAll('#tabela-metas tbody tr[data-vendedor-id]').forEach(tr => {
        const id = tr.getAttribute('data-vendedor-id');
        const row = map.get(String(id));

        const input = tr.querySelector('.meta-input');
        const realizadoEl = tr.querySelector('[data-col="realizado"]');
        const pctEl = tr.querySelector('[data-col="pct"]');
        const projEl = tr.querySelector('[data-col="proj"]');
        const necEl = tr.querySelector('[data-col="nec"]');

        const meta = row ? (row.meta_valor || 0) : 0;
        const realizado = row ? (row.realizado || 0) : 0;
        const pct = row ? (row.pct || 0) : 0;
        const proj = row ? (row.projecao || 0) : 0;
        const nec = row ? (row.necessario_por_dia || 0) : 0;

        if (input) {
          input.value = formatMoneyBR(meta);
          input.dataset.original = String(meta);
          markDirty(input, false);
        }

        if (realizadoEl) realizadoEl.textContent = `R$ ${formatMoneyBR(realizado)}`;
        if (pctEl) pctEl.textContent = `${pct.toFixed ? pct.toFixed(1) : pct}%`;
        if (projEl) projEl.textContent = `R$ ${formatMoneyBR(proj)}`;
        if (necEl) necEl.textContent = `R$ ${formatMoneyBR(nec)}`;
      });

      setStatus('Pronto.');
    } catch (e) {
      console.error(e);
      setStatus('Erro ao carregar metas.');
    }
  }

  async function salvarMeta(vendedorId, metaValor) {
    const { mes, ano } = getPeriodo();

    const payload = {
      vendedor_id: parseInt(vendedorId, 10),
      mes,
      ano,
      meta_valor: metaValor
    };

    const res = await fetch(`${API_METAS}?action=save`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      credentials: 'same-origin',
      body: JSON.stringify(payload)
    });

    return res.json();
  }

  function bindInputs() {
    document.querySelectorAll('.meta-input').forEach(input => {
      input.addEventListener('input', () => {
        const original = parseFloat(input.dataset.original || '0');
        const atual = parseMoneyBR(input.value);
        markDirty(input, Math.abs(atual - original) > 0.0001);
      });

      input.addEventListener('blur', () => {
        input.value = formatMoneyBR(parseMoneyBR(input.value));
      });
    });

    document.querySelectorAll('.btn-salvar-linha').forEach(btn => {
      btn.addEventListener('click', async (ev) => {
        const tr = ev.target.closest('tr');
        const vendedorId = tr.getAttribute('data-vendedor-id');
        const input = tr.querySelector('.meta-input');
        const metaValor = parseMoneyBR(input.value);

        setStatus('Salvando...');
        btn.disabled = true;

        try {
          const r = await salvarMeta(vendedorId, metaValor);
          if (!r.success) {
            setStatus(r.message || 'Falha ao salvar.');
          } else {
            input.dataset.original = String(metaValor);
            markDirty(input, false);
            setStatus('Meta salva.');
            await carregarMetas();
          }
        } catch (e) {
          console.error(e);
          setStatus('Erro ao salvar.');
        } finally {
          btn.disabled = false;
        }
      });
    });
  }

  function bindRowClicks() {
    const tabela = document.getElementById('tabela-metas');
    if (!tabela) return;

    tabela.addEventListener('click', (e) => {
      const sel = window.getSelection ? String(window.getSelection() || '').trim() : '';
      if (sel) return;
      const target = e.target;
      if (target.closest('input, button, select, textarea, a')) return;

      const tr = target.closest('tr[data-vendedor-id]');
      if (!tr) return;

      const vendedorId = tr.getAttribute('data-vendedor-id');
      if (!vendedorId) return;

      window.location.href = `dashboard_vendedor.php?vendedor_id=${encodeURIComponent(vendedorId)}`;
    });
  }

  btnAplicar?.addEventListener('click', (e) => {
    e.preventDefault();
    carregarMetas();
  });

  btnRecarregar?.addEventListener('click', (e) => {
    e.preventDefault();
    carregarMetas();
  });

  btnSalvarTudo?.addEventListener('click', async (e) => {
    e.preventDefault();

    const pendentes = Array.from(document.querySelectorAll('.meta-input.warning'));
    if (pendentes.length === 0) {
      setStatus('Nada para salvar.');
      return;
    }

    setStatus(`Salvando ${pendentes.length} metas...`);
    btnSalvarTudo.disabled = true;

    for (const input of pendentes) {
      const tr = input.closest('tr');
      const vendedorId = tr.getAttribute('data-vendedor-id');
      const metaValor = parseMoneyBR(input.value);

      try {
        const r = await salvarMeta(vendedorId, metaValor);
        if (r.success) {
          input.dataset.original = String(metaValor);
          markDirty(input, false);
        } else {
          console.warn('Falha ao salvar', vendedorId, r);
        }
      } catch (err) {
        console.error(err);
      }
    }

    btnSalvarTudo.disabled = false;
    setStatus('Metas salvas.');
    await carregarMetas();
  });

  bindInputs();
  bindRowClicks();
  toggleSabado?.addEventListener('change', salvarConfiguracao);
  carregarConfiguracao();
  carregarMetas();
})();
</script>

<?php require_once 'includes/footer.php'; ?>
