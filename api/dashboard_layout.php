<?php
require_once __DIR__ . '/../includes/config.php';
iniciarSessao();
header('Content-Type: application/json; charset=utf-8');
$method = $_SERVER['REQUEST_METHOD'];
$conn = getConnection();
if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection error']);
    exit;
}

$user_id = $_SESSION['usuario_id'] ?? null;
$perfil = $_SESSION['perfil'] ?? null;

// Force MySQLi to throw exceptions so we can handle them and return JSON
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    // Quick check: verify the dashboard_layouts table exists to provide a clearer error
    $resCheck = $conn->query("SHOW TABLES LIKE 'dashboard_layouts'");
    if (!$resCheck || $resCheck->num_rows === 0) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => "Tabela 'dashboard_layouts' não encontrada. Execute a migração SQL."]);
        exit;
    }

if ($method === 'GET') {
    // ?list=1 -> list layouts (user + shared)
    if (isset($_GET['list']) && $_GET['list'] == '1') {
        $sql = "SELECT id, user_id, name, is_shared, updated_at FROM dashboard_layouts WHERE user_id = ? OR is_shared = 1 ORDER BY is_shared DESC, updated_at DESC";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $user_id);
        $stmt->execute();
        $res = $stmt->get_result();
        $rows = [];
        while ($r = $res->fetch_assoc()) $rows[] = $r;
        echo json_encode(['success' => true, 'data' => $rows]);
        exit;
    }

    // Return latest layout for user, fallback to shared default
    $sql = "SELECT * FROM dashboard_layouts WHERE user_id = ? ORDER BY updated_at DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $layout = $res->fetch_assoc();

    if (!$layout) {
        // fallback to shared default
        $sql = "SELECT * FROM dashboard_layouts WHERE is_shared = 1 ORDER BY updated_at DESC LIMIT 1";
        $res = $conn->query($sql);
        $layout = $res->fetch_assoc();
    }

    if (!$layout) {
        echo json_encode(['success' => false, 'message' => 'Nenhum layout encontrado']);
    } else {
        echo json_encode(['success' => true, 'data' => $layout]);
    }
    exit;
}

if ($method === 'POST') {
    // Requires user
    if (!$user_id) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Não autorizado']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);
    if (!$input || !isset($input['layout_json'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Dados inválidos']);
        exit;
    }

    $name = isset($input['name']) ? substr($input['name'],0,100) : 'Layout Usuário';
    $layout_json = json_encode($input['layout_json'], JSON_UNESCAPED_UNICODE);
    $is_shared = (!empty($input['is_shared']) && $perfil === 'admin') ? 1 : 0; // only admin can share
    $id = isset($input['id']) ? intval($input['id']) : null;

    if ($id) {
        // Update (only owner or admin)
        $sql = "SELECT user_id FROM dashboard_layouts WHERE id = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if (!$row) {
            http_response_code(404);
            echo json_encode(['success' => false, 'message' => 'Layout não encontrado']);
            exit;
        }
        if ($row['user_id'] != $user_id && $perfil !== 'admin') {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Permissão negada']);
            exit;
        }
        $sql = "UPDATE dashboard_layouts SET name = ?, layout_json = ?, is_shared = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssii', $name, $layout_json, $is_shared, $id);
        $ok = $stmt->execute();
        echo json_encode(['success' => (bool)$ok]);
        exit;
    }

    // Insert new layout
    $sql = "INSERT INTO dashboard_layouts (user_id, name, is_shared, layout_json) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiss', $user_id, $name, $is_shared, $layout_json);
    $ok = $stmt->execute();
    if ($ok) {
        echo json_encode(['success' => true, 'id' => $conn->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Falha ao salvar']);
    }
    exit;
}

} catch (Throwable $e) {
    error_log('dashboard_layout.php error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro interno no servidor', 'detail' => $e->getMessage()]);
    exit;
}

http_response_code(405);
echo json_encode(['success'=>false, 'message'=>'Method not allowed']);
?>