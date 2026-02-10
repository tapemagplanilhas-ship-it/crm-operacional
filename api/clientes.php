<?php
// Definir header JSON APENAS neste arquivo
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

$response = ['success' => false, 'message' => ''];

$usuarioId = null;
if (function_exists('iniciarSessao')) {
    iniciarSessao();
    $usuarioId = $_SESSION['usuario_id'] ?? null;
}

$conn = getConnection();
if (!$conn) {
    $response['message'] = 'Erro de conexão com o banco';
    echo json_encode($response);
    exit;
}

if (empty($usuarioId)) {
    $response['message'] = 'Usuario nao autenticado';
    echo json_encode($response);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case 'GET':
        $response['data'] = [];
        
        if (isset($_GET['id'])) {
            $id = cleanData($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ? AND usuario_id = ?");
            $stmt->bind_param("ii", $id, $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $response['data'] = $row;
                $response['success'] = true;
            } else {
                $response['message'] = 'Cliente não encontrado';
            }
        } else {
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE usuario_id = ? ORDER BY nome");
            $stmt->bind_param("i", $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
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
        
        $id = $data['id'] ?? null;
        $nome = cleanData($data['nome'] ?? '');
        $empresa = cleanData($data['empresa'] ?? '');
        $documento = cleanData($data['documento'] ?? '');
        $telefone = cleanData($data['telefone'] ?? '');
        $email = cleanData($data['email'] ?? '');
        $observacoes = cleanData($data['observacoes'] ?? '');
        
        if (empty($nome)) {
            $response['message'] = 'Nome é obrigatório';
            break;
        }
        
        if ($id) {
            // Atualizar
            $stmt = $conn->prepare("UPDATE clientes SET nome=?, empresa=?, documento=?, telefone=?, email=?, observacoes=? WHERE id=? AND usuario_id=?");
            $stmt->bind_param("ssssssii", $nome, $empresa, $documento, $telefone, $email, $observacoes, $id, $usuarioId);
        } else {
            // Inserir
            $stmt = $conn->prepare("INSERT INTO clientes (nome, empresa, documento, telefone, email, observacoes, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssi", $nome, $empresa, $documento, $telefone, $email, $observacoes, $usuarioId);
        }
        
        if ($stmt->execute()) {
            if ($id && $stmt->affected_rows === 0) {
                $response['message'] = 'VocÃª nÃ£o tem permissÃ£o para editar este cliente';
                break;
            }
            $clienteId = $id ?: $conn->insert_id;
            
            // Buscar cliente salvo
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ? AND usuario_id = ?");
            $stmt->bind_param("ii", $clienteId, $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            $response['data'] = $result->fetch_assoc();
            
            $response['success'] = true;
            $response['message'] = $id ? 'Cliente atualizado!' : 'Cliente cadastrado!';
        } else {
            $response['message'] = 'Erro ao salvar: ' . $conn->error;
        }
        break;
        
    case 'DELETE':
        if (isset($_GET['id'])) {
            $id = cleanData($_GET['id']);

            // Verificar dono
            $stmt = $conn->prepare("SELECT id FROM clientes WHERE id = ? AND usuario_id = ?");
            $stmt->bind_param("ii", $id, $usuarioId);
            $stmt->execute();
            $result = $stmt->get_result();
            if (!$result->fetch_assoc()) {
                $response['message'] = 'VocÃª nÃ£o tem permissÃ£o para excluir este cliente';
                break;
            }

            // Verificar vendas
            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM vendas WHERE cliente_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['total'] > 0) {
                $response['message'] = 'Não é possível excluir cliente com vendas';
                break;
            }
            
            $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response['success'] = true;
                $response['message'] = 'Cliente excluído!';
            } else {
                $response['message'] = 'Erro ao excluir';
            }
        }
        break;
}

$conn->close();
echo json_encode($response);
?>
