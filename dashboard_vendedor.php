<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
verificarLogin();
// Somente admin/gerencia
requerirPermissao('gerencia');

$vendedorId = (int)($_GET['vendedor_id'] ?? 0);
if ($vendedorId <= 0) {
    echo '<div class="dashboard"><p>Vendedor nao encontrado.</p></div>';
    require_once 'includes/footer.php';
    exit;
}

// Buscar vendedor
$conn = getConnection();
$vendedorNome = '';
$vendedorEmail = '';
if ($conn) {
    $stmt = $conn->prepare("SELECT nome, email FROM usuarios WHERE id = ? AND perfil = 'vendedor' LIMIT 1");
    $stmt->bind_param("i", $vendedorId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) {
        $vendedorNome = $row['nome'] ?? '';
        $vendedorEmail = $row['email'] ?? '';
    }
    $stmt->close();
}

if ($vendedorNome === '') {
    if ($conn) $conn->close();
    echo '<div class="dashboard"><p>Vendedor nao encontrado.</p></div>';
    require_once 'includes/footer.php';
    exit;
}

// Dashboard do vendedor (filtrado pelo vendedor)
$usuarioId = $vendedorId;
$isVendedor = true;

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

try {
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

    $dadosDashboard['ultimos_clientes'] = [];

    if ($isVendedor) {
        $stmt = $conn->prepare("
            SELECT c.*
            FROM clientes c
            INNER JOIN (
                SELECT cliente_id, MAX(data_venda) AS ultima
                FROM vendas
                WHERE usuario_id = ?
                GROUP BY cliente_id
            ) v ON v.cliente_id = c.id
            ORDER BY v.ultima DESC
            LIMIT 5
        ");
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $dadosDashboard['ultimos_clientes'][] = $row;
        }
        $stmt->close();
    } else {
        $result = $conn->query("SELECT * FROM clientes ORDER BY data_cadastro DESC LIMIT 5");
        while ($row = $result->fetch_assoc()) {
            $dadosDashboard['ultimos_clientes'][] = $row;
        }
    }

} catch (Exception $e) {
    error_log("Erro dashboard vendedor: " . $e->getMessage());
}

if ($conn) $conn->close();
?>

<script>
    window.DASHBOARD_VENDOR_ID = <?= (int)$vendedorId ?>;
</script>

<div class="dashboard">
    <div class="dashboard-header">
        <h2><i class="fas fa-tachometer-alt"></i> Dashboard do Vendedor - <?= htmlspecialchars($vendedorNome) ?></h2>
        <div class="dashboard-actions">
            <a class="btn-secondary" href="gestao.php">
                <i class="fas fa-arrow-left"></i> Voltar para Gestao
            </a>
        </div>
    </div>

    <div class="config-section" style="margin-bottom: 18px;">
        <div class="section-header" style="margin-bottom: 10px; border: none; padding-bottom: 0;">
            <h2 style="font-size: 1.1rem;"><i class="fas fa-user"></i> <?= htmlspecialchars($vendedorNome) ?></h2>
            <small class="help-text"><?= htmlspecialchars($vendedorEmail) ?></small>
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
                <h3>Vendas do Mes</h3>
                <p class="stat-value" id="vendas-mes"><?= $dadosDashboard['vendas_mes'] ?></p>
                <small class="stat-subtitle"><?= $dadosDashboard['vendas_concluidas'] ?> concluidas no total</small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-chart-line"></i>
            </div>
            <div class="stat-content">
                <h3>Valor do Mes</h3>
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
                <small class="stat-subtitle">Sem compra ha 15+ dias</small>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: #be1616ff;">
                <i class="fas fa-handshake"></i>
            </div>
            <div class="stat-content">
                <h3>Total de Negociacoes</h3>
                <p class="stat-value" id="total-negociacoes"><?= $dadosDashboard['total_vendas'] ?></p>
                <small class="stat-subtitle">
                    <?= $dadosDashboard['vendas_concluidas'] ?> fechadas
                </small>
            </div>
        </div>
    </div>
    
    <div class="dashboard-section">
        <div class="section-header">
            <h3><i class="fas fa-history"></i> Ultimos Clientes Cadastrados</h3>
            <a href="clientes.php" class="btn-link">Ver todos</a>
        </div>
        
        <div class="table-responsive">
            <table class="data-table" id="ultimos-clientes">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>Contato</th>
                        <th>Data Cadastro</th>
                        <th>Ultima Venda</th>
                        <th>Taxa Fechamento</th>
                        <th class="text-center">Acoes</th>
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

<?php 
require_once 'includes/footer.php';
?>
