<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>
    <div class="register-container">
        <!-- Lado esquerdo - Formulário -->
        <div class="register-form-section">
            <!-- Área rolável que contém TUDO -->
            <div class="form-scrollable-wrapper">
                <div class="form-container">
                    <div class="form-header">
                        <h1>Criar Conta</h1>
                        <p>Preencha os dados abaixo para se cadastrar</p>
                    </div>

                    <div class="alerts-container">
                        @if (session('status'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                {{ session('status') }}
                            </div>
                        @endif

                        @if ($errors->any())
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-triangle"></i>
                                {{ $errors->first() }}
                            </div>
                        @endif
                    </div>

                    <form method="POST" action="{{ route('register.post') }}" id="registerForm" autocomplete="off">
                        @csrf

                        <div class="form-group">
                            <label for="name">Nome Completo</label>
                            <div class="input-with-icon">
                                <i class="fas fa-user"></i>
                                <input type="text" id="name" name="name" class="form-control"
                                    value="{{ old('name') }}" placeholder="Digite seu nome completo" required
                                    autocomplete="name">
                            </div>
                            <div class="error-container">
                                <div class="field-error" id="nameError"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <div class="input-with-icon">
                                <i class="fas fa-envelope"></i>
                                <input type="email" id="email" name="email" class="form-control"
                                    value="{{ old('email') }}" placeholder="seu@email.com" required
                                    autocomplete="email">
                            </div>
                            <div class="error-container">
                                <div class="field-error" id="emailError"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Senha</label>
                            <div class="input-with-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" id="password" name="password" class="form-control"
                                    placeholder="••••••••" required autocomplete="new-password">
                                <button type="button" class="password-toggle" id="togglePassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>

                            <!-- Sugestão de senha - Inicia oculta -->
                            <div class="password-suggestion hidden" id="passwordSuggestion">
                                <i class="fas fa-lightbulb"></i>
                                <span>Sugestão: use pelo menos 8 caracteres, incluindo maiúsculas, minúsculas, números e
                                    símbolos</span>
                            </div>

                            <div class="password-strength" id="passwordStrength">
                                <div class="strength-bar">
                                    <div class="strength-fill" id="strengthFill"></div>
                                </div>
                                <span class="strength-text" id="strengthText">Força da senha</span>
                            </div>

                            <div class="error-container">
                                <div class="field-error" id="passwordError"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="password_confirmation">Confirmar Senha</label>
                            <div class="input-with-icon">
                                <i class="fas fa-key"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control" placeholder="••••••••" required autocomplete="new-password">
                                <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="error-container">
                                <div class="field-error" id="confirmPasswordError"></div>
                            </div>
                        </div>

                        <div class="form-group terms-group">
                            <label class="checkbox-label">
                                <input type="checkbox" id="terms" name="terms" required>
                                <span class="checkmark"></span>
                                <span>
                                    Eu concordo com os
                                    <a href="#" class="terms-link">Termos de Serviço</a> e
                                    <a href="#" class="terms-link">Política de Privacidade</a>
                                </span>
                            </label>
                            <div class="error-container">
                                <div class="field-error" id="termsError"></div>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="register-btn" id="submitBtn">
                                <span id="btnText">Criar Conta</span>
                                <i class="fas fa-spinner fa-spin" id="btnSpinner" style="display: none;"></i>
                            </button>
                        </div>

                        <div class="form-footer">
                            <span style="color: #666;">Já tem uma conta? </span>
                            <a href="{{ route('login') }}" class="login-link">Clique para entrar.</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Lado direito - Imagem com logo -->
        <div class="register-image-section"
            style="background: url('{{ asset('images/backgraund-loginpng.png') }}');
                    background-size: cover;
                    background-position: center;">
            <div class="top-logo">
                <img src="{{ asset('images/Logo-login-SIGMAH.png') }}" alt="{{ config('app.name', 'Sistema') }}">
            </div>
        </div>
    </div>

    <script src="{{ asset('js/register.js') }}"></script>
</body>

</html>
