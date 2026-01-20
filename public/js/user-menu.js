// Configurações
const USER_MENU_CONFIG = {
    isLoggedIn: false,
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
