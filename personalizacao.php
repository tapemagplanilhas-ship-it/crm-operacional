<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
verificarLogin();
requerirPermissao('admin'); // Somente administradores podem acessar

// Carregar configurações atuais
$configuracoes = [];
$conn = getConnection();
if ($conn) {
    $result = $conn->query("SELECT * FROM configuracoes_sistema");
    while ($row = $result->fetch_assoc()) {
        $configuracoes[$row['chave']] = $row['valor'];
    }
    $conn->close();
}

// Processar formulário de atualização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn = getConnection();
    if ($conn) {
        try {
            $conn->begin_transaction();
            
            // Atualizar cada configuração
            foreach ($_POST['config'] as $chave => $valor) {
                $stmt = $conn->prepare("INSERT INTO configuracoes_sistema (chave, valor) 
                                      VALUES (?, ?) 
                                      ON DUPLICATE KEY UPDATE valor = ?");
                $stmt->bind_param("sss", $chave, $valor, $valor);
                $stmt->execute();
            }
            
            $conn->commit();
            $mensagem = "Configurações atualizadas com sucesso!";
            $tipoMensagem = "success";
            
            // Recarregar configurações
            $result = $conn->query("SELECT * FROM configuracoes_sistema");
            while ($row = $result->fetch_assoc()) {
                $configuracoes[$row['chave']] = $row['valor'];
            }
        } catch (Exception $e) {
            $conn->rollback();
            $mensagem = "Erro ao atualizar configurações: " . $e->getMessage();
            $tipoMensagem = "error";
        }
        $conn->close();
    }
}
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h2><i class="fas fa-paint-brush"></i> Personalização do Sistema</h2>
    </div>

    <?php if (isset($mensagem)): ?>
    <div class="alert alert-<?= $tipoMensagem ?>">
        <?= $mensagem ?>
    </div>
    <?php endif; ?>

    <form method="POST" class="personalizacao-form">
        <!-- Abas de configuração -->
        <div class="tabs">
            <button type="button" class="tab-button active" data-tab="aparencia">Aparência</button>
            <button type="button" class="tab-button" data-tab="campos">Campos</button>
            <button type="button" class="tab-button" data-tab="notificacoes">Notificações</button>
            <button type="button" class="tab-button" data-tab="avancado">Avançado</button>
        </div>

        <!-- Conteúdo das abas -->
        <div class="tab-content active" id="aparencia">
            <h3><i class="fas fa-palette"></i> Configurações de Aparência</h3>
            
            <div class="form-group">
                <label>Tema do Sistema</label>
                <select name="config[tema]" class="form-control">
                    <option value="claro" <?= ($configuracoes['tema'] ?? 'claro') === 'claro' ? 'selected' : '' ?>>Claro</option>
                    <option value="escuro" <?= ($configuracoes['tema'] ?? 'claro') === 'escuro' ? 'selected' : '' ?>>Escuro</option>
                    <option value="azul" <?= ($configuracoes['tema'] ?? 'claro') === 'azul' ? 'selected' : '' ?>>Azul</option>
                    <option value="verde" <?= ($configuracoes['tema'] ?? 'claro') === 'verde' ? 'selected' : '' ?>>Verde</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Cor Primária</label>
                <input type="color" name="config[cor_primaria]" value="<?= $configuracoes['cor_primaria'] ?? '#4f46e5' ?>" class="form-control">
            </div>
            
            <div class="form-group">
                <label>Cor Secundária</label>
                <input type="color" name="config[cor_secundaria]" value="<?= $configuracoes['cor_secundaria'] ?? '#10b981' ?>" class="form-control">
            </div>
        </div>

        <div class="tab-content" id="campos">
            <h3><i class="fas fa-list"></i> Personalização de Campos</h3>
            
            <div class="form-group">
                <label>Campos Obrigatórios para Clientes</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="config[campo_telefone_obrigatorio]" value="1" 
                               <?= ($configuracoes['campo_telefone_obrigatorio'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Telefone
                    </label>
                    <label>
                        <input type="checkbox" name="config[campo_email_obrigatorio]" value="1" 
                               <?= ($configuracoes['campo_email_obrigatorio'] ?? '1') === '1' ? 'checked' : '' ?>>
                        E-mail
                    </label>
                    <label>
                        <input type="checkbox" name="config[campo_endereco_obrigatorio]" value="1" 
                               <?= ($configuracoes['campo_endereco_obrigatorio'] ?? '0') === '1' ? 'checked' : '' ?>>
                        Endereço
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label>Campos Adicionais para Vendas</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="config[campo_observacoes_habilitado]" value="1" 
                               <?= ($configuracoes['campo_observacoes_habilitado'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Campo de Observações
                    </label>
                    <label>
                        <input type="checkbox" name="config[campo_codigo_orcamento_habilitado]" value="1" 
                               <?= ($configuracoes['campo_codigo_orcamento_habilitado'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Código do Orçamento
                    </label>
                </div>
            </div>
        </div>

        <div class="tab-content" id="notificacoes">
            <h3><i class="fas fa-bell"></i> Configurações de Notificações</h3>
            
            <div class="form-group">
                <label>Notificar Novas Vendas</label>
                <select name="config[notificar_novas_vendas]" class="form-control">
                    <option value="todos" <?= ($configuracoes['notificar_novas_vendas'] ?? 'todos') === 'todos' ? 'selected' : '' ?>>Para todos os administradores</option>
                    <option value="vendedor" <?= ($configuracoes['notificar_novas_vendas'] ?? 'todos') === 'vendedor' ? 'selected' : '' ?>>Somente para o vendedor</option>
                    <option value="ninguem" <?= ($configuracoes['notificar_novas_vendas'] ?? 'todos') === 'ninguem' ? 'selected' : '' ?>>Não notificar</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Notificar Vendas Perdidas</label>
                <select name="config[notificar_vendas_perdidas]" class="form-control">
                    <option value="gerentes" <?= ($configuracoes['notificar_vendas_perdidas'] ?? 'gerentes') === 'gerentes' ? 'selected' : '' ?>>Gerentes e administradores</option>
                    <option value="todos" <?= ($configuracoes['notificar_vendas_perdidas'] ?? 'gerentes') === 'todos' ? 'selected' : '' ?>>Todos os usuários</option>
                    <option value="ninguem" <?= ($configuracoes['notificar_vendas_perdidas'] ?? 'gerentes') === 'ninguem' ? 'selected' : '' ?>>Não notificar</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Método de Notificação</label>
                <div class="checkbox-group">
                    <label>
                        <input type="checkbox" name="config[notificacao_email]" value="1" 
                               <?= ($configuracoes['notificacao_email'] ?? '1') === '1' ? 'checked' : '' ?>>
                        E-mail
                    </label>
                    <label>
                        <input type="checkbox" name="config[notificacao_sistema]" value="1" 
                               <?= ($configuracoes['notificacao_sistema'] ?? '1') === '1' ? 'checked' : '' ?>>
                        Notificação no Sistema
                    </label>
                </div>
            </div>
        </div>

        <div class="tab-content" id="avancado">
            <h3><i class="fas fa-cog"></i> Configurações Avançadas</h3>
            
            <div class="form-group">
                <label>Formato de Data</label>
                <select name="config[formato_data]" class="form-control">
                    <option value="d/m/Y" <?= ($configuracoes['formato_data'] ?? 'd/m/Y') === 'd/m/Y' ? 'selected' : '' ?>>DD/MM/AAAA</option>
                    <option value="m/d/Y" <?= ($configuracoes['formato_data'] ?? 'd/m/Y') === 'm/d/Y' ? 'selected' : '' ?>>MM/DD/AAAA</option>
                    <option value="Y-m-d" <?= ($configuracoes['formato_data'] ?? 'd/m/Y') === 'Y-m-d' ? 'selected' : '' ?>>AAAA-MM-DD</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Formato de Moeda</label>
                <select name="config[formato_moeda]" class="form-control">
                    <option value="R$" <?= ($configuracoes['formato_moeda'] ?? 'R$') === 'R$' ? 'selected' : '' ?>>Real Brasileiro (R$)</option>
                    <option value="US$" <?= ($configuracoes['formato_moeda'] ?? 'R$') === 'US$' ? 'selected' : '' ?>>Dólar Americano (US$)</option>
                    <option value="€" <?= ($configuracoes['formato_moeda'] ?? 'R$') === '€' ? 'selected' : '' ?>>Euro (€)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Idioma do Sistema</label>
                <select name="config[idioma]" class="form-control">
                    <option value="pt_BR" <?= ($configuracoes['idioma'] ?? 'pt_BR') === 'pt_BR' ? 'selected' : '' ?>>Português (Brasil)</option>
                    <option value="en_US" <?= ($configuracoes['idioma'] ?? 'pt_BR') === 'en_US' ? 'selected' : '' ?>>Inglês (EUA)</option>
                    <option value="es_ES" <?= ($configuracoes['idioma'] ?? 'pt_BR') === 'es_ES' ? 'selected' : '' ?>>Espanhol (Espanha)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label>Fuso Horário</label>
                <select name="config[fuso_horario]" class="form-control">
                    <option value="America/Sao_Paulo" <?= ($configuracoes['fuso_horario'] ?? 'America/Sao_Paulo') === 'America/Sao_Paulo' ? 'selected' : '' ?>>Brasil (São Paulo)</option>
                    <option value="America/New_York" <?= ($configuracoes['fuso_horario'] ?? 'America/Sao_Paulo') === 'America/New_York' ? 'selected' : '' ?>>EUA (Nova York)</option>
                    <option value="Europe/Lisbon" <?= ($configuracoes['fuso_horario'] ?? 'America/Sao_Paulo') === 'Europe/Lisbon' ? 'selected' : '' ?>>Portugal (Lisboa)</option>
                </select>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="window.location.href='dashboard.php'">
                <i class="fas fa-times"></i> Cancelar
            </button>
            <button type="submit" class="btn-primary">
                <i class="fas fa-save"></i> Salvar Configurações
            </button>
        </div>
    </form>
</div>

<style>
.tabs {
    display: flex;
    border-bottom: 1px solid #e2e8f0;
    margin-bottom: 20px;
}

.tab-button {
    padding: 10px 20px;
    background: none;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    font-weight: 600;
    color: #4a5568;
    transition: all 0.2s;
}

.tab-button:hover {
    color: #2d3748;
    border-bottom-color: #cbd5e0;
}

.tab-button.active {
    color: #4f46e5;
    border-bottom-color: #4f46e5;
}

.tab-content {
    display: none;
    padding: 20px 0;
}

.tab-content.active {
    display: block;
}

.checkbox-group {
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    margin-top: 10px;
}

.checkbox-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
}

.current-logo {
    margin-top: 10px;
    padding: 10px;
    border: 1px dashed #cbd5e0;
    border-radius: 8px;
    display: inline-block;
}

.form-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
    text-align: right;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Controle das abas
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            
            // Remover classe active de todos
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Adicionar classe active no botão e conteúdo selecionado
            this.classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Preview do logo
    const logoUpload = document.getElementById('logo-upload');
    if (logoUpload) {
        logoUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    let preview = document.querySelector('.current-logo');
                    if (!preview) {
                        preview = document.createElement('div');
                        preview.className = 'current-logo';
                        logoUpload.parentNode.appendChild(preview);
                    }
                    preview.innerHTML = `<img src="${event.target.result}" alt="Novo Logo" style="max-width: 200px;">`;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>