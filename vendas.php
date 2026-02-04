<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}
$usuario_id = (int)$usuario_id;
// Buscar dados iniciais
$conn = getConnection();
$vendas = [];
$total_vendas = 0;
$total_valor = 0;
$filtro_status = $_GET['status'] ?? 'todos';
$filtro_mes = $_GET['mes'] ?? date('m');
$filtro_ano = $_GET['ano'] ?? date('Y');

if ($conn) {
    try {
        // Construir query com filtros
        $where = "WHERE 1=1";
        $params = [];
        $types = "";
        
        if ($filtro_status !== 'todos') {
            $where .= " AND v.status = ?";
            $params[] = $filtro_status;
            $types .= "s";
        }

        if ($filtro_mes && $filtro_ano) {
            $where .= " AND MONTH(v.data_venda) = ? AND YEAR(v.data_venda) = ?";
            $params[] = $filtro_mes;
            $params[] = $filtro_ano;
            $types .= "ii";
        }

        // Restringir vendas para vendedor (cada vendedor só vê as próprias)
        if ($perfil_usuario === 'vendedor') {
            $where .= " AND v.usuario_id = ?";
            $params[] = $usuario_id;
            $types .= "i";
        }
        
        // Buscar vendas
        $sql = "SELECT v.*, c.nome as cliente_nome, c.telefone as cliente_telefone 
                FROM vendas v
                LEFT JOIN clientes c ON v.cliente_id = c.id
                $where
                ORDER BY v.data_venda DESC, v.data_registro DESC";
        
        $stmt = $conn->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $vendas[] = $row;
            if ($row['status'] === 'concluida') {
                $total_valor += $row['valor'];
            }
        }
        
        $total_vendas = count($vendas);
        
        // Buscar estatsticas
        $sql_stats = "SELECT 
                      COUNT(*) as total,
                      COUNT(CASE WHEN status = 'concluida' THEN 1 END) as concluidas,
                      COUNT(CASE WHEN status = 'perdida' THEN 1 END) as perdidas,
                      COUNT(CASE WHEN status = 'orcamento' THEN 1 END) as orcamentos,
                      COALESCE(SUM(CASE WHEN status = 'concluida' THEN valor ELSE 0 END), 0) as valor_total
                      FROM vendas";

        $params_stats = [];
        $types_stats = "";
        if ($perfil_usuario === 'vendedor') {
            $sql_stats .= " WHERE usuario_id = ?";
            $params_stats[] = $usuario_id;
            $types_stats .= "i";
        }

        $stmt_stats = $conn->prepare($sql_stats);
        if (!empty($params_stats)) {
            $stmt_stats->bind_param($types_stats, ...$params_stats);
        }
        $stmt_stats->execute();
        $result_stats = $stmt_stats->get_result();
        $stats = $result_stats->fetch_assoc();
        
    } catch (Exception $e) {
        error_log("Erro ao carregar vendas: " . $e->getMessage());
    }
    
    $conn->close();
}
// Carregar motivos de perda para selects
$motivos_perda = [];
$motivos_conn = getConnection();
if ($motivos_conn) {
    $tableExists = $motivos_conn->query("SHOW TABLES LIKE 'motivos_perda'");
    if ($tableExists && $tableExists->num_rows > 0) {
        $resMotivos = $motivos_conn->query("SELECT id, nome, permite_outro FROM motivos_perda ORDER BY ordem ASC, nome ASC");
        if ($resMotivos) {
            while ($row = $resMotivos->fetch_assoc()) {
                $motivos_perda[] = $row;
            }
        }
    }
    $motivos_conn->close();
}
?>

<div class="page-header">
    <h2><i class="fas fa-shopping-cart"></i> Vendas / Negociações</h2>
    <div class="page-actions">
        <button type="button" class="btn-success" data-action="venda-rapida">
            <i class="fas fa-bolt"></i> Venda Rápida
        </button>
        <button class="btn-primary" onclick="window.location.href='clientes.php'">
            <i class="fas fa-user-friends"></i> Ver Clientes
        </button>
    </div>
</div>

