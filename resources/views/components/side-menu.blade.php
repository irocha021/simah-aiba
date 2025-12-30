<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<nav class="sidebar" id="sidebar">
    <!-- Header fixo no topo -->
    <div class="sidebar-header">
        <div class="header-content">
            <img src="{{ asset('images/Logo-icon.svg') }}" alt="Logo" class="logo-svg">
            <span class="logo-text">Menu</span>
        </div>
    </div>

    <!-- Container principal com flex layout -->
    <div class="sidebar-container">
        <!-- Conteúdo com scroll -->
        <div class="sidebar-content">
            <ul class="sidebar-menu">
                <!-- Item Home -->
                <li class="menu-item {{ request()->is('home') ? 'active' : '' }}">
                    <a href="{{ url('/') }}" title="Home">
                        <i class="fas fa-home"></i>
                        <span class="menu-text">Home</span>
                    </a>
                </li>
                <!-- Item Dashboard -->
                <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
                    <a href="{{ url('/dashboard') }}" title="Dashboard V1">
                        <i class="fas fa-chart-line"></i>
                        <span class="menu-text">Dashboard V1</span>
                    </a>
                </li>
                <!-- Item Dashboard2 -->
                <li class="menu-item {{ request()->is('dashboard2') ? 'active' : '' }}">
                    <a href="{{ url('/dashboard2') }}" title="Dashboard V2">
                        <i class="fas fa-chart-bar"></i>
                        <span class="menu-text">Dashboard V2</span>
                    </a>
                </li>
                <!-- Submenu para Jobs -->
                <li class="menu-item has-submenu">
                    <a href="#" title="Jobs">
                        <i class="fas fa-tasks"></i>
                        <span class="menu-text">Jobs</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ url('jobs/hidroweb/inventory-station') }}">Inventário Estações</a></li>
                        <li><a href="{{ url('jobs/hidroweb/info-ana-adopted-telemetric-series-reading') }}">Séries
                                Teleméticas</a></li>
                        <li><a href="{{ url('jobs/hidroweb/readings/hidro-serie-qa') }}">Séries QA</a></li>
                        <li><a href="{{ url('jobs/lrgs/readings/dcp-messages') }}">Mensagens DCP</a></li>
                    </ul>
                </li>
                <!-- Submenu para Importações -->
                <li class="menu-item has-submenu">
                    <a href="#" title="Importações">
                        <i class="fas fa-file-import"></i>
                        <span class="menu-text">Importações</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </a>
                    <ul class="submenu">
                        <li><a href="{{ url('dbf-import') }}">Importar DBF</a></li>
                        <li><a href="{{ url('cnarh') }}">Importar CNARH</a></li>
                    </ul>
                </li>
                <!-- Separador -->
                <li class="menu-separator"></li>
                <!-- Configurações -->
                <li class="menu-item">
                    <a href="#" title="Configurações">
                        <i class="fas fa-cog"></i>
                        <span class="menu-text">Configurações</span>
                    </a>
                </li>
                <!-- Ajuda -->
                <li class="menu-item">
                    <a href="#" title="Ajuda">
                        <i class="fas fa-question-circle"></i>
                        <span class="menu-text">Ajuda</span>
                    </a>
                </li>
                <!-- Itens adicionais para demonstrar scroll -->
                <li class="menu-item">
                    <a href="#" title="Item Extra 1">
                        <i class="fas fa-plus"></i>
                        <span class="menu-text">Item Extra 1</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" title="Item Extra 2">
                        <i class="fas fa-plus"></i>
                        <span class="menu-text">Item Extra 2</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" title="Item Extra 3">
                        <i class="fas fa-plus"></i>
                        <span class="menu-text">Item Extra 3</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" title="Item Extra 4">
                        <i class="fas fa-plus"></i>
                        <span class="menu-text">Item Extra 4</span>
                    </a>
                </li>
                <li class="menu-item">
                    <a href="#" title="Item Extra 5">
                        <i class="fas fa-plus"></i>
                        <span class="menu-text">Item Extra 5</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Toggle button fixo na base -->
        <div class="sidebar-toggle">
            <div class="toggle-content">
                <img src="{{ asset('images/icons/user-menu.svg') }}" alt="Logo" class="user-icon">
                <span class="login-text">Login Privativo</span>
            </div>
        </div>
    </div>
</nav>

