<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

$response = ['success' => false, 'message' => ''];

$conn = getConnection();
if (!$conn) {
    $response['message'] = 'Erro de conexão com o banco';
    echo json_encode($response);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    $data = $_POST;
}

$id = cleanData($data['id'] ?? '');
$valor = cleanData($data['valor'] ?? '');
$data_venda = cleanData($data['data_venda'] ?? '');
$status = cleanData($data['status'] ?? 'concluida');
$forma_pagamento = cleanData($data['forma_pagamento'] ?? 'na');
$motivo_perda = cleanData($data['motivo_perda'] ?? '');
$observacoes = cleanData($data['observacoes'] ?? '');
$cliente_id = cleanData($data['cliente_id'] ?? '');

// Validações
if (empty($id)) {
    $response['message'] = 'ID da venda não especificado';
    echo json_encode($response);
    exit;
}

if (empty($valor) || $valor === 'R$ 0,00') {
    $response['message'] = 'Valor é obrigatório';
    echo json_encode($response);
    exit;
}

if (empty($data_venda)) {
    $response['message'] = 'Data é obrigatória';
    echo json_encode($response);
    exit;
}

// Converter valor
$valor = str_replace(['R$', '.', ','], ['', '', '.'], $valor);
$valor = floatval($valor);

// Converter data
$data_mysql = '';
if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $data_venda, $matches)) {
    $data_mysql = "{$matches[3]}-{$matches[2]}-{$matches[1]}";
} else {
    $response['message'] = 'Formato de data inválido';
    echo json_encode($response);
    exit;
}

// Preparar observações (inclui motivo da perda se houver)
$observacoes_completas = $observacoes;
if ($status === 'perdida' && !empty($motivo_perda)) {
    $observacoes_completas = "MOTIVO DA PERDA: " . $motivo_perda . "\n\n" . $observacoes;
}

// Atualizar venda
$sql = "UPDATE vendas SET 
        valor = ?, 
        data_venda = ?, 
        status = ?, 
        forma_pagamento = ?, 
        observacoes = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("dssssi", $valor, $data_mysql, $status, $forma_pagamento, $observacoes_completas, $id);

if ($stmt->execute()) {
    // Atualizar métricas do cliente
    if (!empty($cliente_id)) {
        atualizarMetricasCliente($cliente_id);
    }
    
    $response['success'] = true;
    $response['message'] = 'Venda atualizada com sucesso!';
} else {
    $response['message'] = 'Erro ao atualizar venda: ' . $conn->error;
}

$conn->close();
echo json_encode($response);
?>