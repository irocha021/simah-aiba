<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Administrador | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
</head>

<body>
    <div class="register-container">
        <!-- Lado esquerdo - Formulário -->
        <div class="register-form-section">
            <div class="form-scrollable-wrapper">
                <div class="form-container">
                    <div class="form-header">
                        <h1>Cadastrar Administrador</h1>
                        <p>Preencha o nome e e-mail do novo administrador</p>
                    </div>

                    <div class="alerts-container">
                        @if (session('success'))
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                {{ session('success') }}
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
                                    value="{{ old('name') }}" placeholder="Digite o nome completo" required
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
                                    value="{{ old('email') }}" placeholder="email@exemplo.com" required
                                    autocomplete="email">
                            </div>
                            <div class="error-container">
                                <div class="field-error" id="emailError"></div>
                            </div>
                        </div>

                        <div class="btn-container">
                            <button type="submit" class="register-btn" id="submitBtn">
                                <span id="btnText">Cadastrar Administrador</span>
                                <i class="fas fa-spinner fa-spin" id="btnSpinner" style="display: none;"></i>
                            </button>
                        </div>

                        <div class="form-footer">
                            <a href="{{ route('dashboard') }}" class="login-link">
                                <i class="fas fa-arrow-left"></i> Voltar ao Dashboard
                            </a>
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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const registerForm = document.getElementById('registerForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');

            if (registerForm) {
                registerForm.addEventListener('submit', function() {
                    submitBtn.classList.add('loading');
                    submitBtn.disabled = true;
                    btnSpinner.style.display = 'inline-block';
                    btnText.textContent = 'Cadastrando...';
                });
            }
        });
    </script>
</body>

</html>
