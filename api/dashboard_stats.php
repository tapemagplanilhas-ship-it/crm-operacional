<?php
require_once __DIR__ . '/../includes/config.php';
header('Content-Type: application/json; charset=utf-8');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$metric = $_GET['metric'] ?? '';
$conn = getConnection();

if (!$conn) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection error']);
    exit;
}

// Helper: pega o usuário alvo para métricas "por vendedor"
function getTargetUserId(): ?int {
    // prioridade: user_id via GET (para gerência no futuro), senão sessão
    if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) return (int)$_GET['user_id'];
    if (isset($_SESSION['usuario_id']) && is_numeric($_SESSION['usuario_id'])) return (int)$_SESSION['usuario_id'];
    return null;
}

switch ($metric) {

    case 'meta_atingida_percent': {
        $userId = getTargetUserId();
        if (!$userId) {
            echo json_encode(['success'=>false,'message'=>'Usuário não identificado para métrica meta_atingida_percent']);
            break;
        }

        // 1) Meta mensal ativa (faturamento) válida para hoje
        $sqlMeta = "
            SELECT valor_meta
            FROM metas_vendedores
            WHERE usuario_id = ?
              AND periodo = 'mensal'
              AND tipo_meta = 'faturamento'
              AND ativo = 1
              AND data_inicio <= CURDATE()
              AND (data_fim IS NULL OR data_fim >= CURDATE())
            ORDER BY data_inicio DESC
            LIMIT 1
        ";
        $stmt = $conn->prepare($sqlMeta);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $resMeta = $stmt->get_result();
        $rowMeta = $resMeta ? $resMeta->fetch_assoc() : null;
        $stmt->close();

        $meta = $rowMeta ? (float)$rowMeta['valor_meta'] : 0.0;

        // 2) Faturamento do mês do vendedor
        $sqlFat = "
            SELECT COALESCE(SUM(valor),0) AS valor
            FROM vendas
            WHERE status = 'concluida'
              AND usuario_id = ?
              AND MONTH(data_venda) = MONTH(CURRENT_DATE())
              AND YEAR(data_venda) = YEAR(CURRENT_DATE())
        ";
        $stmt = $conn->prepare($sqlFat);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $resFat = $stmt->get_result();
        $rowFat = $resFat ? $resFat->fetch_assoc() : null;
        $stmt->close();

        $faturamentoMes = $rowFat ? (float)$rowFat['valor'] : 0.0;

        // 3) Percentual
        $percent = ($meta > 0) ? round(($faturamentoMes / $meta) * 100, 1) : 0.0;

        echo json_encode(['success'=>true,'value'=>$percent]);
        break;
    }

    case 'total_clients':
        $res = $conn->query("SELECT COUNT(*) as total FROM clientes");
        $row = $res->fetch_assoc();
        echo json_encode(['success'=>true,'value'=>intval($row['total'])]);
        break;

    case 'vendas_mes':
        $res = $conn->query("SELECT COUNT(*) as total FROM vendas WHERE status='concluida' AND MONTH(data_venda)=MONTH(CURRENT_DATE()) AND YEAR(data_venda)=YEAR(CURRENT_DATE())");
        $row = $res->fetch_assoc();
        echo json_encode(['success'=>true,'value'=>intval($row['total'])]);
        break;

    case 'valor_mes':
        $res = $conn->query("SELECT COALESCE(SUM(valor),0) as valor FROM vendas WHERE status='concluida' AND MONTH(data_venda)=MONTH(CURRENT_DATE()) AND YEAR(data_venda)=YEAR(CURRENT_DATE())");
        $row = $res->fetch_assoc();
        echo json_encode(['success'=>true,'value'=>floatval($row['valor'])]);
        break;

    case 'taxa_fechamento':
        $res = $conn->query("SELECT COUNT(*) as total, COUNT(CASE WHEN status='concluida' THEN 1 END) as concluidas FROM vendas");
        $row = $res->fetch_assoc();
        $total = intval($row['total']);
        $concl = intval($row['concluidas']);
        $taxa = $total>0?round(($concl*100)/$total,1):0;
        echo json_encode(['success'=>true,'value'=>$taxa]);
        break;

    case 'clientes_inativos':
        $res = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE ultima_venda IS NULL OR ultima_venda < DATE_SUB(CURRENT_DATE(), INTERVAL 30 DAY)");
        $row = $res->fetch_assoc();
        echo json_encode(['success'=>true,'value'=>intval($row['total'])]);
        break;

    case 'total_negociacoes':
        $res = $conn->query("SELECT COUNT(*) as total FROM vendas");
        $row = $res->fetch_assoc();
        echo json_encode(['success'=>true,'value'=>intval($row['total'])]);
        break;

    case 'vendas_6meses':
        $vals = [];
        for ($i=5;$i>=0;$i--) {
            $m = date('m', strtotime("-{$i} month"));
            $y = date('Y', strtotime("-{$i} month"));
            $res = $conn->query("SELECT COALESCE(SUM(valor),0) as valor FROM vendas WHERE status='concluida' AND MONTH(data_venda)={$m} AND YEAR(data_venda)={$y}");
            $row = $res->fetch_assoc();
            $vals[] = floatval($row['valor']);
        }
        echo json_encode(['success'=>true,'labels'=>[ '5m','4m','3m','2m','1m','atual' ],'values'=>$vals]);
        break;

        case 'faturamento_mes': {
    $userId = getTargetUserId();
    if (!$userId) {
        echo json_encode(['success'=>false,'message'=>'Usuário não identificado para métrica faturamento_mes']);
        break;
    }

    $sql = "
        SELECT COALESCE(SUM(valor),0) AS valor
        FROM vendas
        WHERE status='concluida'
          AND usuario_id = ?
          AND MONTH(data_venda)=MONTH(CURRENT_DATE())
          AND YEAR(data_venda)=YEAR(CURRENT_DATE())
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    echo json_encode(['success'=>true,'value'=> (float)($row['valor'] ?? 0)]);
    break;
}

case 'qtd_vendas_mes': {
    $userId = getTargetUserId();
    if (!$userId) {
        echo json_encode(['success'=>false,'message'=>'Usuário não identificado para métrica qtd_vendas_mes']);
        break;
    }

    $sql = "
        SELECT COUNT(*) AS total
        FROM vendas
        WHERE status='concluida'
          AND usuario_id = ?
          AND MONTH(data_venda)=MONTH(CURRENT_DATE())
          AND YEAR(data_venda)=YEAR(CURRENT_DATE())
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    echo json_encode(['success'=>true,'value'=> (int)($row['total'] ?? 0)]);
    break;
}

case 'ticket_medio_mes': {
    $userId = getTargetUserId();
    if (!$userId) {
        echo json_encode(['success'=>false,'message'=>'Usuário não identificado para métrica ticket_medio_mes']);
        break;
    }

    $sql = "
        SELECT 
          COALESCE(SUM(valor),0) AS total_valor,
          COUNT(*) AS total_vendas
        FROM vendas
        WHERE status='concluida'
          AND usuario_id = ?
          AND MONTH(data_venda)=MONTH(CURRENT_DATE())
          AND YEAR(data_venda)=YEAR(CURRENT_DATE())
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res ? $res->fetch_assoc() : null;
    $stmt->close();

    $totalValor = (float)($row['total_valor'] ?? 0);
    $totalVendas = (int)($row['total_vendas'] ?? 0);

    $ticket = ($totalVendas > 0) ? round($totalValor / $totalVendas, 2) : 0;

    echo json_encode(['success'=>true,'value'=>$ticket]);
    break;
}

case 'clientes_perdidos_60d': {
    $sql = "
        SELECT COUNT(*) AS total
        FROM clientes
        WHERE ultima_venda IS NULL
           OR ultima_venda < DATE_SUB(CURRENT_DATE(), INTERVAL 60 DAY)
    ";
    $res = $conn->query($sql);
    $row = $res ? $res->fetch_assoc() : null;

    echo json_encode(['success'=>true,'value'=>(int)($row['total'] ?? 0)]);
    break;
}

case 'projecao_mes': {
    $userId = getTargetUserId();
    if (!$userId) {
        echo json_encode(['success'=>false,'message'=>'Usuário não identificado para métrica projecao_mes']);
        break;
    }

    // 1) Faturamento do mês (vendas concluídas)
    $sqlFat = "
        SELECT COALESCE(SUM(valor),0) AS valor
        FROM vendas
        WHERE status='concluida'
          AND usuario_id = ?
          AND MONTH(data_venda)=MONTH(CURRENT_DATE())
          AND YEAR(data_venda)=YEAR(CURRENT_DATE())
    ";
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $rowFat = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $faturamentoMes = (float)($rowFat['valor'] ?? 0);

    // 2) Dias úteis passados (inclui hoje) e total de dias úteis do mês
    $sqlDias = "
        SELECT
          SUM(CASE WHEN data BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND CURDATE()
                   THEN dia_util ELSE 0 END) AS dias_uteis_passados,
          SUM(CASE WHEN data BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())
                   THEN dia_util ELSE 0 END) AS dias_uteis_mes
        FROM calendario_uteis
        WHERE data BETWEEN DATE_FORMAT(CURDATE(), '%Y-%m-01') AND LAST_DAY(CURDATE())
    ";
    $resDias = $conn->query($sqlDias);
    $rowDias = $resDias ? $resDias->fetch_assoc() : null;

    $diasPassados = (int)($rowDias['dias_uteis_passados'] ?? 0);
    $diasMes = (int)($rowDias['dias_uteis_mes'] ?? 0);

    // 3) Projeção
    $mediaDiaria = ($diasPassados > 0) ? ($faturamentoMes / $diasPassados) : 0;
    $projecao = ($diasMes > 0) ? round($mediaDiaria * $diasMes, 2) : 0;

    echo json_encode(['success'=>true,'value'=>$projecao]);
    break;
}

