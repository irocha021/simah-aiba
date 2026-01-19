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
                            <a href="user/profile" class="user-menu-option">
                                <img src="{{ asset('images/icons/account-circle.svg') }}" alt="Logo"
                                    class="user-menu-icon">
                                <span class="user-option-name">Perfil</span>
                            </a>
                            <a href="user/password" class="user-menu-option">
                                <img src="{{ asset('images/icons/key-vertical.svg') }}" alt="Logo"
                                    class="user-menu-icon">
                                <span class="user-option-name">Alterar senha</span>
                            </a>
                            <a href="/logout" class="user-menu-option user-logout-link">
                                <img src="{{ asset('images/icons/Log-out.svg') }}" alt="Logo"
                                    class="user-menu-icon">
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
<script src="{{ asset('js/user-menu.js') }}"></script>