<!-- CSS DO COMPONENTE -->
<style>
    /* ===== ESTILOS DO SIDEBAR ===== */
    .sidebar {
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 70px;
        --transition-speed: 0.3s;
        --primary-blue: #3498db;
        --text-light: #ecf0f1;
        --text-gray: #bdc3c7;
        --hover-bg: rgba(52, 152, 219, 0.15);
        --header-height: 80px;
        --toggle-height: 70px;

        width: var(--sidebar-width);
        height: 100vh;
        background: #FFFFFF;
        color: black;
        position: fixed;
        left: 0;
        top: 0;
        overflow: hidden;
        /* Impede scroll na sidebar inteira */
        transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        box-shadow: 3px 0 15px rgba(0, 0, 0, 0.2);
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
        display: flex;
        flex-direction: column;
    }

    /* Estado collapsed */
    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    /* Container principal com flex layout */
    .sidebar-container {
        display: flex;
        flex-direction: column;
        flex: 1;
        min-height: 0;
        /* Importante para scroll interno */
    }

    /* Header fixo no topo */
    .sidebar-header {
        height: var(--header-height);
        padding: 0 20px;
        background: #FAFAFA;
        border-bottom: 1px solid #D9D9D9;
        display: flex;
        align-items: center;
        flex-shrink: 0;
        /* Impede que o header encolha */
        transition: padding var(--transition-speed);
    }

    .sidebar.collapsed .sidebar-header {
        padding: 0 10px;
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        transition: justify-content var(--transition-speed);
        width: 100%;
    }

    .sidebar.collapsed .header-content {
        justify-content: center;
    }

    .logo-svg {
        width: 2.5em;
        height: 2.5em;
        flex-shrink: 0;
    }

    .logo-text {
        font-size: 1.3rem;
        font-weight: 600;
        margin-left: 12px;
        /* Espaço entre imagem e texto */
        transition: opacity var(--transition-speed), transform var(--transition-speed);
        white-space: nowrap;
    }

    .sidebar.collapsed .logo-text {
        margin-left: 0;
    }

    /* Conteúdo com scroll */
    .sidebar-content {
        flex: 1;
        overflow-y: auto;
        overflow-x: hidden;
        min-height: 0;
        /* Importante para scroll funcionar */
        padding: 10px 0;
    }

    /* Menu items */
    .sidebar-menu {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .menu-item {
        position: relative;
        border-bottom: 1px solid rgba(255, 255, 255, 0.05);
    }

    .menu-item:last-child {
        border-bottom: none;
    }

    .menu-item a {
        display: flex;
        align-items: center;
        padding: 16px 20px;
        color: var(--text-gray);
        text-decoration: none;
        transition: all var(--transition-speed);
        position: relative;
        white-space: nowrap;
    }

    .sidebar.collapsed .menu-item a {
        padding: 16px 25px;
        justify-content: center;
    }

    .menu-item a:hover {
        background: var(--hover-bg);
        color: var(--text-light);
    }

    .sidebar:not(.collapsed) .menu-item a:hover {
        padding-left: 25px;
    }

    .menu-item.active a {
        background: rgba(52, 152, 219, 0.25);
        color: var(--text-light);
        border-left: 4px solid var(--primary-blue);
    }

    .menu-item i:first-child {
        font-size: 1.2rem;
        width: 24px;
        text-align: center;
        flex-shrink: 0;
        transition: margin-right var(--transition-speed);
    }

    .sidebar:not(.collapsed) .menu-item i:first-child {
        margin-right: 15px;
    }

    .menu-text {
        transition: all var(--transition-speed);
        white-space: nowrap;
        overflow: hidden;
    }

    /* Elementos escondidos quando colapsado */
    .sidebar.collapsed .logo-text,
    .sidebar.collapsed .login-text,
    .sidebar.collapsed .menu-text,
    .sidebar.collapsed .dropdown-icon {
        opacity: 0;
        transform: translateX(-10px);
        width: 0;
        overflow: hidden;
        display: inline-block;
        margin: 0;
    }

    /* Submenus */
    .has-submenu>a {
        position: relative;
    }

    .dropdown-icon {
        margin-left: auto;
        transition: all var(--transition-speed);
        font-size: 0.8rem;
        flex-shrink: 0;
    }

    .has-submenu.active .dropdown-icon {
        transform: rotate(180deg);
    }

    .submenu {
        list-style: none;
        padding: 0;
        background: rgba(26, 37, 47, 0.95);
        display: none;
        animation: slideDown 0.3s ease;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .has-submenu.active .submenu {
        display: block;
    }

    .submenu li a {
        padding-left: 60px;
        padding-top: 12px;
        padding-bottom: 12px;
        font-size: 0.9em;
        color: #95a5a6;
    }

    .submenu li a:hover {
        background: rgba(52, 73, 94, 0.5);
        color: white;
    }

    .submenu li.active a {
        color: var(--primary-blue);
        background: rgba(52, 152, 219, 0.1);
    }

    /* Separador */
    .menu-separator {
        height: 1px;
        background: rgba(255, 255, 255, 0.1);
        margin: 10px 20px;
        transition: margin var(--transition-speed);
    }

    .sidebar.collapsed .menu-separator {
        margin: 10px 15px;
    }

    /* Toggle button fixo na base */
    .sidebar-toggle {
        height: var(--toggle-height);
        background-color: #FAFAFA;
        border-top: 1px solid #D9D9D9;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 0 20px;
        flex-shrink: 0;
        /* Fixa na base */
        transition: all var(--transition-speed);
    }

    .toggle-content {
        display: flex;
        align-items: center;
        width: 100%;
    }

    /* Quando expandido: alinha à esquerda com espaço */
    .sidebar:not(.collapsed) .sidebar-toggle {
        justify-content: flex-start;
    }

    .sidebar:not(.collapsed) .toggle-content {
        justify-content: flex-start;
    }

    /* Quando colapsado: centraliza */
    .sidebar.collapsed .sidebar-toggle {
        justify-content: center;
        padding: 0;
    }

    .sidebar.collapsed .toggle-content {
        justify-content: center;
    }

    .user-icon {
        width: 2.5em;
        height: 2.5em;
        flex-shrink: 0;
    }

    .login-text {
        font-size: 1.2rem;
        font-weight: 600;
        color: #5C5E64;
        margin-left: 12px;
        /* Espaço entre imagem e texto quando aberto */
        transition: opacity var(--transition-speed), transform var(--transition-speed);
        white-space: nowrap;
    }

    .sidebar.collapsed .login-text {
        margin-left: 0;
    }

    /* Scrollbar personalizada para o conteúdo */
    .sidebar-content::-webkit-scrollbar {
        width: 6px;
    }

    .sidebar-content::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }

    .sidebar-content::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }

    .sidebar-content::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }

    /* Ajusta o conteúdo principal quando sidebar muda */
    .sidebar~* {
        transition: margin-left var(--transition-speed);
        margin-left: var(--sidebar-width);
    }

    .sidebar.collapsed~* {
        margin-left: var(--sidebar-collapsed-width);
    }

    /* Mobile */
    @media (max-width: 768px) {
        .sidebar {
            transform: translateX(-100%);
            width: var(--sidebar-width);
        }

        .sidebar.active {
            transform: translateX(0);
        }

        .sidebar~* {
            margin-left: 0 !important;
        }

        .mobile-toggle {
            display: block;
            position: fixed;
            top: 20px;
            left: 20px;
            z-index: 999;
            background: var(--primary-blue);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 4px;
            cursor: pointer;
        }
    }
