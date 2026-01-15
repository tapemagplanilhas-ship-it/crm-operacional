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
        'id' => 'relatorios',
        'nome' => 'Relatórios',
        'icone' => 'fas fa-chart-bar',
        'url' => 'relatorios.php',
        'perfis' => ['admin', 'gerencia']
    ],
    [
        'id' => 'usuarios',
        'nome' => 'Usuários',
        'icone' => 'fas fa-user-cog',
        'url' => 'admin_usuarios.php',
        'perfis' => ['admin']
    ],
    [
        'id' => 'configuracoes',
        'nome' => 'Configurações',
        'icone' => 'fas fa-cog',
        'url' => 'configuracoes.php',
        'perfis' => ['admin', 'gerencia', 'vendedor']
    ]
];

// Filtrar páginas baseado no perfil do usuário
$paginas_permitidas = array_filter($paginas, function($pagina) use ($usuario_logado) {
    return in_array($usuario_logado['perfil'], $pagina['perfis']);
});

// Mapear nomes dos perfis
$nomes_perfis = [
    'admin' => 'Administrador',
    'gerencia' => 'Gestor',
    'vendedor' => 'Vendedor'
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
            ?>
            <li class="nav-item <?php echo $ativa ? 'active' : ''; ?>">
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
                <div class="profile-avatar-small" data-tooltip="<?php echo $nome_completo; ?>" onclick="abrirModalTrocarFoto()">
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
    <a href="personalizacao.php" class="dropdown-item">
        <i class="fas fa-palette"></i>
        <span>Personalização</span>
    </a>
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

<!-- Incluir JavaScript da Sidebar -->
<script src="assets/js/sidebar.js"></script>

<div class="modal" id="modal-foto">
  <div class="modal-content small">
    <div class="modal-header">
      <h3>Alterar foto de perfil</h3>
      <button class="modal-close" onclick="fecharModalFoto()">×</button>
    </div>

    <form 
      action="includes/actions/upload_foto_usuario.php"
      method="POST"
      enctype="multipart/form-data"
      class="modal-body"
    >
      <div class="foto-preview">
        <img 
          src="<?= $usuario_logado['foto'] ?: 'assets/img/avatar-default.png' ?>" 
          id="previewFoto"
        >
      </div>

      <input 
        type="file" 
        name="foto" 
        accept="image/*"
        required
        onchange="previewFoto(this)"
      >

      <button class="btn-primary full">
        Salvar foto
      </button>
    </form>
  </div>
</div>



<script>
function abrirModalTrocarFoto() {
  document.getElementById('modal-foto').classList.add('active');
}

function fecharModalFoto() {
  document.getElementById('modal-foto').classList.remove('active');
}

function previewFoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => {
      document.getElementById('previewFoto').src = e.target.result;
    };
    reader.readAsDataURL(input.files[0]);
  }
}
</script>
