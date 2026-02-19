<?php
session_start();
ob_start();
require_once 'includes/config.php';

// Verificar se é admin
$perfisPermitidos = ['admin', 'gerencia'];
if (!in_array($_SESSION['perfil'], $perfisPermitidos)) {
    $_SESSION['mensagem_erro'] = 'Acesso negado!';
    header('Location: index.php');
    exit;
}

$conn = getConnection();
if (!$conn) {
    $_SESSION['mensagem_erro'] = "Erro de conexão com o banco de dados";
    header('Location: index.php');
    ob_end_flush();
    exit;
}

// Processar ações
$action = $_GET['action'] ?? '';
$userId = $_GET['id'] ?? 0;

switch ($action) {
    case 'toggle':
        toggleUserStatus($userId);
        break;
    case 'delete':
        deleteUser($userId);
        break;
    case 'edit':
        showEditForm($userId);
        break;
    case 'update':
        updateUser();
        break;
    default:
        displayMainPage();
        break;
}

ob_end_flush();

// ===== Funções ===== //

function toggleUserStatus($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT status FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        $_SESSION['mensagem_erro'] = 'Usuário não encontrado';
        header('Location: admin_usuarios.php');
        exit;
    }
    
    $user = $result->fetch_assoc();
    $newStatus = $user['status'] === 'ativo' ? 'inativo' : 'ativo';
    
    $stmt = $conn->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $newStatus, $userId);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem_sucesso'] = 'Status do usuário atualizado';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao atualizar status';
    }
    
    header('Location: admin_usuarios.php');
    exit;
}

function deleteUser($userId) {
    global $conn;
    
    if ($userId == $_SESSION['usuario_id']) {
        $_SESSION['mensagem_erro'] = 'Você não pode excluir seu próprio usuário';
        header('Location: admin_usuarios.php');
        exit;
    }
    
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem_sucesso'] = 'Usuário excluído com sucesso';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao excluir usuário';
    }
    
    header('Location: admin_usuarios.php');
    exit;
}

