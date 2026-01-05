<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"
    integrity="sha512-..." crossorigin="anonymous" referrerpolicy="no-referrer" />
<link rel="stylesheet" href="{{ asset('css/side-menu.css') }}">

<nav class="sidebar" id="sidebar">
    <!-- Header fixo no topo -->
    <div class="sidebar-header">
        <div class="header-content">
            <!-- Imagem quando FECHADO (sempre visível) -->
            <img src="{{ asset('images/Logo-icon.svg') }}" alt="Logo" class="logo-icon-closed">

            <!-- Imagem quando ABERTO (só aparece quando expandido) -->
            <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo Completo" class="logo-icon-open">
        </div>
    </div>

    <!-- Container principal com flex layout -->
    <div class="sidebar-container">
        <!-- Conteúdo com scroll -->
        <div class="sidebar-content">
            <ul class="sidebar-menu">

                <!-- ===== CONTROLE DE CAMADAS ===== -->
                <li class="menu-item layer-control-container active">
                    <div class="layer-control-header">
                        <!-- Ícone para menu ABERTO (expandido) -->
                        <img src="{{ asset('images/icons/gota-camada-bold.svg') }}" alt="Camadas" class="layer-icon-open">
                        
                        <!-- Ícone para menu FECHADO (colapsado) -->
                        <img src="{{ asset('images/icons/gota-camada.svg') }}" alt="Camadas" class="layer-icon-closed">
                        
                        <span class="menu-text">Camadas do Mapa</span>
                        <i class="fas fa-chevron-down dropdown-icon layer-dropdown"></i>
                    </div>
                    
                    <div class="layer-tooltip">Controle de Camadas</div>
                    
                    <div class="layer-control-dropdown">
                        <div class="layer-control-content">
                            <div class="layer-select">
                                <div class="layer-buttons" id="layer-control-buttons">
                                    <!-- Dinâmico -->
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- ===== FIM CONTROLE DE CAMADAS ===== -->

                <li class="menu-item">
                    <a href="#" title="Item Extra 1">
                        <i class="fas fa-plus"></i>
                        <span class="menu-text">Item Extra 1</span>
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

