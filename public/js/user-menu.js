class UserMenuController {
    constructor() {
        this.menu = null;
        this.menuToggle = null;
        this.dropdown = null;
        this.isInitialized = false;
        this.isOpen = false;
        this.sidebar = document.getElementById('sidebar');
    }

    init() {
        if (this.isInitialized) return;

        var userMenuContainer = document.getElementById('user-menu-container');
        if (userMenuContainer) {
            this.menu = document.getElementById('user-menu-control');
            this.menuToggle = document.getElementById('user-menu-toggle');
            this.dropdown = document.getElementById('user-menu-dropdown');

            if (this.menu && this.menuToggle) {
                this.setupEventListeners();
            }
        }

        this.isInitialized = true;
    }

    setupEventListeners() {
        this.menuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            userMenuController.toggleMenu();
        });

        document.addEventListener('click', function(e) {
            if (userMenuController.menu && !userMenuController.menu.contains(e.target)) {
                userMenuController.closeMenu();
            }
        });

        this.observeSidebar();

        window.addEventListener('resize', function() {
            userMenuController.closeMenu();
        });
    }

    toggleMenu() {
        if (this.isOpen) {
            this.closeMenu();
        } else {
            this.openMenu();
        }
    }

    openMenu() {
        if (!this.menu) return;

        if (this.sidebar && this.sidebar.classList.contains('collapsed')) {
            this.sidebar.classList.remove('collapsed');
            var self = this;
            setTimeout(function() {
                self.menu.classList.add('active');
                self.isOpen = true;
                self.closeOtherControls();
            }, 100);
        } else {
            this.menu.classList.add('active');
            this.isOpen = true;
            this.closeOtherControls();
        }
    }

    closeMenu() {
        if (!this.menu || !this.isOpen) return;
        this.menu.classList.remove('active');
        this.isOpen = false;
    }

    closeOtherControls() {
        if (window.selectControls && typeof window.selectControls.closeAll === 'function') {
            window.selectControls.closeAll();
        }
    }

    observeSidebar() {
        if (!this.sidebar) return;
        var self = this;

        var observer = new MutationObserver(function(mutations) {
            mutations.forEach(function(mutation) {
                if (mutation.attributeName === 'class') {
                    if (self.sidebar.classList.contains('collapsed')) {
                        self.closeMenu();
                    }
                }
            });
        });

        observer.observe(this.sidebar, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
}

var userMenuController = new UserMenuController();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() { userMenuController.init(); }, 100);
    });
} else {
    setTimeout(function() { userMenuController.init(); }, 100);
}

window.userMenu = {
    toggle: function() { userMenuController.toggleMenu(); },
    open: function() { userMenuController.openMenu(); },
    close: function() { userMenuController.closeMenu(); },
    isOpen: function() { return userMenuController.isOpen; }
};
