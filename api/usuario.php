<?php
// api/usuario.php
session_start();
header('Content-Type: application/json');

require_once '../includes/config.php';

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['erro' => 'Não autorizado']);
    exit;
}

$conn = getConnection();
$usuario_id = $_SESSION['usuario_id'];
$acao = $_POST['acao'] ?? $_GET['acao'] ?? '';

try {
    switch ($acao) {
        case 'atualizar_dados':
            atualizarDados($conn, $usuario_id);
            break;
            
        case 'trocar_senha':
            trocarSenha($conn, $usuario_id);
            break;
            
        case 'atualizar_foto':
            atualizarFoto($conn, $usuario_id);
            break;
            
        case 'encerrar_sessoes':
            encerrarSessoes($conn, $usuario_id);
            break;
            
        case 'get_sessoes':
            getSessoes($conn, $usuario_id);
            break;
            
        default:
            echo json_encode(['erro' => 'Ação inválida']);
    }
} catch (Exception $e) {
    echo json_encode(['erro' => $e->getMessage()]);
}

function atualizarDados($conn, $usuario_id) {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($nome) || empty($email)) {
        throw new Exception('Nome e e-mail são obrigatórios');
    }
    
    // Verificar se email já existe para outro usuário
    $sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $email, $usuario_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if (mysqli_fetch_assoc($result)) {
        throw new Exception('Este e-mail já está em uso por outro usuário');
    }
    
    // Atualizar dados
    $sql = "UPDATE usuarios SET nome = ?, email = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $nome, $email, $usuario_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao atualizar dados');
    }
    
    // Log da ação
    logAcao($conn, $usuario_id, 'atualizacao_perfil', 'Dados pessoais atualizados');
    
    echo json_encode(['sucesso' => true, 'mensagem' => 'Dados atualizados com sucesso']);
}

function trocarSenha($conn, $usuario_id) {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        throw new Exception('Todos os campos são obrigatórios');
    }
    
    if ($nova_senha !== $confirmar_senha) {
        throw new Exception('As senhas não conferem');
    }
    
    if (strlen($nova_senha) < 6) {
        throw new Exception('A nova senha deve ter no mínimo 6 caracteres');
    }
    
    // Buscar senha atual
    $sql = "SELECT senha FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $usuario = mysqli_fetch_assoc($result);
    
    if (!$usuario || !password_verify($senha_atual, $usuario['senha'])) {
        throw new Exception('Senha atual incorreta');
    }
    
    // Atualizar senha
    $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
    $sql = "UPDATE usuarios SET senha = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $senha_hash, $usuario_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao alterar senha');
    }
    
    // Log da ação
    logAcao($conn, $usuario_id, 'troca_senha', 'Senha alterada com sucesso');
    
    echo json_encode(['sucesso' => true, 'mensagem' => 'Senha alterada com sucesso']);
}

function atualizarFoto($conn, $usuario_id) {
    if (!isset($_FILES['foto_perfil']) || $_FILES['foto_perfil']['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('Erro no upload da imagem');
    }
    
    $file = $_FILES['foto_perfil'];
    
    // Validar imagem
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    if (!in_array($file['type'], $allowed_types)) {
        throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WebP');
    }
    
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('A imagem deve ter no máximo 5MB');
    }
    
    // Criar diretório se não existir
    $upload_dir = '../uploads/perfis/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    // Gerar nome único
    $extensao = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nome_arquivo = 'perfil_' . $usuario_id . '_' . time() . '.' . $extensao;
    $caminho_completo = $upload_dir . $nome_arquivo;
    
    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $caminho_completo)) {
        throw new Exception('Erro ao salvar a imagem');
    }
    
    // Definir caminho relativo para salvar no banco
    $caminho_relativo = 'uploads/perfis/' . $nome_arquivo;

    // Buscar fotos antigas
    $sql = "SELECT foto_perfil, foto FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $foto_antiga_perfil, $foto_antiga);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    // Remover fotos antigas se existirem (e diferentes do novo caminho)
    foreach ([$foto_antiga_perfil, $foto_antiga] as $old) {
        if ($old && $old !== $caminho_relativo && file_exists('../' . $old)) {
            @unlink('../' . $old);
        }
    }
    
    // Atualizar no banco (sincronizar ambas as colunas)
    $sql = "UPDATE usuarios SET foto_perfil = ?, foto = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssi", $caminho_relativo, $caminho_relativo, $usuario_id);
    
    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception('Erro ao atualizar foto no banco');
    }
    
    // Log da ação
    logAcao($conn, $usuario_id, 'atualizacao_perfil', 'Foto de perfil atualizada');
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => 'Foto atualizada com sucesso',
        'caminho_foto' => $caminho_relativo
    ]);
}

function encerrarSessoes($conn, $usuario_id) {
    $sessao_atual = session_id();
    
    // Excluir todas as sessões exceto a atual
    $sql = "DELETE FROM usuarios_sessoes WHERE usuario_id = ? AND session_id != ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "is", $usuario_id, $sessao_atual);
    mysqli_stmt_execute($stmt);
    
    $sessoes_encerradas = mysqli_stmt_affected_rows($stmt);
    
    // Log da ação
    logAcao($conn, $usuario_id, 'logout', "Encerradas $sessoes_encerradas outras sessões");
    
    echo json_encode([
        'sucesso' => true,
        'mensagem' => "{$sessoes_encerradas} sessões encerradas",
        'sessoes_encerradas' => $sessoes_encerradas
    ]);
}

function getSessoes($conn, $usuario_id) {
    $sessao_atual = session_id();
    $sql = "SELECT id, ip_address, user_agent, ultima_atividade, criado_em,
                   session_id = ? as sessao_atual
            FROM usuarios_sessoes 
            WHERE usuario_id = ? 
            AND ultima_atividade > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
            ORDER BY ultima_atividade DESC";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "si", $sessao_atual, $usuario_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    $sessoes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $sessoes[] = $row;
    }
    
    echo json_encode(['sessoes' => $sessoes]);
}

function logAcao($conn, $usuario_id, $tipo, $detalhes) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    
    $sql = "INSERT INTO usuarios_logs (usuario_id, tipo, ip_address, user_agent, detalhes)
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "issss", $usuario_id, $tipo, $ip_address, $user_agent, $detalhes);
    mysqli_stmt_execute($stmt);
}
