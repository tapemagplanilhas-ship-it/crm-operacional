<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
verificarLogin();
/* Para páginas específicas de perfil, adicione:
// requerirPermissao('admin'); // Para páginas só de admin
// requerirPermissao('gerencia'); // Para páginas de gerência
?> */


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
    'ultimos_clientes' => []
];

if ($conn) {
    try {
        // Total de clientes
        $result = $conn->query("SELECT COUNT(*) as total FROM clientes");
        $dadosDashboard['total_clientes'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Vendas do mês
        $result = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(valor), 0) as valor 
                              FROM vendas 
                              WHERE status = 'concluida' 
                              AND MONTH(data_venda) = MONTH(CURRENT_DATE()) 
                              AND YEAR(data_venda) = YEAR(CURRENT_DATE())");
        $row = $result->fetch_assoc();
        $dadosDashboard['vendas_mes'] = $row['total'] ?? 0;
        $dadosDashboard['valor_mes'] = $row['valor'] ?? 0;
        
        // Clientes inativos
        $result = $conn->query("SELECT COUNT(*) as total 
                              FROM clientes 
                              WHERE ultima_venda IS NULL 
                              OR ultima_venda < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)");
        $dadosDashboard['clientes_inativos'] = $result->fetch_assoc()['total'] ?? 0;
        
        // Taxa de fechamento geral
        $result = $conn->query("SELECT 
                                COUNT(*) as total_vendas,
                                COUNT(CASE WHEN status = 'concluida' THEN 1 END) as vendas_concluidas
                              FROM vendas");
        $row = $result->fetch_assoc();
        
        $total_vendas = $row['total_vendas'] ?? 0;
        $vendas_concluidas = $row['vendas_concluidas'] ?? 0;
        
        if ($total_vendas > 0) {
            $taxa_fechamento_geral = ($vendas_concluidas * 100.0) / $total_vendas;
        } else {
            $taxa_fechamento_geral = 0;
        }
        
        $dadosDashboard['taxa_fechamento_geral'] = round($taxa_fechamento_geral, 1);
        $dadosDashboard['total_vendas'] = $total_vendas;
        $dadosDashboard['vendas_concluidas'] = $vendas_concluidas;
        
        // Últimos clientes
        $result = $conn->query("SELECT * FROM clientes ORDER BY data_cadastro DESC LIMIT 5");
        while ($row = $result->fetch_assoc()) {
            $dadosDashboard['ultimos_clientes'][] = $row;
        }
        
    } catch (Exception $e) {
        error_log("Erro dashboard: " . $e->getMessage());
    }
    
    $conn->close();
}
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard - CRM TAPEMAG</h2>
        <div class="dashboard-actions">
            <button class="btn-primary" onclick="abrirModalCliente()">
                <i class="fas fa-user-plus"></i> Novo Cliente
            </button>
            <button class="btn-success" onclick="abrirModalVendaRapida()">
                <i class="fas fa-bolt"></i> Venda Rápida
            </button>
            <button class="btn-secondary" onclick="openDashboardEditor()">
                
                <i class="fas fa-edit"></i> Editar Dashboard
            </button>
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
            <h3><i class="fas fa-history"></i> Últimos Clientes Cadastrados</h3>
            <a href="clientes.php" class="btn-link">Ver todos</a>
        </div>
        
        <div class="table-responsive">
            <table class="data-table" id="ultimos-clientes">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Data Cadastro</th>
                        <th>Última Venda</th>
                        <th>Taxa Fechamento</th>
                        <th class="text-center">Ações</th>
                    </tr>
                </thead>
                <tbody id="ultimos-clientes-body">
                    <?php if (empty($dadosDashboard['ultimos_clientes'])): ?>
                    <tr>
                        <td colspan="6" class="text-center">Nenhum cliente cadastrado</td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($dadosDashboard['ultimos_clientes'] as $cliente): ?>
                        <tr>
                            <td>
                                <strong><?= htmlspecialchars($cliente['nome']) ?></strong>
                                <?php if (!empty($cliente['observacoes'])): ?>
                                <div class="text-muted"><?= htmlspecialchars(substr($cliente['observacoes'], 0, 50)) ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($cliente['telefone'])): ?>
                                <div><i class="fas fa-phone"></i> <?= htmlspecialchars($cliente['telefone']) ?></div>
                                <?php endif; ?>
                                <?php if (!empty($cliente['email'])): ?>
                                <div><i class="fas fa-envelope"></i> <?= htmlspecialchars($cliente['email']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($cliente['data_cadastro'])) ?></td>
                            <td>
                                <?php if (!empty($cliente['ultima_venda']) && $cliente['ultima_venda'] != '0000-00-00'): ?>
                                    <?= date('d/m/Y', strtotime($cliente['ultima_venda'])) ?>
                                <?php else: ?>
                                    Nunca
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($cliente['taxa_fechamento'])): ?>
                                <div class="progress-indicator">
                                    <div class="progress-bar" style="width: <?= min($cliente['taxa_fechamento'], 100) ?>%"></div>
                                    <span><?= number_format($cliente['taxa_fechamento'], 1) ?>%</span>
                                </div>
                                <?php else: ?>
                                <span class="text-muted">0%</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <button class="btn-icon" onclick="abrirModalCliente(<?= $cliente['id'] ?>)">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn-icon" onclick="abrirModalVenda(<?= $cliente['id'] ?>)">
                                    <i class="fas fa-shopping-cart"></i>
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
            <div style="display:flex; gap:12px; align-items:center; margin-bottom:12px;">
                <input id="editor-layout-name" placeholder="Nome do layout" style="flex:1; padding:8px;" />
                <label style="display:flex; align-items:center; gap:6px; font-size:14px;">
                    <input id="editor-layout-shared" type="checkbox" /> <span>Compartilhar</span>
                </label>
                <button class="btn btn-outline" onclick="resetToDefault()">Resetar para padrão</button>
            </div>
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
                        <button class="editor-card-btn" data-card-id="card_faturamento_mes">Faturamento do Mês</button>
                        <button class="editor-card-btn" data-card-id="card_qtd_vendas_mes">Vendas do Mês</button>
                        <button class="editor-card-btn" data-card-id="card_ticket_medio_mes">Ticket Médio (Mês)</button>
                        <button class="editor-card-btn" data-card-id="card_clientes_perdidos_60d">Clientes Perdidos (60d)</button>
                        <button class="editor-card-btn" data-card-id="card_projecao_mes">Projeção do Mês</button>
                        <button class="editor-card-btn" data-card-id="card_meta_atingida_percent">% da Meta (Mês)</button>
                        <button class="editor-card-btn" data-card-id="card_necessario_por_dia">Necessário por Dia</button>
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
        gap: 16px;
    }
    .editor-left, .editor-right {
        flex: 1;
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
