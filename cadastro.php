<?php
session_start();
require_once 'includes/config.php';

// Redirecionar se já estiver logado
if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';
$sucesso = '';

// Processar cadastro
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    // Validações
    if (empty($nome) || empty($email) || empty($usuario) || empty($senha)) {
        $erro = 'Todos os campos são obrigatórios';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'E-mail inválido';
    } elseif (strlen($senha) < 8) {
        $erro = 'A senha deve ter no mínimo 8 caracteres';
    } elseif ($senha !== $confirmar_senha) {
        $erro = 'As senhas não coincidem';
    } else {
        $conn = getConnection();
        
        if ($conn) {
            // Verificar se email ou usuário já existem
            $sql_check = "SELECT id FROM usuarios WHERE email = ? OR usuario = ?";
            $stmt_check = $conn->prepare($sql_check);
            $stmt_check->bind_param("ss", $email, $usuario);
            $stmt_check->execute();
            $result = $stmt_check->get_result();
            
            if ($result->num_rows > 0) {
                $erro = 'E-mail ou usuário já cadastrados';
            } else {
                // Hash da senha
                $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
                
                // Inserir novo usuário
                $sql_insert = "INSERT INTO usuarios (nome, email, usuario, senha, perfil, status) 
                               VALUES (?, ?, ?, ?, 'user', 'ativo')";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->bind_param("ssss", $nome, $email, $usuario, $senha_hash);
                
                if ($stmt_insert->execute()) {
                    $sucesso = 'Cadastro realizado com sucesso! Redirecionando para login...';
                    header("refresh:3;url=login.php");
                } else {
                    $erro = 'Erro ao cadastrar. Tente novamente.';
                }
            }
            
            $conn->close();
        } else {
            $erro = 'Erro de conexão com o banco de dados';
        }
    }
}

// Carrega o template HTML
require 'cadastro.html';
?>