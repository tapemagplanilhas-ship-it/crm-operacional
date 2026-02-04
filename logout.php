<?php
require_once 'includes/config.php';

// Garantir sessão ativa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Registrar log e limpar sessão no banco (se houver usuário)
if (isset($_SESSION['usuario_id'])) {
    $conn = getConnection();
    if ($conn) {
        try {
            // Registrar log de logout
            $sql = "INSERT INTO usuarios_logs (usuario_id, tipo, ip_address, user_agent, detalhes)
                    VALUES (?, 'logout', ?, ?, 'Logout realizado')";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                mysqli_stmt_bind_param($stmt, "iss", $_SESSION['usuario_id'], $ip_address, $user_agent);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }

            // Remover sessão atual do banco
            $sql = "DELETE FROM usuarios_sessoes WHERE session_id = ?";
            $stmt = mysqli_prepare($conn, $sql);
            if ($stmt) {
                $sid = session_id();
                mysqli_stmt_bind_param($stmt, "s", $sid);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_close($stmt);
            }
        } finally {
            mysqli_close($conn);
        }
    }

    // Implementação completa da função logout
    $_SESSION = array(); // Limpa todos os dados da sessão
    
    // Se deseja matar a sessão, apague também o cookie de sessão
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy(); // Destrói a sessão
}

// Redirecionar para login
header('Location: login.php');
exit;
?>