<?php
session_start();
$cliente_id = $_GET['id'] ?? 0;

if ($perfil_usuario === 'vendedor') {
    $stmt = $conn->prepare("SELECT usuario_id FROM clientes WHERE id = ?");
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        if ($row['usuario_id'] != $_SESSION['usuario_id']) {
            die("Você só pode editar seus próprios clientes");
        }
    }
}
?>