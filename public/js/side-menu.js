(function () {
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

        toggleBtn.addEventListener('click', function () {
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
    window.addEventListener('resize', function () {
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
    document.addEventListener('click', function (e) {
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