<!-- Cards de Estatsticas - Verso Clean -->
<div class="stats-grid clean" style="margin-bottom: 30px;">
    <div class="stat-card clean">
        <div class="stat-icon clean">
            <i class="fas fa-shopping-cart"></i>
        </div>
        <div class="stat-content">
            <h3>Total Negociações</h3>
            <p class="stat-value"><?= $stats['total'] ?? 0 ?></p>
            <small class="stat-subtitle">Todas as negociações</small>
        </div>
    </div>
    
    <div class="stat-card clean">
        <div class="stat-icon clean success">
            <i class="fas fa-check-circle"></i>
        </div>
        <div class="stat-content">
            <h3>Concluídas</h3>
            <p class="stat-value"><?= $stats['concluidas'] ?? 0 ?></p>
            <small class="stat-subtitle">
                <?= number_format(($stats['concluidas'] ?? 0) * 100 / max(($stats['total'] ?? 1), 1), 1) ?>%
            </small>
        </div>
    </div>
    
    <div class="stat-card clean">
        <div class="stat-icon clean warning">
            <i class="fas fa-times-circle"></i>
        </div>
        <div class="stat-content">
            <h3>Perdidas</h3>
            <p class="stat-value"><?= $stats['perdidas'] ?? 0 ?></p>
            <small class="stat-subtitle">
                <?= number_format(($stats['perdidas'] ?? 0) * 100 / max(($stats['total'] ?? 1), 1), 1) ?>%
            </small>
        </div>
    </div>
    
    <div class="stat-card clean">
        <div class="stat-icon clean info">
            <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <div class="stat-content">
            <h3>Valor Total</h3>
            <p class="stat-value">R$ <?= number_format($stats['valor_total'] ?? 0, 2, ',', '.') ?></p>
            <small class="stat-subtitle">Somente concluídas</small>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filters-card">
    <h3><i class="fas fa-filter"></i> Filtros</h3>
    
    <form id="filtros-vendas" method="GET" class="filters-form">
        <div class="form-row">
            <div class="form-group">
                <label for="filtro-status">Status</label>
                <select id="filtro-status" name="status" class="form-control" onchange="this.form.submit()">
                    <option value="todos" <?= $filtro_status === 'todos' ? 'selected' : '' ?>>Todos os Status</option>
                    <option value="concluida" <?= $filtro_status === 'concluida' ? 'selected' : '' ?>>Concluídas</option>
                    <option value="perdida" <?= $filtro_status === 'perdida' ? 'selected' : '' ?>>Perdidas</option>
                    <option value="orcamento" <?= $filtro_status === 'orcamento' ? 'selected' : '' ?>>Orçamentos</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="filtro-mes">Ms</label>
                <select id="filtro-mes" name="mes" class="form-control" onchange="this.form.submit()">
                    <?php for ($i = 1; $i <= 12; $i++): ?>
                        <option value="<?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>" 
                                <?= $filtro_mes == str_pad($i, 2, '0', STR_PAD_LEFT) ? 'selected' : '' ?>>
                            <?= DateTime::createFromFormat('!m', $i)->format('F') ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group">
                <label for="filtro-ano">Ano</label>
                <select id="filtro-ano" name="ano" class="form-control" onchange="this.form.submit()">
                    <?php for ($i = date('Y'); $i >= 2020; $i--): ?>
                        <option value="<?= $i ?>" <?= $filtro_ano == $i ? 'selected' : '' ?>><?= $i ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <div class="form-group" style="align-self: flex-end;">
                <button type="button" class="btn-secondary" onclick="window.location.href='vendas.php'">
                    <i class="fas fa-redo"></i> Limpar Filtros
                </button>
            </div>
        </div>
    </form>
</div>

<!-- Tabela de Vendas com ordenao -->
<div class="table-responsive table-responsive-clientes">
    <table class="data-table sortable" id="tabela-vendas">
        <thead>
            <tr>
                <th class="sortable-header" data-sort="data_venda" data-order="desc">
                    Data <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="cliente_nome" data-order="">
                    Cliente <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="valor" data-order="desc">
                    Valor <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="status" data-order="">
                    Status <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="forma_pagamento" data-order="">
                    Forma Pagamento <i class="fas fa-sort"></i>
                </th>
                <th class="sortable-header" data-sort="data_registro" data-order="desc">
                    Data Registro <i class="fas fa-sort"></i>
                </th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody id="vendas-body">
            <?php 
            // Ordenao inicial baseada nos filtros GET
            $sort_by = $_GET['sort'] ?? 'data_venda';
            $sort_order = $_GET['order'] ?? 'desc';
            
            // Funo para ordenar array multidimensional
            usort($vendas, function($a, $b) use ($sort_by, $sort_order) {
                $val_a = $a[$sort_by] ?? '';
                $val_b = $b[$sort_by] ?? '';
                
                // Tratamento especial para valores numricos
                if ($sort_by === 'valor') {
                    $val_a = floatval($val_a);
                    $val_b = floatval($val_b);
                }
                
                // Tratamento especial para datas
                if (in_array($sort_by, ['data_venda', 'data_registro'])) {
                    $val_a = strtotime($val_a);
                    $val_b = strtotime($val_b);
                }
                
                if ($sort_order === 'asc') {
                    return $val_a <=> $val_b;
                } else {
                    return $val_b <=> $val_a;
                }
            });
            
            if (empty($vendas)): ?>
                <tr>
                    <td colspan="7" class="text-center">Nenhuma venda encontrada</td>
                </tr>
            <?php else: ?>
                <?php foreach ($vendas as $venda): ?>
                <tr>
                    <td>
                        <strong><?= date('d/m/Y', strtotime($venda['data_venda'])) ?></strong>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($venda['cliente_nome'] ?? 'Cliente no encontrado') ?></strong>
                        <?php if (!empty($venda['cliente_telefone'])): ?>
                        <div class="text-muted"><?= htmlspecialchars($venda['cliente_telefone']) ?></div>
                        <?php endif; ?>
                    </td>
                    <td>
                        <strong class="<?= $venda['status'] === 'concluida' ? 'text-success' : 'text-muted' ?>">
                            R$ <?= number_format($venda['valor'], 2, ',', '.') ?>
                        </strong>
                    </td>
                    <td>
                        <?php
                        $status_classes = [
                            'concluida' => 'status-badge status-concluida',
                            'perdida' => 'status-badge status-perdida',
                            'orcamento' => 'status-badge status-orcamento'
                        ];
                        $status_text = [
                            'concluida' => 'CONCLUÍDA',
                            'perdida' => 'PERDIDA',
                            'orcamento' => 'ORÇAMENTO'
                        ];
                        ?>
                        <span class="<?= $status_classes[$venda['status']] ?? 'status-badge' ?>">
                            <?= $status_text[$venda['status']] ?? $venda['status'] ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $pagamento_icons = [
                            'pix' => 'fas fa-qrcode',
                            'cartao' => 'fas fa-credit-card',
                            'dinheiro' => 'fas fa-money-bill-wave',
                            'boleto' => 'fas fa-barcode',
                            'na' => 'fas fa-ban'
                        ];
                        $pagamento_text = [
                            'pix' => 'PIX',
                            'cartao' => 'Cartão',
                            'dinheiro' => 'Dinheiro',
                            'boleto' => 'Boleto',
                            'na' => 'N/A'
                        ];
                        ?>
                        <div class="pagamento-display">
                            <i class="<?= $pagamento_icons[$venda['forma_pagamento']] ?? 'fas fa-question' ?>"></i>
                            <?= $pagamento_text[$venda['forma_pagamento']] ?? $venda['forma_pagamento'] ?>
                        </div>
                    </td>
                    <td>
                        <?= date('d/m/Y H:i', strtotime($venda['data_registro'])) ?>
                    </td>
                    <td class="text-center">
                        <div class="actions-menu">
                            <button class="actions-toggle" onclick="toggleActionsMenu(this)">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="actions-dropdown">
                                <button onclick="verDetalhesVenda(<?= $venda['id'] ?>)">
                                    <i class="fas fa-eye"></i> Ver Detalhes
                                </button>
                                <button onclick="editarVenda(<?= $venda['id'] ?>)">
                                    <i class="fas fa-edit"></i> Editar
                                </button>
                                <button class="danger" onclick="excluirVenda(<?= $venda['id'] ?>, '<?= htmlspecialchars($venda['cliente_nome'] ?? '') ?>')">
                                    <i class="fas fa-trash"></i> Excluir
                                </button>
                            </div>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal de Detalhes da Venda -->
