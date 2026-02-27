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
                        <li><a href="termos_uso.php">Termos de Uso</a></li>
                        <li><a href="privacidade.php">Política de Privacidade</a></li>
                    </ul>
                </div>
                
                <div class="footer-section">
                    <h4 class="footer-title">Contato</h4>
                    <div class="footer-contact">
                        <p class="sup"><i class="fas fa-envelope"></i>suporte@tapemag.com.br</p>
                        <p class="tel"><i class="fas fa-phone"></i>(15) 3451-1419</p>
                        <div class="social-links">
                            <a href="https://www.facebook.com/Tapemagtatui/?locale=pt_BR" target="_blank" rel="noopener noreferrer"><i class="fab fa-facebook"></i></a>
                            <a href="https://www.instagram.com/tapemagtatui/?__d=dist" target="_blank" rel="noopener noreferrer"><i class="fab fa-instagram"></i></a>
                            <a href="https://api.whatsapp.com/send/?phone=1534511400&text=Ol%C3%A1%21+Preciso+de+ajuda+com+o+CRM%21&type=phone_number&app_absent=0" target="_blank" rel="noopener noreferrer"><i class="fab fa-whatsapp"></i></a>
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

<script>
// 
// NOTIFICADOR GLOBAL DE TAREFAS
// Verifica tarefas pendentes a cada 60s
// 
(function iniciarNotificador() {
    verificarTarefas();
    setInterval(verificarTarefas, 60000); // a cada 1 minuto
})();

async function verificarTarefas() {
    try {
        const res  = await fetch('api/tarefas.php?status=pendente&hoje=1');
        const json = await res.json();

        if (!json.success) return;

        const tarefas    = json.data;
        const atrasadas  = tarefas.filter(t => t.atrasada == 1);
        const proximas   = tarefas.filter(t => {
            const diff = (new Date(t.data_agendada) - new Date()) / 60000; // em minutos
            return diff > 0 && diff &lt;= 30;
        });

        // Atualiza badge no menu (se existir)
        const badge = document.getElementById('badge-tarefas');
        if (badge) {
            const total = tarefas.length;
            badge.textContent = total;
            badge.style.display = total > 0 ? 'inline-block' : 'none';
        }

        // Notifica tarefas atrasadas
        atrasadas.forEach(t => {
            if (!sessionStorage.getItem('notif_' + t.id)) {
                mostrarNotificacaoTarefa(t, 'atrasada');
                sessionStorage.setItem('notif_' + t.id, '1');
            }
        });

        // Notifica tarefas próximas (30 min)
        proximas.forEach(t => {
            if (!sessionStorage.getItem('prox_' + t.id)) {
                mostrarNotificacaoTarefa(t, 'proxima');
                sessionStorage.setItem('prox_' + t.id, '1');
            }
        });

    } catch(e) { /* silencioso */ }
}

function mostrarNotificacaoTarefa(tarefa, tipo) {
    const icone = {
        whatsapp: '💬', ligacao: '📞', email: '📧', visita: '🤝', outro: '📌'
    };
    const cor   = tipo === 'atrasada' ? '#dc2626' : '#f59e0b';
    const label = tipo === 'atrasada' ? '⚠️ ATRASADA' : '⏰ em 30min';
    const hora  = new Date(tarefa.data_agendada).toLocaleTimeString('pt-BR', {hour:'2-digit', minute:'2-digit'});

    const notif = document.createElement('div');
    notif.className = 'notif-tarefa';
    notif.style.cssText = `
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: white;
        border-left: 4px solid ${cor};
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        padding: 14px 18px;
        z-index: 999999;
        max-width: 320px;
        animation: slideInRight 0.3s ease;
        cursor: pointer;
    `;

    notif.innerHTML = `
        <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
            <div>
                <div style="font-size:11px; color:${cor}; font-weight:700; margin-bottom:3px;">
                    ${label} — ${hora}
                </div>
                <div style="font-weight:600; font-size:13px; color:#111;">
                    ${icone[tarefa.tipo] || '📌'} ${tarefa.titulo}
                </div>
                <div style="font-size:12px; color:#6b7280; margin-top:3px;">
                    👤 ${tarefa.cliente_nome}
                </div>
            </div>
            <button onclick="this.closest('.notif-tarefa').remove()" 
                style="background:none; border:none; color:#9ca3af; cursor:pointer; font-size:16px; padding:0;">×</button>
        </div>
    `;

    // Fecha automaticamente em 8 segundos
    document.body.appendChild(notif);
    setTimeout(() => notif.remove(), 8000);
}

// CSS da animação
const styleNotif = document.createElement('style');
styleNotif.textContent = `
    @keyframes slideInRight {
        from { transform: translateX(100%); opacity: 0; }
        to   { transform: translateX(0);   opacity: 1; }
    }
    .notif-tarefa:hover { transform: scale(1.01); }
`;
document.head.appendChild(styleNotif);
</script>