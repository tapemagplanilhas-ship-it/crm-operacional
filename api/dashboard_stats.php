

<?php
// api/dashboard_stats.php
header('Content-Type: application/json; charset=utf-8');
session_start();

date_default_timezone_set('America/Sao_Paulo');

$periodo = $_GET['periodo'] ?? 'mensal';
$periodo = strtolower(trim($periodo));
if (!in_array($periodo, ['diario', 'semanal', 'mensal'], true)) {
  $periodo = 'mensal';
}


function get_period_range(string $periodo): array {
  $today = new DateTimeImmutable('today');

  if ($periodo === 'diario') {
    $start = $today;
    $end   = $today;
  } elseif ($periodo === 'semanal') {
    // semana: segunda a domingo (mas depois a gente usa calendario pra dias úteis)
    $start = $today->modify('monday this week');
    $end   = $today->modify('sunday this week');
  } else { // mensal
    $start = $today->modify('first day of this month');
    $end   = $today->modify('last day of this month');
  }

  return [
    'start' => $start->format('Y-m-d'),
    'end'   => $end->format('Y-m-d'),
    'today' => $today->format('Y-m-d'),
  ];
}

$range = get_period_range($periodo);
$startDate = $range['start'];
$endDate   = $range['end'];
$todayDate = $range['today'];


require_once __DIR__ . '/../includes/config.php';

$conn = getConnection();
if (!$conn) {
  echo json_encode([
    'success' => false,
    'message' => 'Falha ao conectar no banco de dados.'
  ]);
  exit;
}

$metric = $_GET['metric'] ?? '';
$metric = trim($metric);

if ($metric === '') {
  echo json_encode([
    'success' => false,
    'message' => 'Parâmetro metric é obrigatório.'
  ]);
  exit;
}

// --- filtro por usuario (escopo do usuario logado) ---
$usuarioId = $_SESSION['usuario_id'] ?? null;
$perfilRaw = $_SESSION['perfil'] ?? '';
$perfil    = strtolower(trim((string)$perfilRaw));

// Permite escopo geral apenas para admin/gerencia.
$scope = strtolower(trim((string)($_GET['scope'] ?? '')));
$allowAllScope = ($scope === 'all' && in_array($perfil, ['admin', 'gerencia'], true));

// Permite filtrar por vendedor especifico (apenas admin/gerencia).
$requestedVendedorId = isset($_GET['vendedor_id']) ? (int)$_GET['vendedor_id'] : 0;
$allowVendorScope = ($requestedVendedorId > 0 && in_array($perfil, ['admin', 'gerencia'], true));
if ($allowVendorScope) {
  $usuarioId = $requestedVendedorId;
  $allowAllScope = false;
}

// Filtra por usuario sempre que houver um usuario logado, a menos que o escopo geral seja permitido.
$isVendedor  = ($perfil === 'vendedor' && !empty($usuarioId));
$filterByUser = (!empty($usuarioId) && !$allowAllScope);

// Helper seguro para bind_param com array (bind_param exige referências)
function stmt_bind_params(mysqli_stmt $stmt, string $types, array $params): void {
  if ($types === '' || empty($params)) return;
  $refs = [];
  foreach ($params as $k => $v) {
    $refs[$k] = &$params[$k];
  }
  array_unshift($refs, $types);
  call_user_func_array([$stmt, 'bind_param'], $refs);
}

// Detectar coluna de vinculo do vendedor na tabela vendas
$vendaUserCol = null;
$possibleCols = ['usuario_id', 'vendedor_id', 'id_vendedor'];

foreach ($possibleCols as $col) {
  try {
    $colRes = $conn->query("SHOW COLUMNS FROM vendas LIKE '{$col}'");
    if ($colRes && $colRes->num_rows > 0) {
      $vendaUserCol = $col;
      break;
    }
  } catch (Throwable $e) {}
}

if ($filterByUser && !$vendaUserCol) {
  // Se ha usuario e nao existe coluna para filtrar, melhor nao "juntar tudo"
  echo json_encode([
    'success' => false,
    'message' => "Nao encontrei coluna de vendedor na tabela vendas. Crie 'usuario_id' ou 'vendedor_id'."
  ]);
  exit;
}

