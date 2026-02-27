<?php
require_once '../includes/config.php';
header('Content-Type: application/json');
header('Access-Control-Allow-Methods: GET, POST, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

$method = $_SERVER['REQUEST_METHOD'];
$id     = isset($_GET['id'])         ? (int)$_GET['id']         : null;
$cliId  = isset($_GET['cliente_id']) ? (int)$_GET['cliente_id'] : null;

try {
    switch ($method) {

        // GET — Buscar histórico de um cliente
        case 'GET':
            if (!$cliId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'cliente_id obrigatório']);
                exit;
            }

            $stmt = $pdo->prepare("
                SELECT 
                    h.*,
                    c.nome AS cliente_nome
                FROM historico_contatos h
                INNER JOIN clientes c ON c.id = h.cliente_id
                WHERE h.cliente_id = ?
                ORDER BY h.data_contato DESC
                LIMIT 50
            ");
            $stmt->execute([$cliId]);

            echo json_encode(['success' => true, 'data' => $stmt->fetchAll(PDO::FETCH_ASSOC)]);
            break;

        // POST — Registrar contato
        case 'POST':
            $body = json_decode(file_get_contents('php://input'), true);

            if (empty($body['cliente_id']) || empty($body['status_contato'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'cliente_id e status_contato são obrigatórios']);
                exit;
            }

            // Insere histórico
            $stmt = $pdo->prepare("
                INSERT INTO historico_contatos 
                    (cliente_id, tipo_contato, status_contato, descricao, data_contato, usuario_id)
                VALUES 
                    (:cliente_id, :tipo_contato, :status_contato, :descricao, NOW(), :usuario_id)
            ");
            $stmt->execute([
                ':cliente_id'     => (int)$body['cliente_id'],
                ':tipo_contato'   => $body['tipo_contato']   ?? 'whatsapp',
                ':status_contato' => $body['status_contato'],
                ':descricao'      => $body['descricao']      ?? null,
                ':usuario_id'     => $body['usuario_id']     ?? null,
            ]);

            // Atualiza status_atendimento e ultimo_contato do cliente
            $pdo->prepare("
                UPDATE clientes 
                SET status_atendimento = :status, ultimo_contato = NOW()
                WHERE id = :id
            ")->execute([
                ':status' => $body['status_contato'] === 'atendido' ? 'atendido' : 'nao_atendido',
                ':id'     => (int)$body['cliente_id']
            ]);

            // Se tarefa vinculada, marca como concluída
            if (!empty($body['tarefa_id'])) {
                $pdo->prepare("UPDATE tarefas SET status = 'concluida' WHERE id = ?")->execute([$body['tarefa_id']]);
            }

            echo json_encode(['success' => true, 'message' => 'Contato registrado!', 'id' => $pdo->lastInsertId()]);
            break;

        // DELETE — Remover registro
        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['success' => false, 'message' => 'ID obrigatório']); exit; }
            $pdo->prepare("DELETE FROM historico_contatos WHERE id = ?")->execute([$id]);
            echo json_encode(['success' => true, 'message' => 'Registro removido!']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Erro no banco: ' . $e->getMessage()]);
}