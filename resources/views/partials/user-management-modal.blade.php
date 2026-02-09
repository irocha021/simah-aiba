<!-- Modal Gerenciar Usuarios -->
<div id="userManagementModal" class="user-mgmt-modal" style="display: none;">
    <div class="user-mgmt-modal-content">
        <!-- Header -->
        <div class="user-mgmt-modal-header">
            <div class="user-mgmt-header-content">
                <img src="{{ asset('images/logo-top-sigmah.svg') }}" alt="Logo SIGMAH" class="user-mgmt-logo" />
                <div class="user-mgmt-header-title">
                    <h2>Gerenciar Usuários</h2>
                    <p>Lista de usuários cadastrados no sistema.</p>
                </div>
            </div>
            <span id="closeUserMgmtModal" class="user-mgmt-modal-close">&times;</span>
        </div>

        <!-- Acoes -->
        <div class="user-mgmt-actions">
            <a href="{{ route('register') }}" class="user-mgmt-btn-register">
                <i class="fas fa-plus"></i> Cadastrar
            </a>
        </div>

        <!-- Loading -->
        <div id="userMgmtLoading" class="user-mgmt-loading" style="display: none;">
            <div class="user-mgmt-spinner"></div>
            <p>Carregando usuários...</p>
        </div>

        <!-- Tabela -->
        <div id="userMgmtTableContainer" class="user-mgmt-table-container" style="display: none;">
            <table class="user-mgmt-table">
                <thead>
                    <tr>
                        <th>Nome</th>
                        <th>E-mail</th>
                        <th>Perfil</th>
                        <th>Cadastrado em</th>
                        @if(Auth::check() && Auth::user()->isRoot())
                        <th>Ações</th>
                        @endif
                    </tr>
                </thead>
                <tbody id="userMgmtTableBody">
                </tbody>
            </table>
        </div>

        <!-- Erro -->
        <div id="userMgmtError" class="user-mgmt-error" style="display: none;">
            <strong>Erro:</strong> <span id="userMgmtErrorText"></span>
        </div>
    </div>
</div>

<style>
    .user-mgmt-modal {
        position: fixed;
        z-index: 9999;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.5);
    }

    .user-mgmt-modal-content {
        background-color: #ffffff;
        margin: 3% auto;
        width: 85%;
        max-width: 900px;
        max-height: 90vh;
        overflow-y: auto;
        border-radius: 12px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
    }

    .user-mgmt-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 20px 25px;
        background-color: #E3EBFF;
        border-radius: 12px 12px 0 0;
    }

    .user-mgmt-header-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .user-mgmt-logo {
        height: 40px;
    }

    .user-mgmt-header-title h2 {
        margin: 0;
        font-size: 18px;
        color: #1a1a2e;
    }

    .user-mgmt-header-title p {
        margin: 4px 0 0;
        font-size: 13px;
        color: #666;
    }

    .user-mgmt-modal-close {
        font-size: 28px;
        font-weight: bold;
        color: #666;
        cursor: pointer;
        line-height: 1;
    }

    .user-mgmt-modal-close:hover {
        color: #333;
    }

    .user-mgmt-actions {
        padding: 15px 25px;
        display: flex;
        justify-content: flex-end;
        border-bottom: 1px solid #eee;
    }

    .user-mgmt-btn-register {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 20px;
        background-color: #165b9c;
        color: #fff;
        border: none;
        border-radius: 6px;
        font-size: 14px;
        text-decoration: none;
        cursor: pointer;
    }

    .user-mgmt-btn-register:hover {
        background-color: #124a80;
    }

    .user-mgmt-loading {
        text-align: center;
        padding: 40px 20px;
    }

    .user-mgmt-spinner {
        width: 40px;
        height: 40px;
        border: 4px solid #e0e0e0;
        border-top: 4px solid #165b9c;
        border-radius: 50%;
        animation: userMgmtSpin 0.8s linear infinite;
        margin: 0 auto 15px;
    }

    @keyframes userMgmtSpin {
        to { transform: rotate(360deg); }
    }

    .user-mgmt-loading p {
        color: #666;
        font-size: 14px;
    }

    .user-mgmt-table-container {
        padding: 0 25px 25px;
    }

    .user-mgmt-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }

    .user-mgmt-table thead th {
        background-color: #f8f9fa;
        padding: 10px 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
        border-bottom: 2px solid #dee2e6;
    }

    .user-mgmt-table tbody td {
        padding: 10px 12px;
        border-bottom: 1px solid #eee;
        color: #555;
    }

    .user-mgmt-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .user-mgmt-role {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
    }

    .role-root {
        background-color: #fff3cd;
        color: #856404;
    }

    .role-admin {
        background-color: #d1ecf1;
        color: #0c5460;
    }

    .user-mgmt-btn-delete {
        background-color: #dc3545;
        color: #fff;
        border: none;
        border-radius: 4px;
        padding: 5px 10px;
        cursor: pointer;
        font-size: 13px;
    }

    .user-mgmt-btn-delete:hover {
        background-color: #c82333;
    }

    .user-mgmt-error {
        padding: 15px 25px;
        color: #721c24;
        background-color: #f8d7da;
        margin: 15px 25px;
        border-radius: 6px;
    }
