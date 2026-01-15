<!-- Modal Cliente -->
<div id="modal-cliente" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-user"></i> <span id="modal-cliente-title">Novo Cliente</span></h2>
            <button class="modal-close" onclick="closeModal('modal-cliente')">&times;</button>
        </div>
        
        <form id="form-cliente" class="modal-form">
            <input type="hidden" id="cliente-id" name="id" value="">
            
            <div class="form-group">
                <label for="cliente-nome" class="required">Nome completo</label>
                <input type="text" id="cliente-nome" name="nome" required 
                       placeholder="Digite o nome do cliente">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="cliente-telefone">Telefone</label>
                    <input type="tel" id="cliente-telefone" name="telefone" 
                           placeholder="(11) 99999-9999">
                </div>
                
                <div class="form-group">
                    <label for="cliente-email">E-mail</label>
                    <input type="email" id="cliente-email" name="email" 
                           placeholder="cliente@email.com">
                </div>
            </div>
            
            <div class="form-group">
                <label for="cliente-observacoes">Observações</label>
                <textarea id="cliente-observacoes" name="observacoes" 
                          placeholder="Anotações sobre o cliente..." 
                          rows="3"></textarea>
            </div>
            
            <!-- Campos automáticos (somente leitura) -->
            <div class="form-row">
                <div class="form-group">
                    <label>Última venda</label>
                    <input type="text" id="cliente-ultima-venda" readonly 
                           class="readonly-field">
                </div>
                
                <div class="form-group">
                    <label>Média de gastos</label>
                    <input type="text" id="cliente-media-gastos" readonly 
                           class="readonly-field">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>Total gasto</label>
                    <input type="text" id="cliente-total-gasto" readonly 
                           class="readonly-field">
                </div>
                
                <div class="form-group">
                    <label>Taxa de fechamento</label>
                    <input type="text" id="cliente-taxa-fechamento" readonly 
                           class="readonly-field">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" 
                        onclick="closeModal('modal-cliente')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary" id="btn-salvar-cliente">
                    <i class="fas fa-save"></i> Salvar Cliente
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Venda -->
<div id="modal-venda" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Registrar Venda</h2>
            <button class="modal-close" onclick="closeModal('modal-venda')">&times;</button>
        </div>
        
        <form id="form-venda" class="modal-form">
            <input type="hidden" id="venda-cliente-id" name="cliente_id" value="">
            
            <div class="form-group">
                <label>Cliente</label>
                <input type="text" id="venda-cliente-nome" readonly 
                       class="readonly-field" placeholder="Selecione um cliente">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venda-valor" class="required">Valor da venda</label>
                    <input type="text" id="venda-valor" name="valor" required 
                           placeholder="R$ 0,00" class="money-input">
                </div>
                
                <div class="form-group">
                    <label for="venda-data" class="required">Data da venda</label>
                    <input type="text" id="venda-data" name="data_venda" required 
                           placeholder="dd/mm/aaaa">
                </div>
            </div>
            
            <div class="form-group">
                <label for="venda-status" class="required">Status</label>
                <select id="venda-status" name="status" required>
                    <option value="concluida">Concluída</option>
                    <option value="orcamento">Orçamento</option>
                    <option value="cancelada">Cancelada</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="venda-observacoes">Observações</label>
                <textarea id="venda-observacoes" name="observacoes" 
                          placeholder="Detalhes da venda..." rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" 
                        onclick="closeModal('modal-venda')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Registrar Venda
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Venda Rápida -->
<div id="modal-venda-rapida" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2><i class="fas fa-bolt"></i> Venda Rápida</h2>
            <button class="modal-close" onclick="closeModal('modal-venda-rapida')">&times;</button>
        </div>
        
        <form id="form-venda-rapida" class="modal-form">
            <div class="form-group">
                <label for="venda-rapida-cliente" class="required">Cliente</label>
                <div class="search-container">
                    <input type="text" id="venda-rapida-cliente" 
                           placeholder="Digite para buscar cliente..." 
                           class="search-input">
                    <button type="button" class="btn-icon" 
                            onclick="abrirModalCliente()">
                        <i class="fas fa-plus"></i> Novo
                    </button>
                </div>
                <div id="clientes-sugestoes" class="suggestions"></div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venda-rapida-valor" class="required">Valor</label>
                    <input type="text" id="venda-rapida-valor" required 
                           placeholder="R$ 0,00" class="money-input">
                </div>
                
                <div class="form-group">
                    <label for="venda-rapida-data" class="required">Data</label>
                    <input type="text" id="venda-rapida-data" required 
                           placeholder="dd/mm/aaaa">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" 
                        onclick="closeModal('modal-venda-rapida')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-bolt"></i> Registrar Venda Rápida
                </button>
            </div>
        </form>
    </div>
</div>