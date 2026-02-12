<?php
// configuracao.php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'includes/config.php';
require_once 'includes/header.php';

$conn = getConnection();

// Buscar dados do usuário
$sql = "SELECT id, nome, email, foto_perfil, perfil, ultimo_login 
        FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);

if (!$usuario) {
    header('Location: logout.php');
    exit;
}

// Buscar logs de login recentes
$sql = "SELECT ip_address, user_agent, criado_em 
        FROM usuarios_logs 
        WHERE usuario_id = ? AND tipo = 'login' 
        ORDER BY criado_em DESC 
        LIMIT 10";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $_SESSION['usuario_id']);
mysqli_stmt_execute($stmt);
$logs_result = mysqli_stmt_get_result($stmt);
$logs_login = [];
while ($row = mysqli_fetch_assoc($logs_result)) {
    $logs_login[] = $row;
}

// Buscar sessões ativas
$current_session_id = session_id();
$sql = "SELECT id, ip_address, user_agent, ultima_atividade, criado_em,
               session_id = ? as sessao_atual
        FROM usuarios_sessoes 
        WHERE usuario_id = ? 
        AND ultima_atividade > DATE_SUB(NOW(), INTERVAL 30 MINUTE)
        ORDER BY ultima_atividade DESC";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "si", $current_session_id, $_SESSION['usuario_id']);
mysqli_stmt_execute($stmt);
$sessoes_result = mysqli_stmt_get_result($stmt);
$sessoes_ativas = [];
while ($row = mysqli_fetch_assoc($sessoes_result)) {
    $sessoes_ativas[] = $row;
}
?>

<link rel="stylesheet" href="assets/css/configuracoes.css">
<main class="settings-page">
    <div class="page-header">
        <h1><i class="fas fa-cog"></i> Configurações da Conta</h1>
        <p>Gerencie seus dados pessoais, segurança e sessões</p>
    </div>
    
    <div class="config-container">
        <!-- Seção Dados Pessoais -->
        <div class="config-section" id="dados-pessoais">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-user"></i> Dados Pessoais
                </h2>
            </div>
            
            <div class="section-content">
                <div class="foto-perfil-container">
                    <div class="foto-preview">

    <?php if (!empty($usuario['foto_perfil'])): ?>

        <img id="fotoPreview" class="foto-circular" 
             src="<?php echo htmlspecialchars($usuario['foto_perfil']); ?>" 
             alt="Foto de perfil">

    <?php else: ?>

        <div id="fotoPreviewLetra" class="avatar-iniciais-perfil">
            <?php echo strtoupper(substr($usuario['nome'], 0, 1)); ?>
        </div>

    <?php endif; ?>

</div>
                    
                    <button type="button" class="btn btn-secondary" onclick="abrirModalFoto()">
                        <i class="fas fa-camera"></i> Alterar Foto
                    </button>
                </div>
                
                <form id="formDadosPessoais" class="form-vertical">
                    <input type="hidden" name="acao" value="atualizar_dados">
                    
                    <div class="form-group">
                        <label for="nome">Nome Completo</label>
                        <input type="text" id="nome" name="nome" value="<?php echo htmlspecialchars($usuario['nome']); ?>" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" class="form-control" required>
                    </div>
                    <?php if ($_SESSION['perfil'] === 'admin'): ?>
                        <a href="permissoes.php" class="btn-permissoes">
                            <i class="fas fa-shield-alt"></i> Gerenciar Permissões
                        </a>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Perfil</label>
                        <div class="readonly-field">
                            <?php echo htmlspecialchars($usuario['perfil']); ?>
                        </div>
                        <small class="help-text">O perfil só pode ser alterado por um administrador</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" id="btnSalvarDados" class="btn btn-primary hidden">
                            <i class="fas fa-save"></i> Salvar Alterações
                        </button>
                        <div id="loadingDados" class="loading hidden">
                            <i class="fas fa-spinner fa-spin"></i> Salvando...
                        </div>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Seção Segurança -->
        <div class="config-section" id="seguranca">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-lock"></i> Segurança
                </h2>
            </div>
            
            <div class="section-content">
                <div class="info-box">
                    <h3>Último Login</h3>
                    <p class="info-value">
                        <?php 
                        echo $usuario['ultimo_login'] 
                            ? date('d/m/Y H:i', strtotime($usuario['ultimo_login']))
                            : 'Primeiro acesso';
                        ?>
                    </p>
                </div>
                
                <div class="action-buttons">
                    <button type="button" class="btn btn-primary" onclick="abrirModalSenha()">
                        <i class="fas fa-key"></i> Alterar Senha
                    </button>
                </div>
                
            </div>
        </div>
        
        <!-- Seção Sessões -->
        <div class="config-section" id="sessoes">
            <div class="section-header">
                <h2 class="section-title">
                    <i class="fas fa-desktop"></i> Sessões Ativas
                </h2>
            </div>
            
            <div class="section-content">
                <div class="sessoes-list">
                    <?php if (empty($sessoes_ativas)): ?>
                        <p class="empty-message">Nenhuma sessão ativa encontrada.</p>
                    <?php else: ?>
                        <?php foreach ($sessoes_ativas as $sessao): ?>
                        <div class="sessao-item <?php echo $sessao['sessao_atual'] ? 'sessao-atual' : ''; ?>">
                            <div class="sessao-info">
                                <div class="sessao-ip">
                                    <strong>IP:</strong> <?php echo htmlspecialchars($sessao['ip_address']); ?>
                                    <?php if ($sessao['sessao_atual']): ?>
                                        <span class="badge-atual">Sessão Atual</span>
                                    <?php endif; ?>
                                </div>
                                <div class="sessao-dispositivo">
                                    <?php echo htmlspecialchars(substr($sessao['user_agent'], 0, 60)); ?>
                                </div>
                                <div class="sessao-tempo">
                                    Ativo desde: <?php echo date('d/m/Y H:i', strtotime($sessao['ultima_atividade'])); ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                
                <?php if (count($sessoes_ativas) > 1): ?>
                <div class="sessoes-actions">
                    <button type="button" class="btn btn-danger" onclick="encerrarOutrasSessoes()">
                        <i class="fas fa-sign-out-alt"></i> Encerrar Outras Sessões
                    </button>
                    <p class="help-text">Esta ação manterá apenas a sessão atual ativa.</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<!-- Incluir modais -->