<!-- CONTROLE DE CAMADAS -->
<script>
    // ===== FUNÇÃO PARA MIGRAR CONTROLE DE CAMADAS =====
    function initLayerControl() {
        // Configurações das camadas
        const layerConfig = {
            'cnarh': {
                name: 'CNARH',
                color: '#A47864',
                icon: 'fas fa-tint'
            },
            'hidroweb_qualidade_agua': {
                name: 'HidroWeb - Qualidade da Água',
                color: '#3388ff',
                icon: 'fas fa-flask'
            },
            'hidroweb_telemetria': {
                name: 'HidroWeb - Telemetria',
                color: '#00cc66',
                icon: 'fas fa-satellite-dish'
            },
            'lrgs_client': {
                name: 'LRGS Client (DCP)',
                color: '#ff7800',
                icon: 'fas fa-satellite'
            },
            'pocos_rimas': {
                name: 'Poços RIMAS',
                color: '#ff0000',
                icon: 'fas fa-water'
            },
            'pocos_siagas': {
                name: 'Poços SIAGAS',
                color: '#e16ccf',
                icon: 'fas fa-oil-well'
            }
        };

        // Variáveis de estado
        let layerCounts = {};
        let clusterGroups = {};
        let isMenuCollapsed = true;
        let dropdownWasOpen = true; // Inicia aberto
        let isInitialized = false;

        // Aguardar o mapa estar pronto
        setTimeout(() => {
            if (typeof map === 'undefined') {
                console.warn('Mapa não encontrado, tentando novamente em 1 segundo...');
                setTimeout(initLayerControl, 1000);
                return;
            }

            // Pegar os cluster groups do mapa global
            clusterGroups = window.clusterGroups;

            if (!clusterGroups) {
                console.warn('Cluster groups não encontrados');
                return;
            }

            // Contar marcadores em cada camada
            Object.keys(layerConfig).forEach(layerKey => {
                if (clusterGroups[layerKey]) {
                    const markers = clusterGroups[layerKey].getLayers();
                    layerCounts[layerKey] = markers.length;
                }
            });

            // Criar botões das camadas
            createLayerButtons();

            // Configurar toggle do controle
            setupLayerControlToggle();

            // Verificar estado inicial das camadas
            updateActiveStates();

            // Observar mudanças no estado do menu
            observeMenuState();

            // Configurar estado inicial
            setupInitialState();

            isInitialized = true;

        }, 1500);

        function setupInitialState() {
            const sidebar = document.getElementById('sidebar');
            const container = document.querySelector('.layer-control-container');

            if (!sidebar || !container) return;

            // Verificar estado inicial do menu
            isMenuCollapsed = sidebar.classList.contains('collapsed');

            // Se menu não está colapsado, abrir dropdown
            if (!isMenuCollapsed) {
                openLayerDropdown();
            }
        }

        function createLayerButtons() {
            const container = document.getElementById('layer-control-buttons');
            if (!container) return;

            container.innerHTML = '';

            Object.entries(layerConfig).forEach(([key, config], index) => {
                const button = document.createElement('button');
                button.className = 'layer-button active';
                button.dataset.layer = key;
                button.style.animationDelay = `${0.1 + (index * 0.05)}s`;

                button.innerHTML = `
                <span class="layer-color-indicator" style="background: ${config.color}"></span>
                <span class="layer-name">${config.name}</span>
                <span class="layer-count">${layerCounts[key] || 0}</span>
                <span class="layer-status"></span>
            `;

                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleLayer(key, this);
                });

                container.appendChild(button);
            });
        }

        function toggleLayer(layerKey, button) {
            if (!clusterGroups[layerKey]) return;

            const isActive = button.classList.contains('active');

            if (isActive) {
                // Desativar camada
                map.removeLayer(clusterGroups[layerKey]);
                button.classList.remove('active');
                button.querySelector('.layer-status').style.background = '#dc3545';
            } else {
                // Ativar camada
                map.addLayer(clusterGroups[layerKey]);
                button.classList.add('active');
                button.querySelector('.layer-status').style.background = '#28a745';
            }
        }

        function setupLayerControlToggle() {
            const header = document.querySelector('.layer-control-header');
            const container = document.querySelector('.layer-control-container');

            if (!header || !container) return;

            header.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const sidebar = document.getElementById('sidebar');

                // Se menu está colapsado, expande primeiro
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.add('expanded');
                    isMenuCollapsed = false;

                    // Aguardar transição e abrir dropdown
                    setTimeout(() => {
                        openLayerDropdown();
                    }, 100);
                } else {
                    // Se já está expandido, toggle dropdown
                    toggleLayerDropdown();
                }

                // Fechar outros submenus se existirem
                document.querySelectorAll('.has-submenu.active').forEach(item => {
                    if (!item.contains(this)) {
                        item.classList.remove('active');
                    }
                });
            });

            // Fechar dropdown quando clicar fora (exceto no próprio dropdown)
            document.addEventListener('click', function(e) {
                const container = document.querySelector('.layer-control-container');
                const dropdown = document.querySelector('.layer-control-dropdown');

                if (!container.contains(e.target) &&
                    !(dropdown && dropdown.contains(e.target))) {
                    if (!isMenuCollapsed) {
                        closeLayerDropdown();
                        dropdownWasOpen = false;
                    }
                }
            });
        }

        function openLayerDropdown() {
            const container = document.querySelector('.layer-control-container');
            if (!container) return;

            container.classList.add('active');
            dropdownWasOpen = true;

            // Forçar reflow para garantir que CSS será aplicado
            requestAnimationFrame(() => {
            });
        }

        function closeLayerDropdown() {
            const container = document.querySelector('.layer-control-container');
            if (!container) return;

            container.classList.remove('active');
            dropdownWasOpen = false;
        }

        function toggleLayerDropdown() {
            const container = document.querySelector('.layer-control-container');
            if (!container) return;

            if (container.classList.contains('active')) {
                closeLayerDropdown();
            } else {
                openLayerDropdown();
            }
        }

        function observeMenuState() {
            const sidebar = document.getElementById('sidebar');
            const container = document.querySelector('.layer-control-container');

            if (!sidebar || !container) return;

            // Observar mudanças no menu
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.attributeName === 'class') {
                        const wasCollapsed = isMenuCollapsed;
                        isMenuCollapsed = sidebar.classList.contains('collapsed');

                        // Se menu acabou de expandir
                        if (wasCollapsed && !isMenuCollapsed) {
                            // Restaurar estado do dropdown
                            if (dropdownWasOpen) {
                                setTimeout(() => {
                                    openLayerDropdown();
                                }, 50);
                            }
                        }

                        // Se menu acabou de colapsar
                        if (!wasCollapsed && isMenuCollapsed) {
                            // Apenas remover classe, CSS vai esconder
                            container.classList.remove('active');
                        }
                    }
                });
            });

            observer.observe(sidebar, {
                attributes: true,
                attributeFilter: ['class']
            });
        }

        function updateActiveStates() {
            Object.keys(layerConfig).forEach(layerKey => {
                if (clusterGroups[layerKey]) {
                    const button = document.querySelector(`.layer-button[data-layer="${layerKey}"]`);
                    if (button) {
                        const isActive = map.hasLayer(clusterGroups[layerKey]);
                        button.classList.toggle('active', isActive);

                        const status = button.querySelector('.layer-status');
                        if (status) {
                            status.style.background = isActive ? '#28a745' : '#dc3545';
                        }
                    }
                }
            });
        }

        function updateLayerCounts() {
            Object.keys(layerConfig).forEach(layerKey => {
                if (clusterGroups[layerKey]) {
                    const markers = clusterGroups[layerKey].getLayers();
                    const count = markers.length;
                    layerCounts[layerKey] = count;

                    const button = document.querySelector(
                        `.layer-button[data-layer="${layerKey}"] .layer-count`);
                    if (button) {
                        button.textContent = count;
                    }
                }
            });
        }

        // Expor funções para uso global
        window.layerControl = {
            updateCounts: updateLayerCounts,
            refresh: createLayerButtons,
            updateActiveStates: updateActiveStates,
            openDropdown: openLayerDropdown,
            closeDropdown: closeLayerDropdown,
            toggleDropdown: toggleLayerDropdown,
            isDropdownOpen: function() {
                const container = document.querySelector('.layer-control-container');
                return container ? container.classList.contains('active') : false;
            }
        };
    }

    // Inicializar quando o DOM estiver pronto
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLayerControl);
    } else {
        initLayerControl();
    }

    // Adicionar evento para garantir que dropdown abra quando menu expandir por hover
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('sidebar');
        if (sidebar) {
            sidebar.addEventListener('mouseenter', function() {
                if (this.classList.contains('collapsed')) {
                    // Quando expande por hover, verificar se precisa abrir dropdown
                    setTimeout(() => {
                        if (window.layerControl && window.layerControl.isDropdownOpen) {
                            if (window.layerControl.isDropdownOpen()) {
                                // Forçar re-aplicação do CSS
                                const container = document.querySelector(
                                    '.layer-control-container');
                                if (container) {
                                    container.classList.remove('active');
                                    setTimeout(() => {
                                        container.classList.add('active');
                                    }, 10);
                                }
                            }
                        }
                    }, 50);
                }
            });
        }
    });
</script>
