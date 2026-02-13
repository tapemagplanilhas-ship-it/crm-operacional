<?php
declare(strict_types=1);

session_start();

// SEMPRE JSON
header('Content-Type: application/json; charset=utf-8');

// Evita que "display_errors=1" vaze HTML e quebre o JSON
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once '../includes/config.php';

function respond(array $payload, int $httpCode = 200): void {
    if (function_exists('http_response_code')) http_response_code($httpCode);

    // Remove qualquer lixo já impresso (warnings/notices)
    while (ob_get_level() > 0) { ob_end_clean(); }

    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit;
}

$usuario_id = $_SESSION['usuario_id'] ?? null;
$perfil_usuario = $_SESSION['perfil'] ?? '';
if (!$usuario_id) {
    respond(['success' => false, 'message' => 'Usuário não autenticado'], 401);
}

$conn = getConnection();
if (!$conn) {
    respond(['success' => false, 'message' => 'Falha ao conectar no banco de dados'], 500);
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    // -------------------------
    // GET
    // -------------------------
    if ($method === 'GET') {
        // Ex: api/vendas.php?cliente_id=123
        if (isset($_GET['cliente_id'])) {
            $cliente_id = (int)cleanData($_GET['cliente_id']);

            $sql = "
                SELECT v.*, u.nome AS vendedor_nome
                FROM vendas v
                LEFT JOIN usuarios u ON u.id = v.usuario_id
                WHERE v.cliente_id = ?
            ";
            if ($perfil_usuario === 'vendedor') {
                $sql .= " AND v.usuario_id = ?";
            }
            $sql .= " ORDER BY v.data_venda DESC, v.id DESC";

            $stmt = $conn->prepare($sql);
            if ($perfil_usuario === 'vendedor') {
                $stmt->bind_param("ii", $cliente_id, $usuario_id);
            } else {
                $stmt->bind_param("i", $cliente_id);
            }
            $stmt->execute();

            $result = $stmt->get_result();
            $rows = [];
            while ($row = $result->fetch_assoc()) {
                $row['status_formatado'] = formatarStatus($row['status']);
                $row['forma_pagamento_formatada'] = formatarFormaPagamento($row['forma_pagamento'] ?? 'na');
                $row['valor_formatado'] = formatCurrency($row['valor'] ?? 0);
                $row['data_venda_formatada'] = formatDate($row['data_venda'] ?? '');
                $rows[] = $row;
            }

            respond(['success' => true, 'data' => $rows]);
        }

        // Listagem padrão (simples)
        $sql = "
            SELECT v.*, u.nome AS vendedor_nome, c.nome AS cliente_nome
            FROM vendas v
            LEFT JOIN usuarios u ON u.id = v.usuario_id
            LEFT JOIN clientes c ON c.id = v.cliente_id
        ";
        if ($perfil_usuario === 'vendedor') {
            $sql .= " WHERE v.usuario_id = ? ";
        }
        $sql .= " ORDER BY v.data_venda DESC, v.id DESC LIMIT 200";

        $stmt = $conn->prepare($sql);
        if ($perfil_usuario === 'vendedor') {
            $stmt->bind_param("i", $usuario_id);
        }
        $stmt->execute();
        $result = $stmt->get_result();

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $row['status_formatado'] = formatarStatus($row['status']);
            $row['forma_pagamento_formatada'] = formatarFormaPagamento($row['forma_pagamento'] ?? 'na');
            $row['valor_formatado'] = formatCurrency($row['valor'] ?? 0);
            $row['data_venda_formatada'] = formatDate($row['data_venda'] ?? '');
            $rows[] = $row;
        }

        respond(['success' => true, 'data' => $rows]);
    }

    // -------------------------
    // POST
    // -------------------------
    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        if (!is_array($data)) {
            // fallback para form-data
            $data = $_POST;
        }

        $cliente_id      = (int)cleanData($data['cliente_id'] ?? 0);
        $valor_raw       = $data['valor'] ?? '';
        $data_venda_br   = cleanData($data['data_venda'] ?? date('d/m/Y'));
        $status          = cleanData($data['status'] ?? 'concluida');
        $forma_pagamento = cleanData($data['forma_pagamento'] ?? 'na');
        $motivo_perda_id = cleanData($data['motivo_perda_id'] ?? '');
        $motivo_perda_outro = cleanData($data['motivo_perda_outro'] ?? '');
        $codigo_orcamento_raw = cleanData($data['codigo_orcamento'] ?? '');
        $observacoes     = cleanData($data['observacoes'] ?? '');
        $canal_venda     = cleanData($data['canal_venda'] ?? 'loja');
        $hora_venda      = cleanData($data['hora_venda'] ?? '');
        $produto_principal = cleanData($data['produto_principal'] ?? '');

        // Validações
        if ($cliente_id <= 0) {
            respond(['success' => false, 'message' => 'Cliente é obrigatório'], 400);
        }

        $valor = parseMoneyBR($valor_raw);
        if ($valor <= 0) {
            respond(['success' => false, 'message' => 'Valor inválido'], 400);
        }

        $status_validos = ['concluida', 'perdida', 'orcamento'];
        if (!in_array($status, $status_validos, true)) {
            respond(['success' => false, 'message' => 'Status inválido'], 400);
        }

        if ($status === 'perdida' && $motivo_perda_id === '') {
            respond(['success' => false, 'message' => 'Motivo da perda é obrigatório para vendas perdidas'], 400);
        }

        if ($status !== 'perdida') {
            $motivo_perda_id = null;
            $motivo_perda_outro = '';
        }

        $formas_validas = ['pix', 'cartao', 'dinheiro', 'boleto', 'na'];
        if (!in_array($forma_pagamento, $formas_validas, true)) {
            respond(['success' => false, 'message' => 'Forma de pagamento inválida'], 400);
        }

        $data_mysql = parseDateBR((string)$data_venda_br);
        if (!$data_mysql) {
            respond(['success' => false, 'message' => 'Data inválida. Use dd/mm/aaaa'], 400);
        }

        // Observações completas
        $observacoes_completas = $observacoes;
        $motivo_perda_texto = '';
        if ($status === 'perdida' && $motivo_perda_id !== '') {
            $motivo_id_int = (int)$motivo_perda_id;
            $stmtMotivo = $conn->prepare("SELECT nome, permite_outro FROM motivos_perda WHERE id = ?");
            $stmtMotivo->bind_param("i", $motivo_id_int);
            $stmtMotivo->execute();
            $motivoRow = $stmtMotivo->get_result()->fetch_assoc();
            if ($motivoRow) {
                if ((int)$motivoRow['permite_outro'] === 1) {
                    if ($motivo_perda_outro === '') {
                        respond(['success' => false, 'message' => 'Descreva o motivo da perda'], 400);
                    }
                    $motivo_perda_texto = $motivo_perda_outro;
                } else {
                    $motivo_perda_texto = $motivoRow['nome'];
                }
            }
            if ($motivo_perda_texto !== '') {
                $observacoes_completas = "MOTIVO DA PERDA: {$motivo_perda_texto}\n\n{$observacoes}";
            }
        }
        $codigo_orcamento = preg_replace('/\\D+/', '', (string)$codigo_orcamento_raw);
        if ($codigo_orcamento === '') $codigo_orcamento = null;

        // INSERT (inclui extras se quiser usar)
        $stmt = $conn->prepare("
            INSERT INTO vendas
                (usuario_id, cliente_id, valor, data_venda, status, observacoes, forma_pagamento, motivo_perda_id, motivo_perda_outro, codigo_orcamento, canal_venda, hora_venda, produto_principal)
            VALUES
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        // hora_venda pode ser vazio; no DB é time nullable -> manda null se vazio
        $hora_sql = ($hora_venda === '') ? null : $hora_venda;

        $stmt->bind_param(
            "iidssssisssss",
            $usuario_id,
            $cliente_id,
            $valor,
            $data_mysql,
            $status,
            $observacoes_completas,
            $forma_pagamento,
            $motivo_perda_id,
            $motivo_perda_outro,
            $codigo_orcamento,
            $canal_venda,
            $hora_sql,
            $produto_principal
        );

        if (!$stmt->execute()) {
            respond(['success' => false, 'message' => 'Erro ao registrar venda: ' . $conn->error], 500);
        }

        atualizarMetricasCliente($cliente_id);

        respond([
            'success' => true,
            'message' => 'Venda registrada com sucesso!',
            'data' => [
                'venda_id' => $conn->insert_id,
                'status' => $status,
                'forma_pagamento' => $forma_pagamento
            ]
        ]);
    }

    respond(['success' => false, 'message' => 'Método não suportado'], 405);

} catch (Throwable $e) {
    respond(['success' => false, 'message' => 'Erro interno: ' . $e->getMessage()], 500);
} finally {
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
}


// Helpers (mantive iguais aos seus)
function formatarStatus($status) {
    $statuses = [
        'concluida' => 'CONCLUÍDA',
        'perdida' => 'PERDIDA',
        'orcamento' => 'ORÇAMENTO'
    ];
    return $statuses[$status] ?? $status;
}

function formatarFormaPagamento($forma) {
    $formas = [
        'pix' => 'PIX',
        'cartao' => 'Cartão',
        'dinheiro' => 'Dinheiro',
        'boleto' => 'Boleto',
        'na' => 'N/A'
    ];
    return $formas[$forma] ?? $forma;
}