function showEditForm($userId) {
    global $conn;
    
    $stmt = $conn->prepare("SELECT id, nome, usuario, email, perfil FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    require_once 'includes/header.php';
    ?>
    <div class="main-container">
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <h3>Editar Usuário</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="admin_usuarios.php?action=update">
                        <input type="hidden" name="id" value="<?= $user['id'] ?>">
                        
                        <div class="form-group">
                            <label>Nome Completo</label>
                            <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($user['nome']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Nome de Usuário</label>
                            <input type="text" name="usuario" class="form-control" value="<?= htmlspecialchars($user['usuario']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>E-mail</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Perfil</label>
                            <select name="perfil" class="form-control" required>
                                <option value="admin" <?= $user['perfil'] === 'admin' ? 'selected' : '' ?>>Administrador</option>
                                <option value="gerencia" <?= $user['perfil'] === 'gerencia' ? 'selected' : '' ?>>Gerência</option>
                                <option value="vendedor" <?= $user['perfil'] === 'vendedor' ? 'selected' : '' ?>>Vendedor</option>
                                <option value="estoque" <?= $user['perfil'] === 'estoque' ? 'selected' : '' ?>>Estoque</option>
                                <option value="rh" <?= $user['perfil'] === 'rh' ? 'selected' : '' ?>>Recursos Humanos</option>
                                <option value="financeiro" <?= $user['perfil'] === 'financeiro' ? 'selected' : '' ?>>Financeiro</option>
                                <option value="caixa" <?= $user['perfil'] === 'caixa' ? 'selected' : '' ?>>Caixa</option>
                                <option value="recebimento" <?= $user['perfil'] === 'recebimento' ? 'selected' : '' ?>>Recebimento</option>
                            </select>
                        </div>
                        
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Salvar</button>
                            <a href="admin_usuarios.php" class="btn btn-secondary">Cancelar</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once 'includes/footer.php';
    exit;
}

function updateUser() {
    global $conn;
    
    $userId = (int)$_POST['id'];
    $nome = trim($_POST['nome']);
    $usuario = trim($_POST['usuario']);
    $email = trim($_POST['email']);
    $perfil = $_POST['perfil'];
    
    // Validações
    if (empty($nome) || empty($usuario) || empty($email) || empty($perfil)) {
        $_SESSION['mensagem_erro'] = 'Todos os campos são obrigatórios';
        header("Location: admin_usuarios.php?action=edit&id=$userId");
        exit;
    }
    
    // Verificar se usuário já existe (exceto o próprio)
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE (usuario = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $usuario, $email, $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['mensagem_erro'] = 'Usuário ou e-mail já cadastrado';
        header("Location: admin_usuarios.php?action=edit&id=$userId");
        exit;
    }
    
    // Atualizar usuário
    $stmt = $conn->prepare("UPDATE usuarios SET nome = ?, usuario = ?, email = ?, perfil = ? WHERE id = ?");
    $stmt->bind_param("ssssi", $nome, $usuario, $email, $perfil, $userId);
    
    if ($stmt->execute()) {
        $_SESSION['mensagem_sucesso'] = 'Usuário atualizado com sucesso';
    } else {
        $_SESSION['mensagem_erro'] = 'Erro ao atualizar usuário';
    }
    
    header('Location: admin_usuarios.php');
    exit;
}

function displayMainPage() {
    global $conn;
    
    $search = $_GET['search'] ?? '';
    $query = "SELECT id, nome, usuario, email, perfil, status, ultimo_login FROM usuarios";
    
    if ($search) {
        $query .= " WHERE nome LIKE ? OR usuario LIKE ? OR email LIKE ?";
        $searchTerm = "%$search%";
    }
    
    $stmt = $conn->prepare($query);
    if ($search) {
        $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $usuarios = $result->fetch_all(MYSQLI_ASSOC);
    
    require_once 'includes/header.php';
    ?>
    <div class="main-container">
        <div class="content">
            <div class="card">
                <div class="card-header">
                    <div class="header-row">
                        <h3><i class="fas fa-user-cog"></i> Administração de Usuários</h3>
                        <div class="header-actions">
                            <div class="search-box">
                                <input type="text" id="user-search" placeholder="Pesquisar..." 
                                       value="<?= htmlspecialchars($search) ?>">
                                <button class="search-btn" onclick="searchUsers()"><i class="fas fa-search"></i></button>
                            </div>
                            <a href="criar_user.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Adicionar
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="card-body">
                    <?php if (isset($_SESSION['mensagem_sucesso'])): ?>
                        <div class="alert alert-success"><?= $_SESSION['mensagem_sucesso'] ?></div>
                        <?php unset($_SESSION['mensagem_sucesso']); ?>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['mensagem_erro'])): ?>
                        <div class="alert alert-danger"><?= $_SESSION['mensagem_erro'] ?></div>
                        <?php unset($_SESSION['mensagem_erro']); ?>
                    <?php endif; ?>
                    
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>Usuário</th>
                                    <th>E-mail</th>
                                    <th>Perfil</th>
                                    <th>Status</th>
                                    <th>Último Login</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($usuarios)): ?>
                                <tr>
                                    <td colspan="8" class="text-center">Nenhum usuário encontrado</td>
                                </tr>
                                <?php else: ?>
                                <?php foreach($usuarios as $u): ?>
                                <tr>
                                    <td><?= $u['id'] ?></td>
                                    <td><?= htmlspecialchars($u['nome']) ?></td>
                                    <td><?= htmlspecialchars($u['usuario']) ?></td>
                                    <td><?= htmlspecialchars($u['email']) ?></td>
                                    <td>
                                        <span class="badge badge-<?= strtolower($u['perfil']) ?>">
                                            <?= ucfirst($u['perfil']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge status-<?= $u['status'] ?>">
                                            <?= ucfirst($u['status']) ?>
                                        </span>
                                    </td>
                                    <td><?= $u['ultimo_login'] ? date('d/m/Y H:i', strtotime($u['ultimo_login'])) : 'Nunca' ?></td>
                                    <td class="actions">
                                        <a href="admin_usuarios.php?action=edit&id=<?= $u['id'] ?>" class="btn btn-sm btn-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <button class="btn btn-sm btn-<?= $u['status'] === 'ativo' ? 'warning' : 'success' ?>"
                                                title="<?= $u['status'] === 'ativo' ? 'Inativar' : 'Ativar' ?>"
                                                onclick="toggleStatus(<?= $u['id'] ?>, '<?= $u['status'] ?>')">
                                            <i class="fas fa-power-off"></i>
                                        </button>
                                        <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                                        <button class="btn btn-sm btn-danger" title="Excluir" onclick="confirmDelete(<?= $u['id'] ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    function searchUsers() {
        const searchValue = document.getElementById('user-search').value.trim();
        window.location.href = `admin_usuarios.php?search=${encodeURIComponent(searchValue)}`;
    }

    function toggleStatus(userId, currentStatus) {
        if (confirm(`Deseja ${currentStatus === 'ativo' ? 'inativar' : 'ativar'} este usuário?`)) {
            window.location.href = `admin_usuarios.php?action=toggle&id=${userId}`;
        }
    }

    function confirmDelete(userId) {
        if (confirm('ATENÇÃO! Esta ação é irreversível. Deseja realmente excluir este usuário?')) {
            window.location.href = `admin_usuarios.php?action=delete&id=${userId}`;
        }
    }

    document.getElementById('user-search').addEventListener('keyup', function(e) {
        if (e.key === 'Enter') {
            searchUsers();
        }
    });
    </script>
    <?php
    require_once 'includes/footer.php';
}
?>
<style>
/* ============================
   ESTILO GERAL CLEAN
============================ */
body {
    font-family: "Inter", "Segoe UI", Tahoma, sans-serif;
    background: #f8f9fb;
    margin: 0;
    color: #333;
}

