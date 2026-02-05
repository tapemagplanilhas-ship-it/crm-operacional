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
    <link rel="icon" type="image/png" href="assets/images/logo-tapemag.png" media="(prefers-color-scheme: light)">
    <link rel="icon" type="image/png" href="assets/images/logo-tapemag2.png" media="(prefers-color-scheme: dark)">
    <link rel="stylesheet" href="assets/css/styles.css">                                                                         
   <link rel="stylesheet" href="assets/css/sidebar.css?v=<?= filemtime('assets/css/sidebar.css') ?>">
   <script src="assets/js/scripts.js?v=1"></script>



    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Estilos específicos do header */
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
        
        .logo-text {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1a202c;
            display: none;
        }
        
        @media (min-width: 768px) {
            .logo-text {
                display: block;
            }
        }
        
        /* Menu Desktop */
        .main-nav {
            display: none;
        }
        
        @media (min-width: 992px) {
            .main-nav {
                display: block;
                flex: 1;
                margin: 0 2rem;
            }
            
            .nav-menu {
                display: flex;
                list-style: none;
                gap: 4px;
                justify-content: center;
            }
            
            .nav-item {
                position: relative;
            }
            
            .nav-link {
                display: flex;
                align-items: center;
                gap: 8px;
                padding: 10px 16px;
                color: #4a5568;
                text-decoration: none;
                border-radius: 6px;
                font-size: 14px;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            
            .nav-link:hover {
                color: #2d3748;
                background-color: #edf2f7;
            }
            
            .nav-link.active {
                color: #2b6cb0;
                background-color: #ebf8ff;
                font-weight: 600;
            }
            
            .nav-link i {
                font-size: 16px;
            }
        }
        
        /* Menu do Usuário */
        .user-menu {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 22px;
        }
        
        .user-dropdown {
            position: relative;
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 8px 12px;
            border-radius: 8px;
            transition: background-color 0.2s;
            border: 1px solid transparent;
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #4299e1, #3182ce);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }
        
        .user-info {
            display: none;
            flex-direction: column;
            gap: 2px;
        }
        
        @media (min-width: 768px) {
            .user-info {
                display: flex;
            }
        }
        
        .user-name {
            font-weight: 600;
            color: #2d3748;
            font-size: 14px;
        }
        
        .user-profile {
            font-size: 12px;
            color: #718096;
        }
        
        /* Dropdown Menu */
        .dropdown-menu {
            position: absolute;
            top: 100%;
            right: 0;
            margin-top: 8px;
            min-width: 240px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            border: 1px solid #e2e8f0;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
            z-index: 1001;
        }
        
        .dropdown-menu.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }
        
        .dropdown-header {
            padding: 16px;
            border-bottom: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        .dropdown-header .user-name {
            font-weight: 700;
            color: #2d3748;
            font-size: 15px;
        }
        
        .dropdown-header .user-email {
            font-size: 13px;
            color: #718096;
            margin-top: 4px;
        }
        
        .dropdown-divider {
            height: 1px;
            background: #e2e8f0;
            margin: 8px 0;
        }
        
        .dropdown-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #4a5568;
            text-decoration: none;
            transition: background-color 0.2s;
            font-size: 14px;
        }
        
        .dropdown-item:hover {
            background-color: #f7fafc;
            color: #2d3748;
        }
        
        .dropdown-item i {
            width: 18px;
            text-align: center;
            color: #718096;
        }
        
        .dropdown-item.logout {
            color: #e53e3e;
        }
        
        .dropdown-item.logout i {
            color: #e53e3e;
        }
        
        .dropdown-item.logout:hover {
            background-color: #fff5f5;
        }
        
        /* Menu Mobile */
        .mobile-menu-toggle {
            display: flex;
            flex-direction: column;
            gap: 4px;
            padding: 10px;
            background: none;
            border: none;
            cursor: pointer;
            margin-right: -10px;
        }
        
        @media (min-width: 992px) {
            .mobile-menu-toggle {
                display: none;
            }
        }
        
        .mobile-menu-toggle span {
            width: 22px;
            height: 2px;
            background: #4a5568;
            transition: all 0.3s;
        }
        
        .mobile-menu-toggle.active span:nth-child(1) {
            transform: rotate(45deg) translate(6px, 6px);
        }
        
        .mobile-menu-toggle.active span:nth-child(2) {
            opacity: 0;
        }
        
        .mobile-menu-toggle.active span:nth-child(3) {
            transform: rotate(-45deg) translate(6px, -6px);
        }
        
        /* Mobile Menu Overlay */
        .mobile-menu-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }
        
        .mobile-menu-overlay.show {
            opacity: 1;
            visibility: visible;
        }
        
        /* Mobile Menu */
        .mobile-menu {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: 300px;
            background: white;
            z-index: 1000;
            transform: translateX(-100%);
            transition: transform 0.3s;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 15px rgba(0, 0, 0, 0.1);
        }
        
        .mobile-menu.show {
            transform: translateX(0);
        }
        
        .mobile-menu-header {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 15px;
            background: #f8fafc;
        }
        
        .mobile-menu-body {
            flex: 1;
            overflow-y: auto;
            padding: 16px 0;
        }
        
        .mobile-nav-menu {
            list-style: none;
        }
        
        .mobile-nav-item {
            border-bottom: 1px solid #f1f5f9;
        }
        
        .mobile-nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 20px;
            color: #4a5568;
            text-decoration: none;
            transition: background-color 0.2s;
        }
        
        .mobile-nav-link:hover,
        .mobile-nav-link.active {
            background-color: #edf2f7;
            color: #2b6cb0;
        }
        
        .mobile-nav-link i {
            width: 20px;
            text-align: center;
            font-size: 16px;
        }
        
        .mobile-menu-footer {
            padding: 20px;
            border-top: 1px solid #e2e8f0;
            background: #f8fafc;
        }
        
        /* Ações Rápidas */
        .quick-actions {
            display: none;
            align-items: center;
            gap: 8px;
        }
        
        @media (min-width: 768px) {
            .quick-actions {
                display: flex;
            }
        }
        
        .quick-action-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            background: #2b6cb0;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .quick-action-btn:hover {
            background: #2c5282;
            transform: translateY(-1px);
        }
        
        .quick-action-btn i {
            font-size: 12px;
        }
        
        /* Botão de Sair no Mobile */
        .mobile-logout-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            width: 100%;
            padding: 12px 20px;
            background: none;
            border: none;
            color: #e53e3e;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .mobile-logout-btn:hover {
            background-color: #fff5f5;
        }
        
        /* Ajustes de responsividade */
        @media (max-width: 768px) {
            .header-container {
                padding: 0 1rem;
                height: 65px;
            }
            
            .logo-img {
                height: 35px;
            }
            
            .user-avatar {
                width: 36px;
                height: 36px;
                font-size: 14px;
            }
            
            .mobile-menu {
                width: 280px;
            }
        }
        
        @media (max-width: 480px) {
            .header-container {
                padding: 0 0.75rem;
            }
            
            .logo-img {
                height: 32px;
            }
            
            .user-avatar {
                width: 32px;
                height: 32px;
                font-size: 13px;
            }
        }

         /* Ações Rápidas */
        .quick-actions {
            display: none !important;
            align-items: center;
            gap: 8px;
        }
    </style>
</head>
<!-- Header simplificado (apenas para páginas públicas) -->
<?php if (!$usuario_logado): ?>
<header class="main-header">
    <!-- Logo como botão de expandir/retrair -->
<div class="sidebar-logo-toggle" id="sidebarToggle">
    <div class="logo-container">
        <!-- Logo pequena (para sidebar retraída) -->
        <img src="assets/images/logo-icon.png" alt="CRM TAPEMAG" class="logo-small">
        
        <!-- Logo completa (para sidebar expandida) -->
        <img src="assets/images/logo-completa.png" alt="CRM TAPEMAG" class="logo-full">
        
        <!-- Ícone de hover (para expandir/retrair) -->
        <div class="logo-hover-icon">
            <i class="fas fa-bars"></i>
        </div>
    </div>
</div>
</header>
<?php endif; ?>

<!-- Incluir sidebar para usuários logados -->
<?php if ($usuario_logado): ?>
    <?php include 'sidebar.php'; ?>
<?php endif; ?>

<!-- Conteúdo Principal -->
<main class="container">
    <script src="assets/js/sidebar.js"></script>
    <!-- Adicione no <head> do header.php -->
<link rel="stylesheet" href="assets/css/tema_<?= $_SESSION['tema'] ?>.css">
