<?php
// api/dashboard_stats.php
header('Content-Type: application/json; charset=utf-8');
session_start();

require_once __DIR__ . '/../includes/config.php';

$conn = getConnection();
if (!$conn) {
  echo json_encode(['success' => false, 'message' => 'Sem conexão com o banco']);
  exit;
}

$metric = $_GET['metric'] ?? '';
$metric = trim($metric);

if ($metric === '') {
  echo json_encode(['success' => false, 'message' => 'Métrica não informada']);
  exit;
}

// --- filtro por usuário (vendedor) ---
$usuarioId = $_SESSION['usuario_id'] ?? null;
$perfil    = $_SESSION['perfil'] ?? '';

$isVendedor = ($perfil === 'vendedor' && !empty($usuarioId));
$hasUsuarioIdColumn = false;
try {
  $colRes = $conn->query("SHOW COLUMNS FROM vendas LIKE 'usuario_id'");
  if ($colRes && $colRes->num_rows > 0) $hasUsuarioIdColumn = true;
} catch (Throwable $e) {
  $hasUsuarioIdColumn = false;
}

if ($isVendedor && !$hasUsuarioIdColumn) {
  // Sem coluna de usuÃ¡rio nas vendas, nÃ£o filtrar para vendedor.
  $isVendedor = false;
}
$userWhere  = $isVendedor ? " AND v.usuario_id = ? " : "";
$userParamTypes = $isVendedor ? "i" : "";
$userParamValues = $isVendedor ? [$usuarioId] : [];

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
  // METAS (se você tiver)
  // =========================
  // Se você tiver uma tabela metas por vendedor, você pluga aqui.
  // Por enquanto: meta mensal fixa 0 (ou define um número pra testar).
  $metaMensal = 0.0;

  // Exemplo rápido pra testar:
  // $metaMensal = 12000.0;

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
        WHERE YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();
      out_value((float)($res['avg_val'] ?? 0));
    }

    case 'clientes_perdidos_60d': {
      // clientes sem venda concluída há 60 dias
      // (se for vendedor, considera clientes que já tiveram venda desse vendedor)
      if ($isVendedor) {
        $sql = "
          SELECT COUNT(DISTINCT c.id) AS total
          FROM clientes c
          JOIN vendas v ON v.cliente_id = c.id
          WHERE v.usuario_id = ?
          GROUP BY v.usuario_id
        ";
        // Para “perdidos 60d” por vendedor de verdade, usa última venda concluída dele:
        $sql = "
          SELECT COUNT(*) AS total
          FROM (
            SELECT c.id,
                   MAX(CASE WHEN v.status='concluida' THEN v.data_venda ELSE NULL END) AS ultima_concluida
            FROM clientes c
            JOIN vendas v ON v.cliente_id = c.id
            WHERE v.usuario_id = ?
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
          AND YEAR(v.data_venda)=YEAR(CURDATE())
          AND MONTH(v.data_venda)=MONTH(CURDATE())
          AND v.data_venda <= CURDATE()
          $userWhere
      ";
      $stmt = $conn->prepare($sql);
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();

      $totalAteHoje = (float)($res['total'] ?? 0);
      $diaAtual = (int)date('j');
      $diasNoMes = (int)date('t');

      $proj = ($diaAtual > 0) ? ($totalAteHoje / $diaAtual) * $diasNoMes : 0;
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();

      $fat = (float)($res['total'] ?? 0);
      $pct = ($metaMensal > 0) ? ($fat / $metaMensal) * 100 : 0;
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
      if ($isVendedor) $stmt->bind_param($userParamTypes, ...$userParamValues);
      $stmt->execute();
      $res = $stmt->get_result()->fetch_assoc();

      $fat = (float)($res['total'] ?? 0);
      $faltam = max(0.0, $metaMensal - $fat);

      $diaAtual = (int)date('j');
      $diasNoMes = (int)date('t');
      $diasRestantes = max(1, $diasNoMes - $diaAtual);

      out_value($faltam / $diasRestantes);
    }

    default:
      echo json_encode(['success' => false, 'message' => 'Métrica não implementada: ' . $metric]);
      exit;
  }

} catch (Throwable $e) {
  echo json_encode(['success' => false, 'message' => 'Erro: ' . $e->getMessage()]);
  exit;
}
