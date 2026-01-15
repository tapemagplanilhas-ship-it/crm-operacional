<?php
// Definir header JSON APENAS neste arquivo
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

$response = ['success' => false, 'data' => []];

$conn = getConnection();
if (!$conn) {
    $response['message'] = 'Erro de conexão com o banco';
    echo json_encode($response);
    exit;
}

try {
    // Total de clientes
    $result = $conn->query("SELECT COUNT(*) as total FROM clientes");
    $response['data']['total_clientes'] = $result->fetch_assoc()['total'];
    
    // Vendas do mês
    $result = $conn->query("SELECT COUNT(*) as total, COALESCE(SUM(valor), 0) as valor 
                          FROM vendas 
                          WHERE status = 'concluida' 
                          AND MONTH(data_venda) = MONTH(CURRENT_DATE()) 
                          AND YEAR(data_venda) = YEAR(CURRENT_DATE())");
    $row = $result->fetch_assoc();
    $response['data']['vendas_mes'] = $row['total'];
    $response['data']['valor_mes'] = $row['valor'];
    
    // Clientes inativos
    $result = $conn->query("SELECT COUNT(*) as total 
                          FROM clientes 
                          WHERE ultima_venda IS NULL 
                          OR ultima_venda < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)");
    $response['data']['clientes_inativos'] = $result->fetch_assoc()['total'];
    
    // TAXA DE FECHAMENTO GERAL
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
    
    $response['data']['taxa_fechamento_geral'] = round($taxa_fechamento_geral, 1);
    $response['data']['total_vendas'] = $total_vendas;
    $response['data']['vendas_concluidas'] = $vendas_concluidas;
    
    // Últimos clientes
    $result = $conn->query("SELECT * FROM clientes ORDER BY data_cadastro DESC LIMIT 5");
    $response['data']['ultimos_clientes'] = [];
    while ($row = $result->fetch_assoc()) {
        $response['data']['ultimos_clientes'][] = $row;
    }
    
    $response['success'] = true;
    
} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
}

$conn->close();
echo json_encode($response);
?>