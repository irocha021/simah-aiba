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
                <li class="menu-item select-control active" id="layer-control">
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

                <!-- ===== EXEMPLO DE OUTRO CONTROLE ===== -->
                <li class="menu-item select-control" id="filters-control">
                    <div class="select-header">
                        <img src="{{ asset('images/icons/cadastro-bold.svg') }}" alt="Filtros" class="select-icon-open">
                        <img src="{{ asset('images/icons/cadastro.svg') }}" alt="Filtros" class="select-icon-closed">
                        <span class="select-text">Cadastro</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>

                    <div class="select-dropdown">
                        <div class="select-content">
                            <div class="select-box">
                                <div class="select-options">
                                    <button class="select-option active">
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
                </li>
                <!-- ===== FIM OUTRO CONTROLE ===== -->

                <!-- ===== EXEMPLO DE TERCEIRO CONTROLE ===== -->
                <li class="menu-item select-control" id="sort-control">
                    <div class="select-header">
                        <img src="{{ asset('images/icons/upload-page-bold.svg') }}" alt="Ordenar" class="select-icon-open">
                        <img src="{{ asset('images/icons/upload-page.svg') }}" alt="Ordenar" class="select-icon-closed">
                        <span class="select-text">Upload</span>
                        <i class="fas fa-chevron-down dropdown-icon"></i>
                    </div>

                    <div class="select-dropdown">
                        <div class="select-content">
                            <div class="select-box">
                                <div class="select-options">
                                    <button class="select-option active">
                                        <span class="option-name">Nome (A-Z)</span>
                                    </button>
                                    <button class="select-option">
                                        <span class="option-name">Nome (Z-A)</span>
                                    </button>
                                    <button class="select-option">
                                        <span class="option-name">Data</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <!-- ===== FIM TERCEIRO CONTROLE ===== -->
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

<script src="{{ asset('js/side-menu.js') }}"></script>
<script src="{{ asset('js/select-controls.js') }}"></script>
<script src="{{ asset('js/layer-control.js') }}"></script>
