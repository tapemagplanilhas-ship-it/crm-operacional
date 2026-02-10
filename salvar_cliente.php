<?php
session_start();
require_once 'config.php';

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado']);
    exit;
}

// Verificar permissões
$perfil = $_SESSION['perfil'] ?? '';
if (!in_array($perfil, ['admin', 'gerencia', 'vendedor'])) {
    echo json_encode(['success' => false, 'message' => 'Acesso negado']);
    exit;
}

$conn = getConnection();

// Para vendedores editando cliente existente, verificar se é o dono
if ($perfil === 'vendedor' && !empty($_POST['id'])) {
    $stmt = $conn->prepare("SELECT usuario_id FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $_POST['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['usuario_id'] != $_SESSION['usuario_id']) {
            echo json_encode(['success' => false, 'message' => 'Você só pode editar seus próprios clientes']);
            exit;
        }
    }
}

// Processar os dados do formulário
$id = $_POST['id'] ?? null;
$nome = trim($_POST['nome'] ?? '');
$empresa = trim($_POST['empresa'] ?? '');
$documento = trim($_POST['documento'] ?? '');
$telefone = trim($_POST['telefone'] ?? '');
$email = trim($_POST['email'] ?? '');
$observacoes = trim($_POST['observacoes'] ?? '');

// Validações básicas
if (empty($nome)) {
    echo json_encode(['success' => false, 'message' => 'Nome é obrigatório']);
    exit;
}

try {
    if (empty($id)) {
        // Novo cliente
        $stmt = $conn->prepare("INSERT INTO clientes 
            (nome, empresa, documento, telefone, email, observacoes, usuario_id, data_criacao) 
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
        
        $usuario_id = ($perfil === 'vendedor') ? $_SESSION['usuario_id'] : null;
        $stmt->bind_param("ssssssi", $nome, $empresa, $documento, $telefone, $email, $observacoes, $usuario_id);
    } else {
        // Atualizar cliente existente
        $stmt = $conn->prepare("UPDATE clientes SET 
            nome = ?, empresa = ?, documento = ?, telefone = ?, email = ?, observacoes = ?
            WHERE id = ?");
        $stmt->bind_param("ssssssi", $nome, $empresa, $documento, $telefone, $email, $observacoes, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Cliente salvo com sucesso']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Erro ao salvar no banco de dados']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
}

$conn->close();
?>