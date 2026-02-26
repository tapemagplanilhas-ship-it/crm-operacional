<div id="modal-cliente-detalhes" class="modal" style="display: none; z-index: 99999;">
    <div class="modal-content" style="max-width: 900px;">
        <div class="modal-header">
            <h2><i class="fas fa-id-card"></i> Detalhes do Cliente</h2>
            <button class="modal-close" onclick="fecharModal('cliente-detalhes')">&times;</button>
        </div>

        <div class="modal-body">
            <div class="cliente-card">
                <div class="cliente-card-header">
                    <div>
                        <div class="cliente-card-nome" id="detalhe-cliente-nome">-</div>
                        <div class="cliente-card-empresa" id="detalhe-cliente-empresa">-</div>
                    </div>
                    <div class="cliente-card-badges">
                        <span class="badge badge-info" id="detalhe-cliente-status">-</span>
                    </div>
                </div>

                <div class="cliente-card-grid">
                    <div class="cliente-card-item">
                        <span class="label">CNPJ/CPF</span>
                        <span class="value" id="detalhe-cliente-documento">-</span>
                    </div>
                    <div class="cliente-card-item">
                        <span class="label">Telefone</span>
                        <span class="value" id="detalhe-cliente-telefone">-</span>
                    </div>
                    <div class="cliente-card-item">
                        <span class="label">E-mail</span>
                        <span class="value" id="detalhe-cliente-email">-</span>
                    </div>
                    <div class="cliente-card-item">
                        <span class="label">Cadastro</span>
                        <span class="value" id="detalhe-cliente-data">-</span>
                    </div>
                </div>

                <div class="cliente-card-metricas">
                    <div class="metric">
                        <span class="label">Última venda</span>
                        <span class="value" id="detalhe-cliente-ultima-venda">-</span>
                    </div>
                    <div class="metric">
                        <span class="label">Média de gastos</span>
                        <span class="value" id="detalhe-cliente-media-gastos">-</span>
                    </div>
                    <div class="metric">
                        <span class="label">Total gasto</span>
                        <span class="value" id="detalhe-cliente-total-gasto">-</span>
                    </div>
                    <div class="metric">
                        <span class="label">Taxa fechamento</span>
                        <span class="value" id="detalhe-cliente-taxa-fechamento">-</span>
                    </div>
                </div>

                <div class="cliente-card-observacoes">
                    <div class="label">Observações</div>
                    <div class="value" id="detalhe-cliente-observacoes">-</div>
                </div>
            </div>

            <div class="cliente-vendas" id="cliente-vendas-container" style="display: none;">
                <div class="cliente-vendas-header">
                    <h3><i class="fas fa-receipt"></i> Histórico de Vendas</h3>
                </div>
                <div id="cliente-vendas-loading" class="text-center" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Carregando vendas...
                </div>
                <div id="cliente-vendas-lista" class="cliente-vendas-lista"></div>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-secondary" onclick="fecharModal('cliente-detalhes')">
                Fechar
            </button>
            <button type="button" class="btn-secondary" id="btn-ver-vendas-cliente" onclick="toggleVendasCliente()">
                <i class="fas fa-receipt"></i> Ver Vendas
            </button>
        </div>
    </div>
</div>
