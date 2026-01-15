// sidebar.js - VERSÃO FUNCIONAL SIMPLIFICADA
console.log('🚀 SIDEBAR.JS CARREGADO');

// Evitar execução dupla
if (window.sidebarLoaded) {
    console.log('⚠️ Sidebar já carregada, ignorando...');
} else {
    window.sidebarLoaded = true;

    document.addEventListener('DOMContentLoaded', function () {
        console.log('📦 DOM Carregado - Iniciando sidebar');



        // Elementos principais
        const sidebar = document.getElementById('sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        const mobileMenuBtn = document.getElementById('mobileMenuBtn');
        const sidebarOverlay = document.getElementById('sidebarOverlay');
        const body = document.body;

        // 1. Desativa animações inicialmente
        body.classList.remove('animations-ready');

        // 2. Aguarda um pouco antes de ativar animações
        setTimeout(() => {
            body.classList.add('animations-ready');
            console.log('✅ Animações ativadas');
        }, 100);

        // Elementos do dropdown
        const actionsToggle = document.getElementById('actionsToggle');
        const actionsDropdown = document.getElementById('actionsDropdown');

        // Estado
        let isExpanded = false;
        let isMobile = window.innerWidth <= 992;
        let isDropdownOpen = false;

        // INICIALIZAÇÃO PRINCIPAL
        function initAll() {
            console.log('🎯 Inicializando todos os componentes');

            // 1. Sidebar básica
            initSidebar();

            // 2. Dropdown dos 3 pontinhos
            initDropdown();

            // 3. Estado inicial
            if (!isMobile) {
                const savedState = localStorage.getItem('sidebarExpanded');
                if (savedState === 'true') {
                    expandSidebar();
                }
            }

            console.log('✅ Todos os componentes inicializados');
        }

        // ============================================
        // 1. SIDEBAR BÁSICA (expandir/retrair)
        // ============================================
        function initSidebar() {
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', toggleSidebar);
                updateToggleTooltip();
            }

            if (mobileMenuBtn) {
                mobileMenuBtn.addEventListener('click', toggleMobileSidebar);
            }

            if (sidebarOverlay) {
                sidebarOverlay.addEventListener('click', closeMobileSidebar);
            }

            body.classList.add('has-sidebar');
        }

        function toggleSidebar() {
            isExpanded ? collapseSidebar() : expandSidebar();
        }

        function expandSidebar() {
            sidebar.classList.add('expanded');
            body.classList.add('sidebar-expanded');
            isExpanded = true;
            updateToggleTooltip();
            if (!isMobile) localStorage.setItem('sidebarExpanded', 'true');
        }

        function collapseSidebar() {
            sidebar.classList.remove('expanded');
            body.classList.remove('sidebar-expanded');
            isExpanded = false;
            updateToggleTooltip();
            if (!isMobile) localStorage.setItem('sidebarExpanded', 'false');
        }

        function updateToggleTooltip() {
            if (sidebarToggle) {
                sidebarToggle.title = isExpanded ? "Esconder barra lateral" : "Mostrar barra lateral";
            }
        }

        function toggleMobileSidebar() {
            sidebar.classList.contains('active') ? closeMobileSidebar() : openMobileSidebar();
        }

        function openMobileSidebar() {
            sidebar.classList.add('active');
            if (sidebarOverlay) sidebarOverlay.classList.add('active');
            body.style.overflow = 'hidden';
        }

        function closeMobileSidebar() {
            sidebar.classList.remove('active');
            if (sidebarOverlay) sidebarOverlay.classList.remove('active');
            body.style.overflow = '';
        }

        // ============================================
        // 2. DROPDOWN DOS 3 PONTINHOS (SOLUÇÃO SIMPLIFICADA)
        // ============================================
        function initDropdown() {
            console.log('🎯 Inicializando dropdown...');

            if (!actionsToggle || !actionsDropdown) {
                console.error('❌ Elementos do dropdown não encontrados!');
                return;
            }

            // Estilos do dropdown são gerenciados pelo CSS (`assets/css/sidebar.css`).

            // Estilos do botão toggle são gerenciados pelo CSS (`assets/css/sidebar.css`).

            // Itens do dropdown: estilos e hover são gerenciados pelo CSS; manter confirmação de logout
            const dropdownItems = actionsDropdown.querySelectorAll('.dropdown-item');
            dropdownItems.forEach(item => {
                // Logout - confirmar
                if (item.classList.contains('logout-item')) {
                    item.addEventListener('click', function (e) {
                        e.preventDefault();
                        if (confirm('Tem certeza que deseja sair?')) {
                            window.location.href = this.href;
                        }
                        hideDropdown();
                    });
                }
            });

            // Mostrar/ocultar dropdown
            actionsToggle.addEventListener('click', function (e) {
                e.stopPropagation();
                e.preventDefault();

                if (isDropdownOpen) {
                    hideDropdown();
                } else {
                    showDropdown();
                }
            });

            // Fechar dropdown ao clicar fora
            document.addEventListener('click', function (e) {
                if (isDropdownOpen &&
                    !actionsDropdown.contains(e.target) &&
                    !actionsToggle.contains(e.target)) {
                    hideDropdown();
                }
            });

            // Fechar com ESC
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && isDropdownOpen) {
                    hideDropdown();
                }
            });

            console.log('✅ Dropdown inicializado');


        }

        function showDropdown() {
            if (!actionsDropdown) return;

            // Mostrar utilizando a classe 'show' (CSS usa !important)
            actionsDropdown.classList.add('show');
            isDropdownOpen = true;

            // z-index é controlado pelo CSS; não é necessário ajustar inline

            console.log('📌 Dropdown ABERTO');
        }

        function hideDropdown() {
            if (!actionsDropdown) return;

            // Remover classe 'show' para ocultar
            actionsDropdown.classList.remove('show');
            isDropdownOpen = false;

            console.log('📌 Dropdown FECHADO');
        }

        // ============================================
        // INICIALIZAR TUDO
        // ============================================
        initAll();

        // Resize handler
        window.addEventListener('resize', function () {
            const wasMobile = isMobile;
            isMobile = window.innerWidth <= 992;

            if (wasMobile !== isMobile) {
                if (isMobile) {
                    collapseSidebar();
                    closeMobileSidebar();
                    body.classList.remove('sidebar-expanded');
                }
            }
        });

        // API pública
        window.sidebarManager = {
            expand: expandSidebar,
            collapse: collapseSidebar,
            toggle: toggleSidebar,
            showDropdown: showDropdown,
            hideDropdown: hideDropdown
        };

        console.log('🎉 SIDEBAR PRONTA PARA USO!');
    });
}
