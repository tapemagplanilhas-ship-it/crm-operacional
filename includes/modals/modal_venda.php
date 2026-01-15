<div id="modal-venda" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Registrar Negociação</h2>
            <button class="modal-close" onclick="fecharModal('venda')">&times;</button>
        </div>
        
        <form id="form-venda" class="modal-form" onsubmit="registrarVenda(event)">
            <input type="hidden" id="venda-cliente-id" name="cliente_id" value="">
            
            <div class="form-group">
                <label>Cliente</label>
                <input type="text" id="venda-cliente-nome" readonly 
                       class="readonly-field" placeholder="Selecione um cliente">
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venda-valor" class="required">Valor</label>
                    <input type="text" id="venda-valor" name="valor" required 
                           placeholder="R$ 0,00" class="money-input"
                           oninput="formatarMoeda(this)">
                </div>
                
                <div class="form-group">
                    <label for="venda-data" class="required">Data da Negociação</label>
                    <input type="text" id="venda-data" name="data_venda" required 
                           placeholder="dd/mm/aaaa" value="<?php echo date('d/m/Y'); ?>"
                           oninput="formatarData(this)">
                    <small class="field-hint">Pode ser data passada ou futura</small>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="venda-status" class="required">Status</label>
                    <select id="venda-status" name="status" required 
                            onchange="mostrarCampoMotivoPerdaVenda()">
                        <option value="concluida">CONCLUÍDA</option>
                        <option value="orcamento">ORÇAMENTO</option>
                        <option value="perdida">PERDIDA</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="venda-forma-pagamento" class="required">Forma de Pagamento</label>
                    <select id="venda-forma-pagamento" name="forma_pagamento" required>
                        <option value="">Selecione...</option>
                        <option value="pix">PIX</option>
                        <option value="cartao">Cartão</option>
                        <option value="dinheiro">Dinheiro</option>
                        <option value="boleto">Boleto</option>
                        <option value="na">N/A (Orçamento/Perdida)</option>
                    </select>
                </div>
            </div>
            
            <!-- Campo de motivo da perda -->
            <div class="form-group" id="campo-motivo-perda-venda" style="display: none;">
                <label for="venda-motivo-perda" class="required">Motivo da Perda</label>
                <textarea id="venda-motivo-perda" name="motivo_perda" 
                          placeholder="Descreva o motivo pelo qual a venda foi perdida..." 
                          rows="3"></textarea>
                <small class="field-hint">Campo obrigatório para vendas perdidas</small>
            </div>
            
            <div class="form-group">
                <label for="venda-observacoes">Observações Adicionais</label>
                <textarea id="venda-observacoes" name="observacoes" 
                          placeholder="Detalhes adicionais sobre a negociação..." 
                          rows="3"></textarea>
            </div>
            
            <div class="form-actions">
                <button type="button" class="btn-secondary" 
                        onclick="fecharModal('venda')">
                    Cancelar
                </button>
                <button type="submit" class="btn-primary">
                    <i class="fas fa-check"></i> Registrar Negociação
                </button>
            </div>
        </form>
    </div>
</div>