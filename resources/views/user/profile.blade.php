<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/user.css') }}">
</head>

<body>
    <div class="profile-container">
        <!-- Lado esquerdo - Formulário -->
        <div class="profile-form-section">
            <!-- Área rolável que contém TUDO -->
            <div class="form-scrollable-wrapper">
                <div class="form-container">
                    <!-- Botão de voltar (canto superior direito) -->
                    <div class="back-btn-container">
                        <a href="/" class="back-btn">
                            <i class="fas fa-arrow-left"></i>
                            <span>Voltar</span>
                        </a>
                    </div>

                    <div class="form-header">
                        <h1>Editar Perfil</h1>
                        <p>Atualize suas informações pessoais</p>
                    </div>

                    <div class="alerts-container">
                        @if (session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                {{ session('success') }}
                            </div>
                        @endif

                        @if (session('error'))
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ session('error') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-triangle"></i>
                                @foreach ($errors->all() as $error)
                                    {{ $error }}<br>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('user.profile.update') }}" id="profileForm"
                        autocomplete="off">
                        @csrf
                        @method('PUT')

                        <!-- Informações Pessoais -->
                        <div class="form-section-header">
                            <h3><i class="fas fa-user-circle"></i> Informações Pessoais</h3>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="first_name">Nome *</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="first_name" name="first_name" class="form-control"
                                        value="{{ old('first_name', $user->first_name) }}" placeholder="Digite seu nome"
                                        required autocomplete="given-name">
                                </div>
                                <div class="error-container">
                                    <div class="field-error" id="first_nameError">
                                        @error('first_name')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="last_name">Sobrenome *</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-user"></i>
                                    <input type="text" id="last_name" name="last_name" class="form-control"
                                        value="{{ old('last_name', $user->last_name) }}"
                                        placeholder="Digite seu sobrenome" required autocomplete="family-name">
                                </div>
                                <div class="error-container">
                                    <div class="field-error" id="last_nameError">
                                        @error('last_name')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="email">E-mail *</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-envelope"></i>
                                    <input type="email" id="email" name="email" class="form-control"
                                        value="{{ old('email', $user->email) }}" placeholder="seu@email.com" required
                                        autocomplete="email">
                                </div>
                                <div class="error-container">
                                    <div class="field-error" id="emailError">
                                        @error('email')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="phone">Telefone</label>
                                <div class="input-with-icon">
                                    <i class="fas fa-phone"></i>
                                    <input type="tel" id="phone" name="phone" class="form-control"
                                        value="{{ old('phone', $user->phone) }}" placeholder="(00) 00000-0000"
                                        autocomplete="tel">
                                </div>
                                <div class="error-container">
                                    <div class="field-error" id="phoneError">
                                        @error('phone')
                                            {{ $message }}
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Botões de ação -->
                        <div class="btn-container">
                            <button type="submit" class="submit-btn" id="submitBtn">
                                <span class="btn-text" id="btnText">Salvar Alterações</span>
                                <i class="fas fa-spinner fa-spin btn-spinner" id="btnSpinner"
                                    style="display: none;"></i>
                            </button>

                            <a href="/" class="cancel-btn">
                                Cancelar
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lado direito - Instruções -->
        <div class="profile-info-section">
            <div class="info-container">
                <h2>Informações Importantes</h2>
                <p>
                    Mantenha seus dados atualizados para uma melhor experiência no sistema.
                </p>

                <div class="info-list">
                    <h3>Dicas para preenchimento:</h3>
                    <ul>
                        <li>Use seu nome completo conforme documentos oficiais</li>
                        <li>O e-mail deve ser válido e de acesso frequente</li>
                        <li>Informe um telefone para contato emergencial</li>
                        <li>Todos os campos marcados com * são obrigatórios</li>
                    </ul>
                </div>

                <div class="info-list">
                    <h3>Privacidade e Segurança:</h3>
                    <ul>
                        <li>Seus dados são protegidos conforme LGPD</li>
                        <li>As informações são usadas apenas para funcionalidades do sistema</li>
                        <li>Você pode solicitar a exclusão de seus dados a qualquer momento</li>
                        <li>Alterações são salvas automaticamente ao confirmar</li>
                    </ul>
                </div>

                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    <p>Após salvar as alterações, você será redirecionado para o dashboard.</p>
                </div>
            </div>
        </div>

        <!-- Botão flutuante para mobile -->
        <a href="/" class="floating-back-btn">
            <div class="btn-circle">
                <i class="fas fa-home"></i>
            </div>
            <span class="btn-label">Dashboard</span>
        </a>
    </div>

    <script src="{{ asset('js/user-profile.js') }}"></script>
