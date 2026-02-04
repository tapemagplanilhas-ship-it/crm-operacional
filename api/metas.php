<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../includes/config.php';
verificarLogin();
requerirPermissao('gerencia');

$conn = getConnection();
if (!$conn) {
    echo json_encode(['success' => false, 'message' => 'Erro de conexão com o banco']);
    exit;
}

// MVP: garante a tabela (você pode mover para um script SQL depois)
$conn->query(
    "CREATE TABLE IF NOT EXISTS metas_vendedor_mensal (
        id INT AUTO_INCREMENT PRIMARY KEY,
        vendedor_id INT NOT NULL,
        ano INT NOT NULL,
        mes INT NOT NULL,
        meta_valor DECIMAL(12,2) NOT NULL DEFAULT 0,
        atualizado_por INT NULL,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_meta (vendedor_id, ano, mes),
        INDEX idx_periodo (ano, mes),
        CONSTRAINT fk_meta_vendedor FOREIGN KEY (vendedor_id) REFERENCES usuarios(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);

// Config: dias uteis e sabado
$conn->query(
    "CREATE TABLE IF NOT EXISTS configuracoes_sistema (
        chave VARCHAR(50) PRIMARY KEY,
        valor VARCHAR(255) NOT NULL DEFAULT '',
        atualizado_por INT NULL,
        atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_config_chave (chave),
        CONSTRAINT fk_config_usuario FOREIGN KEY (atualizado_por) REFERENCES usuarios(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
);
$conn->query(
    "INSERT IGNORE INTO configuracoes_sistema (chave, valor) VALUES ('contar_sabado', '0')"
);

$action = $_GET['action'] ?? 'list';
$action = strtolower(trim($action));

function out_json($arr) {
    echo json_encode($arr, JSON_UNESCAPED_UNICODE);
    exit;
}

function get_config_value($conn, $key, $default = '0') {
    $stmt = $conn->prepare("SELECT valor FROM configuracoes_sistema WHERE chave = ? LIMIT 1");
    $stmt->bind_param('s', $key);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? (string)$row['valor'] : (string)$default;
}

function set_config_value($conn, $key, $value, $userId) {
    $stmt = $conn->prepare(
        "INSERT INTO configuracoes_sistema (chave, valor, atualizado_por)
         VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE
            valor = VALUES(valor),
            atualizado_por = VALUES(atualizado_por),
            atualizado_em = CURRENT_TIMESTAMP"
    );
    $stmt->bind_param('ssi', $key, $value, $userId);
    return $stmt->execute();
}

function get_start_end($ano, $mes) {
    $start = sprintf('%04d-%02d-01', $ano, $mes);
    $end = date('Y-m-t', strtotime($start));
    return [$start, $end];
}

function get_calendar_counts($conn, $start, $end, $includeSaturday) {
    $sql = "
        SELECT
            COUNT(*) AS total,
            SUM(
                CASE
                    WHEN (dia_util = 1 AND feriado = 0)
                         OR (? = 1 AND dia_semana = 'Saturday' AND feriado = 0)
                    THEN 1 ELSE 0
                END
            ) AS uteis
        FROM calendario_uteis
        WHERE data BETWEEN ? AND ?
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) return null;
    $stmt->bind_param('iss', $includeSaturday, $start, $end);
    if (!$stmt->execute()) return null;
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return null;
    return [
        'total' => (int)($row['total'] ?? 0),
        'uteis' => (int)($row['uteis'] ?? 0),
    ];
}

function count_weekdays_fallback($start, $end, $includeSaturday) {
    try {
        $startDt = new DateTimeImmutable($start);
        $endDt = new DateTimeImmutable($end);
    } catch (Exception $e) {
        return 0;
    }
    if ($endDt < $startDt) return 0;
    $count = 0;
    for ($dt = $startDt; $dt <= $endDt; $dt = $dt->modify('+1 day')) {
        $w = (int)$dt->format('N'); // 1=Mon ... 7=Sun
        if ($w >= 1 && $w <= 5) {
            $count++;
        } elseif ($includeSaturday && $w === 6) {
            $count++;
        }
    }
    return $count;
}

if ($action === 'config_get') {
    if (($_SESSION['perfil'] ?? '') !== 'admin') {
        out_json(['success' => false, 'message' => 'Acesso negado']);
    }

    $contarSabado = (int)get_config_value($conn, 'contar_sabado', '0');
    out_json(['success' => true, 'contar_sabado' => $contarSabado]);
}

if ($action === 'config_set') {
    if (($_SESSION['perfil'] ?? '') !== 'admin') {
        out_json(['success' => false, 'message' => 'Acesso negado']);
    }

    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);
    if (!is_array($payload)) {
        out_json(['success' => false, 'message' => 'JSON invalido']);
    }

    $contarSabado = !empty($payload['contar_sabado']) ? '1' : '0';
    $userId = (int)($_SESSION['usuario_id'] ?? 0);
    $ok = set_config_value($conn, 'contar_sabado', $contarSabado, $userId);
    if (!$ok) {
        out_json(['success' => false, 'message' => 'Erro ao salvar configuracao']);
    }
    out_json(['success' => true, 'contar_sabado' => (int)$contarSabado]);
}

