<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitar Chave de API | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <div class="login-form-section">
            <div class="form-container">
                <div class="form-header">
                    <a href="{{ url('/docs') }}" target="_blank" style="
                        display: inline-flex;
                        align-items: center;
                        gap: 8px;
                        padding: 8px 18px;
                        margin-bottom: 20px;
                        background: linear-gradient(135deg, #1e40af, #3b82f6);
                        color: #fff;
                        font-size: 0.82rem;
                        font-weight: 600;
                        letter-spacing: 0.04em;
                        text-transform: uppercase;
                        text-decoration: none;
                        border-radius: 999px;
                        box-shadow: 0 2px 8px rgba(59,130,246,0.35);
                        transition: opacity .2s;
                    " onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                        <i class="fas fa-book-open"></i> Documentação da API
                    </a>
                    <h1>Acesso à API</h1>
                    <p>Informe seu nome e e-mail para receber sua chave de acesso</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-error">
                        {{ session('error') }}
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('api-key.request') }}" id="apiKeyForm">
                    @csrf

                    <div class="form-group">
                        <label for="name">Nome</label>
                        <div class="input-with-icon">
                            <i class="fas fa-user"></i>
                            <input type="text" id="name" name="name" class="form-control"
                                value="{{ old('name') }}" placeholder="Seu nome completo" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control"
                                value="{{ old('email') }}" placeholder="seu@email.com" required>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: center;">
                        <button type="submit" class="login-btn" id="submitBtn">
                            <span id="btnText">Solicitar Chave de API</span>
                            <i class="fas fa-spinner fa-spin" id="btnSpinner" style="display: none;"></i>
                        </button>
                    </div>

                    <div class="form-footer">
                        <a href="{{ url('/') }}" class="forgot-password">
                            <i class="fas fa-arrow-left"></i> Voltar
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
            const form = document.getElementById('apiKeyForm');
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
