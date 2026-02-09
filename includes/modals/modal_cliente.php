<div id="modal-cliente" class="modal" style="display: none; z-index: 99999;">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user"></i> <span id="modal-cliente-title">Novo Cliente</span></h2>
            <button class="modal-close" onclick="fecharModal('cliente')">&times;</button>
        </div>
        
        <form id="form-cliente" class="modal-form" onsubmit="salvarCliente(event)">
            <input type="hidden" id="cliente-id" name="id" value="">
            
            <div class="form-group">
                <label for="cliente-nome" class="required">Nome completo</label>
                <input type="text" id="cliente-nome" name="nome" required 
                       placeholder="Digite o nome do cliente"
                       oninput="verificarAlteracoesCliente()">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label for="cliente-empresa">Nome da empresa</label>
                    <input type="text" id="cliente-empresa" name="empresa"
                           placeholder="Nome da empresa"
                           oninput="verificarAlteracoesCliente()">
                </div>

                <div class="form-group">
                    <label for="cliente-documento">CNPJ/CPF</label>
                    <input type="text" id="cliente-documento" name="documento"
                           placeholder="00.000.000/0000-00 ou 000.000.000-00"
                           oninput="formatarDocumento(this); verificarAlteracoesCliente()"
                           maxlength="18">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cliente-telefone">Telefone</label>
                    <input type="tel" id="cliente-telefone" name="telefone" 
                           placeholder="(00) 00000-0000"
                           oninput="formatarTelefone(this); verificarAlteracoesCliente()"
                           maxlength="15">
                </div>
                
                <div class="form-group">
                    <label for="cliente-email">E-mail</label>
                    <input type="email" id="cliente-email" name="email" 
                           placeholder="cliente@email.com"
                           oninput="verificarAlteracoesCliente()">
                </div>
            </div>
            
            <div class="form-group">
                <label for="cliente-observacoes">Observações</label>
                <textarea id="cliente-observacoes" name="observacoes" 
                          placeholder="Anotações sobre o cliente..." 
                          rows="3"
                          oninput="verificarAlteracoesCliente()"></textarea>
            </div>
            
            <!-- Campos automáticos (somente leitura) -->
            <div class="campos-automaticos" style="display: none;">
                <h3><i class="fas fa-chart-bar"></i> Métricas Automáticas</h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Última venda</label>
                        <input type="text" id="cliente-ultima-venda" readonly 
                               class="readonly-field" value="Nunca">
                    </div>
                    
                    <div class="form-group">
                        <label>Média de gastos</label>
                        <input type="text" id="cliente-media-gastos" readonly 
                               class="readonly-field" value="R$ 0,00">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Total gasto</label>
                        <input type="text" id="cliente-total-gasto" readonly 
                               class="readonly-field" value="R$ 0,00">
                    </div>
                    
                    <div class="form-group">
                        <label>Taxa de fechamento</label>
                        <input type="text" id="cliente-taxa-fechamento" readonly 
                               class="readonly-field" value="0%">
                    </div>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" 
                        onclick="fecharModal('cliente')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary" id="btn-salvar-cliente">
                    <i class="fas fa-save"></i> Salvar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Função para formatar CPF/CNPJ
function formatarDocumento(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length <= 11) {
        // Formata CPF (000.000.000-00)
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d)/, '$1.$2');
        value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    } else {
        // Formata CNPJ (00.000.000/0000-00)
        value = value.replace(/^(\d{2})(\d)/, '$1.$2');
        value = value.replace(/^(\d{2})\.(\d{3})(\d)/, '$1.$2.$3');
        value = value.replace(/\.(\d{3})(\d)/, '.$1/$2');
        value = value.replace(/(\d{4})(\d)/, '$1-$2');
    }
    
    input.value = value;
}

// Função para formatar telefone
function formatarTelefone(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 10) {
        // Formato para celular (11) 99999-9999
        value = value.replace(/^(\d{2})(\d{5})(\d{4})$/, '($1) $2-$3');
    } else if (value.length > 6) {
        // Formato para telefone fixo (11) 9999-9999
        value = value.replace(/^(\d{2})(\d{4})(\d{4})$/, '($1) $2-$3');
    } else if (value.length > 2) {
        value = value.replace(/^(\d{2})(\d+)/, '($1) $2');
    }
    
    input.value = value;
}
</script>

<style>
/* Estilos para os campos formatados */
input[type="text"], 
input[type="tel"],
input[type="email"] {
    padding: 12px 15px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s;
    width: 100%;
}

input[type="text"]:focus, 
input[type="tel"]:focus,
input[type="email"]:focus {
    border-color: #d10101;
    outline: none;
    box-shadow: 0 0 0 2px rgba(209, 1, 1, 0.1);
}

.readonly-field {
    background-color: #f5f5f5;
    color: #666;
    cursor: not-allowed;
}

.form-group {
    margin-bottom: 15px;
}

.form-row {
    display: flex;
    gap: 15px;
}

.form-row .form-group {
    flex: 1;
}

.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 20px;
}

.btn-primary {
    background-color: #d10101;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-primary:hover {
    background-color: #b00000;
}

.btn-secondary {
    background-color: #666;
    color: white;
    border: none;
    padding: 10px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
}

.btn-secondary:hover {
    background-color: #555;
}
</style>