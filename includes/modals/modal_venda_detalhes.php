<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$perfil_usuario = $_SESSION['perfil'] ?? '';
$mostrar_vendedor = in_array($perfil_usuario, ['admin', 'gerencia'], true);
?>
<div id="modal-venda-detalhes" class="modal" style="display: none; z-index: 99999;">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h2><i class="fas fa-receipt"></i> Detalhes da Venda</h2>
            <button class="modal-close" onclick="fecharModal('venda-detalhes')">&times;</button>
        </div>

        <div class="modal-body">
            <div class="venda-card">
                <div class="venda-card-header">
                    <div>
                        <div class="venda-card-title" id="detalhe-venda-cliente">-</div>
                        <div class="venda-card-subtitle" id="detalhe-venda-telefone">-</div>
                    </div>
                    <div class="venda-card-badges">
                        <span class="status-badge" id="detalhe-venda-status">-</span>
                    </div>
                </div>

                <div class="venda-card-grid">
                    <div class="venda-card-item">
                        <span class="label">Data da venda</span>
                        <span class="value" id="detalhe-venda-data">-</span>
                    </div>
                    <div class="venda-card-item">
                        <span class="label">Valor</span>
                        <span class="value" id="detalhe-venda-valor">-</span>
                    </div>
                    <div class="venda-card-item">
                        <span class="label">Forma de pagamento</span>
                        <span class="value" id="detalhe-venda-pagamento">-</span>
                    </div>
                    <div class="venda-card-item">
                        <span class="label">Registro</span>
                        <span class="value" id="detalhe-venda-registro">-</span>
                    </div>
                    <?php if ($mostrar_vendedor): ?>
                    <div class="venda-card-item" id="bloco-vendedor">
                        <span class="label">Vendedor</span>
                        <span class="value" id="detalhe-venda-vendedor">-</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="venda-card-grid" id="bloco-codigo-orcamento" style="display: none;">
                    <div class="venda-card-item">
                        <span class="label">Código do orçamento</span>
                        <span class="value" id="detalhe-venda-codigo-orcamento">-</span>
                    </div>
                </div>

                <div class="venda-card-observacoes" id="bloco-motivo-perda" style="display: none;">
                    <div class="label">Motivo da perda</div>
                    <div class="value" id="detalhe-venda-motivo-perda">-</div>
                </div>

                <div class="venda-card-observacoes">
                    <div class="label">Observações</div>
                    <div class="value" id="detalhe-venda-observacoes">-</div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="fecharModal('venda-detalhes')">
                Fechar
            </button>
            <button type="button" class="btn-primary" onclick="editarVendaDetalhes()">
                <i class="fas fa-edit"></i> Editar
            </button>
        </div>
    </div>
</div>
