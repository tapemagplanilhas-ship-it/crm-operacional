<?php
// login.php
session_start();
require_once 'includes/config.php';

if (isset($_SESSION['usuario_id'])) {
    header('Location: index.php');
    exit;
}

$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    
    if (empty($usuario) || empty($senha)) {
        $erro = 'Usuário e senha são obrigatórios';
    } else {
        $conn = getConnection();
        
        if ($conn) {
            $sql = "SELECT id, nome, email, senha, perfil FROM usuarios 
                    WHERE (usuario = ? OR email = ?) AND status = 'ativo'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ss", $usuario, $usuario);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                if (password_verify($senha, $user['senha'])) {
                    $_SESSION['usuario_id'] = $user['id'];
                    $_SESSION['usuario_nome'] = $user['nome'];
                    $_SESSION['perfil'] = $user['perfil'];
                    
                    // Registrar log
                    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
                    
                    // $sql_log = "INSERT INTO logs_acesso (usuario_id, ip, user_agent, tipo) 
                    //             VALUES (?, ?, ?, 'login')";
                    // $stmt_log = $conn->prepare($sql_log);
                    // $stmt_log->bind_param("iss", $user['id'], $ip, $user_agent);
                    // $stmt_log->execute();
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $erro = 'Credenciais inválidas';
                }
            } else {
                $erro = 'Credenciais inválidas';
            }
            
            $conn->close();
        } else {
            $erro = 'Erro de conexão com o banco de dados';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - CRM TAPEMAG</title>
    <link rel="icon" type="image/png" href="assets/images/logo-tapemag.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <style>
        /* Estilos específicos para o ícone de senha */
        .password-wrapper {
            position: relative;
        }
        
        .password-wrapper input {
            padding-right: 40px;
            width: 100%;
        }
        
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
            padding: 5px;
        }
        
        /* Estilos para mensagens */
        .error-message {
            background-color: rgba(255, 0, 0, 0.1);
            color: #d10101;
            padding: 12px;
            margin: 0 48px 20px;
            border-radius: 4px;
            border: 1px solid rgba(255, 0, 0, 0.2);
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-root">
        <div class="box-root flex-flex flex-direction--column" style="min-height: 100vh;flex-grow: 1;">
            <!-- Fundo animado igual ao cadastro -->
            <div class="loginbackground box-background--white padding-top--64">
                <div class="loginbackground-gridContainer">
                    <div class="box-root flex-flex" style="grid-area: top / start / 8 / end;">
                        <div class="box-root" style="background-image: linear-gradient(white 0%, rgb(247, 250, 252) 33%); flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 4 / 2 / auto / 5;">
                        <div class="box-root box-divider--light-all-2 animationLeftRight tans3s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 6 / start / auto / 2;">
                        <div class="box-root box-background--blue800 animationRightLeft tans4s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 7 / start / auto / 4;">
                        <div class="box-root box-background--blue animationLeftRight tans3s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 8 / 4 / auto / 6;">
                        <div class="box-root box-background--gray100 animationRightLeft tans4s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 2 / 15 / auto / end;">
                        <div class="box-root box-background--cyan200 animationLeftRight tans3s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 3 / 14 / auto / end;">
                        <div class="box-root box-background--blue animationRightLeft tans4s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 4 / 17 / auto / 20;">
                        <div class="box-root box-background--gray100 animationLeftRight tans3s" style="flex-grow: 1;"></div>
                    </div>
                    <div class="box-root flex-flex" style="grid-area: 5 / 14 / auto / 17;">
                        <div class="box-root box-divider--light-all-2 animationRightLeft tans4s" style="flex-grow: 1;"></div>
                    </div>
                </div>
            </div>

            <div class="box-root padding-top--24 flex-flex flex-direction--column" style="flex-grow: 1; z-index: 9;">
                <div class="box-root padding-top--48 padding-bottom--24 flex-flex flex-justifyContent--center">
                    <h1><a href="index.php" style="color: #d10101;">CRM TAPEMAG</a></h1>
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
                                    <label for="usuario">Usuário ou E-mail</label>
                                    <input type="text" id="usuario" name="usuario" 
                                           placeholder="Digite seu usuário ou e-mail" 
                                           required
                                           value="<?php echo htmlspecialchars($usuario ?? ''); ?>">
                                </div>
                                
                                <div class="field padding-bottom--24">
                                    <label for="senha">Senha</label>
                                    <div class="password-wrapper">
                                        <input type="password" id="senha" name="senha" 
                                               placeholder="Digite sua senha" 
                                               required>
                                        <button type="button" class="toggle-password" onclick="togglePassword('senha')" aria-label="Mostrar senha">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                    </div>
    
                                </div>
                                
                                <div class="field field-checkbox padding-bottom--24 flex-flex align-center">
                                    <label for="lembrar">
                                        <input type="checkbox" id="lembrar" name="lembrar">
                                        Permanecer conectado
                                    </label>
                                    
                                </div>
                                
                                <div class="field padding-bottom--24">
                                    <input type="submit" name="submit" value="Entrar" class="btn-submit">
                                </div>
                            </form>
                            
                            <div class="login-link">
                                <span>Não tem uma conta? <a href="cadastro.php">Cadastre-se</a></span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer completo igual ao cadastro -->
                    <div class="footer-link padding-top--24">
                        <div class="listing padding-top--24 padding-bottom--24 flex-flex center-center">
                            <span><a href="#">Sistema de Gestão de Vendas</a></span>
                            <span><a href="#">Suporte</a></span>
                            <span><a href="#">® TAPEMAG</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    // Função para mostrar/ocultar senha
    function togglePassword(fieldId) {
        const input = document.getElementById(fieldId);
        const icon = input.nextElementSibling.querySelector('i');
        
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.replace('fa-eye', 'fa-eye-slash');
            icon.setAttribute('aria-label', 'Ocultar senha');
        } else {
            input.type = 'password';
            icon.classList.replace('fa-eye-slash', 'fa-eye');
            icon.setAttribute('aria-label', 'Mostrar senha');
        }
    }

    // Prevenir múltiplos envios
    document.querySelector('form').addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('.btn-submit');
        submitBtn.disabled = true;
        submitBtn.value = 'Entrando...';
    });
    </script>
</body>
</html>