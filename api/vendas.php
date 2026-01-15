<?php
// Definir header JSON APENAS neste arquivo
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}
$usuario_id = (int)$usuario_id;

$response = ['success' => false, 'message' => ''];

$conn = getConnection();
if (!$conn) {
    $response['message'] = 'Erro de conexão com o banco';
    echo json_encode($response);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        if (isset($_GET['cliente_id'])) {
            $cliente_id = cleanData($_GET['cliente_id']);
            
            $stmt = $conn->prepare("SELECT * FROM vendas WHERE cliente_id = ? ORDER BY data_venda DESC");
            $stmt->bind_param("i", $cliente_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $response['data'] = [];
            while ($row = $result->fetch_assoc()) {
                // Formatar dados para exibição
                $row['status_formatado'] = formatarStatus($row['status']);
                $row['forma_pagamento_formatada'] = formatarFormaPagamento($row['forma_pagamento']);
                $row['valor_formatado'] = formatCurrency($row['valor']);
                $row['data_venda_formatada'] = formatDate($row['data_venda']);
                $response['data'][] = $row;
            }
            $response['success'] = true;
        }
        break;
        
    case 'POST':
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!$data) {
            $data = $_POST;
        }
        
        $cliente_id = cleanData($data['cliente_id'] ?? '');
        $valor = cleanData($data['valor'] ?? '');
        $data_venda = cleanData($data['data_venda'] ?? date('d/m/Y'));
        $status = cleanData($data['status'] ?? 'concluida');
        $forma_pagamento = cleanData($data['forma_pagamento'] ?? 'na');
        $motivo_perda = cleanData($data['motivo_perda'] ?? '');
        $observacoes = cleanData($data['observacoes'] ?? '');
        
        // Validações
        if (empty($cliente_id)) {
            $response['message'] = 'Cliente é obrigatório';
            break;
        }
        
        if (empty($valor) || $valor === 'R$ 0,00') {
            $response['message'] = 'Valor é obrigatório';
            break;
        }
        
        // Validar motivo da perda (se status for PERDIDA)
        if ($status === 'perdida' && empty($motivo_perda)) {
            $response['message'] = 'Motivo da perda é obrigatório para vendas perdidas';
            break;
        }
        
        // Validar forma de pagamento
        $formas_validas = ['pix', 'cartao', 'dinheiro', 'boleto', 'na'];
        if (!in_array($forma_pagamento, $formas_validas)) {
            $response['message'] = 'Forma de pagamento inválida';
            break;
        }
        
        // Converter valor
        $valor = str_replace(['R$', '.', ','], ['', '', '.'], $valor);
        $valor = floatval($valor);
        
        if ($valor <= 0) {
            $response['message'] = 'Valor inválido';
            break;
        }
        
        // Converter data (aceita datas passadas e futuras)
        $data_mysql = '';
        if (preg_match('/(\d{2})\/(\d{2})\/(\d{4})/', $data_venda, $matches)) {
            $dia = $matches[1];
            $mes = $matches[2];
            $ano = $matches[3];
            
            // Validar data
            if (!checkdate($mes, $dia, $ano)) {
                $response['message'] = 'Data inválida. Use dd/mm/aaaa';
                break;
            }
            
            $data_mysql = "{$ano}-{$mes}-{$dia}";
        } else {
            $response['message'] = 'Formato de data inválido. Use dd/mm/aaaa';
            break;
        }
        
        // Preparar observações (inclui motivo da perda se houver)
        $observacoes_completas = $observacoes;
        if ($status === 'perdida' && !empty($motivo_perda)) {
            $observacoes_completas = "MOTIVO DA PERDA: " . $motivo_perda . "\n\n" . $observacoes;
        }
        
        // Inserir venda
$stmt = $conn->prepare("INSERT INTO vendas (cliente_id, usuario_id, valor, data_venda, status, forma_pagamento, observacoes)
                        VALUES (?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("iidssss", $cliente_id, $usuario_id, $valor, $data_mysql, $status, $forma_pagamento, $observacoes_completas);

        if ($stmt->execute()) {
            // Atualizar métricas do cliente
            atualizarMetricasCliente($cliente_id);
            
            $response['success'] = true;
            $response['message'] = 'Negociação registrada com sucesso!';
            $response['data'] = [
                'venda_id' => $conn->insert_id,
                'status' => $status,
                'forma_pagamento' => $forma_pagamento
            ];
        } else {
            $response['message'] = 'Erro ao registrar negociação: ' . $conn->error;
        }
        break;
}

$conn->close();
echo json_encode($response);

// Funções auxiliares
function formatarStatus($status) {
    $statuses = [
        'concluida' => 'CONCLUÍDA',
        'perdida' => 'PERDIDA',
        'orcamento' => 'ORÇAMENTO'
    ];
    return $statuses[$status] ?? $status;
}

function formatarFormaPagamento($forma) {
    $formas = [
        'pix' => 'PIX',
        'cartao' => 'Cartão',
        'dinheiro' => 'Dinheiro',
        'boleto' => 'Boleto',
        'na' => 'N/A'
    ];
    return $formas[$forma] ?? $forma;
}
?>