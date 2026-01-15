<?php
require_once 'includes/config.php';
verificarLogin();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acesso Negado - CRM TAPEMAG</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            padding: 20px;
        }
        
        .access-denied-container {
            text-align: center;
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
        }
        
        .access-denied-icon {
            font-size: 80px;
            color: #e74c3c;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        p {
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .btn-return {
            display: inline-block;
            padding: 12px 30px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: background 0.3s;
        }
        
        .btn-return:hover {
            background: #2980b9;
        }
        
        .user-info {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="access-denied-container">
        <div class="access-denied-icon">
            <i class="fas fa-ban"></i>
        </div>
        
        <h1>Acesso Negado</h1>
        
        <p>Você não possui permissão para acessar esta página ou recurso.</p>
        
        <p>Se você acredita que isso é um erro, entre em contato com o administrador do sistema.</p>
        
        <a href="index.php" class="btn-return">
            <i class="fas fa-arrow-left"></i> Voltar para o Dashboard
        </a>
        
        <div class="user-info">
            <p><strong>Usuário:</strong> <?php echo htmlspecialchars($_SESSION['usuario_nome'] ?? ''); ?></p>
            <p><strong>Perfil:</strong> 
                <?php 
                $perfis = [
                    'admin' => 'Administrador',
                    'gerencia' => 'Gerência', 
                    'vendedor' => 'Vendedor'
                ];
                echo $perfis[$_SESSION['perfil'] ?? ''] ?? 'Desconhecido';
                ?>
            </p>
        </div>
    </div>
</body>
</html>