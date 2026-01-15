<?php
require_once 'config.php';
iniciarSessao();

// Verificar se está logado para páginas que requerem autenticação
// Exceto para a página de login
$pagina_atual = basename($_SERVER['PHP_SELF']);
$paginas_publicas = ['login.php', 'criar_admin.php'];

if (!in_array($pagina_atual, $paginas_publicas)) {
    verificarLogin();
}

// Obter dados do usuário logado
$usuario_logado = getUsuarioLogado();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM TAPEMAG</title>
    
   <!-- Primeiro styles.css -->
<link rel="stylesheet" href="/assets/css/styles.css">

<!-- Depois sidebar.css -->
<link rel="stylesheet" href="/assets/css/sidebar.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Estilos específicos do header (agora simplificado) */
        .main-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }
        
        .header-container {
            max-width: 70%;
            margin: 0 auto;
            padding: 0 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
        }
        
        /* Logo */
        .logo-container {
            display: flex;
            align-items: center;
        }
        
        .logo-img {
            height: 30px;
            width: auto;
            object-fit: contain;
        }
        
        /* Estilos removidos que agora estão na sidebar */
    </style>
</head>
<body>
    <?php if (!$usuario_logado): ?>
    <!-- Header simplificado (apenas para páginas públicas) -->
   <header class="main-header">
    <div class="header-container">
        <div class="logo-container">
            <img src="assets/images/logo-tapemag.png" alt="CRM TAPEMAG" class="logo-img">
        </div>
    </div>
</header>
    <?php endif; ?>

    <!-- Conteúdo Principal -->
    <main class="container">