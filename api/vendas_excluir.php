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

if (isset($_GET['id'])) {
    $venda_id = cleanData($_GET['id']);
    
    // Primeiro buscar o cliente_id para atualizar métricas depois
    $sql_cliente = "SELECT cliente_id, usuario_id FROM vendas WHERE id = ?";
    $stmt = $conn->prepare($sql_cliente);
    $stmt->bind_param("i", $venda_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $cliente_id = $row['cliente_id'];
        $dono_id = (int)$row['usuario_id'];

        if ($perfil_usuario === 'vendedor' && $dono_id !== (int)$usuario_id) {
            $response['message'] = 'VocÃª nÃ£o tem permissÃ£o para excluir esta venda';
            echo json_encode($response);
            exit;
        }
        
        // Excluir a venda
        $sql_delete = "DELETE FROM vendas WHERE id = ?";
        $stmt_delete = $conn->prepare($sql_delete);
        $stmt_delete->bind_param("i", $venda_id);
        
        if ($stmt_delete->execute()) {
            // Atualizar métricas do cliente
            atualizarMetricasCliente($cliente_id);
            
            $response['success'] = true;
            $response['message'] = 'Venda excluída com sucesso!';
        } else {
            $response['message'] = 'Erro ao excluir venda';
        }
    } else {
        $response['message'] = 'Venda não encontrada';
    }
} else {
    $response['message'] = 'ID da venda não especificado';
}

$conn->close();
echo json_encode($response);
?>
