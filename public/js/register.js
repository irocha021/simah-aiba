class RegisterSystem {
    constructor() {
        this.initElements();
        this.bindEvents();
        this.setupFormValidation();
        this.passwordStrength = 0;
        this.validationMode = 'submit'; // Validação apenas no submit
        this.hasSubmitted = false; // Flag para saber se já tentou submeter
    }

    /**
     * Inicializa todos os elementos DOM necessários
     */
    initElements() {
        // Elementos do formulário
        this.registerForm = document.getElementById('registerForm');
        this.nameInput = document.getElementById('name');
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.confirmPasswordInput = document.getElementById('password_confirmation');
        this.togglePasswordBtn = document.getElementById('togglePassword');
        this.toggleConfirmPasswordBtn = document.getElementById('toggleConfirmPassword');
        this.termsCheckbox = document.getElementById('terms');
        this.submitBtn = document.getElementById('submitBtn');
        this.btnText = document.getElementById('btnText');
        this.btnSpinner = document.getElementById('btnSpinner');

        // Sugestão de senha
        this.passwordSuggestion = document.getElementById('passwordSuggestion');

        // Elementos de força da senha
        this.passwordStrengthContainer = document.getElementById('passwordStrength');
        this.strengthFill = document.getElementById('strengthFill');
        this.strengthText = document.getElementById('strengthText');

        // Elementos de erro
        this.errorElements = {
            name: document.getElementById('nameError'),
            email: document.getElementById('emailError'),
            password: document.getElementById('passwordError'),
            confirmPassword: document.getElementById('confirmPasswordError'),
            terms: document.getElementById('termsError')
        };

        // Configurações
        this.isLoading = false;
        this.isPasswordVisible = false;
        this.isConfirmPasswordVisible = false;

        // Desativa o autocomplete do navegador
        this.disableBrowserAutocomplete();
    }

    /**
     * Desativa autocomplete do navegador
     */
    disableBrowserAutocomplete() {
        const inputs = [
            this.nameInput,
            this.emailInput,
            this.passwordInput,
            this.confirmPasswordInput
        ];

        inputs.forEach(input => {
            if (input) {
                input.setAttribute('autocomplete', 'off');
                input.setAttribute('autocapitalize', 'off');
                input.setAttribute('autocorrect', 'off');
                input.setAttribute('spellcheck', 'false');
            }
        });

        // Remove qualquer valor pré-preenchido pelo navegador
        setTimeout(() => {
            inputs.forEach(input => {
                if (input && input.value) {
                    input.value = '';
                }
            });
        }, 100);
    }

    /**
     * Configura os event listeners
     */
    bindEvents() {
        // Alternar visibilidade da senha
        if (this.togglePasswordBtn) {
            this.togglePasswordBtn.addEventListener('click', () => this.togglePasswordVisibility('password'));
        }

        if (this.toggleConfirmPasswordBtn) {
            this.toggleConfirmPasswordBtn.addEventListener('click', () => this.togglePasswordVisibility('confirm'));
        }

        // Foco no campo de senha - mostra sugestão
        if (this.passwordInput) {
            this.passwordInput.addEventListener('focus', () => {
                this.togglePasswordSuggestion(true);
            });

            this.passwordInput.addEventListener('blur', () => {
                // Se o campo estiver vazio, esconde a sugestão
                if (!this.passwordInput.value) {
                    this.togglePasswordSuggestion(false);
                }
            });

            // Validação em tempo real apenas para força da senha
            this.passwordInput.addEventListener('input', () => {
                this.updatePasswordStrength();
                // Se começar a digitar, mostra a sugestão
                if (this.passwordInput.value) {
                    this.togglePasswordSuggestion(true);
                }
                // Limpa erro se já foi submetido antes
                this.clearError('password');
            });
        }

        // Validação de confirmação de senha em tempo real (opcional)
        if (this.confirmPasswordInput) {
            this.confirmPasswordInput.addEventListener('input', () => {
                if (this.passwordInput.value && this.confirmPasswordInput.value) {
                    // Validação suave em tempo real (não impede navegação)
                    this.validatePasswordMatchSoft();
                }
                // Limpa erro se já foi submetido antes
                this.clearError('confirmPassword');
            });
        }

        // Validação suave no blur (apenas feedback visual, não impede navegação)
        if (this.nameInput) {
            this.nameInput.addEventListener('blur', () => {
                if (this.hasSubmitted) {
                    this.validateNameSoft();
                }
            });
            this.nameInput.addEventListener('input', () => this.clearError('name'));
        }

        if (this.emailInput) {
            this.emailInput.addEventListener('blur', () => {
                if (this.hasSubmitted) {
                    this.validateEmailSoft();
                }
            });
            this.emailInput.addEventListener('input', () => this.clearError('email'));
        }

        if (this.passwordInput) {
            this.passwordInput.addEventListener('blur', () => {
                if (this.hasSubmitted) {
                    this.validatePasswordSoft();
                }
            });
        }

        if (this.confirmPasswordInput) {
            this.confirmPasswordInput.addEventListener('blur', () => {
                if (this.hasSubmitted) {
                    this.validatePasswordMatchSoft();
                }
            });
        }

        if (this.termsCheckbox) {
            this.termsCheckbox.addEventListener('change', () => {
                if (this.hasSubmitted) {
                    this.validateTermsSoft();
                }
                this.clearError('terms');
            });
        }

        // Submit do formulário
        if (this.registerForm) {
            this.registerForm.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Prevenir envio com Enter
        this.registerForm?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                this.handleSubmit(e);
            }
        });

        // Foco acessível
        this.setupAccessibleFocus();
    }

    /**
     * Mostra/Esconde sugestão de senha
     */
    togglePasswordSuggestion(show) {
        if (this.passwordSuggestion) {
            if (show) {
                this.passwordSuggestion.classList.remove('hidden');
            } else {
                this.passwordSuggestion.classList.add('hidden');
            }
        }
    }

    /**
     * Atualiza a força da senha (sempre ativa quando há conteúdo)
     */
    updatePasswordStrength() {
        const password = this.passwordInput.value;

        if (!password) {
            this.passwordStrengthContainer.classList.remove('visible');
            return;
        }

        // Calcula a força da senha
        let strength = 0;

        // Comprimento
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;

        // Complexidade
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^A-Za-z0-9]/.test(password)) strength++;

        this.passwordStrength = strength;
        this.passwordStrengthContainer.classList.add('visible');

        // Atualiza visualização
        this.strengthFill.className = 'strength-fill';

        if (strength <= 2) {
            this.strengthFill.classList.add('weak');
            this.strengthText.textContent = 'Fraca';
            this.strengthText.style.color = '#dc3545';
        } else if (strength <= 4) {
            this.strengthFill.classList.add('fair');
            this.strengthText.textContent = 'Média';
            this.strengthText.style.color = '#ffc107';
        } else {
            this.strengthFill.classList.add('good');
            this.strengthText.textContent = 'Forte';
            this.strengthText.style.color = '#28a745';
        }
    }

    /**
     * Validação suave do nome (feedback visual apenas)
     */
    validateNameSoft() {
        const name = this.nameInput.value.trim();

        if (!name) {
            this.showErrorSoft('name', 'Nome é obrigatório');
            return false;
        }

        if (name.length < 3) {
            this.showErrorSoft('name', 'Nome deve ter pelo menos 3 caracteres');
            return false;
        }

        this.clearError('name');
        return true;
    }

    /**
     * Validação suave do email (feedback visual apenas)
     */
    validateEmailSoft() {
        const email = this.emailInput.value.trim();

        if (!email) {
            this.showErrorSoft('email', 'E-mail é obrigatório');
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showErrorSoft('email', 'Por favor, insira um e-mail válido');
            return false;
        }

        this.clearError('email');
        return true;
    }

    /**
     * Validação suave da senha (feedback visual apenas)
     */
    validatePasswordSoft() {
        const password = this.passwordInput.value;

        if (!password) {
            this.showErrorSoft('password', 'Senha é obrigatória');
            return false;
        }

        if (password.length < 8) {
            this.showErrorSoft('password', 'A senha deve ter pelo menos 8 caracteres');
            return false;
        }

        if (this.passwordStrength < 3) {
            this.showErrorSoft('password', 'Escolha uma senha mais forte');
            return false;
        }

        this.clearError('password');
        return true;
    }

    /**
     * Validação suave de correspondência de senhas
     */
    validatePasswordMatchSoft() {
        const password = this.passwordInput.value;
        const confirmPassword = this.confirmPasswordInput.value;

        if (!confirmPassword) {
            return false;
        }

        if (password !== confirmPassword) {
            this.showErrorSoft('confirmPassword', 'As senhas não coincidem');
            return false;
        }

        this.clearError('confirmPassword');
        return true;
    }

    /**
     * Validação suave dos termos
     */
    validateTermsSoft() {
        if (!this.termsCheckbox.checked) {
            this.showErrorSoft('terms', 'Você deve aceitar os termos e condições');
            return false;
        }

        this.clearError('terms');
        return true;
    }

    /**
     * Validação completa da senha (apenas no submit)
     */
    validatePassword() {
        const password = this.passwordInput.value;

        if (!password) {
            this.showError('password', 'Senha é obrigatória');
            return false;
        }

        if (password.length < 8) {
            this.showError('password', 'A senha deve ter pelo menos 8 caracteres');
            return false;
        }

        if (this.passwordStrength < 3) {
            this.showError('password', 'Escolha uma senha mais forte');
            return false;
        }

        return true;
    }

    /**
     * Valida se as senhas coincidem (validação completa)
     */
    validatePasswordMatch() {
        const password = this.passwordInput.value;
        const confirmPassword = this.confirmPasswordInput.value;

        if (!confirmPassword) {
            this.showError('confirmPassword', 'Confirme sua senha');
            return false;
        }

        if (password !== confirmPassword) {
            this.showError('confirmPassword', 'As senhas não coincidem');
            return false;
        }

        return true;
    }

    /**
     * Valida os termos e condições (completa)
     */
    validateTerms() {
        if (!this.termsCheckbox.checked) {
            this.showError('terms', 'Você deve aceitar os termos e condições');
            return false;
        }

        return true;
    }

    /**
     * Validação completa (apenas no submit)
     */
    validateAllFields() {
        const isNameValid = this.validateName();
        const isEmailValid = this.validateEmail();
        const isPasswordValid = this.validatePassword();
        const isPasswordMatchValid = this.validatePasswordMatch();
        const isTermsValid = this.validateTerms();

        return isNameValid && isEmailValid && isPasswordValid && isPasswordMatchValid && isTermsValid;
    }

    /**
     * Validação do nome (completa)
     */
    validateName() {
        const name = this.nameInput.value.trim();

        if (!name) {
            this.showError('name', 'Nome é obrigatório');
            return false;
        }

        if (name.length < 3) {
            this.showError('name', 'Nome deve ter pelo menos 3 caracteres');
            return false;
        }

        if (name.length > 100) {
            this.showError('name', 'Nome muito longo (máximo 100 caracteres)');
            return false;
        }

        return true;
    }

    /**
     * Validação do email (completa)
     */
    validateEmail() {
        const email = this.emailInput.value.trim();

        if (!email) {
            this.showError('email', 'E-mail é obrigatório');
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showError('email', 'Por favor, insira um e-mail válido');
            return false;
        }

        return true;
    }

    /**
     * Exibe erro em um campo específico (validação completa)
     */
    showError(field, message) {
        const input = this[field + 'Input'];
        const errorElement = this.errorElements[field];

        if (input) {
            input.classList.remove('success');
            input.classList.add('error');
        }

        if (errorElement) {
            errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            errorElement.classList.add('show');
        }
    }

    /**
     * Exibe erro suave (feedback visual apenas, não impede navegação)
     */
    showErrorSoft(field, message) {
        const input = this[field + 'Input'];
        const errorElement = this.errorElements[field];

        if (input) {
            input.classList.add('error');
        }

        if (errorElement) {
            errorElement.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${message}`;
            errorElement.classList.add('show');
        }
    }

    /**
     * Remove erro/sucesso de um campo
     */
    clearError(field) {
        const input = this[field + 'Input'];
        const errorElement = this.errorElements[field];

        if (input) {
            input.classList.remove('error', 'success');
        }

        if (errorElement) {
            errorElement.classList.remove('show');
            errorElement.innerHTML = '';
        }
    }

    /**
     * Habilita estado de carregamento
     */
    setLoadingState(isLoading) {
        this.isLoading = isLoading;

        if (isLoading) {
            this.submitBtn.classList.add('loading');
            this.submitBtn.disabled = true;
            this.btnText.textContent = 'Processando...';
            this.btnSpinner.style.display = 'inline-block';
        } else {
            this.submitBtn.classList.remove('loading');
            this.submitBtn.disabled = false;
            this.btnText.textContent = 'Criar Conta';
            this.btnSpinner.style.display = 'none';
        }
    }

    /**
     * Exibe mensagem de alerta
     */
    showAlert(message, type = 'error') {
        // Remove alertas anteriores
        this.removeAlert();

        // Cria novo alerta
        const alertContainer = document.createElement('div');
        alertContainer.className = `alert alert-${type}`;

        // Adiciona ícone baseado no tipo
        const icon = document.createElement('i');
        icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

        // Cria texto
        const text = document.createElement('span');
        text.textContent = message;

        // Monta o alerta
        alertContainer.appendChild(icon);
        alertContainer.appendChild(text);

        // Adiciona botão de fechar para erros
        if (type === 'error') {
            const closeBtn = document.createElement('button');
            closeBtn.className = 'alert-close';
            closeBtn.innerHTML = '<i class="fas fa-times"></i>';
            closeBtn.setAttribute('aria-label', 'Fechar mensagem');
            closeBtn.style.cssText = `
                background: none;
                border: none;
                color: inherit;
                cursor: pointer;
                margin-left: auto;
                font-size: 1.1rem;
            `;
            closeBtn.addEventListener('click', () => this.removeAlert());
            alertContainer.appendChild(closeBtn);
        }

        // Insere após o header do formulário
        const formHeader = document.querySelector('.form-header');
        if (formHeader) {
            formHeader.parentNode.insertBefore(alertContainer, formHeader.nextSibling);
        }

        // Remove automaticamente após 5 segundos (apenas sucesso)
        if (type === 'success') {
            setTimeout(() => this.removeAlert(), 5000);
        }
    }

    /**
     * Remove alerta atual
     */
    removeAlert() {
        const alertContainer = document.querySelector('.alert');
        if (alertContainer) {
            alertContainer.remove();
        }
    }

    /**
     * Alterna a visibilidade da senha
     */
    togglePasswordVisibility(type) {
        if (type === 'password') {
            this.isPasswordVisible = !this.isPasswordVisible;
            const passwordInput = this.passwordInput;
            const toggleBtn = this.togglePasswordBtn;
            const icon = toggleBtn.querySelector('i');

            if (this.isPasswordVisible) {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                toggleBtn.setAttribute('aria-label', 'Ocultar senha');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                toggleBtn.setAttribute('aria-label', 'Mostrar senha');
            }

            setTimeout(() => passwordInput.focus(), 10);

        } else if (type === 'confirm') {
            this.isConfirmPasswordVisible = !this.isConfirmPasswordVisible;
            const confirmInput = this.confirmPasswordInput;
            const toggleBtn = this.toggleConfirmPasswordBtn;
            const icon = toggleBtn.querySelector('i');

            if (this.isConfirmPasswordVisible) {
                confirmInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
                toggleBtn.setAttribute('aria-label', 'Ocultar senha de confirmação');
            } else {
                confirmInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
                toggleBtn.setAttribute('aria-label', 'Mostrar senha de confirmação');
            }

            setTimeout(() => confirmInput.focus(), 10);
        }
    }

    /**
     * Manipula o envio do formulário
     */
    async handleSubmit(event) {
        event.preventDefault();

        // Marca que o usuário já tentou submeter
        this.hasSubmitted = true;

        // Validação completa no submit
        if (!this.validateAllFields()) {
            this.showAlert('Por favor, corrija os erros no formulário');
            return;
        }

        // Previne múltiplos envios
        if (this.isLoading) return;

        try {
            // Inicia estado de carregamento
            this.setLoadingState(true);

            // Envia o formulário
            this.registerForm.submit();

        } catch (error) {
            // Erro no cadastro
            console.error('Erro de cadastro:', error);
            this.showAlert(error.message || 'Ocorreu um erro durante o cadastro. Tente novamente.');
            this.setLoadingState(false);
        }
    }

    /**
     * Configura foco acessível
     */
    setupAccessibleFocus() {
        // Adiciona labels ARIA
        const fields = [
            { element: this.nameInput, label: 'Nome completo' },
            { element: this.emailInput, label: 'Endereço de e-mail' },
            { element: this.passwordInput, label: 'Senha' },
            { element: this.confirmPasswordInput, label: 'Confirmar senha' },
            { element: this.togglePasswordBtn, label: 'Mostrar senha' },
            { element: this.toggleConfirmPasswordBtn, label: 'Mostrar senha de confirmação' },
            { element: this.submitBtn, label: 'Criar conta' },
            { element: this.termsCheckbox, label: 'Aceitar termos e condições' }
        ];

        fields.forEach(field => {
            if (field.element && !field.element.hasAttribute('aria-label')) {
                field.element.setAttribute('aria-label', field.label);
            }
        });
    }
}

/**
 * Inicializa o sistema de cadastro quando o DOM estiver carregado
 */
document.addEventListener('DOMContentLoaded', () => {
    // Inicia o sistema
    window.registerSystem = new RegisterSystem();

    // Log para debug (remova em produção)
    console.log('Sistema de cadastro inicializado');
});