</style>

<!-- JAVASCRIPT DO COMPONENTE -->
<script>
    (function() {
        // Aguarda o DOM estar pronto
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

            // Estado inicial: collapsed (só ícones)
            setTimeout(() => {
                sidebar.classList.add('collapsed');
            }, 100);

            // Hover para expandir
            sidebar.addEventListener('mouseenter', handleMouseEnter);
            sidebar.addEventListener('mouseleave', handleMouseLeave);

            // Submenus
            const submenuItems = sidebar.querySelectorAll('.has-submenu > a');
            submenuItems.forEach(item => {
                item.addEventListener('click', handleSubmenuClick);
            });

            // Fecha submenus quando sidebar colapsa
            sidebar.addEventListener('transitionend', handleTransitionEnd);

        }

        function createMobileToggle() {
            if (window.innerWidth > 768) return;

            const toggleBtn = document.createElement('button');
            toggleBtn.className = 'mobile-toggle';
            toggleBtn.innerHTML = '<i class="fas fa-bars"></i>';
            toggleBtn.setAttribute('aria-label', 'Alternar menu');

            toggleBtn.addEventListener('click', function() {
                const sidebar = document.getElementById('sidebar');
                sidebar.classList.toggle('active');
                this.innerHTML = sidebar.classList.contains('active') ?
                    '<i class="fas fa-times"></i>' :
                    '<i class="fas fa-bars"></i>';
            });

            document.body.appendChild(toggleBtn);
        }

        function handleMouseEnter() {
            if (this.classList.contains('collapsed')) {
                this.classList.remove('collapsed');
                this.classList.add('expanded');
            }
        }

        function handleMouseLeave() {
            if (this.classList.contains('expanded') &&
                !this.classList.contains('pinned')) {
                this.classList.remove('expanded');
                this.classList.add('collapsed');
                closeAllSubmenus();
            }
        }

        function handleSubmenuClick(e) {
            if (this.parentElement.querySelector('.submenu')) {
                e.preventDefault();
                e.stopPropagation();

                const isActive = this.parentElement.classList.contains('active');
                const sidebar = this.closest('.sidebar');

                // Fecha outros submenus
                if (!isActive && !sidebar.classList.contains('expanded')) {
                    closeAllSubmenus();
                }

                // Alterna estado
                this.parentElement.classList.toggle('active');

                // Se sidebar está collapsed e abriu submenu, expande
                if (sidebar.classList.contains('collapsed') &&
                    this.parentElement.classList.contains('active')) {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.add('expanded');
                }
            }
        }

        function handleTransitionEnd(e) {
            if (e.propertyName === 'width' &&
                this.classList.contains('collapsed')) {
                closeAllSubmenus();
            }
        }

        function closeAllSubmenus() {
            document.querySelectorAll('.has-submenu.active').forEach(active => {
                active.classList.remove('active');
            });
        }

        // Resize handler para mobile
        window.addEventListener('resize', function() {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-toggle');

            if (window.innerWidth > 768) {
                if (mobileToggle) mobileToggle.remove();
                sidebar.classList.remove('active');
            } else {
                createMobileToggle();
                sidebar.classList.add('collapsed');
                sidebar.classList.remove('expanded', 'pinned');
            }
        });

        // Fecha sidebar ao clicar fora (mobile)
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const mobileToggle = document.querySelector('.mobile-toggle');

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
    })();
</script>
