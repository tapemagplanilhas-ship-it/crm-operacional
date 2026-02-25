<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../includes/config.php';

$response = ['success' => false, 'message' => '', 'data' => null];

try {
    session_start();
    
    if (!isset($_SESSION['usuario_id'])) {
        throw new Exception('Usuário não autenticado', 401);
    }

    $usuarioId = $_SESSION['usuario_id'];
    $perfil = $_SESSION['perfil'] ?? 'vendedor';
    $isAdmin = ($perfil === 'admin');

    $conn = getConnection();
    if (!$conn) {
        throw new Exception('Erro de conexão com o banco de dados', 500);
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $inputData = json_decode(file_get_contents('php://input'), true) ?? $_REQUEST;

    // Função de sanitização
    $clean = function($data) use ($conn) {
        if (is_array($data)) {
            return array_map(function($item) use ($conn) {
                return $conn->real_escape_string($item ?? '');
            }, $data);
        }
        return $conn->real_escape_string($data ?? '');
    };

    $data = array_map($clean, $inputData);

    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                $id = $clean($_GET['id']);
                $query = "SELECT *, 
                         COALESCE(
                             NULLIF(status_cliente, ''), 
                             'ativo'
                         ) AS status_cliente 
                         FROM clientes 
                         WHERE id = ?";
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                
                $result = $stmt->get_result();
                if ($result->num_rows === 0) {
                    throw new Exception('Cliente não encontrado', 404);
                }
                
                $response['data'] = $result->fetch_assoc();
            } else {
                $query = "SELECT *, 
                         COALESCE(
                             NULLIF(status_cliente, ''), 
                             'ativo'
                         ) AS status_cliente 
                         FROM clientes 
                         ORDER BY nome";
                
                $stmt = $conn->prepare($query);
                $stmt->execute();
                $response['data'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            }
            $response['success'] = true;
            break;

        case 'POST':
        case 'PUT':
            if (empty($data['nome'])) {
                throw new Exception('Nome é obrigatório', 400);
            }

            $fields = ['nome', 'empresa', 'documento', 'telefone', 'email', 'observacoes', 'status_cliente'];
            $values = [];
            foreach ($fields as $field) {
                $values[$field] = $data[$field] ?? ($field === 'status_cliente' ? 'ativo' : '');
            }

            if (isset($data['id']) && $data['id']) {
                $id = $data['id'];
                
                $stmt = $conn->prepare("SELECT id FROM clientes WHERE id = ? AND (usuario_id = ? OR ? = 'admin')");
                $stmt->bind_param("iis", $id, $usuarioId, $perfil);
                $stmt->execute();
                
                if ($stmt->get_result()->num_rows === 0) {
                    throw new Exception('Sem permissão para editar este cliente', 403);
                }

                $setParts = [];
                foreach ($fields as $field) {
                    $setParts[] = "$field = ?";
                }
                
                $query = "UPDATE clientes SET " . implode(', ', $setParts) . " WHERE id = ?";
                $params = array_merge(array_values($values), [$id]);
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
                
                if (!$stmt->execute()) {
                    throw new Exception('Erro ao atualizar cliente: ' . $stmt->error, 500);
                }
                
                $response['message'] = 'Cliente atualizado com sucesso';
            } else {
                $query = "INSERT INTO clientes (" . 
                         implode(', ', $fields) . ", usuario_id) VALUES (" . 
                         rtrim(str_repeat('?, ', count($fields)), ', ') . ", ?)";
                
                $params = array_merge(array_values($values), [$usuarioId]);
                
                $stmt = $conn->prepare($query);
                $stmt->bind_param(str_repeat('s', count($params)), ...$params);
                
                if (!$stmt->execute()) {
                    throw new Exception('Erro ao criar cliente: ' . $stmt->error, 500);
                }
                
                $id = $conn->insert_id;
                $response['message'] = 'Cliente criado com sucesso';
            }
            
            $stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $response['data'] = $stmt->get_result()->fetch_assoc();
            $response['success'] = true;
            break;

        case 'DELETE':
            if (!isset($_GET['id'])) {
                throw new Exception('ID do cliente não fornecido', 400);
            }
            
            $id = $clean($_GET['id']);
            
            $stmt = $conn->prepare("SELECT id FROM clientes WHERE id = ? AND (usuario_id = ? OR ? = 'admin')");
            $stmt->bind_param("iis", $id, $usuarioId, $perfil);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows === 0) {
                throw new Exception('Sem permissão para excluir este cliente', 403);
            }

            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM vendas WHERE cliente_id = ?");
            $stmt->bind_param('i', $id);
            $stmt->execute();
            
            $result = $stmt->get_result()->fetch_assoc();
            if ($result['total'] > 0) {
                throw new Exception('Não é possível excluir cliente com vendas associadas', 400);
            }

            $stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
            $stmt->bind_param('i', $id);
            
            if (!$stmt->execute()) {
                throw new Exception('Erro ao excluir cliente: ' . $stmt->error, 500);
            }
            
            $response['success'] = true;
            $response['message'] = 'Cliente excluído com sucesso';
            break;

        default:
            throw new Exception('Método não permitido', 405);
    }

} catch (Exception $e) {
    http_response_code($e->getCode() ?: 500);
    $response['message'] = $e->getMessage();
    error_log("Erro em clientes.php: " . $e->getMessage());
} finally {
    if (isset($conn)) $conn->close();
    echo json_encode($response);
}