<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />

<nav class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="header-content">
            <img src="{{ asset('images/Logo-icon.svg') }}" alt="Logo" class="logo-svg">
            <span class="logo-text">Menu</span>
        </div>
    </div>

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
    </ul>

    <!-- Toggle button -->
    <div class="sidebar-toggle" id="sidebarToggle">
        <i class="fas fa-chevron-left"></i>
    </div>
</nav>

<!-- CSS DO COMPONENTE -->
<style>
    /* ===== ESTILOS DO SIDEBAR ===== */
    .sidebar {
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 70px;
        --transition-speed: 0.3s;
        --primary-dark: #2c3e50;
        --primary-darker: #1a252f;
        --primary-blue: #3498db;
        --text-light: #ecf0f1;
        --text-gray: #bdc3c7;
        --hover-bg: rgba(52, 152, 219, 0.15);

        width: var(--sidebar-width);
        height: 100vh;
        background: #FFFFFF;
        color: black;
        position: fixed;
        left: 0;
        top: 0;
        overflow-y: auto;
        overflow-x: hidden;
        transition: all var(--transition-speed) cubic-bezier(0.4, 0, 0.2, 1);
        z-index: 1000;
        box-shadow: 3px 0 15px rgba(0, 0, 0, 0.2);
        border-top-right-radius: 20px;
        border-bottom-right-radius: 20px;
    }

    /* Estado collapsed */
    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    /* Ajusta o conteúdo principal quando sidebar muda */
    .sidebar~* {
        transition: margin-left var(--transition-speed);
        margin-left: var(--sidebar-width);
    }

    .sidebar.collapsed~* {
        margin-left: var(--sidebar-collapsed-width);
    }

    /* Header */
    .sidebar-header {
        padding: 25px 20px;
        background: #FAFAFA;
        border-bottom: 1px solid #D9D9D9;
        transition: padding var(--transition-speed);
    }

    .sidebar.collapsed .sidebar-header {
        padding: 20px 10px;
    }

    .header-content {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        transition: justify-content var(--transition-speed);
    }

    .sidebar.collapsed .header-content {
        justify-content: center;
    }

    .logo-svg {
        width: 3.12em;
    }

    .logo-icon {
        font-size: 1.5rem;
        margin-right: 15px;
        transition: margin-right var(--transition-speed);
    }

    .sidebar.collapsed .logo-icon {
        margin-right: 0;
    }

    .logo-text {
        font-size: 1.3rem;
        font-weight: 600;
        transition: opacity var(--transition-speed), transform var(--transition-speed);
    }

    .sidebar.collapsed .logo-text,
    .sidebar.collapsed .menu-text,
    .sidebar.collapsed .dropdown-icon {
        opacity: 0;
        transform: translateX(-10px);
        width: 0;
        overflow: hidden;
        display: inline-block;
    }

    /* Menu items */
    .sidebar-menu {
        list-style: none;
        padding: 15px 0;
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

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
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

    /* Toggle button */
    .sidebar-toggle {
        position: absolute;
        bottom: 20px;
        right: 10px;
        background: rgba(52, 152, 219, 0.3);
        color: white;
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all var(--transition-speed);
        opacity: 0.7;
        z-index: 1001;
    }

    .sidebar-toggle:hover {
        background: rgba(52, 152, 219, 0.6);
        opacity: 1;
        transform: scale(1.1);
    }

    .sidebar.collapsed .sidebar-toggle i {
        transform: rotate(180deg);
    }

    /* Scrollbar */
    .sidebar::-webkit-scrollbar {
        width: 5px;
    }

    .sidebar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }

    .sidebar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 3px;
    }

    .sidebar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
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

        .sidebar-toggle {
            display: none;
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

            // Toggle button click
            const toggleBtn = document.getElementById('sidebarToggle');
            if (toggleBtn) {
                toggleBtn.addEventListener('click', handleToggleClick);
            }

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

        function handleToggleClick(e) {
            e.stopPropagation();
            const sidebar = this.closest('.sidebar');

            if (sidebar.classList.contains('collapsed')) {
                sidebar.classList.remove('collapsed');
                sidebar.classList.add('expanded');
                sidebar.classList.add('pinned'); // Mantém expandido
            } else {
                sidebar.classList.remove('expanded');
                sidebar.classList.remove('pinned');
                sidebar.classList.add('collapsed');
                closeAllSubmenus();
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
