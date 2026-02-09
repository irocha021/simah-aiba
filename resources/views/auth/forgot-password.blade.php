<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esqueci a Senha | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <div class="login-form-section">
            <div class="form-container">
                <div class="form-header">
                    <h1>Esqueceu a Senha?</h1>
                    <p>Informe seu e-mail para receber o link de recuperação</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.email') }}" id="forgotForm">
                    @csrf

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control"
                                value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: center;">
                        <button type="submit" class="login-btn" id="submitBtn">
                            <span id="btnText">Enviar Link de Recuperação</span>
                            <i class="fas fa-spinner fa-spin" id="btnSpinner" style="display: none;"></i>
                        </button>
                    </div>

                    <div class="form-footer">
                        <a href="{{ route('login') }}" class="forgot-password">
                            <i class="fas fa-arrow-left"></i> Voltar ao Login
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="login-image-section"
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
            const form = document.getElementById('forgotForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');

            if (form) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    btnText.textContent = 'Enviando...';
                    btnSpinner.style.display = 'inline-block';
                });
            }
        });
    </script>
</body>

</html>
