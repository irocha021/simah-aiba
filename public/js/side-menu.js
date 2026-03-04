(function () {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSidebar);
    } else {
        initSidebar();
    }

    function initSidebar() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        // Cria botão toggle para mobile
        createMobileToggle();

        // Define estado inicial aberto
        sidebar.classList.remove('collapsed');
        sidebar.classList.add('expanded');

        // Configura o botão de toggle com seta
        setupToggleButton(sidebar);

        // Fecha submenus quando sidebar colapsa
        sidebar.addEventListener('transitionend', handleTransitionEnd);
    }

    function setupToggleButton(sidebar) {
        // Procura pelo botão de toggle existente
        let toggleBtn = document.getElementById('sidebarToggle');
        
        if (!toggleBtn) {
            // Se não existir, cria um novo
            toggleBtn = document.createElement('button');
            toggleBtn.className = 'sidebar-toggle-btn';
            toggleBtn.id = 'sidebarToggle';
            toggleBtn.setAttribute('aria-label', 'Toggle menu');
            toggleBtn.innerHTML = '<i class="fas fa-chevron-left"></i>';
            
            // Adiciona o botão ao sidebar (após o header)
            const header = sidebar.querySelector('.sidebar-header');
            if (header) {
                header.after(toggleBtn);
            } else {
                sidebar.prepend(toggleBtn);
            }
        }

        // Remove listeners antigos e adiciona novo
        toggleBtn.removeEventListener('click', handleToggleClick);
        toggleBtn.addEventListener('click', handleToggleClick);
    }

    function handleToggleClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const sidebar = document.getElementById('sidebar');
        
        // Alterna entre collapsed e expanded
        if (sidebar.classList.contains('collapsed')) {
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('expanded');
        } else {
            sidebar.classList.remove('expanded');
            sidebar.classList.add('collapsed');
            
            closeAllDropdowns();
        }
    }

    function closeAllDropdowns() {
        // Fecha todos os selects ativos
        document.querySelectorAll('.select-control.active').forEach(select => {
            select.classList.remove('active');
        });
        
        // Fecha todos os submenus ativos
        document.querySelectorAll('.has-submenu.active').forEach(submenu => {
            submenu.classList.remove('active');
        });
        
        // Fecha menu do usuário se estiver aberto
        const userMenu = document.querySelector('.user-menu.active');
        if (userMenu) {
            userMenu.classList.remove('active');
        }
    }

    function handleTransitionEnd(e) {
        if (e.propertyName === 'width' && 
            e.target.classList.contains('sidebar') &&
            e.target.classList.contains('collapsed')) {
            
            // Quando o sidebar termina de colapsar, fecha todos os dropdowns
            closeAllDropdowns();
        }
    }

    function createMobileToggle() {
        // Remove toggle antigo se existir
        const oldToggle = document.querySelector('.mobile-toggle');
        if (oldToggle) oldToggle.remove();

        if (window.innerWidth > 768) return;

        const toggleBtn = document.createElement('button');
        toggleBtn.className = 'mobile-toggle';
        toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
        toggleBtn.setAttribute('aria-label', 'Alternar menu');

        toggleBtn.addEventListener('click', function () {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('active');
            this.innerHTML = sidebar.classList.contains('active') ?
                '<i class="fas fa-times"></i>' :
                '<i class="fas fa-bars"></i>';
        });

        document.body.appendChild(toggleBtn);
    }

    // Resize handler para mobile
    window.addEventListener('resize', function () {
        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.querySelector('.mobile-toggle');

        if (window.innerWidth > 768) {
            if (mobileToggle) mobileToggle.remove();
            sidebar.classList.remove('active');
        } else {
            createMobileToggle();
            // No mobile, começa colapsado se não estiver ativo
            if (!sidebar.classList.contains('active')) {
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('expanded');
            }
        }
    });

    // Fecha sidebar ao clicar fora (mobile)
    document.addEventListener('click', function (e) {
        const sidebar = document.getElementById('sidebar');
        const mobileToggle = document.querySelector('.mobile-toggle');
        const toggleBtn = document.getElementById('sidebarToggle');

        // Ignora cliques no botão de toggle do desktop
        if (toggleBtn && toggleBtn.contains(e.target)) {
            return;
        }

        // Ignora cliques em elementos de select (para não fechar quando clicar neles)
        if (e.target.closest('.select-header') || e.target.closest('.select-dropdown')) {
            return;
        }

        // Ignora cliques no menu do usuário
        if (e.target.closest('.user-menu')) {
            return;
        }

        if (window.innerWidth <= 768 &&
            sidebar.classList.contains('active') &&
            !sidebar.contains(e.target) &&
            e.target !== mobileToggle &&
            !mobileToggle?.contains(e.target)) {
            sidebar.classList.remove('active');
            if (mobileToggle) {
                mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
            }
        }
    });

    // Inicializa o estado baseado no tamanho da tela
    function initializeSidebarState() {
        const sidebar = document.getElementById('sidebar');
        if (!sidebar) return;

        if (window.innerWidth <= 768) {
            // Mobile: começa colapsado
            sidebar.classList.add('collapsed');
            sidebar.classList.remove('expanded');
            createMobileToggle();
        } else {
            // Desktop: começa expandido (aberto)
            sidebar.classList.remove('collapsed');
            sidebar.classList.add('expanded');
        }
    }

    // Chama a inicialização do estado
    initializeSidebarState();
})();