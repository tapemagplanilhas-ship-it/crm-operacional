<?php

   if (empty($_SESSION['token'])) {
       $_SESSION['token'] = bin2hex(random_bytes(32));
   }
// ===============================
// Configuração do banco de dados
// ===============================
define('DB_HOST', '127.0.0.1:3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'crm_operacional');

// ===============================
// Conexão com MySQL
// ===============================
function getConnection() {
    $conn = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if (!$conn) {
        // Tentativa alternativa
        $conn = mysqli_connect('127.0.0.1', DB_USER, DB_PASS, DB_NAME, 3306);
        if (!$conn) {
            error_log("Erro MySQL: " . mysqli_connect_error());
            return false;
        }
    }
  
    mysqli_set_charset($conn, "utf8mb4");
    return $conn;
}

// ==========================================
// Atualizar métricas do cliente
// ==========================================
function atualizarMetricasCliente($cliente_id) {
    $conn = getConnection();
    if (!$conn) return false;

    // Última venda concluída
    $sql = "SELECT MAX(data_venda) AS ultima_venda
            FROM vendas
            WHERE cliente_id = ? AND status = 'concluida'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $ultima_venda = $row['ultima_venda'] ?? null;

    // Total e média das vendas concluídas
    $sql = "SELECT 
                COALESCE(SUM(valor), 0) AS total,
                COALESCE(AVG(valor), 0) AS media
            FROM vendas
            WHERE cliente_id = ? AND status = 'concluida'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $total_gasto  = $row['total'];
    $media_gastos = $row['media'];

    // Taxa de fechamento
    $sql = "SELECT 
                COUNT(*) AS total,
                COUNT(CASE WHEN status = 'concluida' THEN 1 END) AS concluidas
            FROM vendas
            WHERE cliente_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $cliente_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    $total_vendas = $row['total'];
    $concluidas   = $row['concluidas'];

    $taxa_fechamento = ($total_vendas > 0)
        ? ($concluidas * 100) / $total_vendas
        : 0;

    // Atualizar cliente
    $sql = "UPDATE clientes
            SET ultima_venda = ?,
                total_gasto = ?,
                media_gastos = ?,
                taxa_fechamento = ?
            WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param(
        "sdddi",
        $ultima_venda,
        $total_gasto,
        $media_gastos,
        $taxa_fechamento,
        $cliente_id
    );

    $success = $stmt->execute();
    $conn->close();

    return $success;
}

// ===============================
// Funções utilitárias
// ===============================
function cleanData($data) {
    if (!isset($data)) return '';
    return htmlspecialchars(stripslashes(trim($data)), ENT_QUOTES, 'UTF-8');
}

function formatDate($date, $format = 'd/m/Y') {
    if (empty($date) || $date === '0000-00-00' || $date === '0000-00-00 00:00:00') {
        return '';
    }
    try {
        return (new DateTime($date))->format($format);
    } catch (Exception $e) {
        return '';
    }
}

function formatCurrency($value) {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function parseMoneyBR($valor): float {
    $v = (string)$valor;
    $v = str_replace(['R$', ' ', "\t", "\n", "\r"], '', $v);
    $v = str_replace('.', '', $v);
    $v = str_replace(',', '.', $v);
    return (float)$v;
}

function parseDateBR(string $dataBR): ?string {
    $dataBR = trim($dataBR);
    if ($dataBR === '') return null;

    if (!preg_match('/^(\\d{2})\\/(\\d{2})\\/(\\d{4})$/', $dataBR, $m)) return null;

    $dia = (int)$m[1];
    $mes = (int)$m[2];
    $ano = (int)$m[3];

    if (!checkdate($mes, $dia, $ano)) return null;

    return sprintf('%04d-%02d-%02d', $ano, $mes, $dia);
}

// ===============================
// Configurações de desenvolvimento
// ===============================
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('America/Sao_Paulo');

// ==============================================
// FUNÇÕES DE AUTENTICAÇÃO E PERMISSÕES
// ==============================================

// Iniciar sessão se não estiver iniciada
function iniciarSessao() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

// Verificar se usuário está logado
function verificarLogin() {
    iniciarSessao();
    
    if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['perfil'])) {
        header('Location: login.php');
        exit;
    }
    
    // Verificar timeout da sessão (8 horas)
    $timeout = 8 * 60 * 60; // 8 horas em segundos
    if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $timeout)) {
        logout();
        header('Location: login.php?timeout=1');
        exit;
    }
    
    // Atualizar tempo da sessão
    $_SESSION['login_time'] = time();
    
    return true;
}

// Verificar permissão específica
function verificarPermissao($perfilRequerido) {
    iniciarSessao();
    
    if (!isset($_SESSION['perfil'])) {
        return false;
    }
    
    $perfisHierarquia = [
        'admin' => 3,
        'gerencia' => 2,
        'vendedor' => 1
    ];
    
    $perfilUsuario = $_SESSION['perfil'];
    $perfilRequeridoNivel = $perfisHierarquia[$perfilRequerido] ?? 0;
    $perfilUsuarioNivel = $perfisHierarquia[$perfilUsuario] ?? 0;
    
    return $perfilUsuarioNivel >= $perfilRequeridoNivel;
}

// Redirecionar se não tiver permissão
function requerirPermissao($perfilRequerido) {
    if (!verificarPermissao($perfilRequerido)) {
        header('Location: acesso_negado.php');
        exit;
    }
}


// Obter dados do usuário logado
function getUsuarioLogado() {
    iniciarSessao();
    
    if (!isset($_SESSION['usuario_id'])) {
        return null;
    }
    
    $conn = getConnection();
    if (!$conn) return null;
    
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $_SESSION['usuario_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    
    $conn->close();
    return $usuario;
}

// Logout
function logout() {
    iniciarSessao();
    
    // Registrar log de logout
    if (isset($_SESSION['usuario_id'])) {
        $conn = getConnection();
        if ($conn) {
            $session_id = session_id();
            $sql = "DELETE FROM sessoes_ativas WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $session_id);
            $stmt->execute();
            $conn->close();
        }
    }
    
    // Destruir sessão
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}

// Registrar log de atividade
function registrarLog($acao, $sucesso = true, $detalhes = null) {
    if (!isset($_SESSION['usuario_id'])) return;
    
    $conn = getConnection();
    if (!$conn) return;
    
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $so = 'Desconhecido';
    
    if (strpos($user_agent, 'Windows') !== false) $so = 'Windows';
    elseif (strpos($user_agent, 'Mac') !== false) $so = 'macOS';
    elseif (strpos($user_agent, 'Linux') !== false) $so = 'Linux';
    elseif (strpos($user_agent, 'Android') !== false) $so = 'Android';
    elseif (strpos($user_agent, 'iOS') !== false) $so = 'iOS';
    
    $sql = "INSERT INTO logs_acesso (usuario_id, ip, navegador, sistema_operacional, acao, sucesso) 
            VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issssi", $_SESSION['usuario_id'], $ip, $user_agent, $so, $acao, $sucesso);
    $stmt->execute();
    
    $conn->close();
}
// Configuração padrão do tema
if (!isset($_SESSION['tema'])) {
    $_SESSION['tema'] = 'claro'; // Tema padrão
}
// =============================================
// SISTEMA DE PERMISSÕES – ACESSO POR PÁGINA
// =============================================
require_once __DIR__ . "/permissoes.php";

function acessoPermitido($pagina)
{
    if (!isset($_SESSION['perfil'])) return false;

    global $permissoes;
    $perfil = $_SESSION['perfil'];

    if (!isset($permissoes[$perfil])) return false;

    return in_array($pagina, $permissoes[$perfil]);
}