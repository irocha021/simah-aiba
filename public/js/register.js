class RegisterSystem {
    constructor() {
        this.initElements();
        this.bindEvents();
        this.setupFormValidation();
        this.passwordStrength = 0;
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

        // Validação em tempo real
        if (this.nameInput) {
            this.nameInput.addEventListener('blur', () => this.validateName());
            this.nameInput.addEventListener('input', () => this.clearError('name'));
        }

        if (this.emailInput) {
            this.emailInput.addEventListener('blur', () => this.validateEmail());
            this.emailInput.addEventListener('input', () => this.clearError('email'));
        }

        if (this.passwordInput) {
            this.passwordInput.addEventListener('blur', () => this.validatePassword());
            this.passwordInput.addEventListener('input', () => {
                this.clearError('password');
                this.updatePasswordStrength();
                this.validatePasswordMatch();
            });
        }

        if (this.confirmPasswordInput) {
            this.confirmPasswordInput.addEventListener('blur', () => this.validatePasswordMatch());
            this.confirmPasswordInput.addEventListener('input', () => {
                this.clearError('confirmPassword');
                this.validatePasswordMatch();
            });
        }

        if (this.termsCheckbox) {
            this.termsCheckbox.addEventListener('change', () => this.clearError('terms'));
        }

        // Submit do formulário
        if (this.registerForm) {
            this.registerForm.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Prevenir envio com Enter em campos inválidos
        this.registerForm?.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' && !this.isFormValid()) {
                e.preventDefault();
                this.validateAllFields();
            }
        });

        // Foco acessível
        this.setupAccessibleFocus();
    }

    /**
     * Configura validação do formulário
     */
    setupFormValidation() {
        // Adiciona padrão de email
        if (this.emailInput && !this.emailInput.pattern) {
            this.emailInput.pattern = '[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}$';
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
     * Valida o campo de nome
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

        this.showSuccess('name', 'Nome válido');
        return true;
    }

    /**
     * Valida o campo de email
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

        this.showSuccess('email', 'E-mail válido');
        return true;
    }

    /**
     * Atualiza a força da senha
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
     * Valida o campo de senha
     */
    validatePassword() {
        const password = this.passwordInput.value;

        if (!password) {
            this.showError('password', 'Senha é obrigatória');
            return false;
        }

        if (password.length < 6) {
            this.showError('password', 'A senha deve ter pelo menos 6 caracteres');
            return false;
        }

        if (this.passwordStrength < 2) {
            this.showError('password', 'Escolha uma senha mais forte');
            return false;
        }

        this.showSuccess('password', 'Senha válida');
        return true;
    }

    /**
     * Valida se as senhas coincidem
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

        if (password && confirmPassword && password === confirmPassword) {
            this.showSuccess('confirmPassword', 'Senhas coincidem');
            return true;
        }

        return false;
    }

    /**
     * Valida os termos e condições
     */
    validateTerms() {
        if (!this.termsCheckbox.checked) {
            this.showError('terms', 'Você deve aceitar os termos e condições');
            return false;
        }

        this.clearError('terms');
        return true;
    }

    /**
     * Valida todos os campos
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
     * Verifica se o formulário é válido
     */
    isFormValid() {
        return this.validateAllFields();
    }

    /**
     * Exibe erro em um campo específico
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

        // Foco no campo com erro
        if (input) {
            input.focus();
        }
    }

    /**
     * Exibe sucesso em um campo
     */
    showSuccess(field, message) {
        const input = this[field + 'Input'];
        const errorElement = this.errorElements[field];

        if (input) {
            input.classList.remove('error');
            input.classList.add('success');
        }

        if (errorElement) {
            errorElement.innerHTML = `<i class="fas fa-check-circle"></i> ${message}`;
            errorElement.classList.add('show');
            errorElement.style.color = '#28a745';
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
     * Manipula o envio do formulário
     */
    async handleSubmit(event) {
        event.preventDefault();

        // Validação inicial
        if (!this.isFormValid()) {
            this.showAlert('Por favor, corrija os erros no formulário');
            return;
        }

        // Previne múltiplos envios
        if (this.isLoading) return;

        try {
            // Inicia estado de carregamento
            this.setLoadingState(true);

            // Animação de sucesso
            this.submitBtn.classList.add('success-animation');
            setTimeout(() => {
                this.submitBtn.classList.remove('success-animation');
            }, 600);

        } catch (error) {
            // Erro no cadastro
            console.error('Erro de cadastro:', error);
            this.showAlert(error.message || 'Ocorreu um erro durante o cadastro. Tente novamente.');

            // Adiciona efeito visual de erro
            this.submitBtn.classList.add('error');
            setTimeout(() => this.submitBtn.classList.remove('error'), 500);

            // Finaliza estado de carregamento em caso de erro
            this.setLoadingState(false);
        }
    }

    /**
     * Envia formulário via AJAX (opcional)
     */
    async submitFormViaAjax() {
        const formData = new FormData(this.registerForm);

        const response = await fetch(this.registerForm.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        const data = await response.json();

        if (!response.ok) {
            throw new Error(data.message || 'Erro no servidor');
        }

        if (data.redirect) {
            window.location.href = data.redirect;
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

    /**
     * Inicializa efeitos visuais
     */
    initVisualEffects() {
        // Efeito de digitação no título
        const title = document.querySelector('.form-header h1');
        if (title) {
            const originalText = title.textContent;
            title.textContent = '';

            let i = 0;
            const typeWriter = () => {
                if (i < originalText.length) {
                    title.textContent += originalText.charAt(i);
                    i++;
                    setTimeout(typeWriter, 50);
                }
            };

            // Inicia após um breve delay
            setTimeout(typeWriter, 500);
        }
    }
}

/**
 * Inicializa o sistema de cadastro quando o DOM estiver carregado
 */
document.addEventListener('DOMContentLoaded', () => {
    // Inicia o sistema
    window.registerSystem = new RegisterSystem();

    // Inicia efeitos visuais
    window.registerSystem.initVisualEffects();

    // Log para debug (remova em produção)
    console.log('Sistema de cadastro inicializado');
});

/**
 * Funções auxiliares globais (para compatibilidade)
 */
window.showRegisterAlert = function (message, type = 'error') {
    if (window.registerSystem) {
        window.registerSystem.showAlert(message, type);
    }
};

window.setRegisterLoading = function (isLoading) {
    if (window.registerSystem) {
        window.registerSystem.setLoadingState(isLoading);
    }
};

/**
 * Handler para mensagens do Laravel
 */
document.addEventListener('DOMContentLoaded', () => {
    // Verifica se há mensagens Flash do Laravel
    const flashMessages = document.querySelectorAll('[data-flash-message]');
    flashMessages.forEach(element => {
        const message = element.getAttribute('data-flash-message');
        const type = element.getAttribute('data-flash-type') || 'info';

        if (window.registerSystem && message) {
            window.registerSystem.showAlert(message, type === 'success' ? 'success' : 'error');
            element.remove();
        }
    });

    // Verifica erros de validação do Laravel
    const errorElements = document.querySelectorAll('.invalid-feedback');
    if (errorElements.length > 0 && window.registerSystem) {
        const firstError = errorElements[0];
        const fieldName = firstError.getAttribute('data-field');
        const message = firstError.textContent;

        if (fieldName && window.registerSystem.errorElements[fieldName]) {
            window.registerSystem.showError(fieldName, message);
        } else {
            window.registerSystem.showAlert(message, 'error');
        }
    }
});

/**
 * Exporta a classe para uso externo (se necessário)
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = RegisterSystem;
}