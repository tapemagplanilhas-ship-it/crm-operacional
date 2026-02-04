<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
$response = ['success' => false, 'message' => ''];
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!$usuario_id) {
    $response['message'] = 'UsuÃ¡rio nÃ£o autenticado';
    echo json_encode($response);
    exit;
}

$conn = getConnection();
if (!$conn) {
    $response['message'] = 'Erro de conexão com o banco';
    echo json_encode($response);
    exit;
}

if (isset($_GET['id'])) {
    $venda_id = cleanData($_GET['id']);
    
    // Buscar venda com informações do cliente
    $sql = "SELECT v.*, c.nome as cliente_nome, c.telefone as cliente_telefone,
                   u.nome as vendedor_nome,
                   m.nome as motivo_perda_nome, m.permite_outro as motivo_perda_permite_outro
            FROM vendas v
            LEFT JOIN clientes c ON v.cliente_id = c.id
            LEFT JOIN usuarios u ON v.usuario_id = u.id
            LEFT JOIN motivos_perda m ON v.motivo_perda_id = m.id
            WHERE v.id = ?";
    if ($perfil_usuario === 'vendedor') {
        $sql .= " AND v.usuario_id = ?";
    }
    
    $stmt = $conn->prepare($sql);
    if ($perfil_usuario === 'vendedor') {
        $stmt->bind_param("ii", $venda_id, $usuario_id);
    } else {
        $stmt->bind_param("i", $venda_id);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($venda = $result->fetch_assoc()) {
        // Motivo da perda
        if ($venda['status'] === 'perdida') {
            if (!empty($venda['motivo_perda_id'])) {
                if ((int)($venda['motivo_perda_permite_outro'] ?? 0) === 1) {
                    $venda['motivo_perda'] = $venda['motivo_perda_outro'] ?? '';
                } else {
                    $venda['motivo_perda'] = $venda['motivo_perda_nome'] ?? '';
                }
            } else {
                // fallback para legado em observacoes
                $observacoes = $venda['observacoes'] ?? '';
                if (strpos($observacoes, 'MOTIVO DA PERDA:') === 0) {
                    $linhas = explode("\n", $observacoes);
                    $motivo = str_replace('MOTIVO DA PERDA: ', '', $linhas[0]);
                    $venda['motivo_perda'] = trim($motivo);
                    unset($linhas[0]);
                    $venda['observacoes'] = trim(implode("\n", $linhas));
                }
            }
        }
        
        $response['success'] = true;
        $response['data'] = $venda;
    } else {
        $response['message'] = 'Venda não encontrada';
    }
} else {
    $response['message'] = 'ID da venda não especificado';
}

$conn->close();
echo json_encode($response);
?>
