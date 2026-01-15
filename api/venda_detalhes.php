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

if (isset($_GET['id'])) {
    $venda_id = cleanData($_GET['id']);
    
    // Buscar venda com informações do cliente
    $sql = "SELECT v.*, c.nome as cliente_nome, c.telefone as cliente_telefone 
            FROM vendas v
            LEFT JOIN clientes c ON v.cliente_id = c.id
            WHERE v.id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $venda_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($venda = $result->fetch_assoc()) {
        // Extrair motivo da perda das observações (se houver)
        if ($venda['status'] === 'perdida') {
            $observacoes = $venda['observacoes'] ?? '';
            if (strpos($observacoes, 'MOTIVO DA PERDA:') === 0) {
                $linhas = explode("\n", $observacoes);
                $motivo = str_replace('MOTIVO DA PERDA: ', '', $linhas[0]);
                $venda['motivo_perda'] = trim($motivo);
                
                // Remover motivo das observações normais
                unset($linhas[0]);
                $venda['observacoes'] = trim(implode("\n", $linhas));
            }
        }
        
        $response['success'] = true;
        $response['data'] = $venda;
    } else {
        $response['message'] = 'Venda não encontrada';
    }
} else {
    $response['message'] = 'ID da venda não especificado';
}

$conn->close();
echo json_encode($response);
?>