if ($action === 'list') {
    $mes = (int)($_GET['mes'] ?? date('n'));
    $ano = (int)($_GET['ano'] ?? date('Y'));
    if ($mes < 1 || $mes > 12) $mes = (int)date('n');
    if ($ano < 2000 || $ano > 2100) $ano = (int)date('Y');

    [$startDate, $endDate] = get_start_end($ano, $mes);
    $today = date('Y-m-d');

    // limite do "ate hoje" dentro do periodo
    $ateHoje = $today;
    if ($ateHoje < $startDate) $ateHoje = $startDate;
    if ($ateHoje > $endDate) $ateHoje = $endDate;

    // dados base: vendedores + meta (se existir)
    $sql = "
        SELECT 
            u.id AS vendedor_id,
            u.nome,
            u.email,
            u.ativo,
            COALESCE(m.meta_valor, 0) AS meta_valor
        FROM usuarios u
        LEFT JOIN metas_vendedor_mensal m
            ON m.vendedor_id = u.id AND m.ano = ? AND m.mes = ?
        WHERE u.perfil = 'vendedor'
        ORDER BY u.ativo DESC, u.nome ASC
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $ano, $mes);
    $stmt->execute();
    $res = $stmt->get_result();

    $rows = [];
    while ($r = $res->fetch_assoc()) {
        $rows[] = $r;
    }

    if (empty($rows)) {
        out_json(['success' => true, 'data' => []]);
    }

    // Busca realizado por vendedor (concluidas) no periodo
    $sqlR = "
        SELECT v.usuario_id AS vendedor_id, COALESCE(SUM(v.valor), 0) AS realizado
        FROM vendas v
        WHERE v.status = 'concluida'
          AND v.data_venda BETWEEN ? AND ?
        GROUP BY v.usuario_id
    ";

    $stmtR = $conn->prepare($sqlR);
    $stmtR->bind_param('ss', $startDate, $endDate);
    $stmtR->execute();
    $resR = $stmtR->get_result();
    $realizadoMap = [];
    while ($rr = $resR->fetch_assoc()) {
        $realizadoMap[(string)$rr['vendedor_id']] = (float)$rr['realizado'];
    }

    // Projecao por dias uteis (calendario_uteis) com opcao de contar sabado.
    $sqlA = "
        SELECT v.usuario_id AS vendedor_id, COALESCE(SUM(v.valor), 0) AS realizado_ate_hoje
        FROM vendas v
        WHERE v.status = 'concluida'
          AND v.data_venda BETWEEN ? AND ?
        GROUP BY v.usuario_id
    ";

    $stmtA = $conn->prepare($sqlA);
    $stmtA->bind_param('ss', $startDate, $ateHoje);
    $stmtA->execute();
    $resA = $stmtA->get_result();
    $ateHojeMap = [];
    while ($ra = $resA->fetch_assoc()) {
        $ateHojeMap[(string)$ra['vendedor_id']] = (float)$ra['realizado_ate_hoje'];
    }

    // calculos por vendedor
    $contarSabado = (int)get_config_value($conn, 'contar_sabado', '0');
    $startTs = strtotime($startDate);
    $endTs = strtotime($endDate);
    $ateTs = strtotime($ateHoje);

    $diasCorridosPeriodo = max(1, (int)floor(($endTs - $startTs) / 86400) + 1);
    $diasCorridosPassados = max(1, (int)floor(($ateTs - $startTs) / 86400) + 1);

    $periodoCounts = get_calendar_counts($conn, $startDate, $endDate, $contarSabado);
    $passadoCounts = get_calendar_counts($conn, $startDate, $ateHoje, $contarSabado);

    if ($periodoCounts && $periodoCounts['total'] >= $diasCorridosPeriodo) {
        $diasPeriodo = (int)$periodoCounts['uteis'];
        $diasPassados = $passadoCounts ? (int)$passadoCounts['uteis'] : 0;
    } else {
        $diasPeriodo = count_weekdays_fallback($startDate, $endDate, (bool)$contarSabado);
        $diasPassados = count_weekdays_fallback($startDate, $ateHoje, (bool)$contarSabado);
    }

    $diasPeriodo = max(1, $diasPeriodo);
    $diasPassados = max(1, $diasPassados);
    $diasRestantes = max(0, $diasPeriodo - $diasPassados);

    $out = [];
    foreach ($rows as $r) {
        $vid = (int)$r['vendedor_id'];
        $meta = (float)$r['meta_valor'];
        $realizado = (float)($realizadoMap[(string)$vid] ?? 0);
        $realizadoAte = (float)($ateHojeMap[(string)$vid] ?? 0);

        $pct = ($meta > 0) ? ($realizado * 100.0 / $meta) : 0.0;
        $faltante = max(0, $meta - $realizado);
        $necDia = ($diasRestantes > 0) ? ($faltante / $diasRestantes) : $faltante;

        $proj = ($diasPassados > 0) ? (($realizadoAte / $diasPassados) * $diasPeriodo) : 0.0;

        $out[] = [
            'vendedor_id' => $vid,
            'nome' => $r['nome'],
            'email' => $r['email'],
            'ativo' => (int)$r['ativo'],
            'meta_valor' => $meta,
            'realizado' => $realizado,
            'pct' => round($pct, 1),
            'projecao' => round($proj, 2),
            'necessario_por_dia' => round($necDia, 2),
            'periodo' => [
                'ano' => $ano,
                'mes' => $mes,
                'start' => $startDate,
                'end' => $endDate,
                'ateHoje' => $ateHoje,
                'dias_periodo' => $diasPeriodo,
                'dias_passados' => $diasPassados,
                'dias_restantes' => $diasRestantes
            ]
        ];
    }

    out_json(['success' => true, 'data' => $out]);
}