.main-container {
    display: flex;
    padding: 20px;
}

.content {
    flex: 1;
    padding: 10px 20px;
}

/* ============================
   CARDS MODERNOS
============================ */
.card {
    background: #fff;
    border-radius: 12px;
    border: 1px solid #e5e7eb;
    padding: 0;
    margin-bottom: 25px;
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #ededed;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1.3rem;
    color: #222;
    font-weight: 600;
}

.card-body {
    padding: 20px;
}

/* ============================
   BARRA DE PESQUISA
============================ */
.search-box {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 8px;
    border: 1px solid #d1d5db;
    padding: 2px 2px;
    width: 260px;
}

.search-box input {
    flex: 1;
    border: none;
    background: transparent;
    margin-left: 30px;
    outline: none;
    font-size: 13px;
}

.search-btn {
    background: none;
    border: none;
    cursor: pointer;
    color: #fdf9f9;
    padding: 6px;
}

/* ============================
   TABELAS MINIMALISTAS
============================ */
.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.table thead th {
    background: #f1f5f9;
    padding: 14px;
    font-weight: 600;
    color: #444;
    border-bottom: 1px solid #e2e8f0;
}

.table tbody td {
    padding: 12px;
    border-bottom: 1px solid #f1f5f9;
}

.table tbody tr:hover {
    background: #fafafa;
}

/* ============================
   BOTÕES MODERNOS
============================ */
.btn {
    padding: 8px 14px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    cursor: pointer;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: 0.2s;
}

.btn-primary {
    background: #d10101;
    color: white;
}

.btn-primary:hover {
    background: #b30000;
}

.btn-secondary {
    background: #6c757d;
    color: white;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 12px;
}

/* ============================
   BADGES
============================ */
.badge {
    display: inline-block;
    padding: 5px 9px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
}

.badge-admin {
    background: #fce7e7;
    color: #7f1d1d;
}

.badge-vendedor {
    background: #e0e7ff;
    color: #3730a3;
}

.badge-gerencia {
    background: #fff7d6;
    color: #8a6d00;
}

.status-ativo {
    background: #dcfce7;
    color: #166534;
}

.status-inativo {
    background: #fee2e2;
    color: #991b1b;
}

/* ============================
   FORMULÁRIOS CLEAN
============================ */
.form-group {
    margin-bottom: 18px;
}

.form-group label {
    display: block;
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #555;
}

.form-control {
    width: 100%;
    padding: 10px 12px;
    border: 1px solid #d4d4d8;
    border-radius: 8px;
    background: #fafafa;
    transition: 0.2s;
}

.form-control:focus {
    background: #fff;
    border-color: #d10101;
    box-shadow: 0 0 0 2px rgba(209,1,1,0.15);
}

/* ============================
   ALERTAS CLEAN
============================ */
.alert {
    border-radius: 8px;
    padding: 12px 16px;
    margin-bottom: 18px;
    font-size: 14px;
}

.alert-success {
    background: #ecfdf5;
    color: #065f46;
    border: 1px solid #bbf7d0;
}

.alert-danger {
    background: #fef2f2;
    color: #991b1b;
    border: 1px solid #fecaca;
}

/* ============================
   AÇÕES DA TABELA
============================ */
.actions {
    display: flex;
    gap: 6px;
}

/* ============================
   RESPONSIVIDADE
============================ */
@media (max-width: 768px) {
    .header-row {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .search-box {
        width: 100%;
    }
}
</style>