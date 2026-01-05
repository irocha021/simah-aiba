/**
 * Script de Login - Sistema de Autenticação
 * @version 1.0.0
 */

class LoginSystem {
    constructor() {
        this.initElements();
        this.bindEvents();
        this.checkRememberMe();
        this.setupFormValidation();
    }

    /**
     * Inicializa todos os elementos DOM necessários
     */
    initElements() {
        // Elementos do formulário
        this.loginForm = document.getElementById('loginForm');
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.togglePasswordBtn = document.getElementById('togglePassword');
        this.togglePasswordIcon = this.togglePasswordBtn?.querySelector('i');
        this.rememberCheckbox = document.getElementById('remember');
        this.submitBtn = document.getElementById('submitBtn');
        this.btnText = document.getElementById('btnText');
        this.btnSpinner = document.getElementById('btnSpinner');

        // Elementos de feedback
        this.alertContainer = null;

        // Configurações
        this.isLoading = false;
        this.isPasswordVisible = false;
    }

    /**
     * Configura os event listeners
     */
    bindEvents() {
        // Alternar visibilidade da senha
        if (this.togglePasswordBtn) {
            this.togglePasswordBtn.addEventListener('click', () => this.togglePasswordVisibility());
        }

        // Submit do formulário
        if (this.loginForm) {
            this.loginForm.addEventListener('submit', (e) => this.handleSubmit(e));
        }

        // Validação em tempo real
        if (this.emailInput) {
            this.emailInput.addEventListener('blur', () => this.validateEmail());
            this.emailInput.addEventListener('input', () => this.clearError(this.emailInput));
        }

        if (this.passwordInput) {
            this.passwordInput.addEventListener('blur', () => this.validatePassword());
            this.passwordInput.addEventListener('input', () => this.clearError(this.passwordInput));
        }

        // Prevenir envio com Enter em campos inválidos
        this.loginForm?.addEventListener('keydown', (e) => {
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
        // Adiciona padrão de email se não existir
        if (this.emailInput && !this.emailInput.pattern) {
            this.emailInput.pattern = '[a-z0-9._%+-]+@[a-z0-9.-]+\\.[a-z]{2,}$';
        }
    }

    /**
     * Verifica se há credenciais salvas no localStorage
     */
    checkRememberMe() {
        try {
            const savedEmail = localStorage.getItem('login_email');
            const savedRemember = localStorage.getItem('login_remember') === 'true';

            if (savedEmail && this.emailInput && savedRemember) {
                this.emailInput.value = savedEmail;
                if (this.rememberCheckbox) {
                    this.rememberCheckbox.checked = true;
                }
            }
        } catch (error) {
            console.warn('Não foi possível acessar localStorage:', error);
        }
    }

    /**
     * Alterna a visibilidade da senha
     */
    togglePasswordVisibility() {
        this.isPasswordVisible = !this.isPasswordVisible;

        if (this.isPasswordVisible) {
            this.passwordInput.type = 'text';
            this.togglePasswordIcon.classList.remove('fa-eye');
            this.togglePasswordIcon.classList.add('fa-eye-slash');
            this.togglePasswordBtn.setAttribute('aria-label', 'Ocultar senha');
        } else {
            this.passwordInput.type = 'password';
            this.togglePasswordIcon.classList.remove('fa-eye-slash');
            this.togglePasswordIcon.classList.add('fa-eye');
            this.togglePasswordBtn.setAttribute('aria-label', 'Mostrar senha');
        }

        // Foco de volta no campo de senha
        setTimeout(() => this.passwordInput.focus(), 10);
    }

    /**
     * Valida o campo de email
     */
    validateEmail() {
        const email = this.emailInput.value.trim();

        if (!email) {
            this.showFieldError(this.emailInput, 'E-mail é obrigatório');
            return false;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            this.showFieldError(this.emailInput, 'Por favor, insira um e-mail válido');
            return false;
        }

        this.clearError(this.emailInput);
        return true;
    }

    /**
     * Valida o campo de senha
     */
    validatePassword() {
        const password = this.passwordInput.value;

        if (!password) {
            this.showFieldError(this.passwordInput, 'Senha é obrigatória');
            return false;
        }

        if (password.length < 6) {
            this.showFieldError(this.passwordInput, 'A senha deve ter pelo menos 6 caracteres');
            return false;
        }

        this.clearError(this.passwordInput);
        return true;
    }

    /**
     * Valida todos os campos
     */
    validateAllFields() {
        const isEmailValid = this.validateEmail();
        const isPasswordValid = this.validatePassword();

        return isEmailValid && isPasswordValid;
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
    showFieldError(inputElement, message) {
        // Remove erros anteriores
        this.clearError(inputElement);

        // Adiciona classe de erro
        inputElement.classList.add('error');

        // Cria elemento de erro
        const errorDiv = document.createElement('div');
        errorDiv.className = 'field-error';
        errorDiv.textContent = message;
        errorDiv.style.cssText = `
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        `;

        // Adiciona ícone de erro
        const icon = document.createElement('i');
        icon.className = 'fas fa-exclamation-circle';
        errorDiv.prepend(icon);

        // Insere após o campo
        inputElement.parentNode.parentNode.appendChild(errorDiv);

        // Foco no campo com erro
        inputElement.focus();
    }

    /**
     * Remove erro de um campo
     */
    clearError(inputElement) {
        inputElement.classList.remove('error');

        const fieldError = inputElement.parentNode.parentNode.querySelector('.field-error');
        if (fieldError) {
            fieldError.remove();
        }
    }

    /**
     * Salva dados no localStorage se "Lembrar-me" estiver marcado
     */
    saveCredentials() {
        try {
            if (this.rememberCheckbox?.checked) {
                localStorage.setItem('login_email', this.emailInput.value.trim());
                localStorage.setItem('login_remember', 'true');
            } else {
                localStorage.removeItem('login_email');
                localStorage.removeItem('login_remember');
            }
        } catch (error) {
            console.warn('Não foi possível salvar no localStorage:', error);
        }
    }

    /**
     * Exibe mensagem de alerta
     */
    showAlert(message, type = 'error') {
        // Remove alertas anteriores
        this.removeAlert();

        // Cria novo alerta
        this.alertContainer = document.createElement('div');
        this.alertContainer.className = `alert alert-${type}`;

        // Adiciona ícone baseado no tipo
        const icon = document.createElement('i');
        icon.className = type === 'success' ? 'fas fa-check-circle' : 'fas fa-exclamation-triangle';

        // Cria texto
        const text = document.createElement('span');
        text.textContent = message;

        // Monta o alerta
        this.alertContainer.appendChild(icon);
        this.alertContainer.appendChild(text);

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
            this.alertContainer.appendChild(closeBtn);
        }

        // Insere após o header do formulário
        const formHeader = document.querySelector('.form-header');
        if (formHeader) {
            formHeader.parentNode.insertBefore(this.alertContainer, formHeader.nextSibling);
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
        if (this.alertContainer && this.alertContainer.parentNode) {
            this.alertContainer.remove();
            this.alertContainer = null;
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
            this.btnText.textContent = 'Entrando...';
            this.btnSpinner.style.display = 'inline-block';
        } else {
            this.submitBtn.classList.remove('loading');
            this.submitBtn.disabled = false;
            this.btnText.textContent = 'Entrar';
            this.btnSpinner.style.display = 'none';
        }
    }

    /**
     * Simula requisição de login (substitua pelo seu endpoint real)
     */
    async performLogin(email, password) {
        // Simula delay de rede
        await new Promise(resolve => setTimeout(resolve, 1500));

        // Aqui você faria a requisição real:
        // const response = await fetch('/api/login', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //         'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        //     },
        //     body: JSON.stringify({ email, password })
        // });
        // 
        // const data = await response.json();
        // return data;

        // Simulação de resposta (REMOVA EM PRODUÇÃO)
        if (email === 'toolsys@toolsys.com' && password === 'toolsys') {
            return {
                success: true,
                message: 'Login realizado com sucesso!',
                redirect: '/'
            };
        } else {
            throw new Error('E-mail ou senha incorretos');
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

            // Salva credenciais se necessário
            this.saveCredentials();

            // Obtém dados do formulário
            const email = this.emailInput.value.trim();
            const password = this.passwordInput.value;

            // Executa login
            const result = await this.performLogin(email, password);

            if (result.success) {
                // Sucesso
                this.showAlert(result.message, 'success');

                // Simula redirecionamento
                setTimeout(() => {
                    if (result.redirect) {
                        window.location.href = result.redirect;
                    }
                }, 1000);

                // Reset do formulário
                this.loginForm.reset();
            } else {
                throw new Error(result.message || 'Falha no login');
            }

        } catch (error) {
            // Erro no login
            console.error('Erro de login:', error);
            this.showAlert(error.message || 'Ocorreu um erro durante o login. Tente novamente.');

            // Adiciona efeito visual de erro
            this.submitBtn.classList.add('error');
            setTimeout(() => this.submitBtn.classList.remove('error'), 500);

        } finally {
            // Finaliza estado de carregamento
            this.setLoadingState(false);
        }
    }

    /**
     * Configura foco acessível
     */
    setupAccessibleFocus() {
        // Adiciona labels ARIA
        if (this.emailInput && !this.emailInput.hasAttribute('aria-label')) {
            this.emailInput.setAttribute('aria-label', 'Endereço de e-mail');
        }

        if (this.passwordInput && !this.passwordInput.hasAttribute('aria-label')) {
            this.passwordInput.setAttribute('aria-label', 'Senha');
        }

        if (this.togglePasswordBtn && !this.togglePasswordBtn.hasAttribute('aria-label')) {
            this.togglePasswordBtn.setAttribute('aria-label', 'Mostrar senha');
        }

        if (this.submitBtn && !this.submitBtn.hasAttribute('aria-label')) {
            this.submitBtn.setAttribute('aria-label', 'Entrar no sistema');
        }

        // Adiciona suporte a teclado
        document.addEventListener('keydown', (e) => {
            // Fecha alerta com ESC
            if (e.key === 'Escape' && this.alertContainer) {
                this.removeAlert();
            }

            // Navegação por tabs
            if (e.key === 'Tab') {
                this.handleTabNavigation(e);
            }
        });
    }

    /**
     * Manipula navegação por tab
     */
    handleTabNavigation(event) {
        // Adiciona feedback visual para navegação por teclado
        if (event.target.classList.contains('form-control')) {
            event.target.classList.add('keyboard-nav');
            setTimeout(() => event.target.classList.remove('keyboard-nav'), 300);
        }
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
 * Inicializa o sistema de login quando o DOM estiver carregado
 */
document.addEventListener('DOMContentLoaded', () => {
    // Inicia o sistema
    window.loginSystem = new LoginSystem();

    // Inicia efeitos visuais
    window.loginSystem.initVisualEffects();

    // Log para debug (remova em produção)
    console.log('Sistema de login inicializado');
});

/**
 * Funções auxiliares globais (para compatibilidade)
 */
window.showLoginAlert = function (message, type = 'error') {
    if (window.loginSystem) {
        window.loginSystem.showAlert(message, type);
    }
};

window.setLoginLoading = function (isLoading) {
    if (window.loginSystem) {
        window.loginSystem.setLoadingState(isLoading);
    }
};

/**
 * Suporte para ambiente Laravel
 */
if (typeof window.livewire !== 'undefined') {
    document.addEventListener('livewire:load', () => {
        console.log('Login system ready for Livewire');
    });
}

/**
 * Handler para mensagens do Laravel
 */
document.addEventListener('DOMContentLoaded', () => {
    // Verifica se há mensagens Flash do Laravel
    const flashMessages = document.querySelectorAll('[data-flash-message]');
    flashMessages.forEach(element => {
        const message = element.getAttribute('data-flash-message');
        const type = element.getAttribute('data-flash-type') || 'info';

        if (window.loginSystem && message) {
            window.loginSystem.showAlert(message, type === 'success' ? 'success' : 'error');
            element.remove();
        }
    });

    // Verifica erros de validação do Laravel
    const errorElements = document.querySelectorAll('.invalid-feedback');
    if (errorElements.length > 0 && window.loginSystem) {
        window.loginSystem.showAlert(errorElements[0].textContent, 'error');
    }
});

/**
 * Exporta a classe para uso externo (se necessário)
 */
if (typeof module !== 'undefined' && module.exports) {
    module.exports = LoginSystem;
}