<?php
// Definir header JSON APENAS neste arquivo
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

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
        $response['data'] = [];
        
        if (isset($_GET['id'])) {
            $id = cleanData($_GET['id']);
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $response['data'] = $row;
                $response['success'] = true;
            } else {
                $response['message'] = 'Cliente não encontrado';
            }
        } else {
            $result = $conn->query("SELECT * FROM clientes ORDER BY nome");
            
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
        $telefone = cleanData($data['telefone'] ?? '');
        $email = cleanData($data['email'] ?? '');
        $observacoes = cleanData($data['observacoes'] ?? '');
        
        if (empty($nome)) {
            $response['message'] = 'Nome é obrigatório';
            break;
        }
        
        if ($id) {
            // Atualizar
            $stmt = $conn->prepare("UPDATE clientes SET nome=?, telefone=?, email=?, observacoes=? WHERE id=?");
            $stmt->bind_param("ssssi", $nome, $telefone, $email, $observacoes, $id);
        } else {
            // Inserir
            $stmt = $conn->prepare("INSERT INTO clientes (nome, telefone, email, observacoes) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nome, $telefone, $email, $observacoes);
        }
        
        if ($stmt->execute()) {
            $clienteId = $id ?: $conn->insert_id;
            
            // Buscar cliente salvo
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
            $stmt->bind_param("i", $clienteId);
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