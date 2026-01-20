@php
    // Define valores padrão para evitar erros
    $modalId = $modalId ?? 'modal-default';
    $title = $title ?? 'Título do Modal';
    $showLogo = $showLogo ?? true;
    $logoUrl = $logoUrl ?? asset('images/logo-top-sigmah.svg');
    $logoAlt = $logoAlt ?? 'Logo';
    $subtitle = $subtitle ?? null;
    $content = $content ?? null;
    $contentUrl = $contentUrl ?? false;
@endphp

<!-- Modal para Termos e Políticas -->
<div id="{{ $modalId }}" class="modal-termos" style="display: none;">
    <div class="modal-termos-content">
        <!-- Cabeçalho -->
        <div class="modal-termos-header">
            <div class="modal-termos-header-content">
                @if ($showLogo)
                    <img src="{{ $logoUrl }}" alt="{{ $logoAlt }}" class="modal-termos-logo" />
                @endif
                <div class="modal-termos-header-content-title">
                    <h2>{{ $title }}</h2>
                    @if ($subtitle)
                        <p>{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <span class="modal-termos-close">&times;</span>
        </div>

        <!-- Conteúdo -->
        <div class="modal-termos-body">
            <!-- Loading -->
            <div class="modal-termos-loading" style="display: none;">
                <div class="modal-termos-spinner"></div>
                <p>Carregando...</p>
            </div>

            <!-- Conteúdo principal -->
            <div class="modal-termos-content-text">
                <p>Conteúdo será carregado...</p>
            </div>

            <!-- Erro -->
            <div class="modal-termos-error" style="display: none;">
                <strong>Erro:</strong> <span class="modal-termos-error-text"></span>
            </div>
        </div>
    </div>
</div>

<style>
    .modal-termos {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s ease-in-out;
    }

    .modal-termos-content {
        background-color: #ffffff;
        margin: 2% auto;
        border-radius: 8px;
        border: 1px solid #888;
        width: 85%;
        max-width: 900px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        animation: slideDown 0.3s ease-out;
    }

    .modal-termos-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 20px;
        background-color: #E3EBFF;
        padding: 30px;
        border-radius: 8px 8px 0 0;
        position: sticky;
        top: 0;
        z-index: 10;
    }

    .modal-termos-logo {
        height: 4.5rem;
        width: auto;
    }

    .modal-termos-header-content {
        display: flex;
        align-items: flex-start;
        gap: 2.25rem;
        flex: 1;
    }

    .modal-termos-header-content-title {
        display: flex;
        flex-direction: column;
        gap: 5px;
        width: 100%;
    }

    .modal-termos-header-content-title h2 {
        color: #000000;
        font-weight: bold;
        font-size: 1.5rem;
        margin: 0;
    }

    .modal-termos-header-content-title p {
        color: #575F6E;
        font-size: 1rem;
        margin: 0;
        line-height: 1.4;
    }

    .modal-termos-close {
        cursor: pointer;
        font-size: 40px;
        font-weight: 300;
        color: #5C5E64;
        line-height: 1;
        margin-left: 20px;
        transition: color 0.2s;
    }

    .modal-termos-close:hover {
        color: #000000;
    }

    .modal-termos-body {
        padding: 0 30px 30px 30px;
    }

    .modal-termos-content-text {
        font-size: 14px;
        line-height: 1.6;
        color: #333;
    }

    .modal-termos-content-text h3 {
        color: #1a365d;
        margin-top: 20px;
        margin-bottom: 10px;
        font-size: 1.2rem;
    }

    .modal-termos-content-text p {
        margin-bottom: 15px;
    }

    .modal-termos-content-text ul,
    .modal-termos-content-text ol {
        margin-bottom: 15px;
        padding-left: 20px;
    }

    .modal-termos-content-text li {
        margin-bottom: 5px;
    }

    .modal-termos-loading {
        text-align: center;
        padding: 40px;
    }

    .modal-termos-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #3388ff;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px auto;
    }

    .modal-termos-error {
        color: #d9534f;
        padding: 15px;
        background-color: #f2dede;
        border: 1px solid #ebccd1;
        border-radius: 4px;
        margin-top: 20px;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-50px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes spin {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    @media (max-width: 768px) {
        .modal-termos-content {
            width: 95%;
            margin: 5% auto;
            max-height: 95vh;
        }

        .modal-termos-header {
            flex-direction: column;
            gap: 15px;
            padding: 20px;
        }

        .modal-termos-header-content {
            flex-direction: column;
            gap: 15px;
        }

        .modal-termos-close {
            align-self: flex-end;
            margin: 0;
            font-size: 35px;
        }

        .modal-termos-body {
            padding: 0 20px 20px 20px;
        }
    }
</style>

<script>
    // DADOS FAKE - Simulando resposta da API
    const FAKE_API_DATA = {
        "termos-uso": {
            "content": `
                <h3>1. Aceitação dos Termos</h3>
                <p>Ao acessar este sistema, você concorda com os termos.</p>
                <h3>2. Uso do Sistema</h3>
                <p>Use apenas para fins legítimos.</p>
                <h3>3. Responsabilidades</h3>
                <p>Você é responsável por todas as atividades da sua conta.</p>
            `
        },
        "politicas-privacidade": {
            "content": `
                <h3>1. Coleta de Dados</h3>
                <p>Coletamos nome, e-mail e dados de uso.</p>
                <h3>2. Uso dos Dados</h3>
                <p>Dados são usados para fornecer serviços.</p>
                <h3>3. Segurança</h3>
                <p>Protegemos seus dados.</p>
            `
        }
    };

    // Inicializar modais
    document.addEventListener('DOMContentLoaded', function() {
        window.modals = window.modals || {};

        document.querySelectorAll('.modal-termos').forEach(modalElement => {
            const modalId = modalElement.id;
            if (modalId) {
                // Configurar botão de fechar
                const closeBtn = modalElement.querySelector('.modal-termos-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        modalElement.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    });
                }

                // Fechar ao clicar fora
                modalElement.addEventListener('click', (e) => {
                    if (e.target === modalElement) {
                        modalElement.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });

                // Armazenar referência
                window.modals[modalId] = modalElement;
            }
        });
    });

    // Função para abrir modal
    window.openModalFake = function(modalId, tipoConteudo) {
        const modal = document.getElementById(modalId);
        if (modal) {
            const contentContainer = modal.querySelector('.modal-termos-content-text');
            const loading = modal.querySelector('.modal-termos-loading');
            const errorContainer = modal.querySelector('.modal-termos-error');

            if (loading) loading.style.display = 'block';
            if (contentContainer) contentContainer.innerHTML = '';
            if (errorContainer) errorContainer.style.display = 'none';

            // Simular carregamento
            setTimeout(() => {
                if (loading) loading.style.display = 'none';

                const fakeData = FAKE_API_DATA[tipoConteudo];
                if (fakeData && contentContainer) {
                    contentContainer.innerHTML = fakeData.content;
                } else if (contentContainer) {
                    contentContainer.innerHTML = '<p>Conteúdo não disponível</p>';
                }

                modal.style.display = 'block';
                document.body.style.overflow = 'hidden';
            });
        }
    };
</script>
