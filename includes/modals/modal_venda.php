<div id="modal-venda" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2><i class="fas fa-shopping-cart"></i> Registrar Negociação</h2>
            <button class="modal-close" onclick="fecharModal('venda')">&times;</button>
        </div>
        
        <form id="form-venda" class="modal-form" onsubmit="registrarVenda(event)">
            <?php
            if (!isset($motivos_perda)) {
                $motivos_perda = [];
                if (function_exists('getConnection')) {
                    $conn = getConnection();
                    if ($conn) {
                        $result = $conn->query("SELECT id, nome, permite_outro FROM motivos_perda ORDER BY ordem ASC, nome ASC");
                        if ($result) {
                            while ($row = $result->fetch_assoc()) {
                                $motivos_perda[] = $row;
                            }
                        }
                        $conn->close();
                    }
                }
            }
            ?>
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
                           oninput="formatarMoeda(this)"
                           style="text-align: left; padding-left: 15px;">
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

            <!-- Campo de Código do Orçamento -->
            <div class="form-group" id="campo-codigo-orcamento">
                <label for="venda-codigo-orcamento">Código do Orçamento</label>
                <input type="text" id="venda-codigo-orcamento" name="codigo_orcamento"
                       placeholder="Ex: 12345" inputmode="numeric"
                       oninput="limparNaoNumericos(this)">
                <small class="field-hint">Apenas Números</small>
                </select>
            </div>
            
            <!-- Campo de motivo da perda -->
            <div class="form-group" id="campo-motivo-perda-venda" style="display: none;">
                <label for="venda-motivo-perda-select" class="required">Motivo da Perda</label>
                <select id="venda-motivo-perda-select" name="motivo_perda_id" onchange="mostrarCampoMotivoPerdaVenda()">
                    <option value="">Selecione...</option>
                    <?php foreach ($motivos_perda as $motivo): ?>
                        <option value="<?= (int)$motivo['id'] ?>" data-permite-outro="<?= (int)$motivo['permite_outro'] ?>">
                            <?= htmlspecialchars($motivo['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="venda-motivo-perda-outro-container" style="display: none; margin-top: 10px;">
                    <input type="text" id="venda-motivo-perda-outro" name="motivo_perda_outro"
                           placeholder="Descreva o motivo" />
                </div>
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