if ($action === 'save') {
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);

    if (!is_array($payload)) {
        out_json(['success' => false, 'message' => 'JSON inválido']);
    }

    $vendedorId = (int)($payload['vendedor_id'] ?? 0);
    $mes = (int)($payload['mes'] ?? 0);
    $ano = (int)($payload['ano'] ?? 0);
    $meta = (float)($payload['meta_valor'] ?? 0);

    if ($vendedorId <= 0) out_json(['success' => false, 'message' => 'vendedor_id inválido']);
    if ($mes < 1 || $mes > 12) out_json(['success' => false, 'message' => 'mês inválido']);
    if ($ano < 2000 || $ano > 2100) out_json(['success' => false, 'message' => 'ano inválido']);
    if ($meta < 0) out_json(['success' => false, 'message' => 'meta não pode ser negativa']);

    // valida se é vendedor
    $stmtV = $conn->prepare("SELECT id FROM usuarios WHERE id = ? AND perfil='vendedor' LIMIT 1");
    $stmtV->bind_param('i', $vendedorId);
    $stmtV->execute();
    $rv = $stmtV->get_result()->fetch_assoc();
    if (!$rv) {
        out_json(['success' => false, 'message' => 'Vendedor não encontrado']);
    }

    $atualizadoPor = (int)($_SESSION['usuario_id'] ?? 0);

    $sql = "
        INSERT INTO metas_vendedor_mensal (vendedor_id, ano, mes, meta_valor, atualizado_por)
        VALUES (?, ?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
            meta_valor = VALUES(meta_valor),
            atualizado_por = VALUES(atualizado_por),
            atualizado_em = CURRENT_TIMESTAMP
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param('iiidi', $vendedorId, $ano, $mes, $meta, $atualizadoPor);

    $ok = $stmt->execute();
    if (!$ok) {
        out_json(['success' => false, 'message' => 'Erro ao salvar meta']);
    }

    out_json(['success' => true]);
}

out_json(['success' => false, 'message' => 'Ação inválida']);
