<?php
// processar_editar_usuario.php
session_start();
require_once __DIR__ . '/../../config.php';


// Verificar se é admin
if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado!']);
    exit;
}

// Verificar se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido!']);
    exit;
}

// Receber dados
$id = intval($_POST['id']);
$nome = cleanData($_POST['nome']);
$usuario = cleanData($_POST['usuario']);
$email = cleanData($_POST['email']);
$perfil = cleanData($_POST['perfil']);
$senha_atual = $_POST['senha_atual'];
$nova_senha = $_POST['nova_senha'];

// Validações básicas
if (empty($nome) || empty($usuario) || empty($email) || empty($perfil)) {
    echo json_encode(['success' => false, 'message' => 'Preencha todos os campos obrigatórios!']);
    exit;
}

// Verificar se email é válido
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'E-mail inválido!']);
    exit;
}

// Verificar se usuário já existe (exceto o atual)
$conn = getConnection();
$sql = "SELECT id FROM usuarios WHERE (usuario = ? OR email = ?) AND id != ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $usuario, $email, $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Usuário ou e-mail já cadastrado!']);
    exit;
}

// Verificar senha atual se for fornecida
if (!empty($senha_atual)) {
    $sql = "SELECT senha FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    if (!password_verify($senha_atual, $user['senha'])) {
        echo json_encode(['success' => false, 'message' => 'Senha atual incorreta!']);
        exit;
    }
}

// Preparar query de atualização
if (!empty($nova_senha)) {
    // Atualizar com nova senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios SET 
            nome = ?, 
            usuario = ?, 
            email = ?, 
            perfil = ?, 
            senha = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssi", $nome, $usuario, $email, $perfil, $senha_hash, $id);
} else {
    // Atualizar sem alterar senha
    $sql = "UPDATE usuarios SET 
            nome = ?, 
            usuario = ?, 
            email = ?, 
            perfil = ? 
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nome, $usuario, $email, $perfil, $id);
}

// Executar atualização
if ($stmt->execute()) {
    // Registrar log
    registrarLog("Editou usuário ID: $id", true, "Nome: $nome, Perfil: $perfil");
    
    echo json_encode(['success' => true, 'message' => 'Usuário atualizado com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário no banco de dados!']);
}

$conn->close();
?>