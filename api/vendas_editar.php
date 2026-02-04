<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
$response = ['success' => false, 'message' => ''];
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!$usuario_id) {
    $response['message'] = 'UsuÃ¡rio nÃ£o autenticado';
    echo json_encode($response);
    exit;
}

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
$motivo_perda_id = cleanData($data['motivo_perda_id'] ?? '');
$motivo_perda_outro = cleanData($data['motivo_perda_outro'] ?? '');
$observacoes = cleanData($data['observacoes'] ?? '');
$codigo_orcamento_raw = cleanData($data['codigo_orcamento'] ?? '');
$cliente_id = cleanData($data['cliente_id'] ?? '');

// Validações
if (empty($id)) {
    $response['message'] = 'ID da venda não especificado';
    echo json_encode($response);
    exit;
}

if (empty($valor)) {
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
$valor = parseMoneyBR($valor);
if ($valor <= 0) {
    $response['message'] = 'Valor é obrigatório';
    echo json_encode($response);
    exit;
}

// Converter data
$data_mysql = parseDateBR($data_venda);
if (!$data_mysql) {
    $response['message'] = 'Formato de data inválido';
    echo json_encode($response);
    exit;
}

if ($status === 'perdida' && empty($motivo_perda_id)) {
    $response['message'] = 'Motivo da perda é obrigatório para vendas perdidas';
    echo json_encode($response);
    exit;
}
if ($status !== 'perdida') {
    $motivo_perda_id = null;
    $motivo_perda_outro = '';
}

// Validar permissão do vendedor
$stmt_owner = $conn->prepare("SELECT usuario_id FROM vendas WHERE id = ?");
$stmt_owner->bind_param("i", $id);
$stmt_owner->execute();
$owner_row = $stmt_owner->get_result()->fetch_assoc();
if (!$owner_row) {
    $response['message'] = 'Venda não encontrada';
    echo json_encode($response);
    exit;
}
if ($perfil_usuario === 'vendedor' && (int)$owner_row['usuario_id'] !== (int)$usuario_id) {
    $response['message'] = 'Você não tem permissão para editar esta venda';
    echo json_encode($response);
    exit;
}

$codigo_orcamento = preg_replace('/\\D+/', '', (string)$codigo_orcamento_raw);
if ($codigo_orcamento === '') $codigo_orcamento = null;

// Preparar observações (inclui motivo da perda se houver)
$observacoes_completas = $observacoes;
$motivo_perda_texto = '';
if ($status === 'perdida' && !empty($motivo_perda_id)) {
    $motivo_id_int = (int)$motivo_perda_id;
    $stmtMotivo = $conn->prepare("SELECT nome, permite_outro FROM motivos_perda WHERE id = ?");
    $stmtMotivo->bind_param("i", $motivo_id_int);
    $stmtMotivo->execute();
    $motivoRow = $stmtMotivo->get_result()->fetch_assoc();
    if ($motivoRow) {
        if ((int)$motivoRow['permite_outro'] === 1) {
            $motivo_perda_texto = $motivo_perda_outro;
            if ($motivo_perda_outro === '') {
                $response['message'] = 'Descreva o motivo da perda';
                echo json_encode($response);
                exit;
            }
        } else {
            $motivo_perda_texto = $motivoRow['nome'];
        }
    }
    if ($motivo_perda_texto !== '') {
        $observacoes_completas = "MOTIVO DA PERDA: " . $motivo_perda_texto . "\n\n" . $observacoes;
    }
}

// Atualizar venda
$sql = "UPDATE vendas SET 
        valor = ?, 
        data_venda = ?, 
        status = ?, 
        forma_pagamento = ?, 
        observacoes = ?,
        motivo_perda_id = ?,
        motivo_perda_outro = ?,
        codigo_orcamento = ?
        WHERE id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("dssssissi", $valor, $data_mysql, $status, $forma_pagamento, $observacoes_completas, $motivo_perda_id, $motivo_perda_outro, $codigo_orcamento, $id);

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
