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
                    <label for="cliente-telefone">Telefone</label>
                    <input type="tel" id="cliente-telefone" name="telefone" 
                           placeholder="(11) 99999-9999"
                           oninput="verificarAlteracoesCliente()">
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
                <button type="submit" class="btn-primary" id="btn-salvar-cliente" style="display: none;">
                    <i class="fas fa-save"></i> Salvar Cliente
                </button>
            </div>
        </form>
    </div>
</div>