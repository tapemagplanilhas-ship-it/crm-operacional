<?php
// Certifique-se de que este é o PRIMEIRO comando no arquivo
header('Content-Type: application/json; charset=utf-8');

// Configuração de erros
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Inicia sessão se não estiver ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$response = ['success' => false, 'message' => 'Erro desconhecido'];

try {
    require_once '../includes/config.php';
    
    // Verifica autenticação
    if (empty($_SESSION['usuario_id'])) {
        $response['message'] = 'Não autenticado';
        echo json_encode($response);
        exit;
    }

    $conn = getConnection();
    if (!$conn) {
        $response['message'] = 'Erro de conexão com o banco';
        echo json_encode($response);
        exit;
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $usuarioId = $_SESSION['usuario_id'];
    $perfil = $_SESSION['perfil'] ?? 'vendedor';

    switch ($method) {
        case 'GET':
            // Sua lógica GET aqui
            break;
            
        case 'POST':
            // Sua lógica POST aqui
            break;
            
        case 'DELETE':
            // Sua lógica DELETE aqui
            break;
            
        default:
            $response['message'] = 'Método não permitido';
            http_response_code(405);
    }
    
} catch (Exception $e) {
    $response['message'] = 'Erro no servidor: ' . $e->getMessage();
    http_response_code(500);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
    echo json_encode($response);
    exit;
}