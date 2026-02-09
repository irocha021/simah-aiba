<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha | SIMAH</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>

<body>
    <div class="login-container">
        <div class="login-form-section">
            <div class="form-container">
                <div class="form-header">
                    <h1>Redefinir Senha</h1>
                    <p>Digite sua nova senha</p>
                </div>

                @if ($errors->any())
                    <div class="alert alert-error">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" id="resetForm">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <input type="hidden" name="email" value="{{ $email }}">

                    <div class="form-group">
                        <label for="password">Nova Senha</label>
                        <div class="input-with-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" id="password" name="password" class="form-control"
                                placeholder="••••••••" required>
                            <button type="button" class="password-toggle" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-strength" id="passwordStrength">
                            <div class="strength-bar">
                                <div class="strength-fill" id="strengthFill"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Força da senha</span>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Nova Senha</label>
                        <div class="input-with-icon">
                            <i class="fas fa-key"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation"
                                class="form-control" placeholder="••••••••" required>
                            <button type="button" class="password-toggle" id="toggleConfirmPassword">
                                <i class="fas fa-eye"></i>
                            </button>
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

                    <div style="display: flex; justify-content: center;">
                        <button type="submit" class="login-btn" id="submitBtn">
                            <span id="btnText">Redefinir Senha</span>
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

    <style>
        .password-strength {
            margin-top: 10px;
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

        .password-requirements {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 20px;
            margin-top: 20px;
            margin-bottom: 20px;
        }

        .password-requirements p {
            margin-bottom: 10px;
            color: #333;
            font-weight: 500;
        }

        .password-requirements ul {
            list-style: none;
            padding: 0;
            margin: 0;
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
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('resetForm');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const passwordInput = document.getElementById('password');

            // Toggle password
            document.querySelectorAll('.password-toggle').forEach(button => {
                button.addEventListener('click', function() {
                    const input = this.previousElementSibling;
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
            if (passwordInput) {
                passwordInput.addEventListener('input', function() {
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

            // Loading no submit
            if (form) {
                form.addEventListener('submit', function() {
                    submitBtn.disabled = true;
                    btnText.textContent = 'Redefinindo...';
                    btnSpinner.style.display = 'inline-block';
                });
            }
        });
    </script>
</body>

</html>