<?php include 'includes/modals/modais.php'; ?>

<script src="js/configuracoes.js"></script>

<script>
// Inicialização
function initConfiguracoes() {
    // Inicialização mínima para evitar erros e preparar estado inicial
    window.dadosOriginais = window.dadosOriginais || {};
    const btnSalvarDados = document.getElementById('btnSalvarDados');
    if (btnSalvarDados) btnSalvarDados.style.display = 'none';
}

document.addEventListener('DOMContentLoaded', function() {
    initConfiguracoes();
    
    // Monitorar alterações nos campos de dados pessoais
    const formDados = document.getElementById('formDadosPessoais');
    const inputsDados = formDados.querySelectorAll('input[type="text"], input[type="email"]');
    const btnSalvarDados = document.getElementById('btnSalvarDados');
    
    let dadosOriginais = {};
    inputsDados.forEach(input => {
        dadosOriginais[input.name] = input.value;
        input.addEventListener('input', function() {
            verificarAlteracoes();
        });
    });
    
    function verificarAlteracoes() {
        let alterado = false;
        inputsDados.forEach(input => {
            if (input.value !== dadosOriginais[input.name]) {
                alterado = true;
            }
        });
        btnSalvarDados.style.display = alterado ? 'inline-flex' : 'none';
    }
    
    // Salvar dados pessoais
    btnSalvarDados.addEventListener('click', salvarDadosPessoais);
});

function salvarDadosPessoais() {
    const form = document.getElementById('formDadosPessoais');
    const formData = new FormData(form);
    const loading = document.getElementById('loadingDados');
    const btnSalvar = document.getElementById('btnSalvarDados');
    
    btnSalvar.disabled = true;
    loading.style.display = 'inline-flex';
    
    fetch('api/usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarMensagem('sucesso', data.mensagem);
            // Atualizar dados originais para ocultar botão
            const inputs = form.querySelectorAll('input[type="text"], input[type="email"]');
            inputs.forEach(input => {
                window.dadosOriginais[input.name] = input.value;
            });
            btnSalvar.style.display = 'none';
        } else {
            mostrarMensagem('erro', data.erro || 'Erro ao salvar dados');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('erro', 'Erro de conexão');
    })
    .finally(() => {
        btnSalvar.disabled = false;
        loading.style.display = 'none';
    });
}

// Funções para mostrar/ocultar modais
function abrirModalSenha() {
    // Criar modal dinamicamente se não existir
    if (!document.getElementById('modalSenha')) {
        const modalHTML = `
            <div id="modalSenha" class="modal active">
                <div class="modal-content">
                    <div class="modal-header">
                        <h3><i class="fas fa-key"></i> Alterar Senha</h3>
                        <button type="button" class="modal-close" onclick="fecharModalSenha()">&times;</button>
                    </div>
                    <div class="modal-form">
                        <form id="formTrocarSenha">
                            <div class="form-group">
                                <label for="senha_atual" class="required">Senha Atual</label>
                                <input type="password" id="senha_atual" name="senha_atual" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="nova_senha" class="required">Nova Senha</label>
                                <input type="password" id="nova_senha" name="nova_senha" class="form-control" required minlength="6">
                                <small class="field-hint">Mínimo 6 caracteres</small>
                            </div>
                            <div class="form-group">
                                <label for="confirmar_senha" class="required">Confirmar Nova Senha</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" class="form-control" required>
                            </div>
                            <div class="form-actions">
                                <button type="button" class="btn btn-secondary" onclick="fecharModalSenha()">Cancelar</button>
                                <button type="submit" class="btn btn-primary">Alterar Senha</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        `;
        document.body.insertAdjacentHTML('beforeend', modalHTML);
        
        // Configurar submit do formulário
        document.getElementById('formTrocarSenha').addEventListener('submit', trocarSenha);
    } else {
        document.getElementById('modalSenha').classList.add('active');
    }
}

