<div id="modal-venda-rapida" class="modal" style="display: none;">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2><i class="fas fa-bolt"></i> Venda Rápida</h2>
            <button class="modal-close" onclick="fecharModal('venda-rapida')">&times;</button>
        </div>
        
        <form id="form-venda-rapida" class="modal-form" onsubmit="registrarVendaRapida(event)">
            <?php
            if (!isset($motivos_perda)) {
                $motivos_perda = [];
                if (function_exists('getConnection')) {
                    $conn = getConnection();
                    if ($conn) {
                        $tableExists = $conn->query("SHOW TABLES LIKE 'motivos_perda'");
                        if ($tableExists && $tableExists->num_rows > 0) {
                            $result = $conn->query("SELECT id, nome, permite_outro FROM motivos_perda ORDER BY ordem ASC, nome ASC");
                            if ($result) {
                                while ($row = $result->fetch_assoc()) {
                                    $motivos_perda[] = $row;
                                }
                            }
                        }
                        $conn->close();
                    }
                }
            }
            ?>
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

            <div class="form-group" id="campo-codigo-orcamento-rapida">
                <label for="venda-rapida-codigo-orcamento">Código do Orçamento</label>
                <input type="text" id="venda-rapida-codigo-orcamento" name="codigo_orcamento"
                       placeholder="Ex: 12345" inputmode="numeric"
                       oninput="limparNaoNumericos(this)">
                <small class="field-hint">Opcional, apenas números</small>
            </div>
            
            <!-- Campo de motivo da perda (aparece apenas quando status = PERDIDA) -->
            <div class="form-group" id="campo-motivo-perda" style="display: none;">
                <label for="venda-rapida-motivo-perda-select" class="required">Motivo da Perda</label>
                <select id="venda-rapida-motivo-perda-select" name="motivo_perda_id" onchange="mostrarCampoMotivoPerda()">
                    <option value="">Selecione...</option>
                    <?php foreach ($motivos_perda as $motivo): ?>
                        <option value="<?= (int)$motivo['id'] ?>" data-permite-outro="<?= (int)$motivo['permite_outro'] ?>">
                            <?= htmlspecialchars($motivo['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <div id="venda-rapida-motivo-perda-outro-container" style="display: none; margin-top: 10px;">
                    <input type="text" id="venda-rapida-motivo-perda-outro" name="motivo_perda_outro"
                           placeholder="Descreva o motivo" />
                </div>
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