</style>

<script>
    var userMgmtIsRoot = {{ Auth::check() && Auth::user()->isRoot() ? 'true' : 'false' }};
    var userMgmtCsrfToken = '{{ csrf_token() }}';
    var userMgmtCurrentUserId = {{ Auth::check() ? Auth::user()->id : 0 }};

    function openUserManagementModal() {
        var modal = document.getElementById('userManagementModal');
        var loading = document.getElementById('userMgmtLoading');
        var tableContainer = document.getElementById('userMgmtTableContainer');
        var errorDiv = document.getElementById('userMgmtError');
        var tableBody = document.getElementById('userMgmtTableBody');

        modal.style.display = 'block';
        document.body.style.overflow = 'hidden';
        loading.style.display = 'block';
        tableContainer.style.display = 'none';
        errorDiv.style.display = 'none';
        tableBody.innerHTML = '';

        fetch('{{ route("users.list") }}')
            .then(function(response) {
                if (!response.ok) throw new Error('Erro ao buscar usuários');
                return response.json();
            })
            .then(function(data) {
                loading.style.display = 'none';

                if (data.data && data.data.length > 0) {
                    data.data.forEach(function(user) {
                        var row = document.createElement('tr');
                        var roleLabel = user.role === 'root' ? 'Root' : 'Admin';
                        var roleClass = user.role === 'root' ? 'role-root' : 'role-admin';

                        var html = '<td>' + escapeHtml(user.name) + '</td>'
                            + '<td>' + escapeHtml(user.email) + '</td>'
                            + '<td><span class="user-mgmt-role ' + roleClass + '">' + roleLabel + '</span></td>'
                            + '<td>' + (user.created_at || '-') + '</td>';

                        if (userMgmtIsRoot) {
                            if (user.id !== userMgmtCurrentUserId) {
                                html += '<td><button class="user-mgmt-btn-delete" onclick="deleteUser(' + user.id + ', \'' + escapeHtml(user.name).replace(/'/g, "\\'") + '\')"><i class="fas fa-trash"></i></button></td>';
                            } else {
                                html += '<td>-</td>';
                            }
                        }

                        row.innerHTML = html;
                        tableBody.appendChild(row);
                    });

                    tableContainer.style.display = 'block';
                } else {
                    errorDiv.style.display = 'block';
                    document.getElementById('userMgmtErrorText').textContent = 'Nenhum usuário encontrado.';
                }
            })
            .catch(function(error) {
                loading.style.display = 'none';
                errorDiv.style.display = 'block';
                document.getElementById('userMgmtErrorText').textContent = error.message;
            });
    }

    function closeUserManagementModal() {
        document.getElementById('userManagementModal').style.display = 'none';
        document.body.style.overflow = 'auto';
    }

    function deleteUser(userId, userName) {
        if (!confirm('Tem certeza que deseja excluir o usuário "' + userName + '"?')) {
            return;
        }

        fetch('/users/' + userId, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': userMgmtCsrfToken,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        })
        .then(function(response) {
            return response.json().then(function(data) {
                return { status: response.status, body: data };
            });
        })
        .then(function(result) {
            if (result.status === 200 && result.body.success) {
                openUserManagementModal();
            } else {
                alert(result.body.error || 'Erro ao excluir usuário.');
            }
        })
        .catch(function(error) {
            alert('Erro ao excluir usuário: ' + error.message);
        });
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    document.addEventListener('DOMContentLoaded', function() {
        var closeBtn = document.getElementById('closeUserMgmtModal');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeUserManagementModal);
        }

        var modal = document.getElementById('userManagementModal');
        if (modal) {
            modal.addEventListener('click', function(e) {
                if (e.target === modal) {
                    closeUserManagementModal();
                }
            });
        }
    });
</script>