case 'necessario_por_dia': {
    $userId = getTargetUserId();
    if (!$userId) {
        echo json_encode(['success'=>false,'message'=>'Usuário não identificado para métrica necessario_por_dia']);
        break;
    }

    // 1) Meta mensal ativa
    $sqlMeta = "
        SELECT valor_meta
        FROM metas_vendedores
        WHERE usuario_id = ?
          AND periodo = 'mensal'
          AND tipo_meta = 'faturamento'
          AND ativo = 1
          AND data_inicio <= CURDATE()
          AND (data_fim IS NULL OR data_fim >= CURDATE())
        ORDER BY data_inicio DESC
        LIMIT 1
    ";
    $stmt = $conn->prepare($sqlMeta);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $rowMeta = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $meta = (float)($rowMeta['valor_meta'] ?? 0);

    if ($meta <= 0) {
        echo json_encode(['success'=>true,'value'=>0]);
        break;
    }

    // 2) Faturamento do mês (concluídas)
    $sqlFat = "
        SELECT COALESCE(SUM(valor),0) AS valor
        FROM vendas
        WHERE status='concluida'
          AND usuario_id = ?
          AND MONTH(data_venda)=MONTH(CURRENT_DATE())
          AND YEAR(data_venda)=YEAR(CURRENT_DATE())
    ";
    $stmt = $conn->prepare($sqlFat);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $rowFat = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    $faturamentoMes = (float)($rowFat['valor'] ?? 0);

    $faltante = $meta - $faturamentoMes;
    if ($faltante <= 0) {
        echo json_encode(['success'=>true,'value'=>0]);
        break;
    }

    // 3) Dias úteis restantes (a partir de amanhã até fim do mês)
    $sqlRestantes = "
        SELECT SUM(dia_util) AS dias_uteis_restantes
        FROM calendario_uteis
        WHERE data > CURDATE()
          AND data <= LAST_DAY(CURDATE())
    ";
    $res = $conn->query($sqlRestantes);
    $row = $res ? $res->fetch_assoc() : null;
    $diasRestantes = (int)($row['dias_uteis_restantes'] ?? 0);

    if ($diasRestantes <= 0) {
        echo json_encode(['success'=>true,'value'=>0]);
        break;
    }

    $necessario = round($faltante / $diasRestantes, 2);
    echo json_encode(['success'=>true,'value'=>$necessario]);
    break;
}

        
    default:
        echo json_encode(['success'=>false,'message'=>'Métrica não encontrada']);
}

$conn->close();
