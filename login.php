<?php
session_start();
require_once 'includes/config.php';

// Se já estiver logado, redirecionar para dashboard
if (isset($_SESSION['usuario_id']) && isset($_SESSION['perfil'])) {
    header('Location: index.php');
    exit;
}

$erro = '';         

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = cleanData($_POST['usuario'] ?? '');
    $senha = cleanData($_POST['senha'] ?? '');
    
    if (empty($usuario) || empty($senha)) {
        $erro = 'Usuário e senha são obrigatórios';
    } else {
        $conn = getConnection();
        
        if ($conn) {
            // Buscar usuário
            $sql = "SELECT * FROM usuarios WHERE (usuario = ? OR email = ?) AND status = 'ativo'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $usuario, $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($user = $result->fetch_assoc()) {
                // Verificar senha
                if (password_verify($senha, $user['senha'])) {
                    // Registrar log de acesso
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    $so = 'Desconhecido';
                    
                    // Detectar sistema operacional
                    if (strpos($user_agent, 'Windows') !== false) $so = 'Windows';
                    elseif (strpos($user_agent, 'Mac') !== false) $so = 'macOS';
                    elseif (strpos($user_agent, 'Linux') !== false) $so = 'Linux';
                    elseif (strpos($user_agent, 'Android') !== false) $so = 'Android';
                    elseif (strpos($user_agent, 'iOS') !== false) $so = 'iOS';
                    
                    // Registrar log
                    $sql_log = "INSERT INTO logs_acesso (usuario_id, ip, navegador, sistema_operacional, acao, sucesso) 
                                VALUES (?, ?, ?, ?, 'login', TRUE)";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->bind_param("isss", $user['id'], $ip, $user_agent, $so);
                    $stmt_log->execute();
                    
                    // Atualizar último login
                    $sql_update = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
                    $stmt_update = $conn->prepare($sql_update);
                    $stmt_update->bind_param("i", $user['id']);
                    $stmt_update->execute();
                    
                    // Criar sessão
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nome'] = $user['nome'];
                    $_SESSION['usuario_email'] = $user['email'];
                    $_SESSION['perfil'] = $user['perfil'];
                    $_SESSION['login_time'] = time();
                    
                    // Criar sessão ativa
                    $session_id = session_id();
                    $sql_sessao = "INSERT INTO sessoes_ativas (id, usuario_id, ip, user_agent) VALUES (?, ?, ?, ?)
                                   ON DUPLICATE KEY UPDATE data_ultima_atividade = NOW()";
                    $stmt_sessao = $conn->prepare($sql_sessao);
                    $stmt_sessao->bind_param("siss", $session_id, $user['id'], $ip, $user_agent);
                    $stmt_sessao->execute();
                    
                    $conn->close();
                    
                    // Redirecionar
                    header('Location: index.php');
                    exit;
                } else {
                    // Senha incorreta - registrar tentativa falha
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    
                    $sql_log = "INSERT INTO logs_acesso (usuario_id, ip, navegador, acao, sucesso) 
                                VALUES (?, ?, ?, 'login_falha', FALSE)";
                    $stmt_log = $conn->prepare($sql_log);
                    $stmt_log->bind_param("iss", $user['id'], $ip, $user_agent);
                    $stmt_log->execute();
                    
                    $erro = 'Usu+�rio ou senha incorretos';
                }
            } else {
                $erro = 'Usu+�rio ou senha incorretos';
            }
            
            $conn->close();
        } else {
            $erro = 'Erro de conex+�o com o banco de dados';
        }
    }
}

