<?php
require_once '../includes/config.php';
header('Content-Type: application/json');

$conn = getConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão']);
    exit;
}

$data = [];

// TOTAL DE CLIENTES
$res = $conn->query("SELECT COUNT(*) AS total FROM clientes");
$row = $res->fetch_assoc();
$data['total_clientes'] = intval($row['total']);

// TOTAL DE VENDAS
$res = $conn->query("SELECT COUNT(*) AS total FROM vendas");
$row = $res->fetch_assoc();
$data['total_vendas'] = intval($row['total']);

// VENDAS POR STATUS
$res = $conn->query("
    SELECT status, COUNT(*) AS qtd
    FROM vendas
    GROUP BY status
");
$data['vendas_status'] = [];
while ($row = $res->fetch_assoc()) {
    $data['vendas_status'][$row['status']] = intval($row['qtd']);
}

// SOMA DO VALOR TOTAL DAS VENDAS CONCLUÍDAS
$res = $conn->query("
    SELECT SUM(valor) AS total 
    FROM vendas 
    WHERE status = 'concluida'
");
$row = $res->fetch_assoc();
$data['valor_total'] = floatval($row['total'] ?? 0);

// TICKET MÉDIO
if ($data['vendas_status']['concluida'] ?? 0 > 0) {
    $data['ticket_medio'] = $data['valor_total'] / $data['vendas_status']['concluida'];
} else {
    $data['ticket_medio'] = 0;
}

echo json_encode([
    'success' => true,
    'data' => $data
]);