</body>

</html>

<style>
    @import url("https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap");

    * {
        font-family: "Inter", sans-serif;
        text-decoration: none;
        color: inherit;
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        height: 100vh;
        overflow: hidden;
    }

    /* ===== CONTAINER PRINCIPAL ===== */
    .profile-container {
        display: flex;
        width: 100%;
        height: 100vh;
    }

    /* ===== LADO ESQUERDO - FORMULÁRIO ===== */
    .profile-form-section {
        flex: 1;
        min-width: 400px;
        max-width: 50%;
        background: #ffffff;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow-y: auto;
    }

    /* Wrapper para conteúdo rolável */
    .form-scrollable-wrapper {
        flex: 1;
        display: flex;
        flex-direction: column;
        padding: 40px;
        overflow-y: auto;
        min-height: 0;
    }

    .form-container {
        width: 100%;
        max-width: 530px;
        margin: auto;
        animation: fadeIn 0.8s ease-out;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* ===== CABEÇALHO DO FORMULÁRIO ===== */
    .form-header {
        text-align: center;
        margin-bottom: 30px;
        flex-shrink: 0;
    }

    .form-header h1 {
        font-size: 2.5rem;
        color: #333;
        margin-bottom: 10px;
        font-weight: 600;
    }

    .form-header p {
        color: #666;
        font-size: 1.1rem;
        line-height: 1.5;
    }

    /* ===== SEÇÕES DO FORMULÁRIO ===== */
    .form-section-header {
        margin: 30px 0 20px 0;
        padding-bottom: 10px;
        border-bottom: 2px solid #f0f0f0;
        flex-shrink: 0;
    }

    .form-section-header h3 {
        font-size: 1.3rem;
        color: #333;
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 5px;
    }

    .form-section-header h3 i {
        color: #165b9c;
    }

    /* ===== LINHAS DO FORMULÁRIO ===== */
    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 10px;
        flex-shrink: 0;
    }

    /* ===== GRUPOS DE FORMULÁRIO ===== */
    .form-group {
        margin-bottom: 20px;
        position: relative;
        flex-shrink: 0;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #444;
        font-size: 0.95rem;
    }

    /* ===== INPUTS COM ÍCONES ===== */
    .input-with-icon {
        position: relative;
    }

    .input-with-icon i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #165b9c;
        font-size: 1.1rem;
        z-index: 2;
    }

    .form-control {
        width: 100%;
        padding: 15px 15px 15px 45px;
        border: 1px solid #000000;
        border-radius: 25px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #ffffff;
        color: #000000;
    }

    .form-control::placeholder {
        color: #999;
        font-size: 0.95rem;
    }

    .form-control:focus {
        outline: none;
        border-color: #667eea;
        background: #fff;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-control.error {
        border-color: #dc3545;
        background: #fff5f5;
    }

    /* ===== CONTAINER DE ERRO ===== */
    .error-container {
        min-height: 0;
        height: 0;
        overflow: hidden;
        transition: all 0.3s ease;
        margin-top: 0;
        flex-shrink: 0;
    }

    .error-container:has(.field-error.show) {
        min-height: 24px;
        height: auto;
        margin-top: 5px;
    }

    /* ===== MENSAGENS DE ERRO ===== */
    .field-error {
        color: #dc3545;
        font-size: 0.85rem;
        display: flex;
        align-items: center;
        gap: 5px;
        opacity: 0;
        transform: translateY(-10px);
        transition: all 0.3s ease;
        max-height: 0;
        overflow: hidden;
        flex-shrink: 0;
    }

    .field-error.show {
        opacity: 1;
        transform: translateY(0);
        max-height: 50px;
    }

    /* ===== MENSAGENS DE ALERTA ===== */
    .alerts-container {
        flex-shrink: 0;
        margin-bottom: 20px;
    }

    .alert {
        padding: 15px 20px;
        border-radius: 10px;
        margin-bottom: 15px;
        font-size: 0.95rem;
        line-height: 1.5;
        animation: slideIn 0.5s ease-out;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(-20px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    .alert i {
        font-size: 1.2rem;
    }

    .alert-success {
        background: linear-gradient(135deg, #d4edda 0%, #c3e6cb 100%);
        color: #155724;
        border: 1px solid #b1dfbb;
    }

    .alert-success i {
        color: #28a745;
    }

    .alert-error {
        background: linear-gradient(135deg, #f8d7da 0%, #f5c6cb 100%);
        color: #721c24;
        border: 1px solid #f1b0b7;
    }

    .alert-error i {
        color: #dc3545;
    }

    /* ===== BOTÕES ===== */
    .btn-container {
        display: flex;
        gap: 15px;
        margin: 40px 0 20px 0;
        flex-shrink: 0;
    }

    .submit-btn {
        flex: 1;
        padding: 16px;
        background: #007952;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }

    .submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 7px 20px rgba(0, 121, 82, 0.4);
        background: #006b47;
    }

    .submit-btn:active {
        transform: translateY(0);
    }

    .submit-btn.loading {
        opacity: 0.8;
        cursor: not-allowed;
    }

    .submit-btn .btn-text {
        display: inline-block;
        transition: opacity 0.3s ease;
    }

    .submit-btn .btn-spinner {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
        display: none;
        font-size: 1.2rem;
    }

    .submit-btn.loading .btn-text {
        opacity: 0;
    }

    .submit-btn.loading .btn-spinner {
        display: block;
    }

    .cancel-btn {
        padding: 16px 30px;
        background: #6c757d;
        color: white;
        border: none;
        border-radius: 25px;
        font-size: 1.1rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        text-align: center;
        white-space: nowrap;
    }

    .cancel-btn:hover {
        background: #5a6268;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(108, 117, 125, 0.3);
    }

    /* ===== LADO DIREITO - INFORMAÇÕES ===== */
    .profile-info-section {
        flex: 1;
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)),
            url("../images/backgraund-loginpng.png");
        background-size: cover;
        background-position: center;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        padding: 40px;
        color: white;
        min-height: 100vh;
    }

    .info-container {
        max-width: 600px;
        text-align: center;
    }

    .info-container h2 {
        font-size: 2.5rem;
        margin-bottom: 20px;
        color: white;
    }

    .info-container p {
        font-size: 1.1rem;
        line-height: 1.6;
        margin-bottom: 30px;
        opacity: 0.9;
    }

    .info-list {
        text-align: left;
        margin-top: 30px;
    }

    .info-list h3 {
        font-size: 1.3rem;
        margin-bottom: 15px;
        color: white;
    }

    .info-list ul {
        list-style: none;
        padding: 0;
    }

    .info-list li {
        margin-bottom: 10px;
        padding-left: 25px;
        position: relative;
    }

    .info-list li:before {
        content: "✓";
        position: absolute;
        left: 0;
        color: #007952;
        font-weight: bold;
    }

    .info-note {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 12px;
        padding: 20px;
        margin-top: 30px;
        text-align: left;
        display: flex;
        gap: 15px;
        align-items: flex-start;
        border-left: 4px solid #007952;
    }

    .info-note i {
        font-size: 1.5rem;
        color: #007952;
        flex-shrink: 0;
    }

    .info-note p {
        margin: 0;
        font-size: 0.95rem;
        opacity: 0.9;
    }

    /* ===== BOTÃO DE VOLTAR ===== */
    .back-btn-container {
        position: absolute;
        top: 30px;
        right: 30px;
        z-index: 10;
    }

    .back-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 10px 20px;
        background: transparent;
        color: #165b9c;
        border: 2px solid #165b9c;
        border-radius: 25px;
        font-size: 0.95rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .back-btn:hover {
        background: #165b9c;
        color: white;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(22, 91, 156, 0.2);
    }

    /* Botão flutuante */
    .floating-back-btn {
        position: fixed;
        bottom: 30px;
        right: 30px;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
        text-decoration: none;
        animation: float 3s ease-in-out infinite;
    }

    .floating-back-btn .btn-circle {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #165b9c;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        box-shadow: 0 4px 15px rgba(22, 91, 156, 0.3);
        transition: all 0.3s ease;
    }

    .floating-back-btn .btn-label {
        background: white;
        padding: 4px 12px;
        border-radius: 15px;
        font-size: 0.85rem;
        color: #165b9c;
        font-weight: 500;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        opacity: 0;
        transform: translateY(10px);
        transition: all 0.3s ease;
    }

    .floating-back-btn:hover .btn-circle {
        background: #007952;
        transform: translateY(-5px);
        box-shadow: 0 6px 20px rgba(22, 91, 156, 0.4);
    }

    .floating-back-btn:hover .btn-label {
        opacity: 1;
        transform: translateY(0);
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    /* ===== RESPONSIVIDADE ===== */
    @media (max-width: 992px) {
        .profile-container {
            flex-direction: column;
        }

        .profile-form-section {
            max-width: 100%;
            min-width: 100%;
            order: 2;
            min-height: 60vh;
        }

        .profile-info-section {
            display: none;
        }

        .form-scrollable-wrapper {
            padding: 30px 20px;
        }

        .form-header h1 {
            font-size: 2rem;
        }
    }

    @media (max-width: 768px) {
        .form-row {
            grid-template-columns: 1fr;
        }

        .form-scrollable-wrapper {
            padding: 20px 15px;
        }

        .form-header h1 {
            font-size: 1.8rem;
        }

        .form-header p {
            font-size: 1rem;
        }

        .form-control {
            padding: 12px 12px 12px 40px;
            font-size: 0.95rem;
        }

        .submit-btn,
        .cancel-btn {
            padding: 14px;
            font-size: 1rem;
        }

        .btn-container {
            flex-direction: column;
        }

        .back-btn span {
            display: none;
        }

        .back-btn i {
            font-size: 1.2rem;
        }

        .floating-back-btn {
            bottom: 20px;
            right: 20px;
        }

        .floating-back-btn .btn-circle {
            width: 50px;
            height: 50px;
            font-size: 1.3rem;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Elementos DOM
        const profileForm = document.getElementById('profileForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const phoneInput = document.getElementById('phone');

        // Mostrar erros do Laravel
        document.querySelectorAll('.field-error').forEach(errorDiv => {
            if (errorDiv.textContent.trim() !== '') {
                errorDiv.classList.add('show');
                const input = document.getElementById(errorDiv.id.replace('Error', ''));
                if (input) {
                    input.classList.add('error');
                    errorDiv.closest('.error-container').style.minHeight = '24px';
                }
            }
        });

        // Limpar erros ao digitar
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('input', function() {
                const errorDiv = document.getElementById(this.id + 'Error');
                if (errorDiv) {
                    errorDiv.classList.remove('show');
                    this.classList.remove('error');
                    errorDiv.closest('.error-container').style.minHeight = '0';
                }
            });
        });

        // Máscara para telefone
        phoneInput.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');

            if (value.length > 10) {
                value = value.replace(/^(\d{2})(\d{5})(\d{4}).*/, '($1) $2-$3');
            } else if (value.length > 6) {
                value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
            } else if (value.length > 2) {
                value = value.replace(/^(\d{2})(\d{0,5})/, '($1) $2');
            } else if (value.length > 0) {
                value = value.replace(/^(\d*)/, '($1');
            }

            e.target.value = value;
        });

        // Validação em tempo real
        function validateField(input, errorDiv) {
            const value = input.value.trim();
            let isValid = true;
            let message = '';

            // Remover mensagens de erro anteriores
            errorDiv.classList.remove('show');
            input.classList.remove('error');
            errorDiv.closest('.error-container').style.minHeight = '0';

            // Validações específicas por campo
            switch (input.id) {
                case 'first_name':
                case 'last_name':
                    if (!value) {
                        isValid = false;
                        message = 'Este campo é obrigatório';
                    } else if (value.length < 2) {
                        isValid = false;
                        message = 'Mínimo 2 caracteres';
                    } else if (value.length > 100) {
                        isValid = false;
                        message = 'Máximo 100 caracteres';
                    }
                    break;

                case 'email':
                    if (!value) {
                        isValid = false;
                        message = 'Este campo é obrigatório';
                    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value)) {
                        isValid = false;
                        message = 'Digite um e-mail válido';
                    } else if (value.length > 255) {
                        isValid = false;
                        message = 'E-mail muito longo';
                    }
                    break;

                case 'phone':
                    const cleanPhone = value.replace(/\D/g, '');
                    if (cleanPhone && cleanPhone.length < 10) {
                        isValid = false;
                        message = 'Digite um telefone válido (com DDD)';
                    } else if (cleanPhone.length > 11) {
                        isValid = false;
                        message = 'Telefone muito longo';
                    }
                    break;
            }

            // Mostrar erro se houver
            if (!isValid && message) {
                errorDiv.textContent = message;
                errorDiv.classList.add('show');
                input.classList.add('error');
                errorDiv.closest('.error-container').style.minHeight = '24px';
            } else if (isValid) {
                // Se válido, marcar como sucesso
                input.classList.add('success');
            }

            return isValid;
        }

        // Validar ao sair do campo
        document.querySelectorAll('.form-control').forEach(input => {
            input.addEventListener('blur', function() {
                const errorDiv = document.getElementById(this.id + 'Error');
                if (errorDiv) {
                    validateField(this, errorDiv);
                }
            });

            input.addEventListener('input', function() {
                const errorDiv = document.getElementById(this.id + 'Error');
                if (errorDiv && this.classList.contains('success')) {
                    this.classList.remove('success');
                }
            });
        });

        // Simular salvamento bem-sucedido
        function simulateSuccessSave() {
            // Limpar todos os alerts existentes
            const alertsContainer = document.querySelector('.alerts-container');
            alertsContainer.innerHTML = '';

            // Mostrar mensagem de sucesso
            const successAlert = document.createElement('div');
            successAlert.className = 'alert alert-success';
            successAlert.innerHTML = `
            <i class="fas fa-check-circle"></i>
            Perfil atualizado com sucesso! Redirecionando para o dashboard...
        `;
            alertsContainer.appendChild(successAlert);

            // Animar o alert
            successAlert.style.animation = 'slideIn 0.5s ease-out';

            // Resetar classes de sucesso
            document.querySelectorAll('.form-control.success').forEach(input => {
                input.classList.remove('success');
            });

            // Simular redirecionamento após 2 segundos
            setTimeout(() => {
                window.location.href = "/";
            }, 1000);
        }

        // Função para verificar se todos os campos obrigatórios estão preenchidos
        function checkAllRequiredFields() {
            const requiredFields = ['first_name', 'last_name', 'email'];
            let allValid = true;

            requiredFields.forEach(fieldId => {
                const input = document.getElementById(fieldId);
                const errorDiv = document.getElementById(fieldId + 'Error');

                if (input && errorDiv) {
                    if (!validateField(input, errorDiv)) {
                        allValid = false;
                    }
                }
            });

            // Validar telefone (não obrigatório)
            const phoneErrorDiv = document.getElementById('phoneError');
            if (phoneInput && phoneErrorDiv && phoneInput.value.trim() !== '') {
                validateField(phoneInput, phoneErrorDiv);
            }

            return allValid;
        }

        // Submit do formulário
        profileForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            console.log('Formulário submetido - Modo de simulação ativado');

            // Validar todos os campos
            if (!checkAllRequiredFields()) {
                showAlert('Por favor, corrija os erros no formulário.', 'error');
                return;
            }

            // Enviar formulário (SIMULAÇÃO)
            try {
                // Mostrar loading
                submitBtn.classList.add('loading');
                btnSpinner.style.display = 'block';
                btnText.style.opacity = '0';
                submitBtn.disabled = true;

                // Simular delay de rede (1-2 segundos)
                const delay = Math.random() * 1000 + 1000;

                await new Promise(resolve => setTimeout(resolve, delay));

                // Simular resposta de sucesso
                const success = true; // Sempre simular sucesso

                if (success) {
                    // Simular salvamento bem-sucedido
                    simulateSuccessSave();
                } else {
                    // Isso não deve acontecer no modo de simulação
                    showAlert('Erro ao atualizar perfil.', 'error');
                }
            } catch (error) {
                console.error('Erro na simulação:', error);
                showAlert('Erro na simulação. Tente novamente.', 'error');
            } finally {
                // Não remover loading imediatamente - a mensagem de sucesso vai cuidar disso
                // O timeout no simulateSuccessSave vai redirecionar
            }
        });

        // Função para mostrar alertas temporários
        function showAlert(message, type) {
            // Limpar alerts existentes
            const alertsContainer = document.querySelector('.alerts-container');
            const tempAlerts = alertsContainer.querySelectorAll('.alert:not(.alert-success):not(.alert-error)');
            tempAlerts.forEach(alert => alert.remove());

            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type}`;
            alertDiv.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'check-circle'}"></i>
            ${message}
        `;

            alertsContainer.appendChild(alertDiv);

            // Animar entrada
            alertDiv.style.animation = 'slideIn 0.5s ease-out';

            // Remover após 5 segundos
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateX(100%)';
                alertDiv.style.transition = 'all 0.3s ease';
                setTimeout(() => {
                    if (alertDiv.parentNode) {
                        alertDiv.remove();
                    }
                }, 300);
            }, 5000);
        }

        // Validação inicial ao carregar a página
        document.querySelectorAll('.form-control').forEach(input => {
            if (input.value.trim() !== '') {
                const errorDiv = document.getElementById(input.id + 'Error');
                if (errorDiv) {
                    validateField(input, errorDiv);
                }
            }
        });

        // Focar no primeiro campo com erro
        const firstError = document.querySelector('.field-error.show');
        if (firstError) {
            const inputId = firstError.id.replace('Error', '');
            const input = document.getElementById(inputId);
            if (input) {
                input.focus();
            }
        }

        // Adicionar validação ao pressionar Enter
        profileForm.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA') {
                e.preventDefault();

                // Encontrar próximo campo
                const fields = Array.from(document.querySelectorAll('.form-control'));
                const currentIndex = fields.indexOf(e.target);

                if (currentIndex < fields.length - 1) {
                    fields[currentIndex + 1].focus();
                } else {
                    // Último campo, submeter formulário
                    submitBtn.click();
                }
            }
        });

        // Log para debug
        console.log('Perfil do usuário - Modo de simulação ativado');
        console.log('Todos os dados serão processados localmente');
        console.log('Ao preencher corretamente, será simulado um salvamento bem-sucedido');
    });
</script>
