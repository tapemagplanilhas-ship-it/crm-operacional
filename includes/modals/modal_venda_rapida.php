<div id="modal-venda-rapida" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2><i class="fas fa-bolt"></i> Venda Rápida</h2>
            <button class="modal-close" onclick="fecharModal('venda-rapida')">&times;</button>
        </div>
        
        <form id="form-venda-rapida" class="modal-form" onsubmit="registrarVendaRapida(event)">
            <div class="form-group">
                <label for="venda-rapida-cliente" class="required">Cliente</label>
                <div class="search-container">
                    <input type="text" id="venda-rapida-cliente" 
                           placeholder="Digite para buscar cliente..." 
                           class="search-input"
                           onkeyup="buscarClientesSugestoes(this.value)"
                           autocomplete="off">
                    <button type="button" class="btn-icon" onclick="abrirModalCliente()">
                        <i class="fas fa-plus"></i> Novo
                    </button>
                </div>
                <div id="clientes-sugestoes" class="suggestions"></div>
                <input type="hidden" id="venda-rapida-cliente-id" name="cliente_id" value="">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venda-rapida-valor" class="required">Valor</label>
                    <input type="text" id="venda-rapida-valor" name="valor" required 
                           placeholder="R$ 0,00" class="money-input"
                           oninput="formatarMoeda(this)">
                </div>
                
                <div class="form-group">
                    <label for="venda-rapida-data" class="required">Data da Negociação</label>
                    <input type="text" id="venda-rapida-data" name="data_venda" required 
                           placeholder="dd/mm/aaaa" value="<?php echo date('d/m/Y'); ?>"
                           oninput="formatarData(this)">
                    <small class="field-hint">Pode ser data passada ou futura</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venda-rapida-status" class="required">Status</label>
                    <select id="venda-rapida-status" name="status" required 
                            onchange="mostrarCampoMotivoPerda()">
                        <option value="concluida">Concluída</option>
                        <option value="orcamento">Orçamento</option>
                        <option value="perdida">Perdida</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="venda-rapida-forma-pagamento" class="required">Forma de Pagamento</label>
                    <select id="venda-rapida-forma-pagamento" name="forma_pagamento" required>
                        <option value="">Selecione...</option>
                        <option value="pix">PIX</option>
                        <option value="cartao">Cartão</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="boleto">Boleto</option>
                        <option value="na">N/A (Orçamento/Perdida)</option>
                    </select>
                </div>
            </div>
            
            <!-- Campo de motivo da perda (aparece apenas quando status = PERDIDA) -->
            <div class="form-group" id="campo-motivo-perda" style="display: none;">
                <label for="venda-rapida-motivo-perda" class="required">Motivo da Perda</label>
                <textarea id="venda-rapida-motivo-perda" name="motivo_perda" 
                          placeholder="Descreva o motivo pelo qual a venda foi perdida..." 
                          rows="3"></textarea>
                <small class="field-hint">Campo obrigatório para vendas perdidas</small>
            </div>
            
            <div class="form-group">
                <label for="venda-rapida-observacoes">Observações Adicionais</label>
                <textarea id="venda-rapida-observacoes" name="observacoes" 
                          placeholder="Detalhes adicionais sobre a negociação..." 
                          rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" 
                        onclick="fecharModal('venda-rapida')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-bolt"></i> Registrar Negociação
                </button>
            </div>
        </form>
    </div>
</div>