$userWhere = ($filterByUser && $vendaUserCol) ? " AND v.{$vendaUserCol} = ? " : "";
$userParamTypes = ($filterByUser && $vendaUserCol) ? "i" : "";
$userParamValues = ($filterByUser && $vendaUserCol) ? [$usuarioId] : [];

// helpers
function out_value($value) {
  echo json_encode(['success' => true, 'value' => $value]);
  exit;
}
function out_chart($labels, $values) {
  echo json_encode(['success' => true, 'labels' => $labels, 'values' => $values]);
  exit;
}

try {

  // =========================
  // METAS (gestão)
  // =========================
  $metaMensal = 0.0;
  try {
    $hasMetas = $conn->query("SHOW TABLES LIKE 'metas_vendedor_mensal'");
    if ($hasMetas && $hasMetas->num_rows > 0) {
      $anoAtual = (int)date('Y');
      $mesAtual = (int)date('n');

      if ($filterByUser) {
        $sqlMeta = "
          SELECT COALESCE(meta_valor,0) AS meta
          FROM metas_vendedor_mensal
          WHERE vendedor_id = ? AND ano = ? AND mes = ?
          LIMIT 1
        ";
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param('iii', $usuarioId, $anoAtual, $mesAtual);
      } else {
        $sqlMeta = "
          SELECT COALESCE(SUM(meta_valor),0) AS meta
          FROM metas_vendedor_mensal
          WHERE ano = ? AND mes = ?
        ";
        $stmtMeta = $conn->prepare($sqlMeta);
        $stmtMeta->bind_param('ii', $anoAtual, $mesAtual);
      }

      $stmtMeta->execute();
      $metaRow = $stmtMeta->get_result()->fetch_assoc();
      $metaMensal = (float)($metaRow['meta'] ?? 0);
    }
  } catch (Throwable $e) {
    $metaMensal = 0.0;
  }

  switch ($metric) {

    // =========================
    // MÉTRICAS ANTIGAS
    // =========================
    case 'total_clients': {
      // total de clientes (não depende de vendas)
      $sql = "SELECT COUNT(*) AS total FROM clientes";
      $r = $conn->query($sql);
      $row = $r ? $r->fetch_assoc() : null;
      out_value((int)($row['total'] ?? 0));
    }

    case 'vendas_mes': {
      // conta vendas do mês (todas) - se quiser só concluídas, adiciona status = 'concluida'
      $sql = "
      SELECT COUNT(*) AS total
          FROM vendas v
          WHERE v.data_venda BETWEEN ? AND ?
            $userWhere
        ";
        $stmt = $conn->prepare($sql);

        $types = "ss" . $userParamTypes;
        $params = array_merge([$startDate, $endDate], $userParamValues);

        stmt_bind_params($stmt, $types, $params);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        out_value((int)($res['total'] ?? 0));
        }
    case 'valor_mes': {
      // soma do mês (concluídas)
      $sql = "
        SELECT COALESCE(SUM(v.valor),0) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['total'] ?? 0));
    }

    case 'taxa_fechamento': {
      // % concluída no mês (concluida / total)
      $sql = "
        SELECT
          COUNT(*) AS total,
          SUM(CASE WHEN v.status='concluida' THEN 1 ELSE 0 END) AS concluidas
        FROM vendas v
        WHERE YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();

      $total = (int)($res['total'] ?? 0);
      $ok    = (int)($res['concluidas'] ?? 0);
      $pct   = $total > 0 ? ($ok / $total) * 100 : 0;
      out_value(round($pct, 1));
    }

    case 'clientes_inativos': {
      // clientes sem compra concluída nos últimos 30 dias (baseado em ultima_venda)
      $sql = "
        SELECT COUNT(*) AS total
        FROM clientes
        WHERE (ultima_venda IS NULL OR ultima_venda < DATE_SUB(CURDATE(), INTERVAL 30 DAY))
      ";
      $r = $conn->query($sql);
      $row = $r ? $r->fetch_assoc() : null;
      out_value((int)($row['total'] ?? 0));
    }

    case 'total_negociacoes': {
      // total de vendas (qualquer status) no mês
      $sql = "
        SELECT COUNT(*) AS total
        FROM vendas v
        WHERE YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((int)($res['total'] ?? 0));
    }

    case 'vendas_6meses': {
      // gráfico: soma de concluídas por mês (últimos 6 meses)
      $sql = "
        SELECT
          DATE_FORMAT(v.data_venda, '%m/%Y') AS mes,
          COALESCE(SUM(CASE WHEN v.status='concluida' THEN v.valor ELSE 0 END),0) AS total
        FROM vendas v
        WHERE v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
          $userWhere
        GROUP BY YEAR(v.data_venda), MONTH(v.data_venda)
        ORDER BY YEAR(v.data_venda), MONTH(v.data_venda)
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $rs = $stmt->get_result();

      $labels = [];
      $values = [];
      while ($row = $rs->fetch_assoc()) {
        $labels[] = $row['mes'];
        $values[] = (float)$row['total'];
      }

      out_chart($labels, $values);
    }

    // =========================
    // MÉTRICAS NOVAS
    // =========================

    case 'faturamento_mes': {
      // igual valor_mes (concluídas)
      $sql = "
        SELECT COALESCE(SUM(v.valor),0) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['total'] ?? 0));
    }

    case 'qtd_vendas_mes': {
      $sql = "
        SELECT COUNT(*) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((int)($res['total'] ?? 0));
    }

    case 'ticket_medio_mes': {
      $sql = "
        SELECT COALESCE(AVG(v.valor),0) AS avg_val
        FROM vendas v
        WHERE v.status='concluida'
          AND YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['avg_val'] ?? 0));
    }

    case 'faturamento_dia': {
      $sql = "
        SELECT COALESCE(SUM(v.valor),0) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND DATE(v.data_venda)=CURDATE()
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['total'] ?? 0));
    }

    case 'qtd_vendas_dia': {
      $sql = "
        SELECT COUNT(*) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND DATE(v.data_venda)=CURDATE()
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((int)($res['total'] ?? 0));
    }

    case 'ticket_medio_dia': {
      $sql = "
        SELECT COALESCE(AVG(v.valor),0) AS avg_val
        FROM vendas v
        WHERE v.status='concluida'
          AND DATE(v.data_venda)=CURDATE()
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['avg_val'] ?? 0));
    }

    case 'faturamento_semana': {
      $sql = "
        SELECT COALESCE(SUM(v.valor),0) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND YEARWEEK(v.data_venda, 1)=YEARWEEK(CURDATE(), 1)
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['total'] ?? 0));
    }

    case 'qtd_vendas_semana': {
      $sql = "
        SELECT COUNT(*) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND YEARWEEK(v.data_venda, 1)=YEARWEEK(CURDATE(), 1)
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((int)($res['total'] ?? 0));
    }

    case 'ticket_medio_semana': {
      $sql = "
        SELECT COALESCE(AVG(v.valor),0) AS avg_val
        FROM vendas v
        WHERE v.status='concluida'
          AND YEARWEEK(v.data_venda, 1)=YEARWEEK(CURDATE(), 1)
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['avg_val'] ?? 0));
    }

    case 'clientes_perdidos_60d': {
      // clientes sem venda concluída há 60 dias
      // (se for vendedor, considera clientes que já tiveram venda desse vendedor)
      if ($filterByUser) {
        // usa a coluna detectada (usuario_id / vendedor_id / id_vendedor)
        $col = $vendaUserCol ?: 'usuario_id';
        $sql = "
          SELECT COUNT(DISTINCT c.id) AS total
          FROM clientes c
          JOIN vendas v ON v.cliente_id = c.id
          WHERE v.{$col} = ?
          GROUP BY v.{$col}
        ";
        // Para “perdidos 60d” por vendedor de verdade, usa última venda concluída dele:
        $sql = "
          SELECT COUNT(*) AS total
          FROM (
            SELECT c.id,
                   MAX(CASE WHEN v.status='concluida' THEN v.data_venda ELSE NULL END) AS ultima_concluida
            FROM clientes c
            JOIN vendas v ON v.cliente_id = c.id
            WHERE v.{$col} = ?
            GROUP BY c.id
          ) x
          WHERE (x.ultima_concluida IS NULL OR x.ultima_concluida < DATE_SUB(CURDATE(), INTERVAL 60 DAY))
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $usuarioId);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        out_value((int)($res['total'] ?? 0));
      } else {
        $sql = "
          SELECT COUNT(*) AS total
          FROM clientes
          WHERE (ultima_venda IS NULL OR ultima_venda < DATE_SUB(CURDATE(), INTERVAL 60 DAY))
        ";
        $r = $conn->query($sql);
        $row = $r ? $r->fetch_assoc() : null;
        out_value((int)($row['total'] ?? 0));
      }
    }

    case 'projecao_mes': {
      // projeção simples: (faturamento até hoje / dia do mês) * total dias do mês
      $sql = "
  SELECT COALESCE(SUM(v.valor),0) AS total
  FROM vendas v
  WHERE v.status='concluida'
    AND v.data_venda BETWEEN ? AND ?
    AND v.data_venda <= ?
    $userWhere
";
$stmt = $conn->prepare($sql);

$types = "sss" . $userParamTypes;
$params = array_merge([$startDate, $endDate, $todayDate], $userParamValues);

stmt_bind_params($stmt, $types, $params);
$stmt->execute();
$res = $stmt->get_result()->fetch_assoc();

$totalAteHoje = (float)($res['total'] ?? 0);

// dias corridos do período (por enquanto; dias úteis é o passo 2)
$start = new DateTimeImmutable($startDate);
$end   = new DateTimeImmutable($endDate);
$today = new DateTimeImmutable($todayDate);

$passados = max(1, (int)$start->diff($today)->days + 1); // inclui hoje
$totalDiasPeriodo = max(1, (int)$start->diff($end)->days + 1);

$proj = ($totalAteHoje / $passados) * $totalDiasPeriodo;
out_value((float)$proj);
    }

    case 'meta_atingida_percent': {
      // se metaMensal for 0, retorna 0 pra não dividir por zero
      if ($metaMensal <= 0) out_value(0);

      // faturamento atual
      $sql = "
        SELECT COALESCE(SUM(v.valor),0) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();

      $fat = (float)($res['total'] ?? 0);
      $pct = ($metaMensal > 0) ? ($fat / $metaMensal) * 100 : 0;
      out_value(round($pct, 1));
    }

    case 'meta_mes': {
      out_value((float)$metaMensal);
    }

    case 'meta_dia': {
      if ($metaMensal <= 0) out_value(0);

      $diasNoMes = (int)date('t');
      $metaDia = $diasNoMes > 0 ? ($metaMensal / $diasNoMes) : 0;
      out_value(round($metaDia, 2));
    }

    case 'meta_atingida_percent_dia': {
      if ($metaMensal <= 0) out_value(0);

      $diasNoMes = (int)date('t');
      $metaDia = $diasNoMes > 0 ? ($metaMensal / $diasNoMes) : 0;
      if ($metaDia <= 0) out_value(0);

      $sql = "
        SELECT COALESCE(SUM(v.valor),0) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND DATE(v.data_venda)=CURDATE()
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();

      $fat = (float)($res['total'] ?? 0);
      $pct = ($fat / $metaDia) * 100;
      out_value(round($pct, 1));
    }

    case 'necessario_por_dia': {
      // necessário por dia até o fim do mês (simples: dias restantes corridos)
      if ($metaMensal <= 0) out_value(0);

      $sql = "
        SELECT COALESCE(SUM(v.valor),0) AS total
        FROM vendas v
        WHERE v.status='concluida'
          AND YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($filterByUser) stmt_bind_params($stmt, $userParamTypes, $userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();

      $fat = (float)($res['total'] ?? 0);
      $faltam = max(0.0, $metaMensal - $fat);

      $diaAtual = (int)date('j');
      $diasNoMes = (int)date('t');
      $diasRestantes = max(1, $diasNoMes - $diaAtual);

      out_value($faltam / $diasRestantes);
    }

    default: {
      echo json_encode([
        'success' => false,
        'message' => 'Métrica inválida: ' . $metric
      ]);
      exit;
    }
  }

} catch (Throwable $e) {
  echo json_encode([
    'success' => false,
    'message' => 'Erro interno: ' . $e->getMessage()
  ]);
  exit;
}
