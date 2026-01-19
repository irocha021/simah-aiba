<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/side-menu.css') }}">

<nav class="sidebar" id="sidebar">
    <!-- Header fixo no topo -->
    <div class="sidebar-header">
        <div class="header-content">
            <img src="{{ asset('images/Logo-icon.svg') }}" alt="Logo" class="logo-icon-closed">
            <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo Completo" class="logo-icon-open">
        </div>
    </div>

    <!-- Container principal com flex layout -->
    <div class="sidebar-container">
        <!-- Conteúdo com scroll -->
        <div class="sidebar-content">
            <ul class="sidebar-menu">

                <!-- ===== CONTROLE DE CAMADAS ===== -->
                <li class="menu-item select-control" id="layer-control">
                    <div class="select-header">
                        <img src="{{ asset('images/icons/gota-camada-bold.svg') }}" alt="Camadas"
                            class="select-icon-open">
                        <img src="{{ asset('images/icons/gota-camada.svg') }}" alt="Camadas"
                            class="select-icon-closed">
                        <span class="select-text">Camadas</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>

                    <div class="select-dropdown">
                        <div class="select-content">
                            <div class="select-box">
                                <div class="select-options" id="layer-control-buttons">
                                    <!-- Dinâmico -->
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- ===== FIM CONTROLE DE CAMADAS ===== -->

                <!-- ===== TEXTO DE CONFIGURAÇÃO ===== -->
                <li class="config-text-item">
                    <div class="config-text-wrapper">
                        <span class="config-text-open">CONFIGURAÇÕES</span>
                        <span class="config-text-closed">CONFIG.</span>
                    </div>
                </li>
                <!-- ===== FIM TEXTO DE CONFIGURAÇÃO ===== -->

                <!-- ===== CONTROLE DE CADASTRO ===== -->
                {{-- <li class="menu-item select-control" id="filters-control">
                    <div class="select-header">
                        <img src="{{ asset('images/icons/cadastro-bold.svg') }}" alt="Filtros"
                            class="select-icon-open">
                        <img src="{{ asset('images/icons/cadastro.svg') }}" alt="Filtros" class="select-icon-closed">
                        <span class="select-text">Cadastro</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>

                    <div class="select-dropdown">
                        <div class="select-content">
                            <div class="select-box">
                                <div class="select-options">
                                    <button class="select-option">
                                        <span class="option-indicator" style="background: #ff6b6b"></span>
                                        <span class="option-name">Todos</span>
                                    </button>
                                    <button class="select-option">
                                        <span class="option-indicator" style="background: #4ecdc4"></span>
                                        <span class="option-name">Ativos</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </li> --}}
                <!-- ===== FIM CONTROLE DE CADASTRO ===== -->

                <!-- ===== CONTROLE DE UPLOAD ===== -->
                <li class="menu-item select-control" id="sort-control">
                    <div class="select-header">
                        <img src="{{ asset('images/icons/upload-page-bold.svg') }}" alt="Ordenar"
                            class="select-icon-open">
                        <img src="{{ asset('images/icons/upload-page.svg') }}" alt="Ordenar"
                            class="select-icon-closed">
                        <span class="select-text">Upload</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>

                    <div class="select-dropdown">
                        <div class="select-content">
                            <div class="select-box">
                                <div class="select-options">
                                    <!-- Links com classe específica -->
                                    <a href="{{ route('dbf-import.index') }}" class="select-link">
                                        <span class="option-name">Importar DBF</span>
                                    </a>
                                    <a href="{{ route('cnarh.index') }}" class="select-link">
                                        <span class="option-name">Importar CNRH</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- ===== FIM CONTROLE DE UPLOAD ===== -->
            </ul>
        </div>

        <!-- Toggle button fixo na base -->
        <div class="sidebar-toggle">

            <!-- Estado de não logado (mostrar link de login) -->
            <div id="login-state" style="display: none;">
                <a href="/login" class="user-login-link">
                    <div class="user-toggle-content">
                        <img src="{{ asset('images/icons/user-menu.svg') }}" alt="Logo" class="user-icon">
                        <span class="user-login-text">Login Privativo</span>
                    </div>
                </a>
            </div>

            <!-- Estado logado (mostrar menu do usuário) -->
            <div id="user-menu-container" style="display: none;">
                <div class="user-menu" id="user-menu-control">
                    <div class="user-menu-header" id="user-menu-toggle">
                        <!-- Avatar com iniciais (sempre visível) -->
                        <div class="user-avatar">
                            <span class="user-avatar-initials">AS</span>
                        </div>

                        <!-- Nome completo (menu expandido) -->
                        <div class="user-menu-info">
                            <span class="user-menu-name">Andre Silva</span>
                            <i class="fas fa-chevron-down user-dropdown-icon"></i>
                        </div>
                    </div>

                    <div class="user-menu-dropdown" id="user-menu-dropdown">
                        <div class="user-menu-options">
                            <a href="/perfil" class="user-menu-option">
                                <i class="fas fa-user user-menu-icon"></i>
                                <span class="user-option-name">Perfil</span>
                            </a>
                            <a href="/alterar-senha" class="user-menu-option">
                                <i class="fas fa-key user-menu-icon"></i>
                                <span class="user-option-name">Alterar senha</span>
                            </a>
                            <a href="/logout" class="user-menu-option user-logout-link">
                                <i class="fas fa-sign-out-alt user-menu-icon"></i>
                                <span class="user-option-name">Sair</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

        </div>
