// select-controls.js

// Função para atualizar ícones de um controle específico
function updateSelectIcons(control) {
    const isActive = control.classList.contains('active');
    const iconOpen = control.querySelector('.select-icon-open');
    const iconClosed = control.querySelector('.select-icon-closed');

    if (iconOpen && iconClosed) {
        if (isActive) {
            // Select ABERTO: mostrar bold, esconder normal
            iconOpen.style.opacity = '1';
            iconOpen.style.visibility = 'visible';
            iconOpen.style.display = 'block';

            iconClosed.style.opacity = '0';
            iconClosed.style.visibility = 'hidden';
            iconClosed.style.display = 'none';
        } else {
            // Select FECHADO: mostrar normal, esconder bold
            iconOpen.style.opacity = '0';
            iconOpen.style.visibility = 'hidden';
            iconOpen.style.display = 'none';

            iconClosed.style.opacity = '1';
            iconClosed.style.visibility = 'visible';
            iconClosed.style.display = 'block';
        }
    }
}

// Função genérica para inicializar todos os controles
function initSelectControls() {
    const selectControls = document.querySelectorAll('.select-control');

    selectControls.forEach(control => {
        const header = control.querySelector('.select-header');

        if (header) {
            header.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                const sidebar = document.getElementById('sidebar');
                const wasActive = control.classList.contains('active');

                // Se menu está colapsado, expande primeiro
                if (sidebar.classList.contains('collapsed')) {
                    sidebar.classList.remove('collapsed');
                    sidebar.classList.add('expanded');

                    // Aguardar transição e abrir dropdown
                    setTimeout(() => {
                        control.classList.add('active');
                        updateSelectIcons(control);
                    }, 100);
                } else {
                    // Se já está expandido, toggle dropdown
                    if (wasActive) {
                        control.classList.remove('active');
                    } else {
                        control.classList.add('active');
                    }
                    updateSelectIcons(control);
                }

                // Fechar outros controles e atualizar seus ícones
                selectControls.forEach(otherControl => {
                    if (otherControl !== control) {
                        otherControl.classList.remove('active');
                        updateSelectIcons(otherControl);
                    }
                });
            });
        }

        // Adicionar eventos aos botões dentro do controle
        const options = control.querySelectorAll('.select-option');
        options.forEach(option => {
            option.addEventListener('click', function (e) {
                e.stopPropagation();

                // Remover active de todas as opções deste controle
                options.forEach(opt => opt.classList.remove('active'));

                // Adicionar active à opção clicada
                this.classList.add('active');

                // Disparar evento customizado
                const event = new CustomEvent('selectOptionChanged', {
                    detail: {
                        controlId: control.id,
                        option: this
                    }
                });
                control.dispatchEvent(event);
            });
        });

        // Inicializar ícones no estado correto (todos fechados inicialmente)
        updateSelectIcons(control);
    });

    // Observar mudanças no menu para manter estado dos controles
    const sidebar = document.getElementById('sidebar');
    if (sidebar) {
        const observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                if (mutation.attributeName === 'class') {
                    const isCollapsed = sidebar.classList.contains('collapsed');

                    // Se menu colapsou, fechar todos os dropdowns
                    if (isCollapsed) {
                        selectControls.forEach(control => {
                            control.classList.remove('active');
                            updateSelectIcons(control);
                        });
                    } else {
                        // Se menu expandiu, apenas atualizar ícones (mantém estado)
                        selectControls.forEach(control => {
                            updateSelectIcons(control);
                        });
                    }
                }
            });
        });

        observer.observe(sidebar, {
            attributes: true,
            attributeFilter: ['class']
        });
    }
}

// Inicializar quando o DOM estiver pronto
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSelectControls);
} else {
    initSelectControls();
}

// Funções públicas para manipulação dos controles
window.initSelectControls = {
    // Abrir um controle específico
    open: function (controlId) {
        const control = document.getElementById(controlId);
        if (control) {
            control.classList.add('active');
            updateSelectIcons(control);
        }
    },

    // Fechar um controle específico
    close: function (controlId) {
        const control = document.getElementById(controlId);
        if (control) {
            control.classList.remove('active');
            updateSelectIcons(control);
        }
    },

    // Fechar todos os controles
    closeAll: function () {
        document.querySelectorAll('.select-control').forEach(control => {
            control.classList.remove('active');
            updateSelectIcons(control);
        });
    },

    // Obter valor selecionado de um controle
    getSelected: function (controlId) {
        const control = document.getElementById(controlId);
        if (control) {
            const selected = control.querySelector('.select-option.active');
            return selected ? selected.textContent.trim() : null;
        }
        return null;
    },

    // Definir opção selecionada
    setSelected: function (controlId, optionIndex) {
        const control = document.getElementById(controlId);
        if (control) {
            const options = control.querySelectorAll('.select-option');
            if (options[optionIndex]) {
                options.forEach(opt => opt.classList.remove('active'));
                options[optionIndex].classList.add('active');
                return true;
            }
        }
        return false;
    }
};