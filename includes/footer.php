    </main>
    
    <footer class="main-footer">
        <div class="container">
            <p>CRM Operacional &copy; <?php echo date('Y'); ?> - Desenvolvido para vendedores de loja física</p>
        </div>
    </footer>
    
    <!-- Incluir modais -->
    <?php
    if (file_exists('includes/modals/modal_cliente.php')) {
        include 'includes/modals/modal_cliente.php';
    }
    if (file_exists('includes/modals/modal_venda.php')) {
        include 'includes/modals/modal_venda.php';
    }
    if (file_exists('includes/modals/modal_venda_rapida.php')) {
        include 'includes/modals/modal_venda_rapida.php';
    }
    if (file_exists('includes/modals/modal_venda_detalhes.php')) {
        include 'includes/modals/modal_venda_detalhes.php';
    }
    if (file_exists('includes/modals/modal_cliente_detalhes.php')) {
        include 'includes/modals/modal_cliente_detalhes.php';
    }
    if (file_exists('includes/modals/modal_confirmacao.php')) {
        include 'includes/modals/modal_confirmacao.php';
    }
    ?>
    
    <!-- Toast Container para feedback -->
    <div id="toast-container"></div>
    
    <script src="assets/js/scripts.js"></script>
</body>
</html>
