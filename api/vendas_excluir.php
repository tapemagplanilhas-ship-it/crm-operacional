<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
require_once '../includes/config.php';

// Inicia sessão se não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => ''];

// Verifica autenticação
$usuario_id = $_SESSION['usuario_id'] ?? null;
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!$usuario_id) {
    $response['message'] = 'Usuário não autenticado';
    echo json_encode($response);
    exit;
}

// Verifica método HTTP - DEVE SER POST ou DELETE
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'DELETE') {
    $response['message'] = 'Método não permitido';
    echo json_encode($response);
    exit;
}

// Verifica CSRF token
if (!isset($_POST['_token']) || $_POST['_token'] !== ($_SESSION['token'] ?? '')) {
    $response['message'] = 'Token de segurança inválido';
    echo json_encode($response);
    exit;
}

$conn = getConnection();
if (!$conn) {
    $response['message'] = 'Erro de conexão com o banco';
    echo json_encode($response);
    exit;
}

try {
    // Obtém ID da venda
    $venda_id = (int)($_POST['id'] ?? 0);
    if ($venda_id <= 0) {
        throw new Exception('ID da venda inválido');
    }

    // Busca informações da venda
    $sql_cliente = "SELECT cliente_id, usuario_id FROM vendas WHERE id = ?";
    $stmt = $conn->prepare($sql_cliente);
    $stmt->bind_param("i", $venda_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        throw new Exception('Venda não encontrada');
    }

    $row = $result->fetch_assoc();
    $cliente_id = (int)$row['cliente_id'];
    $dono_id = (int)$row['usuario_id'];

    // Verifica permissões
    if ($perfil_usuario === 'vendedor' && $dono_id !== $usuario_id) {
        throw new Exception('Você não tem permissão para excluir esta venda');
    }
    
    // Exclui a venda
    $sql_delete = "DELETE FROM vendas WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $venda_id);
    
    if (!$stmt_delete->execute()) {
        throw new Exception('Erro ao excluir venda: ' . $conn->error);
    }

    // Atualiza métricas do cliente
    atualizarMetricasCliente($cliente_id);
    
    $response['success'] = true;
    $response['message'] = 'Venda excluída com sucesso!';

} catch (Exception $e) {
    $response['message'] = $e->getMessage();
} finally {
    $conn->close();
    echo json_encode($response);
}
?>