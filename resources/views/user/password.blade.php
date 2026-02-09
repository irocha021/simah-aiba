<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alterar Senha | SIMAH</title>
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
                        <h1>Alterar Senha</h1>
                        <p>Atualize sua senha de acesso</p>
                    </div>

                    <div class="alerts-container">
                        @if (session('info'))
                            <div class="alert alert-info" role="alert">
                                <i class="fas fa-info"></i>
                                {{ session('info') }}
                            </div>
                        @endif

                        @if (session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
                            </div>
                            <script>
                                setTimeout(function() {
                                    window.location.href = '/';
                                }, 2000);
                            </script>
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

                    <form method="POST" action="{{ route('user.password.update') }}" id="passwordForm"
                        autocomplete="off">
                        @csrf

                        <!-- Segurança da Conta -->
                        <div class="form-section-header">
                            <h3><i class="fas fa-lock"></i> Segurança da Conta</h3>
                        </div>

                        @if (!Auth::user()->must_change_password)
                        <div class="form-group">
                            <label for="current_password">Senha Atual *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" id="current_password" name="current_password"
                                    class="form-control" placeholder="Digite sua senha atual" required
                                    autocomplete="current-password">
                                <button type="button" class="toggle-password" data-target="current_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="error-container">
                                <div class="field-error" id="current_passwordError">
                                    @error('current_password')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>
                        @endif


                        <div class="form-group">
                            <label for="new_password">Nova Senha *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="new_password" name="new_password" class="form-control"
                                    placeholder="Digite a nova senha" required autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="new_password">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <span class="strength-text" id="strengthText">Força da senha</span>
                            </div>
                            <div class="error-container">
                                <div class="field-error" id="new_passwordError">
                                    @error('new_password')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="new_password_confirmation">Confirmar Nova Senha *</label>
                            <div class="input-with-icon">
                                <i class="fas fa-lock"></i>
                                <input type="password" id="new_password_confirmation" name="new_password_confirmation"
                                    class="form-control" placeholder="Confirme a nova senha" required
                                    autocomplete="new-password">
                                <button type="button" class="toggle-password" data-target="new_password_confirmation">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="error-container">
                                <div class="field-error" id="new_password_confirmationError">
                                    @error('new_password_confirmation')
                                        {{ $message }}
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="password-requirements">
                            <p><strong>Requisitos da senha:</strong></p>
                            <ul>
                                <li id="req-length"><i class="fas fa-check-circle"></i> Mínimo 6 caracteres</li>
                                <li id="req-uppercase"><i class="fas fa-check-circle"></i> Pelo menos uma letra
                                    maiúscula</li>
                                <li id="req-lowercase"><i class="fas fa-check-circle"></i> Pelo menos uma letra
                                    minúscula</li>
                                <li id="req-number"><i class="fas fa-check-circle"></i> Pelo menos um número</li>
                            </ul>
                        </div>

                        <!-- Botões de ação -->
                        <div class="btn-container">
                            <button type="submit" class="submit-btn" id="submitBtn">
                                <span class="btn-text" id="btnText">Alterar Senha</span>
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
                <h2>Segurança da Conta</h2>
                <p>
                    Mantenha sua conta segura com uma senha forte e única.
                </p>

                <div class="info-list">
                    <h3>Dicas para uma senha segura:</h3>
                    <ul>
                        <li>Use pelo menos 6 caracteres</li>
                        <li>Combine letras maiúsculas e minúsculas</li>
                        <li>Inclua números e caracteres especiais</li>
                        <li>Não use informações pessoais</li>
                    </ul>
                </div>

                <div class="info-list">
                    <h3>Boas práticas:</h3>
                    <ul>
                        <li>Altere sua senha periodicamente</li>
                        <li>Não compartilhe sua senha com ninguém</li>
                        <li>Use senhas diferentes para cada serviço</li>
                        <li>Ative a autenticação de dois fatores</li>
                    </ul>
                </div>

                <div class="info-note">
                    <i class="fas fa-info-circle"></i>
                    <p>Após alterar a senha, você será desconectado e precisará fazer login novamente.</p>
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
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.3);
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
        transition: all 0.3s ease;
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
        max-width: 500px;
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
        letter-spacing: -0.5px;
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

    .input-with-icon .toggle-password {
        position: absolute;
        right: 40px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #888;
        cursor: pointer;
        font-size: 1.1rem;
        padding: 5px;
        transition: color 0.3s ease;
        z-index: 2;
    }

    .input-with-icon .toggle-password:hover {
        color: #165b9c;
    }

    .form-control {
        width: 100%;
        padding: 15px 50px 15px 45px;
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

    /* ===== BARRA DE FORÇA DA SENHA ===== */
    .password-strength {
        margin-top: 10px;
        flex-shrink: 0;
    }

    .strength-bar {
        height: 6px;
        background: #e9ecef;
        border-radius: 3px;
        overflow: hidden;
        margin-bottom: 5px;
    }

    .strength-fill {
        height: 100%;
        width: 0%;
        border-radius: 3px;
        transition: all 0.3s ease;
    }

    .strength-fill.weak {
        width: 25%;
        background: #dc3545;
    }

    .strength-fill.fair {
        width: 60%;
        background: #ffc107;
    }

    .strength-fill.good {
        width: 100%;
        background: #28a745;
    }

    .strength-text {
        font-size: 0.85rem;
        color: #666;
    }

    /* ===== REQUISITOS DA SENHA ===== */
    .password-requirements {
        background: #f8f9fa;
        border-radius: 12px;
        padding: 20px;
        margin-top: 20px;
        flex-shrink: 0;
    }

    .password-requirements p {
        margin-bottom: 10px;
        color: #333;
        font-weight: 500;
    }

    .password-requirements ul {
        list-style: none;
    }

    .password-requirements li {
        margin-bottom: 8px;
        color: #666;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: color 0.3s ease;
    }

    .password-requirements li i {
        font-size: 0.9rem;
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
        box-shadow: 0 3px 10px rgba(0, 121, 82, 0.3);
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
        animation: slideDown 0.8s ease-out;
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

    /* ===== BOTÃO DE VOLTAR ===== */
    .back-btn-container {
        position: absolute;
        top: 30px;
        right: 30px;
        z-index: 10;
    }

    .alert-info {
        background-color: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
        border-radius: 8px;
        padding: 12px 16px;
        margin-bottom: 16px;
        font-size: 0.9rem;
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

    .back-btn i {
        font-size: 1rem;
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
            padding: 0;
            order: 2;
            min-height: 60vh;
        }

        .profile-info-section {
            display: none;
        }

        .form-scrollable-wrapper {
            padding: 30px 20px;
        }

        .form-container {
            padding: 20px 0;
        }

        .form-header h1 {
            font-size: 2rem;
        }

        .back-btn-container {
            top: 20px;
            right: 20px;
        }
    }

    @media (max-width: 768px) {
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
            padding: 12px 45px 12px 40px;
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

    @media (max-height: 700px) {
        .form-scrollable-wrapper {
            padding: 20px;
        }

        .form-header {
            margin-bottom: 20px;
        }

        .form-section-header {
            margin: 20px 0 15px 0;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .btn-container {
            margin: 30px 0;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const passwordForm = document.getElementById('passwordForm');
        const submitBtn = document.getElementById('submitBtn');
        const btnText = document.getElementById('btnText');
        const btnSpinner = document.getElementById('btnSpinner');
        const newPasswordInput = document.getElementById('new_password');
        const confirmPasswordInput = document.getElementById('new_password_confirmation');

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

        // Toggle password visibility
        document.querySelectorAll('.toggle-password').forEach(button => {
            button.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const input = document.getElementById(targetId);
                const icon = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

        // Password strength checker
        if (newPasswordInput) {
            newPasswordInput.addEventListener('input', function() {
                checkPasswordStrength(this.value);
            });
        }

        function checkPasswordStrength(password) {
            let strength = 0;
            const requirements = {
                length: password.length >= 6,
                uppercase: /[A-Z]/.test(password),
                lowercase: /[a-z]/.test(password),
                number: /[0-9]/.test(password)
            };

            Object.keys(requirements).forEach(key => {
                const element = document.getElementById(`req-${key}`);
                if (element) {
                    if (requirements[key]) {
                        element.style.color = '#28a745';
                        strength++;
                    } else {
                        element.style.color = '#dc3545';
                    }
                }
            });

            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');

            if (strengthFill && strengthText) {
                strengthFill.className = 'strength-fill';

                if (password.length === 0) {
                    strengthText.textContent = 'Força da senha';
                    strengthText.style.color = '#666';
                } else if (strength <= 1) {
                    strengthFill.classList.add('weak');
                    strengthText.textContent = 'Fraca';
                    strengthText.style.color = '#dc3545';
                } else if (strength <= 3) {
                    strengthFill.classList.add('fair');
                    strengthText.textContent = 'Média';
                    strengthText.style.color = '#ffc107';
                } else {
                    strengthFill.classList.add('good');
                    strengthText.textContent = 'Forte';
                    strengthText.style.color = '#28a745';
                }
            }
        }

        // Loading no submit - form submete normalmente ao servidor
        if (passwordForm) {
            passwordForm.addEventListener('submit', function() {
                submitBtn.classList.add('loading');
                submitBtn.disabled = true;
                btnSpinner.style.display = 'block';
                btnText.style.opacity = '0';
            });
        }
    });
</script>
