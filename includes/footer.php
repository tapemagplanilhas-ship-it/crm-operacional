    <link rel="stylesheet" href="assets/css/footer.css">
    </main>
    
    <footer class="main-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo">
                        <img src="assets/images/logo-tapeemaag-cmpt.png" alt="CRM Operacional" width="190">
                    </div>
                    <p class="footer-description">Solução completa para gestão de vendas e relacionamento com clientes.</p>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Suporte</h4>
                    <ul class="footer-links">
                        <li><a href="ajuda.php">Central de Ajuda</a></li>
                        <li><a href="termos.php">Termos de Uso</a></li>
                        <li><a href="privacidade.php">Política de Privacidade</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Contato</h4>
                    <div class="footer-contact">
                        <p class="sup"><i class="fas fa-envelope"></i>suporte@crmoperacional.com.br</p>
                        <p class="tel"><i class="fas fa-phone"></i>(15) 3451-1400</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/Tapemagtatui/?locale=pt_BR" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook"></i></a>
                            <a href="https://www.instagram.com/tapemagtatui/?__d=dist" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                            <a href="https://api.whatsapp.com/send/?phone=1534511400&text=Ol%C3%A1%21+Gostaria+de+fazer+uma+compra+pelo+WhatsApp&type=phone_number&app_absent=0" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="footer-bottom">
                <p class="copyright">
                    CRM Operacional &copy; <?php echo date('Y'); ?> - Todos os direitos reservados
                </p>
            </div>
        </div>
    </footer>
    
    <!-- Incluir modais -->
    <?php
    $modals = [
        'modal_cliente.php',
        'modal_venda.php',
        'modal_venda_rapida.php',
        'modal_venda_detalhes.php',
        'modal_cliente_detalhes.php',
        'modal_confirmacao.php'
    ];
    
    foreach ($modals as $modal) {
        $path = 'includes/modals/' . $modal;
        if (file_exists($path)) {
            include $path;
        }
    }
    ?>
    
    <!-- Toast Container para feedback -->
    <div id="toast-container" class="toast-position"></div>
    
    <script src="assets/js/scripts.js"></script>
</body>
</html>