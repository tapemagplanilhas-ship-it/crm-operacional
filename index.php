<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

verificarLogin();
/* Para páginas específicas de perfil, adicione:
// requerirPermissao('admin'); // Para páginas só de admin
// requerirPermissao('gerencia'); // Para páginas de gerência
?> */

if (!acessoPermitido('dashboard')) {
    include 'acesso_negado.php';
    exit;
}

$usuarioId = $_SESSION['usuario_id'] ?? null;
$perfil = $_SESSION['perfil'] ?? null;

// Filtra pelos dados do usuario logado quando possivel.
$isVendedor = !empty($usuarioId);


// Buscar dados iniciais via PHP
$conn = getConnection();
$dadosDashboard = [
    'total_clientes' => 0,
    'vendas_mes' => 0,
    'valor_mes' => 0,
    'clientes_inativos' => 0,
    'taxa_fechamento_geral' => 0,
    'total_vendas' => 0,
    'vendas_concluidas' => 0,
    'ultimas_vendas' => []
];

try {

    // -----------------------------
    // 1) TOTAL DE CLIENTES
    // -----------------------------
    if ($isVendedor) {
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT v.cliente_id) AS total
            FROM vendas v
            WHERE v.usuario_id = ?
        ");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $dadosDashboard['total_clientes'] = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
        $stmt->close();
    } else {
        $result = $conn->query("SELECT COUNT(*) as total FROM clientes");
        $dadosDashboard['total_clientes'] = (int)($result->fetch_assoc()['total'] ?? 0);
    }

    // -----------------------------
    // 2) VENDAS DO MÊS (CONCLUÍDAS) + VALOR DO MÊS
    // -----------------------------
    $sql = "
        SELECT COUNT(*) as total, COALESCE(SUM(valor), 0) as valor
        FROM vendas
        WHERE status = 'concluida'
          AND MONTH(data_venda) = MONTH(CURRENT_DATE())
          AND YEAR(data_venda) = YEAR(CURRENT_DATE())
          " . ($isVendedor ? " AND usuario_id = ? " : "");

    $stmt = $conn->prepare($sql);
    if ($isVendedor) $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $dadosDashboard['vendas_mes'] = (int)($row['total'] ?? 0);
    $dadosDashboard['valor_mes']  = (float)($row['valor'] ?? 0);
    $stmt->close();

    // -----------------------------
    // 3) CLIENTES INATIVOS (30+ DIAS SEM COMPRA CONCLUÍDA)
    // -----------------------------
    $sql = "
        SELECT COUNT(*) AS total
        FROM clientes c
        LEFT JOIN (
            SELECT cliente_id, MAX(data_venda) AS ultima
            FROM vendas
            WHERE status = 'concluida'
              " . ($isVendedor ? " AND usuario_id = ? " : "") . "
            GROUP BY cliente_id
        ) v ON v.cliente_id = c.id
        WHERE v.ultima IS NULL
           OR v.ultima < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)
    ";

    $stmt = $conn->prepare($sql);
    if ($isVendedor) $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $dadosDashboard['clientes_inativos'] = (int)($stmt->get_result()->fetch_assoc()['total'] ?? 0);
    $stmt->close();

    // -----------------------------
    // 4) TAXA DE FECHAMENTO (CONCLUÍDAS / TOTAL)
    // -----------------------------
    $sql = "
        SELECT
            COUNT(*) as total_vendas,
            SUM(CASE WHEN status = 'concluida' THEN 1 ELSE 0 END) as vendas_concluidas
        FROM vendas
        WHERE 1=1
        " . ($isVendedor ? " AND usuario_id = ? " : "");

    $stmt = $conn->prepare($sql);
    if ($isVendedor) $stmt->bind_param("i", $usuarioId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $total_vendas = (int)($row['total_vendas'] ?? 0);
    $vendas_concluidas = (int)($row['vendas_concluidas'] ?? 0);

    $dadosDashboard['total_vendas'] = $total_vendas;
    $dadosDashboard['vendas_concluidas'] = $vendas_concluidas;
    $dadosDashboard['taxa_fechamento_geral'] = $total_vendas > 0 ? round(($vendas_concluidas * 100.0) / $total_vendas, 1) : 0;

    $stmt->close();

    // -----------------------------
    // 5) ÚLTIMOS CLIENTES (GERAL ou POR VENDEDOR)
    // -----------------------------
    $dadosDashboard['ultimas_vendas'] = [];

    if ($isVendedor) {
        $stmt = $conn->prepare("
            SELECT v.*, c.nome AS cliente_nome
            FROM vendas v
            INNER JOIN clientes c ON c.id = v.cliente_id
            WHERE v.usuario_id = ?
            ORDER BY v.data_venda DESC, v.id DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $dadosDashboard['ultimas_vendas'][] = $row;
        }
        $stmt->close();
    } else {
        $result = $conn->query("
            SELECT v.*, c.nome AS cliente_nome
            FROM vendas v
            INNER JOIN clientes c ON c.id = v.cliente_id
            ORDER BY v.data_venda DESC, v.id DESC
            LIMIT 10
        ");
        while ($row = $result->fetch_assoc()) {
            $dadosDashboard['ultimas_vendas'][] = $row;
        }
    }

} catch (Exception $e) {
    error_log("Erro dashboard: " . $e->getMessage());
}
$conn->close();
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard - CRM TAPEMAG</h2>
        <div class="dashboard-actions">
            <div class="dashboard-actions">

  <button type="button" class="btn-primary" data-action="novo-cliente">
    <i class="fas fa-user-plus"></i> Novo Cliente
  </button>


        <button type="button" class="btn-success" data-action="venda-rapida">
            <i class="fas fa-bolt"></i> Venda Rápida
        </button>

  <button type="button" class="btn-secondary" data-action="editar-dashboard">
    <i class="fas fa-edit"></i> Editar Dashboard
  </button>
</div>

        </div>
    </div>
    
    <div class="stats-grid" id="stats-container">
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-users"></i>
            </div>
            <div class="stat-content">
                <h3>Total de Clientes</h3>
                <p class="stat-value" id="total-clientes"><?= $dadosDashboard['total_clientes'] ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-shopping-cart"></i>
            </div>
            <div class="stat-content">
                <h3>Vendas do Mês</h3>
                <p class="stat-value" id="vendas-mes"><?= $dadosDashboard['vendas_mes'] ?></p>
                <small class="stat-subtitle"><?= $dadosDashboard['vendas_concluidas'] ?> concluídas no total</small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>Valor do Mês</h3>
                <p class="stat-value" id="valor-mes">R$ <?= number_format($dadosDashboard['valor_mes'], 2, ',', '.') ?></p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-percentage"></i>
            </div>
            <div class="stat-content">
                <h3>Taxa de Fechamento</h3>
                <p class="stat-value" id="taxa-fechamento">
                    <?= $dadosDashboard['taxa_fechamento_geral'] ?>%
                </p>
                <small class="stat-subtitle">
                    <?= $dadosDashboard['vendas_concluidas'] ?>/<?= $dadosDashboard['total_vendas'] ?> vendas
                </small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="stat-content">
                <h3>Clientes Inativos</h3>
                <p class="stat-value" id="clientes-inativos"><?= $dadosDashboard['clientes_inativos'] ?></p>
                <small class="stat-subtitle">Sem compra há 15+ dias</small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-content">
                <h3>Total de Negociações</h3>
                <p class="stat-value" id="total-negociacoes"><?= $dadosDashboard['total_vendas'] ?></p>
                <small class="stat-subtitle">
                    <?= $dadosDashboard['vendas_concluidas'] ?> fechadas
                </small>
            </div>
        </div>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-history"></i> Ultimas Vendas</h3>
            <a href="vendas.php" class="btn-link">Ver todas</a>
        </div>
        
        <div class="table-responsive">
            <table class="data-table" id="ultimas-vendas">
                <thead>
                    <tr>
                        <th>Cliente</th>
                        <th>Valor</th>
                        <th>Data</th>
                        <th>Status</th>
                        <th class="text-center">Acoes</th>
                    </tr>
                </thead>
                <tbody id="ultimas-vendas-body">
                    <?php if (empty($dadosDashboard['ultimas_vendas'])): ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhuma venda registrada</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($dadosDashboard['ultimas_vendas'] as $venda): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($venda['cliente_nome']) ?></strong>
                            </td>
                            <td>R$ <?= number_format((float)$venda['valor'], 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y', strtotime($venda['data_venda'])) ?></td>
                            <td><?= htmlspecialchars($venda['status']) ?></td>
                            <td class="text-center">
                                <button class="btn-icon" onclick="abrirModalCliente(<?= (int)$venda['cliente_id'] ?>)">
                                    <i class="fas fa-user"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Editor de Dashboard -->
<div id="modal-dashboard-editor" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-th-large"></i> Editar Dashboard</h2>
            <button class="modal-close" onclick="closeModal('modal-dashboard-editor')">&times;</button>
        </div>
        <div class="modal-body">
           
            <div class="editor-columns">
                <div class="editor-left">
                    <h4>Layout atual</h4>
                    <div id="editor-layout-list" class="editor-layout-list"></div>
                </div>
                <div class="editor-right">
                    <h4>Adicionar card</h4>
                    <div id="editor-available-cards">
                        <button class="btn btn-outline" data-card-id="card_total_clientes">Total de Clientes</button>
                        <button class="btn btn-outline" data-card-id="card_vendas_mes">Vendas do Mês</button>
                        <button class="btn btn-outline" data-card-id="card_valor_mes">Valor do Mês</button>
                        <button class="btn btn-outline" data-card-id="card_taxa_fechamento">Taxa de Fechamento</button>
                        <button class="btn btn-outline" data-card-id="card_clientes_inativos">Clientes Inativos</button>
                        <button class="btn btn-outline" data-card-id="card_total_negociacoes">Total de Negociações</button>
                        <button class="btn btn-outline" data-card-id="card_faturamento_mes">Faturamento do Mês</button>
                        <button class="btn btn-outline" data-card-id="card_qtd_vendas_mes">Vendas do Mês</button>
                        <button class="btn btn-outline" data-card-id="card_ticket_medio_mes">Ticket Médio (Mês)</button>
                        <button class="btn btn-outline" data-card-id="card_faturamento_semana">Faturamento da Semana</button>
                        <button class="btn btn-outline" data-card-id="card_qtd_vendas_semana">Vendas da Semana</button>
                        <button class="btn btn-outline" data-card-id="card_ticket_medio_semana">Ticket Medio (Semana)</button>
                        <button class="btn btn-outline" data-card-id="card_faturamento_dia">Faturamento do Dia</button>
                        <button class="btn btn-outline" data-card-id="card_qtd_vendas_dia">Vendas do Dia</button>
                        <button class="btn btn-outline" data-card-id="card_ticket_medio_dia">Ticket Médio (Dia)</button>
                        <button class="btn btn-outline" data-card-id="card_meta_dia">Meta do Dia</button>
                        <button class="btn btn-outline" data-card-id="card_meta_atingida_percent_dia">% da Meta (Dia)</button>
                        <button class="btn btn-outline" data-card-id="card_clientes_perdidos_60d">Clientes Perdidos (60d)</button>
                        <button class="btn btn-outline" data-card-id="card_projecao_mes">Projeção do Mês</button>
                        <button class="btn btn-outline" data-card-id="card_meta_mes">Meta do Mês</button>
                        <button class="btn btn-outline" data-card-id="card_meta_atingida_percent">% da Meta (Mês)</button>
                        <button class="btn btn-outline" data-card-id="card_necessario_por_dia">Meta Ticket Medio (Dia)</button>
                        <button class="btn btn-outline" data-card-id="card_grafico_exemplo">Gráfico (6 meses)</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-secondary" onclick="closeModal('modal-dashboard-editor')">Cancelar</button>
            <button class="btn btn-primary" onclick="saveDashboardLayout()">Salvar</button>
        </div>
    </div>
</div>
<?php include 'includes/modals/modal_venda_rapida.php'; ?>

<script src="assets/js/scripts.js"></script>
<style>
    /* Estilos para a barra de progresso da taxa de fechamento */
    .progress-indicator {
        position: relative;
        height: 24px;
        background: #e2e8f0;
        border-radius: 12px;
        overflow: hidden;
        display: flex;
        align-items: center;
        padding: 0 8px;
    }
    
    .progress-bar {
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        border-radius: 12px;
        transition: width 0.5s ease;
    }

    /* Dashboard editor styles */
    .editor-columns {
        display: flex;
        gap: 36px;
        padding: 24px 38px;
    }
                                    
    .editor-layout-list {
        border: 1px dashed #e2e8f0;
        min-height: 200px;
        padding: 8px;
        border-radius: 8px;
        background: #fff;
    }
    .editor-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 8px 10px;
        border: 1px solid #f1f5f9;
        border-radius: 6px;
        margin-bottom: 8px;
        background: #fafafa;
    }
    .editor-handle {
        cursor: grab;
        margin-right: 10px;
    }
    .editor-title {
        flex: 1;
    }
    #editor-available-cards button { display:block; margin-bottom:8px; }
    
    .progress-indicator span {
        position: relative;
        z-index: 1;
        font-size: 12px;
        font-weight: 600;
        color: #ffffffff;
    }
    
    /* Cores da barra de progresso baseadas na taxa */
    .progress-bar {
        background: #48bb78;
    }
    
    .progress-bar[style*="width: 0%"] {
        background: #e2e8f0;
    }
    
    .progress-bar[style*="width: 25%"] {
        background: #ed8936;
    }
    
    .progress-bar[style*="width: 50%"] {
        background: #ecc94b;
    }
    
    .progress-bar[style*="width: 75%"] {
        background: #48bb78;
    }
    
    .progress-bar[style*="width: 100%"] {
        background: #38b2ac;
    }
    
    /* Subtítulo para os cards */
    .stat-subtitle {
        font-size: 12px;
        color: #718096;
        margin-top: 2px;
        display: block;
    }
    
    /* Cores dos ícones dos cards */
    .stat-icon {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 12px;
        width: 60px;
        height: 60px;
        font-size: 24px;
    }
</style>



<?php 
require_once 'includes/footer.php';
?>
