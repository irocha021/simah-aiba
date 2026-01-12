{{-- login.blade.php --}}
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <!-- Lado esquerdo - Formulário -->
        <div class="login-form-section">
            <div class="form-container">
                <div class="form-header">
                    <h1>Bem-vindo(a)</h1>
                    <p>Faça login para gerenciar o sistema</p>
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

                <form method="POST" action="{{ route('login') }}" id="loginForm">
                    @csrf

                    <div class="form-group">
                        <label for="email">E-mail</label>
                        <div class="input-with-icon">
                            <i class="fas fa-envelope"></i>
                            <input type="email" id="email" name="email" class="form-control"
                                value="{{ old('email') }}" placeholder="seu@email.com" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Senha</label>
                        <div class="input-with-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="••••••••" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="remember" name="remember">
                            Lembrar-me
                        </label>
                    </div>
                    <div style="display: flex; justify-content: center;">
                        <button type="submit" class="login-btn" id="submitBtn">
                            <span id="btnText">Entrar</span>
                            <i class="fas fa-spinner fa-spin" id="btnSpinner" style="display: none;"></i>
                        </button>
                    </div>

                    <div class="form-footer">
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="forgot-password">
                                Esqueceu sua senha?
                            </a>
                            <br>
                        @endif
                        <span style="color: #666;">Não tem uma conta? </span>
                        <a href="#" class="signup-link">Cadastre-se</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lado direito - Imagem com logo -->
        <div class="login-image-section"
            style="background: url('{{ asset('images/backgraund-loginpng.png') }}');
                    background-size: cover;
                    background-position: center;">
            <div class="top-logo">
                <img src="{{ asset('images/Logo-login-SIGMAH.png') }}" alt="{{ config('app.name', 'Sistema') }}">
            </div>
        </div>
    </div>

    <script src="{{ asset('js/login.js') }}"></script>
</body>

</html>