function registrarLogin($conn, $usuario_id) {
    // Atualizar ultimo login
    $sql = "UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $usuario_id);
    mysqli_stmt_execute($stmt);

    // Registrar sessao
    $session_id = session_id();
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    $sql = "INSERT INTO usuarios_sessoes (usuario_id, session_id, ip_address, user_agent, ultima_atividade, criado_em)
            VALUES (?, ?, ?, ?, NOW(), NOW())
            ON DUPLICATE KEY UPDATE ultima_atividade = NOW()";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "isss", $usuario_id, $session_id, $ip_address, $user_agent);
    mysqli_stmt_execute($stmt);

    // Registrar log
    $sql = "INSERT INTO usuarios_logs (usuario_id, tipo, ip_address, user_agent, detalhes)
            VALUES (?, 'login', ?, ?, 'Login realizado com sucesso')";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "iss", $usuario_id, $ip_address, $user_agent);
    mysqli_stmt_execute($stmt);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM TAPEMAG - Login</title>
    <link rel="icon" type="image/png" href="assets/images/logo-tapemag.png" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" href="assets/images/logo-tapemag2.png" media="(prefers-color-scheme: dark)">
    <link rel="stylesheet" href="login.css">
    <style>
        /* Estilos adicionais específicos para PHP */
        .error-message {
            background-color: rgba(255, 0, 0, 0.1);
            color: #d10101;
            padding: 12px;
            margin: 20px 48px;
            border-radius: 4px;
            font-size: 14px;
            border: 1px solid rgba(255, 0, 0, 0.2);
            text-align: center;
        }
        
        .error-message:empty {
            display: none;
        }

        /* Ajuste para manter o usuário logado */
        .field-checkbox {
            margin-top: 10px;
        }
        
        /* Melhorias na responsividade */
        @media (max-width: 480px) {
            .padding-horizontal--48 {
                padding: 24px;
            }
            
            .formbg {
                margin: 0 20px;
            }
        }

        /* Link de recuperação de senha */
        .reset-pass a {
            color: #d10101;
        }
        
        .reset-pass a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-root">
        <div class="box-root flex-flex flex-direction--column" style="min-height: 100vh;flex-grow: 1;">
            <div class="loginbackground box-background--white padding-top--64">
                <div class="loginbackground-gridContainer">
                    <div class="box-root flex-flex" style="grid-area: top / start / 8 / end;">
                        <div class="box-root" style="background-image: linear-gradient(white 0%, rgb(247, 250, 252) 33%); flex-grow: 1;">
                        </div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 4 / 2 / auto / 5;">
                        <div class="box-root box-divider--light-all-2 animationLeftRight tans3s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 6 / start / auto / 2;">
                        <div class="box-root box-background--blue800" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 7 / start / auto / 4;">
                        <div class="box-root box-background--blue animationLeftRight" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 8 / 4 / auto / 6;">
                        <div class="box-root box-background--gray100 animationLeftRight tans3s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 2 / 15 / auto / end;">
                        <div class="box-root box-background--cyan200 animationRightLeft tans4s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 3 / 14 / auto / end;">
                        <div class="box-root box-background--blue animationRightLeft" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 4 / 17 / auto / 20;">
                        <div class="box-root box-background--gray100 animationRightLeft tans4s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 5 / 14 / auto / 17;">
                        <div class="box-root box-divider--light-all-2 animationRightLeft tans3s" style="flex-grow: 1;"></div>
                    </div>
                </div>
            </div>
            
            <div class="box-root padding-top--24 flex-flex flex-direction--column" style="flex-grow: 1; z-index: 9;">
                <div class="box-root padding-top--48 padding-bottom--24 flex-flex flex-justifyContent--center">
                    <h1><a href="login.php" rel="dofollow" style="color: #d10101;">CRM TAPEMAG</a></h1>
                </div>
                
                <?php if ($erro): ?>
                <div class="error-message">
                    <?php echo htmlspecialchars($erro); ?>
                </div>
                <?php endif; ?>
                
                <div class="formbg-outer">
                    <div class="formbg">
                        <div class="formbg-inner padding-horizontal--48">
                            <span class="padding-bottom--15">Entre na sua conta</span>
                            <form method="POST" action="">
                                <div class="field padding-bottom--24">
                                    <label for="usuario"></label>
                                    <!-- No seu login.php/html -->
                                    <input type="text" id="usuario" name="usuario" placeholder="Digite seu usuário ou e-mail">  <!-- Removido autofocus -->
                                </div>
                                
                                <div class="field padding-bottom--24">
                                    <div class="grid--50-50">
                                        <label for="senha"></label>
                                        
                                    </div>
                                    <input type="password" id="senha" name="senha" 
                                           placeholder="Digite sua senha" required>
                                </div>
                                
                                <div class="field field-checkbox padding-bottom--24 flex-flex justify-content--center align-center">
                                    <label for="lembrar">
                                        <input type="checkbox" id="lembrar" name="lembrar">
                                        Permanecer conectado
                                    </label>
                                    
                                </div>
                                
                                <div class="field padding-bottom--24">
                                    <input type="submit" name="submit" value="Entrar no Sistema">
                                </div>

                                <div class="signup-link padding-top--16">
                                <span>Ainda não tem cadastro? <a href="cadastro.php" style="color: #d10101;">Crie sua conta</a></span>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <div class="footer-link padding-top--24">
                        <div class="listing padding-top--24 padding-bottom--24 flex-flex center-center">
                            <span><a href="#">Sistema de Gestão de Vendas</a></span>
                            <span><a href="#">Suporte</a></span>
                            <span><a href="#">® TAPEMAG</a></span>
                        </div>
                        <div class="listing padding-bottom--24 flex-flex center-center">
                            <span>Em caso de problemas, entre em contato com o administrador.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Foco automático no campo de usuário
        document.getElementById('usuario').focus();
        
        // Mostrar/ocultar senha
        const senhaInput = document.getElementById('senha');
        const toggleSenha = document.createElement('span');
        toggleSenha.innerHTML = '👁️';
        toggleSenha.style.position = 'absolute';
        toggleSenha.style.right = '10px';
        toggleSenha.style.top = '50%';
        toggleSenha.style.transform = 'translateY(-50%)';
        toggleSenha.style.cursor = 'pointer';
        toggleSenha.style.fontSize = '18px';
        
        // Adicionar wrapper ao campo senha
        const senhaWrapper = document.createElement('div');
        senhaWrapper.style.position = 'relative';
        senhaInput.parentNode.insertBefore(senhaWrapper, senhaInput);
        senhaWrapper.appendChild(senhaInput);
        senhaWrapper.appendChild(toggleSenha);
        
        toggleSenha.addEventListener('click', function() {
            if (senhaInput.type === 'password') {
                senhaInput.type = 'text';
                this.innerHTML = '👁️';
            } else {
                senhaInput.type = 'password';
                this.innerHTML = '🙈';
            }
        });

        // Prevenir múltiplos envios do formulário
        const form = document.querySelector('form');
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('input[type="submit"]');
            submitBtn.value = 'Entrando...';
            submitBtn.disabled = true;
        });

        // Após autenticação bem-sucedida
</script>
</body>
</html>

