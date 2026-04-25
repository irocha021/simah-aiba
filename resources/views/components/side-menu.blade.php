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

    <!-- Botão de toggle -->
    <button class="sidebar-toggle-btn" id="sidebarToggle" aria-label="Toggle menu">
        <i class="fas fa-chevron-left"></i>
    </button>

    <!-- Container principal com flex layout -->
    <div class="sidebar-container">
        <!-- Conteúdo com scroll -->
        <div class="sidebar-content">
            <ul class="sidebar-menu">
                <!-- ===== CONTROLE DE CAMADAS ===== -->
                <li class="menu-item select-control active" id="layer-control">{{-- active para camadas(aberto) --}}
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
                @auth
                    <!-- ===== TEXTO DE CONFIGURAÇÃO ===== -->
                    <li class="config-text-item">
                        <div class="config-text-wrapper">
                            <span class="config-text-open">CONFIGURAÇÕES</span>
                            <span class="config-text-closed">CONFIG.</span>
                        </div>
                    </li>
                    <!-- ===== FIM TEXTO DE CONFIGURAÇÃO ===== -->

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
                                        <a href="{{ route('poco-simah.import.select') }}" class="select-link">
                                            <span class="option-name">Importar SIMAH</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!-- ===== FIM CONTROLE DE UPLOAD ===== -->

                    <!-- ===== EQUIPAMENTOS ===== -->
                    <li class="menu-item select-control" id="equipamentos-control">
                        <div class="select-header">
                            <img src="{{ asset('images/icons/satellite-dish-bold.svg') }}" alt="Equipamentos"
                                class="select-icon-open">
                            <img src="{{ asset('images/icons/satellite-dish.svg') }}" alt="Equipamentos"
                                class="select-icon-closed">
                            <span class="select-text">Equipamentos</span>
                            <i class="fas fa-chevron-down dropdown-icon"></i>
                        </div>

                        <div class="select-dropdown">
                            <div class="select-content">
                                <div class="select-box">
                                    <div class="select-options">
                                        <a href="{{ route('lrgs-stations.index') }}" class="select-link">
                                            <span class="option-name">SIMAH</span>
                                        </a>
                                        <a href="{{ route('poco-simah.stations.index') }}" class="select-link">
                                            <span class="option-name">Poços SIMAH</span>
                                        </a>
                                        <a href="{{ route('hw-inventory-stations.index') }}" class="select-link">
                                            <span class="option-name">ANA/HidroWeb</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                    <!-- ===== FIM EQUIPAMENTOS ===== -->

                    <!-- ===== GERENCIAR USUARIOS ===== -->
                    <li class="menu-item select-control" id="user-management-control">
                        <div class="select-header" onclick="openUserManagementModal()">
                            <img src="{{ asset('images/icons/account-circle.svg') }}" alt="Usuarios"
                                class="select-icon-open">
                            <img src="{{ asset('images/icons/account-circle.svg') }}" alt="Usuarios"
                                class="select-icon-closed">
                            <span class="select-text">Gerenciar Usuários</span>
                        </div>
                    </li>
                    <!-- ===== FIM GERENCIAR USUARIOS ===== -->

                  @endauth
            </ul>
        </div>

        <!-- Toggle button fixo na base -->
        <div class="sidebar-toggle">

            @guest
                <!-- Estado de não logado (mostrar link de login) -->
                <div style="display: flex; flex-direction: column; width: 100%;">
                    <div id="login-state">
                        <a href="{{ route('login') }}" class="user-login-link">
                            <div class="user-toggle-content">
                                <img src="{{ asset('images/icons/user-menu.svg') }}" alt="Logo" class="user-icon">
                                <span class="user-login-text">Login Privativo</span>
                            </div>
                        </a>
                    </div>

                    <div style="text-align: center; padding: 6px 16px 10px; border-top: 1px solid #ebebeb;">
                        <a href="{{ route('api-key.form') }}" style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #165b9c; text-decoration: none; font-weight: 500;">
                            <i class="fas fa-key" style="font-size: 11px;"></i> Solicitar Acesso à API
                        </a>
                    </div>
                </div>
            @endguest

            @auth
                <!-- Estado logado (mostrar menu do usuário) -->
                <div id="user-menu-container">
                    <div class="user-menu" id="user-menu-control">
                        <div class="user-menu-header" id="user-menu-toggle">
                            <div class="user-avatar">
                                <span
                                    class="user-avatar-initials">{{ strtoupper(substr(Auth::user()->first_name, 0, 1) . substr(Auth::user()->last_name, 0, 1)) }}</span>
                            </div>
                            <div class="user-menu-info">
                                <span class="user-menu-name">{{ Auth::user()->name }}</span>
                                <i class="fas fa-chevron-down user-dropdown-icon"></i>
                            </div>
                        </div>

                        <div class="user-menu-dropdown" id="user-menu-dropdown">
                            <div class="user-menu-options">
                                <a href="{{ route('user.profile') }}" class="user-menu-option">
                                    <img src="{{ asset('images/icons/account-circle.svg') }}" alt="Perfil"
                                        class="user-menu-icon">
                                    <span class="user-option-name">Perfil</span>
                                </a>
                                <a href="{{ route('user.password') }}" class="user-menu-option">
                                    <img src="{{ asset('images/icons/key-vertical.svg') }}" alt="Senha"
                                        class="user-menu-icon">
                                    <span class="user-option-name">Alterar senha</span>
                                </a>
                                <form method="POST" action="{{ route('logout') }}" style="margin:0;padding:0;">
                                    @csrf
                                    <button type="submit" class="user-menu-option user-logout-link"
                                        style="width:100%;background:none;border:none;cursor:pointer;font-family:inherit;font-size:inherit;">
                                        <img src="{{ asset('images/icons/Log-out.svg') }}" alt="Sair"
                                            class="user-menu-icon">
                                        <span class="user-option-name">Sair</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endauth
        </div>
    </div>
</nav>

@auth
    @include('partials.user-management-modal')
@endauth

<script src="{{ asset('js/side-menu.js') }}"></script>
<script src="{{ asset('js/select-controls.js') }}"></script>
<script src="{{ asset('js/layer-control.js') }}"></script>
<script src="{{ asset('js/user-menu.js') }}"></script>
