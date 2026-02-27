<?php
// includes/sidebar.php
// Sidebar lateral fixa para o CRM TAPEMAG

// Verificar se usuário está logado
if (!isset($usuario_logado) || !$usuario_logado) {
    return; // Não exibir sidebar se não estiver logado
}

// Definir páginas disponíveis por perfil
$paginas = [
    [
        'id' => 'dashboard',
        'nome' => 'Dashboard',
        'icone' => 'fas fa-chart-line',
        'url' => 'index.php',
        'perfis' => ['admin', 'gerencia', 'vendedor']
    ],
    [
        'id' => 'dashboard_geral',
        'nome' => 'Dashboard Geral',
        'icone' => 'fas fa-tachometer-alt',
        'url' => 'dashboard_geral.php',
        'perfis' => ['admin', 'gerencia']
    ],
    [
        'id' => 'clientes',
        'nome' => 'Clientes',
        'icone' => 'fas fa-users',
        'url' => 'clientes.php',
        'perfis' => ['admin', 'gerencia', 'vendedor']
    ],
    [
        'id' => 'vendas',
        'nome' => 'Vendas',
        'icone' => 'fas fa-shopping-cart',
        'url' => 'vendas.php',
        'perfis' => ['admin', 'gerencia', 'vendedor']
    ],
    [
        'id' => 'entregas',
        'nome' => 'Entregas',
        'icone' => 'fas fa-truck',
        'url' => 'entregas.php',
        'perfis' => ['admin', 'gerencia', 'vendedor']
    ],
    [
        'id' => 'usuarios',
        'nome' => 'Usuários',
        'icone' => 'fas fa-user-cog',
        'url' => 'admin_usuarios.php',
        'perfis' => ['admin']
    ],
    [
        'id' => 'gestao',
        'nome' => 'Gestão',
        'icone' => 'fas fa-briefcase',
        'url' => 'gestao.php',
        'perfis' => ['admin', 'gerencia']
    ],
    // [
    //     'id' => 'configuracoes',
    //     'nome' => 'Configurações',
    //     'icone' => 'fas fa-cog',
    //     'url' => 'configuracoes.php',
    //     'perfis' => ['admin', 'gerencia', 'vendedor']
    // ],
];

// Filtrar páginas baseado no perfil do usuário
$paginas_permitidas = array_filter($paginas, function($pagina) {
    return acessoPermitido($pagina['id']);
});

// Mapear nomes dos perfis
$nomes_perfis = [
    'admin' => 'Administrador',
    'gerencia' => 'Gestor',
    'vendedor' => 'Vendedor',
    'financeiro' => 'Financeiro',
    'caixa' => 'Caixa',
    'recebimento' => 'Recebimento',
    'rh' => 'Recursos Humanos',
    'estoque' => 'Estoque'
];

// Obter iniciais para avatar
$iniciais = strtoupper(substr($usuario_logado['nome'], 0, 1));
$nome_completo = htmlspecialchars($usuario_logado['nome']);
$perfil_nome = $nomes_perfis[$usuario_logado['perfil']] ?? $usuario_logado['perfil'];
$email = htmlspecialchars($usuario_logado['email']);
?>

<!-- Sidebar Principal -->
<aside class="sidebar" id="sidebar">
    <!-- Logo como botão de expandir/retrair -->
    <div class="sidebar-logo-toggle" id="sidebarToggle">
        <div class="logo-container">
            <!-- Logo pequena (para sidebar retraída) -->
            <img src="assets/images/logo-tapemag.png" alt="CRM TAPEMAG" class="logo-small">
            
            <!-- Logo completa (para sidebar expandida) -->
            <img src="assets/images/logo-tapeemaag-cmpt.png" alt="CRM TAPEMAG" class="logo-full">
            
            <!-- Ícone de hover (para expandir/retrair) -->
            <div class="logo-hover-icon">
                <i class="fas fa-bars"></i>
            </div>
        </div>
    </div>
    
    <!-- Menu de Navegação -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <?php foreach ($paginas_permitidas as $pagina): 
                // Determinar se a página está ativa
                $pagina_arquivo = basename($pagina['url']);
                $ativa = ($pagina_atual == $pagina_arquivo || 
                         ($pagina['id'] == 'dashboard' && $pagina_atual == 'index.php') ||
                         ($pagina['id'] == 'dashboard' && empty($pagina_atual)));
                $destaque = $pagina['destaque'] ?? false;
            ?>
            <li class="nav-item <?php echo $ativa ? 'active' : ''; ?> <?php echo $destaque ? 'destaque-item' : ''; ?>">
                <a href="<?php echo $pagina['url']; ?>" 
                   class="nav-link"
                   title="<?php echo $pagina['nome']; ?>"
                   data-page="<?php echo $pagina['id']; ?>">
                    <i class="<?php echo $pagina['icone']; ?>"></i>
                    <span class="nav-text"><?php echo $pagina['nome']; ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ul>
    </nav>
    
    <!-- Footer com perfil e menu de ações -->
    <div class="sidebar-footer">
        <div class="profile-container">
            <!-- Avatar e informações do usuário -->
            <div class="profile-info">
                <div class="profile-avatar-small" data-tooltip="<?php echo $nome_completo; ?>">
                    <?php if (isset($usuario_logado['foto']) && !empty($usuario_logado['foto'])): ?>
                        <img src="<?php echo htmlspecialchars($usuario_logado['foto']); ?>" alt="<?php echo $nome_completo; ?>">
                    <?php else: ?>
                        <div class="avatar-iniciais-small"><?php echo $iniciais; ?></div>
                    <?php endif; ?>
                </div>
                <div class="profile-details">
                    <div class="profile-name-small"><?php echo $nome_completo; ?></div>
                    <div class="profile-role-small"><?php echo $perfil_nome; ?></div>
                </div>
            </div>
            
            <!-- Botão de ações (3 pontos) -->
            <button class="actions-toggle" id="actionsToggle">
                <i class="fas fa-ellipsis-v"></i>
            </button>

            <!-- Dropdown menu -->
            <div class="actions-dropdown" id="actionsDropdown">
                <a href="configuracoes.php" class="dropdown-item">
                    <i class="fas fa-cog"></i>
                    <span>Configurações</span>
                </a>
                <!-- <a href="personalizacao.php" class="dropdown-item">
                    <i class="fas fa-palette"></i>
                    <span>Personalização</span>
                </a> -->
                <a href="ajuda.php" class="dropdown-item">
                    <i class="fas fa-question-circle"></i>
                    <span>Ajuda</span>
                </a>
                <div class="dropdown-divider"></div>
                    <a href="logout.php" class="dropdown-item logout-item">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Sair</span>
                    </a>
                </div>
        </div>
    </div>
</aside>

<!-- Overlay para mobile -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Botão Hamburguer para Mobile -->
<button class="mobile-menu-btn" id="mobileMenuBtn">
    <i class="fas fa-bars"></i>
</button>

<style>
/* Estilos para o novo item destacado */
.destaque-item {
    position: relative;
}

.destaque-item .nav-link {
    background-color: rgba(79, 70, 229, 0.1);
}
</style>

<script src="assets/js/sidebar.js"></script>