</nav>

<script src="{{ asset('js/side-menu.js') }}"></script>
<script src="{{ asset('js/select-controls.js') }}"></script>
<script src="{{ asset('js/layer-control.js') }}"></script>

<style>
    /* ===== ESTILOS GERAIS ===== */
    :root {
        --transition-speed: 0.3s;
    }

    /* ===== ESTILOS PARA MENU DO USUÁRIO ===== */

    /* Container do menu do usuário */
    .user-menu {
        width: 100%;
        height: auto;
        position: relative;
        margin: 0;
    }

    /* Header do menu do usuário */
    .user-menu-header {
        height: 6rem;
        display: flex;
        align-items: center;
        border: none;
        background: #fafafa;
        border-radius: 0;
        cursor: pointer;
        padding: 0 20px;
        position: relative;
        width: 100%;
        transition: background-color 0.2s ease;
    }

    /* Avatar com iniciais */
    .user-avatar {
        width: 3.5rem;
        height: 3.5rem;
        min-width: 3.5rem;
        border-radius: 50%;
        background: linear-gradient(135deg, #3498db, #2c3e50);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: 12px;
        flex-shrink: 0;
    }

    /* Menu colapsado - ajustes do avatar */
    .sidebar.collapsed .user-avatar {
        margin-right: 0;
    }

    /* Informações do usuário (nome + ícone) */
    .user-menu-info {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex: 1;
        min-width: 0;
        opacity: 1;
        visibility: visible;
        transition: all var(--transition-speed);
    }

    /* Menu colapsado - esconde informações */
    .sidebar.collapsed .user-menu-info {
        opacity: 0;
        visibility: hidden;
        width: 0;
    }

    /* Nome do usuário */
    .user-menu-name {
        font-weight: 600;
        font-size: 1.15rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
        margin-right: 10px;
        color: #333;
    }

    /* Ícone dropdown do usuário */
    .user-dropdown-icon {
        margin-left: auto;
        font-size: 0.8rem;
        transition: transform 0.3s ease;
        color: #333;
    }

    /* Rotaciona ícone quando menu está aberto */
    .user-menu.active .user-dropdown-icon {
        transform: rotate(180deg);
    }

    /* Dropdown do menu do usuário */
    .user-menu-dropdown {
        position: relative;
        top: 100%;
        left: 0;
        right: 0;
        overflow: hidden;
        z-index: 1000;
        max-height: 0;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s ease;
        margin: 0;
    }

    /* Quando dropdown está aberto */
    .user-menu.active .user-menu-dropdown {
        max-height: 500px;
        opacity: 1;
        visibility: visible;
    }

    /* ===== LINHA CONTÍNUA CINZA NAS OPÇÕES DO MENU ===== */

    /* Container das opções com linha lateral contínua */
    .user-menu-options {
        display: flex;
        flex-direction: column;
        position: relative;
        padding-left: 20px;
        /* Espaço para a linha */
    }

    /* Linha vertical contínua CINZA */
    .user-menu-options::before {
        content: '';
        position: absolute;
        left: 20px;
        /* Posição da linha */
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #cccccc;
        /* COR CINZA */
        opacity: 0.7;
        border-radius: 1px;
    }

    /* Cada opção do menu */
    .user-menu-option {
        display: flex;
        align-items: center;
        padding: 12px 20px;
        background: #fafafa;
        color: #333;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: left;
        text-decoration: none;
        border: none;
        width: 100%;
        position: relative;
        padding-left: 40px;
        /* Espaço para ícone */
    }

    /* Ícones das opções */
    .user-menu-icon {
        width: 20px;
        margin-right: 12px;
        font-size: 1rem;
        text-align: center;
        color: #5c5e64;
    }

    /* Nome das opções */
    .user-option-name {
        font-weight: 500;
    }

    /* Estado hover das opções */
    .user-menu-option:hover {
        background: #f0f0f0;
    }

    .user-menu-option:hover .user-menu-icon {
        color: #3498db;
    }

    /* Opção de logout específico */
    .user-logout-link {
        color: #e74c3c;
    }

    .user-logout-link .user-menu-icon {
        color: #e74c3c;
    }

    .user-logout-link:hover {
        background: #ffeaea;
        color: #e74c3c;
    }

    /* Estado de login */
    .user-login-link {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 6rem;
        width: 100%;
        text-decoration: none;
        color: #333;
        padding: 0 20px;
    }

    .user-login-link:hover {
        text-decoration: none;
        background: #fafafa;
    }

    .user-toggle-content {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .user-icon {
        width: 2.5rem;
        height: 2.5rem;
    }

    .user-login-text {
        font-weight: 600;
        font-size: 1rem;
        white-space: nowrap;
    }

    .sidebar.collapsed .user-login-text {
        display: none;
    }

    /* Ajuste do container do toggle para o menu do usuário */
    .sidebar-toggle {
        height: auto;
        min-height: 6rem;
        display: flex;
        align-items: stretch;
        padding: 0;
        overflow: visible;
        transition: min-height 0.3s ease;
        position: relative;
    }

    /* Quando menu do usuário está aberto */
    .user-menu.active {
        background: #fafafa;
        border-radius: 8px 8px 0 0;
        margin: 0 10px;
    }

    .user-menu.active .user-menu-header {
        background: #fafafa;
        padding: 0 15px;
    }

    .user-menu.active .user-menu-dropdown {
        background: #fafafa;
    }

    /* Para menu colapsado */
    .sidebar.collapsed .user-menu-header {
        padding: 0;
        justify-content: center;
    }

    .sidebar.collapsed .user-menu-header:hover {
        background: #fafafa;
    }

    /* Ajustes para menu colapsado - LINHA CONTÍNUA */
    .sidebar.collapsed .user-menu-options {
        padding-left: 15px;
    }

    .sidebar.collapsed .user-menu-options::before {
        left: 15px;
    }

    /* Menu colapsado - dropdown fica sobreposto */
    .sidebar.collapsed .user-menu-dropdown {
        position: absolute;
        bottom: auto;
        top: 100%;
        left: 50%;
        transform: translateX(-50%);
        width: 200px;
        border-radius: 8px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        z-index: 1001;
    }

    .sidebar.collapsed .user-menu.active .user-menu-dropdown {
        transform: translateX(-50%) translateY(0);
    }

    /* Animação para as opções aparecerem com delay */
    @keyframes fadeInUserOption {
        from {
            opacity: 0;
            transform: translateY(-5px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .user-menu.active .user-menu-option {
        animation: fadeInUserOption 0.3s ease forwards;
        opacity: 0;
    }

    .user-menu.active .user-menu-option:nth-child(1) {
        animation-delay: 0.1s;
    }

    .user-menu.active .user-menu-option:nth-child(2) {
        animation-delay: 0.15s;
    }

    .user-menu.active .user-menu-option:nth-child(3) {
        animation-delay: 0.2s;
    }

    /* ===== AJUSTES ADICIONAIS ===== */

    /* Garantir que a linha apareça em todo o height das opções */
    .user-menu.active .user-menu-dropdown {
        background: #fafafa;
    }

    /* Ajuste fino na linha para ficar mais visível */
    .user-menu-options::before {
        z-index: 1;
    }
</style>

<script>
    // ===== CONTROLE DE MENU DO USUÁRIO INDEPENDENTE =====

    // Configurações
    const USER_MENU_CONFIG = {
        isLoggedIn: true,
        currentUser: {
            firstName: "Andre",
            lastName: "Silva"
        }
    };

    // Classe para gerenciar o menu do usuário
    class UserMenuController {
        constructor() {
            this.menu = null;
            this.menuToggle = null;
            this.dropdown = null;
            this.isInitialized = false;
            this.isOpen = false;
            this.sidebar = document.getElementById('sidebar');
        }

        // Inicializar o menu
        init() {
            if (this.isInitialized) return;

            // Verificar estado de login
            if (USER_MENU_CONFIG.isLoggedIn) {
                this.showUserMenu();
            } else {
                this.showLoginState();
            }

            this.isInitialized = true;
        }

        // Mostrar menu do usuário (logado)
        showUserMenu() {
            const loginState = document.getElementById('login-state');
            const userMenuContainer = document.getElementById('user-menu-container');

            if (loginState) loginState.style.display = 'none';
            if (userMenuContainer) userMenuContainer.style.display = 'block';

            // Configurar elementos
            this.menu = document.getElementById('user-menu-control');
            this.menuToggle = document.getElementById('user-menu-toggle');
            this.dropdown = document.getElementById('user-menu-dropdown');

            if (this.menu && this.menuToggle) {
                this.setupEventListeners();
                this.updateUserInfo();
            }
        }

        // Mostrar estado de login (não logado)
        showLoginState() {
            const loginState = document.getElementById('login-state');
            const userMenuContainer = document.getElementById('user-menu-container');

            if (loginState) loginState.style.display = 'block';
            if (userMenuContainer) userMenuContainer.style.display = 'none';
        }

        // Atualizar informações do usuário
        updateUserInfo() {
            const {
                firstName,
                lastName
            } = USER_MENU_CONFIG.currentUser;

            // Atualizar iniciais do avatar
            const avatarInitials = document.querySelector('.user-avatar-initials');
            if (avatarInitials && firstName && lastName) {
                const initials = firstName.charAt(0) + lastName.charAt(0);
                avatarInitials.textContent = initials.toUpperCase();
            }

            // Atualizar nome completo
            const userName = document.querySelector('.user-menu-name');
            if (userName) {
                userName.textContent = `${firstName} ${lastName}`;
            }
        }

        // Configurar event listeners
        setupEventListeners() {
            // Toggle do menu
            this.menuToggle.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                this.toggleMenu();
            });

            // Fechar menu ao clicar fora
            document.addEventListener('click', (e) => {
                if (!this.menu.contains(e.target)) {
                    this.closeMenu();
                }
            });

            // Fechar menu ao colapsar sidebar
            this.observeSidebar();

            // Fechar menu ao redimensionar janela
            window.addEventListener('resize', () => {
                this.closeMenu();
            });
        }

        // Alternar menu (abrir/fechar)
        toggleMenu() {
            if (this.isOpen) {
                this.closeMenu();
            } else {
                this.openMenu();
            }
        }

        // Abrir menu
        openMenu() {
            if (!this.menu) return;

            // Se sidebar está colapsado, expande primeiro
            if (this.sidebar && this.sidebar.classList.contains('collapsed')) {
                this.sidebar.classList.remove('collapsed');

                // Aguarda a transição e abre o menu
                setTimeout(() => {
                    this.menu.classList.add('active');
                    this.isOpen = true;
                    this.closeOtherControls();
                }, 100);
            } else {
                // Abrir menu do usuário
                this.menu.classList.add('active');
                this.isOpen = true;
                this.closeOtherControls();
            }
        }

        // Fechar menu
        closeMenu() {
            if (!this.menu || !this.isOpen) return;

            this.menu.classList.remove('active');
            this.isOpen = false;
        }

        // Fechar outros controles (não interfere com select-controls.js)
        closeOtherControls() {
            // Fecha todos os .select-control se a função existir
            if (window.selectControls && typeof window.selectControls.closeAll === 'function') {
                window.selectControls.closeAll();
            }
        }

        // Observar mudanças no sidebar
        observeSidebar() {
            if (!this.sidebar) return;

            const observer = new MutationObserver((mutations) => {
                mutations.forEach((mutation) => {
                    if (mutation.attributeName === 'class') {
                        // Se sidebar colapsou, fechar menu do usuário
                        if (this.sidebar.classList.contains('collapsed')) {
                            this.closeMenu();
                        }
                    }
                });
            });

            observer.observe(this.sidebar, {
                attributes: true,
                attributeFilter: ['class']
            });
        }

        // Métodos públicos
        setUser(firstName, lastName) {
            USER_MENU_CONFIG.currentUser.firstName = firstName;
            USER_MENU_CONFIG.currentUser.lastName = lastName;
            this.updateUserInfo();
        }

        setLoginState(isLoggedIn) {
            USER_MENU_CONFIG.isLoggedIn = isLoggedIn;
            if (isLoggedIn) {
                this.showUserMenu();
            } else {
                this.showLoginState();
            }
        }
    }

    // Instanciar e exportar
    const userMenuController = new UserMenuController();

    // Inicializar quando DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            setTimeout(() => {
                userMenuController.init();
            }, 100);
        });
    } else {
        setTimeout(() => {
            userMenuController.init();
        }, 100);
    }

    // Exportar para uso global
    window.userMenu = {
        toggle: () => userMenuController.toggleMenu(),
        open: () => userMenuController.openMenu(),
        close: () => userMenuController.closeMenu(),
        isOpen: () => userMenuController.isOpen,
        setUser: (firstName, lastName) => userMenuController.setUser(firstName, lastName),
        setLoginState: (isLoggedIn) => userMenuController.setLoginState(isLoggedIn)
    };

    console.log('User Menu Controller initialized');
</script>
