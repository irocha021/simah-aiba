class LoginSystem {
    constructor() {
        this.initElements();
        this.bindEvents();
    }

    initElements() {
        this.loginForm = document.getElementById('loginForm');
        this.emailInput = document.getElementById('email');
        this.passwordInput = document.getElementById('password');
        this.togglePasswordBtn = document.getElementById('togglePassword');
        this.togglePasswordIcon = this.togglePasswordBtn?.querySelector('i');
        this.submitBtn = document.getElementById('submitBtn');
        this.btnText = document.getElementById('btnText');
        this.btnSpinner = document.getElementById('btnSpinner');
        this.isPasswordVisible = false;
    }

    bindEvents() {
        if (this.togglePasswordBtn) {
            this.togglePasswordBtn.addEventListener('click', () => this.togglePasswordVisibility());
        }

        if (this.loginForm) {
            this.loginForm.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }

    togglePasswordVisibility() {
        this.isPasswordVisible = !this.isPasswordVisible;

        if (this.isPasswordVisible) {
            this.passwordInput.type = 'text';
            this.togglePasswordIcon.classList.remove('fa-eye');
            this.togglePasswordIcon.classList.add('fa-eye-slash');
        } else {
            this.passwordInput.type = 'password';
            this.togglePasswordIcon.classList.remove('fa-eye-slash');
            this.togglePasswordIcon.classList.add('fa-eye');
        }

        setTimeout(() => this.passwordInput.focus(), 10);
    }

    handleSubmit(event) {
        this.submitBtn.classList.add('loading');
        this.submitBtn.disabled = true;
        this.btnText.textContent = 'Entrando...';
        this.btnSpinner.style.display = 'inline-block';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.loginSystem = new LoginSystem();
});
