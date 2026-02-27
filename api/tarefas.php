<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id']) ? (int)$_GET['id'] : null;

try {
    switch ($method) {

        // 
        // GET — Listar tarefas (com dados do cliente)
        // 
        case 'GET':
            $where  = ['1=1'];
            $params = [];

            if ($id) {
                $where[]  = 't.id = ?';
                $params[] = $id;
            }
            if (!empty($_GET['cliente_id'])) {
                $where[]  = 't.cliente_id = ?';
                $params[] = (int)$_GET['cliente_id'];
            }
            if (!empty($_GET['status'])) {
                $where[]  = 't.status = ?';
                $params[] = $_GET['status'];
            }
            if (!empty($_GET['prioridade'])) {
                $where[]  = 't.prioridade = ?';
                $params[] = $_GET['prioridade'];
            }
            // Tarefas de hoje
            if (!empty($_GET['hoje'])) {
                $where[]  = 'DATE(t.data_agendada) = CURDATE()';
            }
            // Tarefas em atraso
            if (!empty($_GET['atrasadas'])) {
                $where[]  = 't.data_agendada &lt; NOW() AND t.status = "pendente"';
            }

            $sql = "
                SELECT 
                    t.*,
                    c.nome        AS cliente_nome,
                    c.telefone    AS cliente_telefone,
                    c.status_cliente,
                    c.status_atendimento,
                    CASE 
                        WHEN t.data_agendada &lt; NOW() AND t.status = 'pendente' THEN 1
                        ELSE 0
                    END AS atrasada
                FROM tarefas t
                INNER JOIN clientes c ON c.id = t.cliente_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY 
                    FIELD(t.status,'pendente','concluida','cancelada'),
                    t.data_agendada ASC
            ";

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $tarefas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['success' => true, 'data' => $tarefas, 'total' => count($tarefas)]);
            break;

        // 
        // POST — Criar nova tarefa
        // 
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true);

            if (empty($body['cliente_id']) || empty($body['titulo']) || empty($body['data_agendada'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Campos obrigatórios: cliente_id, titulo, data_agendada']);
                exit;
            }

            $stmt = $pdo->prepare("
                INSERT INTO tarefas 
                    (cliente_id, titulo, descricao, tipo, data_agendada, prioridade, status, usuario_id)
                VALUES 
                    (:cliente_id, :titulo, :descricao, :tipo, :data_agendada, :prioridade, 'pendente', :usuario_id)
            ");

            $stmt->execute([
                ':cliente_id'    => (int)$body['cliente_id'],
                ':titulo'        => $body['titulo'],
                ':descricao'     => $body['descricao']    ?? null,
                ':tipo'          => $body['tipo']          ?? 'whatsapp',
                ':data_agendada' => $body['data_agendada'],
                ':prioridade'    => $body['prioridade']    ?? 'media',
                ':usuario_id'    => $body['usuario_id']   ?? null,
            ]);

            echo json_encode([
                'success' => true,
                'message' => 'Tarefa agendada com sucesso!',
                'id'      => $pdo->lastInsertId()
            ]);
            break;

        // 
        // PUT — Atualizar tarefa
        // 
        case 'PUT':
            if (!$id) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'ID obrigatório']); exit; }

            $body   = json_decode(file_get_contents('php://input'), true);
            $campos = [];
            $params = [];

            $permitidos = ['titulo','descricao','tipo','data_agendada','status','prioridade','notificado'];
            foreach ($permitidos as $campo) {
                if (isset($body[$campo])) {
                    $campos[] = "$campo = :$campo";
                    $params[":$campo"] = $body[$campo];
                }
            }

            if (empty($campos)) {
                echo json_encode(['success' => false, 'message' => 'Nenhum campo para atualizar']);
                exit;
            }

            $params[':id'] = $id;
            $pdo->prepare("UPDATE tarefas SET " . implode(', ', $campos) . " WHERE id = :id")->execute($params);

            echo json_encode(['success' => true, 'message' => 'Tarefa atualizada!']);
            break;

        // 
        // DELETE — Excluir tarefa
        // 
        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'ID obrigatório']); exit; }

            $pdo->prepare("DELETE FROM tarefas WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Tarefa excluída!']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
}