function fecharModalSenha() {
    const modal = document.getElementById('modalSenha');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

function abrirModalFoto() {
    // Implementação similar para modal de foto
    const modalHTML = `
        <div id="modalFoto" class="modal active">
            <div class="modal-content">
                <div class="modal-header">
                    <h3><i class="fas fa-camera"></i> Alterar Foto</h3>
                    <button type="button" class="modal-close" onclick="fecharModalFoto()">&times;</button>
                </div>
                <div class="modal-form">
                    <form id="formAlterarFoto" enctype="multipart/form-data">
                        <input type="hidden" name="acao" value="atualizar_foto">
                        <div class="form-group">
                            <label for="foto_perfil" class="required">Selecione uma imagem</label>
                            <input type="file" id="foto_perfil" name="foto_perfil" 
                                   accept="image/jpeg,image/png,image/gif,image/webp" class="form-control" required>
                            <small class="field-hint">Formatos: JPG, PNG, GIF, WebP (Máx: 5MB)</small>
                        </div>
                        <div id="fotoPreviewModal" class="foto-preview-modal hidden">
                            <img id="fotoPreviewImg" src="" alt="Preview" class="preview-img">
                        </div>
                        <div class="form-actions">
                            <button type="button" class="btn btn-secondary" onclick="fecharModalFoto()">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Enviar Foto</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Preview de imagem
    document.getElementById('foto_perfil').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const preview = document.getElementById('fotoPreviewImg');
                const container = document.getElementById('fotoPreviewModal');
                preview.src = e.target.result;
                container.style.display = 'block';
            };
            reader.readAsDataURL(file);
        }
    });
    
    // Configurar submit
    document.getElementById('formAlterarFoto').addEventListener('submit', alterarFoto);
}

function fecharModalFoto() {
    const modal = document.getElementById('modalFoto');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
}

function encerrarOutrasSessoes() {
    if (!confirm('Tem certeza que deseja encerrar todas as outras sessões?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('acao', 'encerrar_sessoes');
    
    fetch('api/usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarMensagem('sucesso', data.mensagem);
            // Recarregar página após 1 segundo
            setTimeout(() => location.reload(), 1000);
        } else {
            mostrarMensagem('erro', data.erro || 'Erro ao encerrar sessões');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('erro', 'Erro de conexão');
    });
}

function trocarSenha(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    formData.append('acao', 'trocar_senha');
    
    // Validações client-side
    const novaSenha = document.getElementById('nova_senha').value;
    const confirmarSenha = document.getElementById('confirmar_senha').value;
    
    if (novaSenha !== confirmarSenha) {
        mostrarMensagem('erro', 'As senhas não conferem');
        return;
    }
    
    if (novaSenha.length < 6) {
        mostrarMensagem('erro', 'A senha deve ter no mínimo 6 caracteres');
        return;
    }
    
    fetch('api/usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarMensagem('sucesso', data.mensagem);
            fecharModalSenha();
            form.reset();
        } else {
            mostrarMensagem('erro', data.erro || 'Erro ao alterar senha');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('erro', 'Erro de conexão');
    });
}

function alterarFoto(e) {
    e.preventDefault();
    
    const form = e.target;
    const formData = new FormData(form);
    
    fetch('api/usuario.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.sucesso) {
            mostrarMensagem('sucesso', data.mensagem);
            // Atualizar preview da foto
            if (data.caminho_foto) {
                document.getElementById('fotoPreview').src = data.caminho_foto;
                // Atualizar avatares na sidebar e preview do modal de sidebar, se existirem
                const sidebarImgs = document.querySelectorAll('.profile-avatar-small img, #previewFoto');
                sidebarImgs.forEach(img => { img.src = data.caminho_foto; });
            }
            fecharModalFoto();
        } else {
            mostrarMensagem('erro', data.erro || 'Erro ao atualizar foto');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        mostrarMensagem('erro', 'Erro de conexão');
    });
}

// Função para mostrar mensagens (usar sistema existente se disponível)
function mostrarMensagem(tipo, mensagem) {
    // Tentar usar sistema de toast existente
    if (typeof window.mostrarToast === 'function') {
        window.mostrarToast(tipo, mensagem);
    } else {
        // Implementação simples se não existir
        alert(mensagem);
    }
}
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>