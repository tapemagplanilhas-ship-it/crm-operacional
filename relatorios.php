<?php
session_start();
require_once 'includes/config.php';

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Conexão com o banco
$conn = getConnection();
if (!$conn) {
    die("Erro de conexão com o banco de dados");
}

// Processar filtros
$dataInicio = $_GET['data_inicio'] ?? date('Y-m-01');
$dataFim = $_GET['data_fim'] ?? date('Y-m-t');
$tipoRelatorio = $_GET['tipo_relatorio'] ?? 'vendas';
$filtroCliente = $_GET['cliente_id'] ?? '';
$filtroVendedor = $_GET['vendedor_id'] ?? '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$porPagina = 15;

// Função para exportar CSV
function exportarParaCSV($dados, $colunas) {
    // Configurar headers para download
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="relatorio_vendas_' . date('Y-m-d_His') . '.csv"');
    
    // Criar arquivo de saída
    $output = fopen('php://output', 'w');
    
    // Adicionar BOM para UTF-8 (importante para Excel)
    fwrite($output, "\xEF\xBB\xBF");
    
    // Escrever cabeçalhos
    fputcsv($output, $colunas, ';');
    
    // Escrever dados formatados
    foreach ($dados as $linha) {
        $row = [
            date('d/m/Y', strtotime($linha['data_venda'])), // Data formatada
            $linha['cliente'],
            $linha['vendedor'],
            number_format($linha['valor'], 2, ',', '.'),    // Valor formatado
            $linha['status']
        ];
        fputcsv($output, $row, ';');
    }
    
    fclose($output);
    exit;
}

// Processar exportação ANTES de qualquer output HTML
if (isset($_GET['exportar']) && $_GET['exportar'] == 'csv') {
    // Gerar relatório completo (sem paginação)
    $relatorioExport = relatorioVendas($conn, $dataInicio, $dataFim, $filtroCliente, $filtroVendedor, PHP_INT_MAX, 0, 1);
    exportarParaCSV($relatorioExport['dados'], $relatorioExport['colunas']);
}

// Função principal para gerar relatório de vendas
function relatorioVendas($conn, $dataInicio, $dataFim, $clienteId, $vendedorId, $porPagina, $offset, $paginaAtual) {
    $query = "SELECT SQL_CALC_FOUND_ROWS 
                v.id, v.data_venda, c.nome as cliente, 
                u.nome as vendedor, v.valor, v.status
              FROM vendas v
              JOIN clientes c ON v.cliente_id = c.id
              JOIN usuarios u ON v.usuario_id = u.id
              WHERE v.data_venda BETWEEN ? AND ?";
    
    $params = [$dataInicio, $dataFim];
    $types = 'ss';
    
    if (!empty($clienteId)) {
        $query .= " AND v.cliente_id = ?";
        $params[] = $clienteId;
        $types .= 'i';
    }
    
    if (!empty($vendedorId)) {
        $query .= " AND v.usuario_id = ?";
        $params[] = $vendedorId;
        $types .= 'i';
    }
    
    $query .= " ORDER BY v.data_venda DESC LIMIT ? OFFSET ?";
    $params[] = $porPagina;
    $params[] = $offset;
    $types .= 'ii';
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $dados = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    $total = $conn->query("SELECT FOUND_ROWS()")->fetch_row()[0];
    
    return [
        'titulo' => 'Relatório de Vendas',
        'colunas' => ['Data', 'Cliente', 'Vendedor', 'Valor (R$)', 'Status'],
        'dados' => $dados,
        'total_paginas' => ceil($total / $porPagina),
        'pagina' => $paginaAtual,
        'total_registros' => $total
    ];
}

// Gerar relatório para exibição na página
$relatorio = relatorioVendas($conn, $dataInicio, $dataFim, $filtroCliente, $filtroVendedor, $porPagina, ($pagina-1)*$porPagina, $pagina);

// Obter clientes e vendedores para os filtros
$clientes = [];
$vendedores = [];

$stmt = $conn->prepare("SELECT id, nome FROM clientes ORDER BY nome");
$stmt->execute();
$clientes = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$stmt = $conn->prepare("SELECT id, nome FROM usuarios WHERE perfil = 'vendedor' ORDER BY nome");
$stmt->execute();
$vendedores = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

require_once 'includes/header.php';
?>

<!-- Interface do usuário -->
<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><i class="fas fa-chart-bar"></i> <?= $relatorio['titulo'] ?></h2>
            
            <!-- Filtros -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="get" action="relatorios.php">
                        <input type="hidden" name="pagina" value="1">
                        
                        <div class="row">
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Data Início</label>
                                    <input type="date" name="data_inicio" class="form-control" value="<?= $dataInicio ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Data Fim</label>
                                    <input type="date" name="data_fim" class="form-control" value="<?= $dataFim ?>">
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Cliente</label>
                                    <select name="cliente_id" class="form-control">
                                        <option value="">Todos</option>
                                        <?php foreach ($clientes as $c): ?>
                                            <option value="<?= $c['id'] ?>" <?= $filtroCliente == $c['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($c['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>Vendedor</label>
                                    <select name="vendedor_id" class="form-control">
                                        <option value="">Todos</option>
                                        <?php foreach ($vendedores as $v): ?>
                                            <option value="<?= $v['id'] ?>" <?= $filtroVendedor == $v['id'] ? 'selected' : '' ?>>
                                                <?= htmlspecialchars($v['nome']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mt-4">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-filter"></i> Aplicar Filtros
                                </button>
                                
                                <button type="submit" name="exportar" value="csv" class="btn btn-success ml-2">
                                    <i class="fas fa-file-csv"></i> Exportar CSV
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Tabela de resultados -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered table-hover">
                            <thead class="thead-dark">
                                <tr>
                                    <th class="text-center">Data</th>
                                    <th class="text-center">Cliente</th>
                                    <th class="text-center">Vendedor</th>
                                    <th class="text-center">Valor (R$)</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($relatorio['dados'])): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4">Nenhuma venda encontrada</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($relatorio['dados'] as $linha): ?>
                                        <tr>
                                            <td class="text-center"><?= date('d/m/Y', strtotime($linha['data_venda'])) ?></td>
                                            <td><?= htmlspecialchars($linha['cliente']) ?></td>
                                            <td><?= htmlspecialchars($linha['vendedor']) ?></td>
                                            <td class="text-right"><?= number_format($linha['valor'], 2, ',', '.') ?></td>
                                            <td class="text-center">
                                                <span class="badge <?= $linha['status'] === 'concluida' ? 'badge-success' : 'badge-warning' ?>">
                                                    <?= htmlspecialchars($linha['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Paginação -->
                    <?php if ($relatorio['total_paginas'] > 1): ?>
                    <nav aria-label="Page navigation" class="mt-4">
                        <ul class="pagination justify-content-center">
                            <li class="page-item <?= $pagina == 1 ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge($_GET, ['pagina' => $pagina-1])) 
                                ?>">Anterior</a>
                            </li>
                            
                            <?php for ($i = 1; $i <= $relatorio['total_paginas']; $i++): ?>
                                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                                    <a class="page-link" href="?<?= 
                                        http_build_query(array_merge($_GET, ['pagina' => $i])) 
                                    ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?= $pagina == $relatorio['total_paginas'] ? 'disabled' : '' ?>">
                                <a class="page-link" href="?<?= 
                                    http_build_query(array_merge($_GET, ['pagina' => $pagina+1])) 
                                ?>">Próxima</a>
                            </li>
                        </ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>