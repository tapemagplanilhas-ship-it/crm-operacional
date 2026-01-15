<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario_id'])) {
    exit('Usuário não autenticado');
}

$conn = getConnection();
$id = $_SESSION['usuario_id'];

if (!isset($_FILES['foto'])) {
    exit('Nenhuma imagem enviada');
}

$foto = $_FILES['foto'];

$ext = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
$permitidas = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($ext, $permitidas)) {
    exit('Formato não permitido');
}

// Pasta de destino (padronizada)
$pasta = __DIR__ . '/../../uploads/perfis/';
if (!is_dir($pasta)) {
    mkdir($pasta, 0777, true);
}

$nomeArquivo = "perfil_$id.$ext";
$destino = $pasta . $nomeArquivo;

// Move o arquivo
if (!move_uploaded_file($foto['tmp_name'], $destino)) {
    exit('Erro ao salvar imagem');
}

// Caminho salvo no banco (relativo)
$caminhoBanco = "uploads/perfis/$nomeArquivo";

// Atualiza no banco (MySQLi) - sincronizar ambas as colunas
$sql = "UPDATE usuarios SET foto = ?, foto_perfil = ? WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssi", $caminhoBanco, $caminhoBanco, $id);
$stmt->execute();

header('Location: ../../index.php');
exit;
