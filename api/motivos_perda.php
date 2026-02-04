<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';

$response = ['success' => false, 'message' => '', 'data' => []];

$conn = getConnection();
if (!$conn) {
    $response['message'] = 'Erro de conexÃ£o com o banco';
    echo json_encode($response);
    exit;
}

$result = $conn->query("SELECT id, nome, permite_outro FROM motivos_perda ORDER BY ordem ASC, nome ASC");
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $response['data'][] = $row;
    }
    $response['success'] = true;
} else {
    $response['message'] = 'Erro ao buscar motivos';
}

$conn->close();
echo json_encode($response);
?>