<div id="modal-detalhes-venda" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 600px;">
        <div class="modal-header">
            <h2><i class="fas fa-eye"></i> Detalhes da Negociação</h2>
            <button class="modal-close" onclick="fecharModal('detalhes-venda')">&times;</button>
        </div>
        
        <div class="modal-body" id="detalhes-venda-conteudo" style="padding: 0px 30px;">
            <!-- Conteúdo será carregado via JavaScript -->
            <div class="loading-details">
                <i class="fas fa-spinner fa-spin"></i> Carregando detalhes...
            </div>
        </div>
        
        <div class="form-actions" style="padding: 30px;">
            <button type="button" class="btn-secondary" onclick="fecharModal('detalhes-venda')" >
                Fechar
            </button>
            <button type="button" class="btn-primary" id="btn-editar-venda-modal" onclick="editarVendaModal()">
                <i class="fas fa-edit"></i> Editar Venda
            </button>
        </div>
    </div>
</div>

<!-- Modal de Edio de Venda -->
<div id="modal-editar-venda" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2><i class="fas fa-edit"></i> Editar Negociação</h2>
            <button class="modal-close" onclick="fecharModal('editar-venda')">&times;</button>
        </div>
        
        <form id="form-editar-venda" class="modal-form" onsubmit="salvarEdicaoVenda(event)">
            <input type="hidden" id="editar-venda-id" name="id" value="">
            <input type="hidden" id="editar-cliente-id" name="cliente_id" value="">
            
            <div class="form-group">
                <label>Cliente</label>
                <input type="text" id="editar-cliente-nome" readonly class="readonly-field">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="editar-valor" class="required">Valor</label>
                    <input type="text" id="editar-valor" name="valor" required 
                           class="money-input" oninput="formatarMoeda(this)">
                </div>
                
                <div class="form-group">
                    <label for="editar-data" class="required">Data da Negociação</label>
                    <input type="text" id="editar-data" name="data_venda" required 
                           oninput="formatarData(this)">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="editar-status" class="required">Status</label>
                    <select id="editar-status" name="status" required 
                            onchange="mostrarCampoMotivoPerdaEdicao()">
                        <option value="concluida">CONCLUÍDA</option>
                        <option value="orcamento">ORÇAMENTO</option>
                        <option value="perdida">PERDIDA</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="editar-forma-pagamento" class="required">Forma de Pagamento</label>
                    <select id="editar-forma-pagamento" name="forma_pagamento" required>
                        <option value="">Selecione...</option>
                        <option value="pix">PIX</option>
                        <option value="cartao">Cartão</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="boleto">Boleto</option>
                        <option value="na">N/A</option>
                    </select>
                </div>
            </div>

            <!-- Campo de código de orçamento -->
            <div class="form-group" id="campo-codigo-orcamento-edicao">
                <label for="editar-codigo-orcamento">Código do Orçamento</label>
                <input type="text" id="editar-codigo-orcamento" name="codigo_orcamento"
                       placeholder="Ex: 12345" inputmode="numeric"
                       oninput="limparNaoNumericos(this)">
                <small class="field-hint">Opcional, apenas números</small>
            </div>

            <div class="form-group" id="campo-motivo-perda-edicao" style="display: none;">
                <label for="editar-motivo-perda-select" class="required">Motivo da Perda</label>
                <select id="editar-motivo-perda-select" name="motivo_perda_id" onchange="mostrarCampoMotivoPerdaEdicao()">
                    <option value="">Selecione...</option>
                    <?php foreach ($motivos_perda as $motivo): ?>
                        <option value="<?= (int)$motivo['id'] ?>" data-permite-outro="<?= (int)$motivo['permite_outro'] ?>">
                            <?= htmlspecialchars($motivo['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="editar-motivo-perda-outro-container" style="display: none; margin-top: 10px;">
                    <input type="text" id="editar-motivo-perda-outro" name="motivo_perda_outro" placeholder="Descreva o motivo" />
                </div>
            </div>
            
            <div class="form-group">
                <label for="editar-observacoes">Observações</label>
                <textarea id="editar-observacoes" name="observacoes" rows="4"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" onclick="fecharModal('editar-venda')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-save"></i> Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Estilos especficos para a pgina de vendas */
    .filters-card {
        background: white;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
    }
    
    .filters-card h3 {
        margin-top: 0;
        margin-bottom: 20px;
        color: #2d3748;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .filters-form .form-row {
        margin-bottom: 0;
    }
    
    .status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
    }
    
    .status-concluida {
        background-color: #c6f6d5;
        color: #22543d;
        border: 1px solid #9ae6b4;
    }
    
    .status-perdida {
        background-color: #fed7d7;
        color: #742a2a;
        border: 1px solid #fc8181;
    }
    
    .status-orcamento {
        background-color: #feebc8;
        color: #744210;
        border: 1px solid #f6ad55;
    }
    
    .pagamento-display {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
    }
    
    .text-success {
        color: #38a169;
    }
    
    .text-muted {
        color: #718096;
    }
    
    .loading-details {
        text-align: center;
        padding: 40px;
        color: #718096;
    }
    
    .detalhes-venda {
        padding: 10px 0;
    }
    
    .detalhes-item {
        padding: 15px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .detalhes-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
    }
    
    .detalhes-label {
        font-size: 12px;
        color: #718096;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
        font-weight: 600;
        margin-top: 20px
        margin-bottom: -20px;
    }
    
    .detalhes-value {
        font-size: 16px;
        color: #2d3748;
        font-weight: 500;
        margin-top: 20px
        margin-bottom: -20px;
    }
    
    .detalhes-value strong {
        font-size: 20px;
        color: #2d3748;
    }
    
    .observacoes-box {
        background: #f7fafc;
        border-radius: 8px;
        padding: 15px;
        margin-top: 5px;
        border-left: 4px solid #4299e1;
        white-space: pre-wrap;
        font-size: 14px;
        line-height: 1.5;
    }
    
    .actions-menu {
        position: relative;
        display: inline-block;
    }
    
    .actions-toggle {
        background: none;
        border: 1px solid #e2e8f0;
        padding: 8px 12px;
        border-radius: 6px;
        cursor: pointer;
        color: #718096;
        transition: all 0.2s;
    }
    
    .actions-toggle:hover {
        background: #f7fafc;
        border-color: #cbd5e0;
    }
    
    .actions-dropdown {
        position: absolute;
        top: 100%;
        right: 0;
        background: white;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        min-width: 180px;
        display: none;
        z-index: 1000;
        border: 1px solid #e2e8f0;
    }
    
    .actions-dropdown.show {
        display: block;
    }
    
    .actions-dropdown button {
        width: 100%;
        padding: 12px 16px;
        text-align: left;
        border: none;
        background: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 10px;
        font-size: 14px;
        color: #4a5568;
        transition: background 0.2s;
        border-bottom: 1px solid #f7fafc;
    }
    
    .actions-dropdown button:last-child {
        border-bottom: none;
    }
    
    .actions-dropdown button:hover {
        background: #f7fafc;
    }
    
    .actions-dropdown .danger {
        color: #e53e3c;
    }
    
    .actions-dropdown .danger:hover {
        background: #fff5f5;
    }
</style>

<script>
// Funes para a pgina de vendas
document.addEventListener('DOMContentLoaded', function() {
    // Configurar formatao de moeda e data nos inputs
    document.querySelectorAll('.money-input').forEach(input => {
        if (input.value) formatarMoeda(input);
    });
    
    document.querySelectorAll('input[oninput*="formatarData"]').forEach(input => {
        if (input.value) formatarData(input);
    });
});

// Ver detalhes da venda
async function verDetalhesVenda(vendaId) {
    try {
        const modal = document.getElementById('modal-detalhes-venda');
        const conteudo = document.getElementById('detalhes-venda-conteudo');
        const btnEditar = document.getElementById('btn-editar-venda-modal');
        
        conteudo.innerHTML = '<div class="loading-details"><i class="fas fa-spinner fa-spin"></i> Carregando detalhes...</div>';
        modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';
        
        // Buscar detalhes da venda
        const response = await fetch(`api/venda_detalhes.php?id=${vendaId}`);
        const data = await response.json();
        
        if (data.success) {
            const venda = data.data;
            
            // Formatar dados
            const dataVenda = venda.data_venda ? new Date(venda.data_venda).toLocaleDateString('pt-BR') : 'No informada';
            const dataRegistro = venda.data_registro ? new Date(venda.data_registro).toLocaleString('pt-BR') : 'No informada';
            const valorFormatado = 'R$ ' + parseFloat(venda.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            
            // Status e pagamento
            const statusText = {
                'concluida': 'CONCLUÍDA',
                'perdida': 'PERDIDA', 
                'orcamento': 'ORÇAMENTO'
            };
            
            const pagamentoText = {
                'pix': 'PIX',
                'cartao': 'Cartão',
                'dinheiro': 'Dinheiro',
                'boleto': 'Boleto',
                'na': 'N/A'
            };

            const statusClass = {
                'concluida': 'status-concluida',
                'perdida': 'status-perdida',
                'orcamento': 'status-orcamento'
            };

            const mostrarVendedor = <?php echo json_encode(in_array($perfil_usuario, ['admin', 'gerencia'], true)); ?>;
            const vendedorNome = (venda.vendedor_nome ?? '').toString().trim();
            const codigoOrcamento = (venda.codigo_orcamento ?? '').toString().trim();
            
            conteudo.innerHTML = `
                <div class="detalhes-venda">
                    <div class="detalhes-item">
                        <div class="detalhes-label">Cliente</div>
                        <div class="detalhes-value">
                            <strong>${escapeHtml(venda.cliente_nome || 'Cliente no encontrado')}</strong>
                            ${venda.cliente_telefone ? `<div style="margin-top: 5px;">?? ${escapeHtml(venda.cliente_telefone)}</div>` : ''}
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="detalhes-label">Valor</div>
                            <div class="detalhes-value">${valorFormatado}</div>
                        </div>
                        
                        <div class="form-group">
                            <div class="detalhes-label">Data da Negociação</div>
                            <div class="detalhes-value">${dataVenda}</div>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <div class="detalhes-label">Status</div>
                            <div class="detalhes-value">
                                <span class="status-badge ${statusClass[venda.status] || ''}">
                                    ${statusText[venda.status] || venda.status}
                                </span>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="detalhes-label">Forma de Pagamento</div>
                            <div class="detalhes-value">
                                <div class="pagamento-display">
                                    <i class="fas fa-${venda.forma_pagamento === 'pix' ? 'qrcode' : venda.forma_pagamento === 'cartao' ? 'credit-card' : venda.forma_pagamento === 'dinheiro' ? 'money-bill-wave' : venda.forma_pagamento === 'boleto' ? 'barcode' : 'ban'}"></i>
                                    ${pagamentoText[venda.forma_pagamento] || venda.forma_pagamento}
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    ${mostrarVendedor ? `
                    <div class="detalhes-item">
                        <div class="detalhes-label">Vendedor</div>
                        <div class="detalhes-value">${escapeHtml(vendedorNome || '-')}</div>
                    </div>
                    ` : ''}

                    ${codigoOrcamento ? `
                    <div class="detalhes-item">
                        <div class="detalhes-label">Número do Orçamento</div>
                        <div class="detalhes-value">${escapeHtml(codigoOrcamento)}</div>
                    </div>
                    ` : ''}

                    ${venda.status === 'perdida' && venda.motivo_perda ? `
                    <div class="detalhes-item">
                        <div class="detalhes-label">Motivo da Perda</div>
                        <div class="observacoes-box">${escapeHtml(venda.motivo_perda)}</div>
                    </div>
                    ` : ''}
                    
                    ${venda.observacoes ? `
                    <div class="detalhes-item">
                        <div class="detalhes-label">Observações</div>
                        <div class="observacoes-box">${escapeHtml(venda.observacoes)}</div>
                    </div>
                    ` : ''}
                    
                    <div class="detalhes-item">
                        <div class="detalhes-label">Data de Registro</div>
                        <div class="detalhes-value">${dataRegistro}</div>
                    </div>
                </div>
            `;
            
            // Configurar boto de editar
            btnEditar.onclick = function() {
                fecharModal('detalhes-venda');
                setTimeout(() => editarVenda(vendaId), 300);
            };
            
        } else {
            conteudo.innerHTML = `<div class="text-center" style="padding: 40px; color: #e53e3e;">
                <i class="fas fa-exclamation-circle"></i> ${data.message || 'Erro ao carregar detalhes'}
            </div>`;
            btnEditar.style.display = 'none';
        }
        
    } catch (error) {
        console.error('Erro:', error);
        document.getElementById('detalhes-venda-conteudo').innerHTML = 
            '<div class="text-center" style="padding: 40px; color: #e53e3e;">Erro de conexo</div>';
    }
}

// Editar venda
async function editarVenda(vendaId) {
    try {
        const modal = document.getElementById('modal-editar-venda');
        const form = document.getElementById('form-editar-venda');
        
        // Limpar formulrio
        form.reset();
        document.getElementById('campo-motivo-perda-edicao').style.display = 'none';
        
        // Buscar dados da venda
        const response = await fetch(`api/venda_detalhes.php?id=${vendaId}`);
        const data = await response.json();
        
        if (data.success) {
            const venda = data.data;
            
            // Preencher formulrio
            document.getElementById('editar-venda-id').value = venda.id;
            document.getElementById('editar-cliente-id').value = venda.cliente_id;
            document.getElementById('editar-cliente-nome').value = venda.cliente_nome || 'Cliente no encontrado';
            
            // Valor
            const valorInput = document.getElementById('editar-valor');
            valorInput.value = 'R$ ' + parseFloat(venda.valor).toLocaleString('pt-BR', {minimumFractionDigits: 2});
            
            // Data
            const dataVenda = venda.data_venda ? new Date(venda.data_venda) : new Date();
            const dia = String(dataVenda.getDate()).padStart(2, '0');
            const mes = String(dataVenda.getMonth() + 1).padStart(2, '0');
            const ano = dataVenda.getFullYear();
            document.getElementById('editar-data').value = `${dia}/${mes}/${ano}`;
            
            // Status
            document.getElementById('editar-status').value = venda.status;
            
            // Forma de pagamento
            document.getElementById('editar-forma-pagamento').value = venda.forma_pagamento || 'na';
            
            // Código do orçamento
            const codigoOrcamentoInput = document.getElementById('editar-codigo-orcamento');
            if (codigoOrcamentoInput) {
                codigoOrcamentoInput.value = venda.codigo_orcamento ? String(venda.codigo_orcamento) : '';
            }

            // Motivo da perda (se houver)
            preencherMotivoPerdaEdicao(venda.motivo_perda_id, venda.motivo_perda_outro);
            mostrarCampoMotivoPerdaEdicao();
            
            // Observações
            document.getElementById('editar-observacoes').value = venda.observacoes || '';
            
            // Mostrar modal
            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
            
        } else {
            mostrarToast(data.message || 'Erro ao carregar venda', 'error');
        }
        
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexo', 'error');
    }
}

// Funo para editar a partir do modal de detalhes
function editarVendaModal() {
    const vendaId = document.getElementById('editar-venda-id').value;
    if (vendaId) {
        fecharModal('detalhes-venda');
        setTimeout(() => editarVenda(vendaId), 300);
    }
}

// Mostrar/ocultar campo motivo na edio
function obterMotivoPerdaEdicao() {
    const select = document.getElementById('editar-motivo-perda-select');
    const outroInput = document.getElementById('editar-motivo-perda-outro');
    if (!select) return { motivoId: '', motivoOutro: '' };
    const motivoId = (select.value || '').trim();
    const permiteOutro = select.selectedOptions?.[0]?.dataset?.permiteOutro === '1';
    const motivoOutro = permiteOutro ? (outroInput?.value || '').trim() : '';
    return { motivoId, motivoOutro };
}

function preencherMotivoPerdaEdicao(motivoId, motivoOutro) {
    const select = document.getElementById('editar-motivo-perda-select');
    const outroContainer = document.getElementById('editar-motivo-perda-outro-container');
    const outroInput = document.getElementById('editar-motivo-perda-outro');
    if (!select) return;
    select.value = motivoId ? String(motivoId) : '';
    const permiteOutro = select.selectedOptions?.[0]?.dataset?.permiteOutro === '1';
    if (permiteOutro && motivoOutro) {
        if (outroContainer) outroContainer.style.display = 'block';
        if (outroInput) outroInput.value = motivoOutro;
    } else {
        if (outroContainer) outroContainer.style.display = 'none';
        if (outroInput) outroInput.value = '';
    }
}

function mostrarCampoMotivoPerdaEdicao() {
    const statusSelect = document.getElementById('editar-status');
    const motivoCampo = document.getElementById('campo-motivo-perda-edicao');
    const select = document.getElementById('editar-motivo-perda-select');
    const outroContainer = document.getElementById('editar-motivo-perda-outro-container');
    const outroInput = document.getElementById('editar-motivo-perda-outro');
    const isPerdida = statusSelect && statusSelect.value === 'perdida';
    if (motivoCampo) motivoCampo.style.display = isPerdida ? 'block' : 'none';
    if (select) {
        select.required = isPerdida;
        if (!isPerdida) select.value = '';
    }
    const isOutro = isPerdida && select && select.selectedOptions?.[0]?.dataset?.permiteOutro === '1';
    if (outroContainer) outroContainer.style.display = isOutro ? 'block' : 'none';
    if (outroInput) {
        outroInput.required = isOutro;
        if (!isOutro) outroInput.value = '';
    }

    const campoCodigo = document.getElementById('campo-codigo-orcamento-edicao');
    if (campoCodigo) campoCodigo.style.display = 'block';
}


// Salvar edio da venda
async function salvarEdicaoVenda(event) {
    event.preventDefault();
    
    const form = document.getElementById('form-editar-venda');
    const formData = new FormData(form);
    
    const data = {};
    formData.forEach((value, key) => {
        data[key] = value;
    });
    
    // Validações
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
    
    const motivoPayload = obterMotivoPerdaEdicao();
    if (data.status === 'perdida' && !motivoPayload.motivoId) {
        mostrarToast('Motivo da perda  obrigatrio para vendas perdidas', 'error');
        return;
    }
    if (data.status === 'perdida' && !motivoPayload.motivoOutro && document.getElementById('editar-motivo-perda-select')?.selectedOptions?.[0]?.dataset?.permiteOutro === '1') {
        mostrarToast('Descreva o motivo da perda', 'error');
        return;
    }
    data.motivo_perda_id = motivoPayload.motivoId || '';
    data.motivo_perda_outro = motivoPayload.motivoOutro || '';
    delete data.motivo_perda;

    const btnSubmit = form.querySelector('button[type="submit"]');
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';
    
    try {
        // Usar a mesma API de vendas, mas com mtodo PUT para atualizao
        const response = await fetch('api/vendas_editar.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarToast(result.message, 'success');
            fecharModal('editar-venda');
            
            // Recarregar a pgina aps 1 segundo
            setTimeout(() => {
                window.location.reload();
            }, 1000);
        } else {
            mostrarToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexo', 'error');
    } finally {
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<i class="fas fa-save"></i> Salvar Alterações';
    }
}

// Excluir venda
async function excluirVenda(vendaId, clienteNome) {
    if (typeof window.confirmarExclusao !== 'function') return;
    const confirmado = await window.confirmarExclusao(`Tem certeza que deseja excluir esta negociacao do cliente "${clienteNome}"?`);
    if (!confirmado) return;

    try {
        const response = await fetch(`api/vendas_excluir.php?id=${vendaId}`, {
            method: 'DELETE'
        });
        
        const result = await response.json();
        
        if (result.success) {
            mostrarToast(result.message, 'success');
            // Recarregar a pgina
            setTimeout(() => {
                window.location.reload();
            }, 500);
        } else {
            mostrarToast(result.message, 'error');
        }
    } catch (error) {
        console.error('Erro:', error);
        mostrarToast('Erro de conexo', 'error');
    }
}

// Toggle do menu de ações
function toggleActionsMenu(button) {
    const dropdown = button.nextElementSibling;
    const isShowing = dropdown.classList.contains('show');
    
    // Fechar todos os dropdowns
    document.querySelectorAll('.actions-dropdown.show').forEach(d => {
        d.classList.remove('show');
    });
    
    // Abrir/fechar o atual
    if (!isShowing) {
        dropdown.classList.add('show');
        
        // Fechar ao clicar fora
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
}

// Fechar modal
function fecharModal(tipo) {
    const modal = document.getElementById(`modal-${tipo}`);
    if (modal) {
        modal.style.display = 'none';
        document.body.style.overflow = 'auto';
    }
}

// Funes utilitrias
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function formatarMoeda(input) {
    let valor = input.value.replace(/\D/g, '');
    valor = (valor / 100).toFixed(2);
    valor = valor.replace('.', ',');
    valor = valor.replace(/(\d)(?=(\d{3})+,)/g, "$1.");
    input.value = 'R$ ' + valor;
}

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

function validarData(dataStr) {
    if (!dataStr) return false;
    const regex = /^(\d{2})\/(\d{2})\/(\d{4})$/;
    if (!regex.test(dataStr)) return false;
    
    const [, dia, mes, ano] = dataStr.match(regex);
    const diaNum = parseInt(dia, 10);
    const mesNum = parseInt(mes, 10);
    const anoNum = parseInt(ano, 10);
    
    if (mesNum < 1 || mesNum > 12) return false;
    const diasNoMes = new Date(anoNum, mesNum, 0).getDate();
    if (diaNum < 1 || diaNum > diasNoMes) return false;
    if (anoNum < 2000 || anoNum > 2100) return false;
    
    return true;
}

// Funo para mostrar toast (reutilizar se j existir)
function mostrarToast(mensagem, tipo = 'info') {
    // Evita recursao caso esta funcao sobrescreva a global
    if (typeof window.mostrarToast === 'function' && window.mostrarToast !== mostrarToast) {
        window.mostrarToast(mensagem, tipo);
        return;
    }
    alert(mensagem);
}


// ==============================================
// SISTEMA DE ORDENAO DAS TABELAS
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    inicializarOrdenacaoTabelas();
});

function inicializarOrdenacaoTabelas() {
    // Selecionar todos os headers sortable
    const headers = document.querySelectorAll('.sortable-header');
    
    headers.forEach(header => {
        header.style.cursor = 'pointer';
        
        // Adicionar evento de clique
        header.addEventListener('click', function() {
            const sortBy = this.dataset.sort;
            const currentOrder = this.dataset.order || '';
            
            // Determinar nova ordem
            let newOrder = 'asc';
            if (currentOrder === 'asc') {
                newOrder = 'desc';
            } else if (currentOrder === 'desc') {
                newOrder = '';
            } else {
                newOrder = 'asc';
            }
            
            // Atualizar cone
            updateSortIcons(this, newOrder);
            
            // Se houver dados no cliente (via JS), ordenar localmente
            if (window.location.pathname.includes('vendas.php')) {
                // Para vendas.php, recarregar com parmetros GET
                ordenarVendas(sortBy, newOrder);
            } else if (window.location.pathname.includes('clientes.php')) {
                // Para clientes.php, ordenar via JavaScript
                ordenarClientes(sortBy, newOrder);
            }
        });
    });
    
    // Inicializar cones baseados na URL atual
    atualizarIconesOrdenacao();
}

function updateSortIcons(header, order) {
    // Resetar todos os headers
    document.querySelectorAll('.sortable-header').forEach(h => {
        h.dataset.order = '';
        const icon = h.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-sort';
            icon.style.color = 'rgba(255, 255, 255, 0)';
        }
    });
    
    // Atualizar header atual
    if (order) {
        header.dataset.order = order;
        const icon = header.querySelector('i');
        if (icon) {
            if (order === 'asc') {
                icon.className = 'fas fa-sort-up';
                icon.style.color = 'rgba(255, 255, 255, 0)';
            } else if (order === 'desc') {
                icon.className = 'fas fa-sort-down';
                icon.style.color = 'rgba(255, 255, 255, 0)';
            }
        }
    }
}

function atualizarIconesOrdenacao() {
    const urlParams = new URLSearchParams(window.location.search);
    const sortBy = urlParams.get('sort');
    const order = urlParams.get('order');
    
    if (sortBy && order) {
        const header = document.querySelector(`.sortable-header[data-sort="${sortBy}"]`);
        if (header) {
            updateSortIcons(header, order);
        }
    }
}

 // ==============================================
// SISTEMA DE ORDENAO DAS TABELAS - SEM RECARREGAR
// ==============================================

document.addEventListener('DOMContentLoaded', function() {
    inicializarOrdenacaoTabelas();
});

function inicializarOrdenacaoTabelas() {
    // Selecionar todos os headers sortable
    const headers = document.querySelectorAll('.sortable-header');
    
    headers.forEach(header => {
        header.style.cursor = 'pointer';
        
        // Adicionar evento de clique
        header.addEventListener('click', function(e) {
            e.preventDefault();
            
            const sortBy = this.dataset.sort;
            const currentOrder = this.dataset.order || '';
            
            // Determinar nova ordem
            let newOrder = 'asc';
            if (currentOrder === 'asc') {
                newOrder = 'desc';
            } else if (currentOrder === 'desc') {
                newOrder = '';
            } else {
                newOrder = 'asc';
            }
            
            // Atualizar cone
            updateSortIcons(this, newOrder);
            
            // Ordenar a tabela localmente
            if (window.location.pathname.includes('vendas.php')) {
                ordenarTabelaVendasLocalmente(sortBy, newOrder);
            }
            // Remover a parte de clientes j que estamos na pgina de vendas
        });
    });
    
    // Inicializar cones baseados na URL atual (mantm estado visual)
    atualizarIconesOrdenacao();
}

function updateSortIcons(header, order) {
    // Resetar cones de todos os headers da mesma tabela
    const table = header.closest('table');
    table.querySelectorAll('.sortable-header').forEach(h => {
        h.dataset.order = '';
        const icon = h.querySelector('i');
        if (icon) {
            icon.className = 'fas fa-sort';
            icon.style.color = 'rgba(255, 255, 255, 0)';
        }
    });
    
    // Atualizar header atual se houver ordenao
    if (order) {
        header.dataset.order = order;
        const icon = header.querySelector('i');
        if (icon) {
            if (order === 'asc') {
                icon.className = 'fas fa-sort-up';
                icon.style.color = '#ffffffff';
            } else if (order === 'desc') {
                icon.className = 'fas fa-sort-down';
                icon.style.color = 'hsla(0, 0%, 100%, 1.00)';
            }
        }
    }
}

function atualizarIconesOrdenacao() {
    // Esta funo agora s atualiza os cones baseados no estado atual
    // No usa mais URL, usa os data-attributes que j esto no DOM
    document.querySelectorAll('.sortable-header').forEach(header => {
        const order = header.dataset.order;
        if (order) {
            const icon = header.querySelector('i');
            if (icon) {
                if (order === 'asc') {
                    icon.className = 'fas fa-sort-up';
                    icon.style.color = 'rgba(255, 255, 255, 0)';
                } else if (order === 'desc') {
                    icon.className = 'fas fa-sort-down';
                    icon.style.color = 'rgba(255, 255, 255, 0)';
                }
            }
        }
    });
}

// Funo para ordenar a tabela de vendas localmente (SEM RECARREGAR)
function ordenarTabelaVendasLocalmente(sortBy, order) {
    if (!order) {
        // Se order estiver vazio, volta  ordenao original (por data_venda desc)
        ordenarTabelaVendasLocalmente('data_venda', 'desc');
        return;
    }
    
    const tbody = document.getElementById('vendas-body');
    const rows = Array.from(tbody.querySelectorAll('tr'));
    
    // Filtrar apenas linhas com dados (exclui a linha "Nenhuma venda encontrada")
    const dataRows = rows.filter(row => !row.classList.contains('text-center') || row.cells.length > 1);
    
    if (dataRows.length <= 1) return; // No precisa ordenar se tem 0 ou 1 linha
    
    // Ordenar as linhas
    dataRows.sort((rowA, rowB) => {
        const valueA = getCellValueVendas(rowA, sortBy);
        const valueB = getCellValueVendas(rowB, sortBy);
        
        let comparison = 0;
        
        // Comparao baseada no tipo de dado
        if (typeof valueA === 'number' && typeof valueB === 'number') {
            comparison = valueA - valueB;
        } else if (valueA instanceof Date && valueB instanceof Date) {
            comparison = valueA.getTime() - valueB.getTime();
        } else {
            // Comparao de strings
            const strA = String(valueA || '').toLowerCase();
            const strB = String(valueB || '').toLowerCase();
            comparison = strA.localeCompare(strB);
        }
        
        // Aplicar ordem
        return order === 'asc' ? comparison : -comparison;
    });
    
    // Reordenar linhas no DOM
    dataRows.forEach(row => tbody.appendChild(row));
}

// Funo auxiliar para extrair valores das clulas da tabela de vendas
function getCellValueVendas(row, sortBy) {
    // Mapeamento de sortBy para ndice da coluna
    const columnMap = {
        'data_venda': 0,
        'cliente_nome': 1,
        'valor': 2,
        'status': 3,
        'forma_pagamento': 4,
        'data_registro': 5
    };
    
    const cellIndex = columnMap[sortBy];
    if (cellIndex === undefined) return '';
    
    const cell = row.cells[cellIndex];
    if (!cell) return '';
    
    // Extrair valor baseado no tipo de coluna
    switch(sortBy) {
        case 'valor':
            // Extrair nmero do formato "R$ 1.234,56"
            const valorText = cell.querySelector('strong')?.textContent || cell.textContent;
            const cleanValor = valorText.replace('R$', '').replace(/\./g, '').replace(',', '.').trim();
            const number = parseFloat(cleanValor);
            return isNaN(number) ? 0 : number;
            
        case 'data_venda':
        case 'data_registro':
            // Extrair data do formato "dd/mm/aaaa" ou "dd/mm/aaaa HH:MM"
            const dateText = cell.textContent.trim();
            
            // Para data_venda (s data)
            if (sortBy === 'data_venda') {
                const dateMatch = dateText.match(/(\d{2})\/(\d{2})\/(\d{4})/);
                if (dateMatch) {
                    const [, dia, mes, ano] = dateMatch;
                    return new Date(ano, mes - 1, dia);
                }
            }
            
            // Para data_registro (data e hora)
            if (sortBy === 'data_registro') {
                const dateTimeMatch = dateText.match(/(\d{2})\/(\d{2})\/(\d{4})\s+(\d{2}):(\d{2})/);
                if (dateTimeMatch) {
                    const [, dia, mes, ano, hora, minuto] = dateTimeMatch;
                    return new Date(ano, mes - 1, dia, hora, minuto);
                }
            }
            
            return new Date(0); // Data padro se no conseguir parse
            
        case 'status':
            // Extrair texto do status badge
            const statusBadge = cell.querySelector('.status-badge');
            return statusBadge ? statusBadge.textContent.trim() : cell.textContent.trim();
            
        case 'forma_pagamento':
            // Extrair texto do pagamento display
            const pagamentoText = cell.querySelector('.pagamento-display');
            return pagamentoText ? pagamentoText.textContent.replace('N/A', '').trim() : cell.textContent.trim();
            
        case 'cliente_nome':
            // Extrair nome do cliente (primeiro strong)
            const clienteNome = cell.querySelector('strong');
            return clienteNome ? clienteNome.textContent.trim() : cell.textContent.trim();
            
        default:
            return cell.textContent.trim();
    }
}


// Funes utilitrias (reutilizar se j existirem)
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
</script>

<?php 
require_once 'includes/footer.php';
?>




