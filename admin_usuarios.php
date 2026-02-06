<?php
session_start();
ob_start();
require_once 'includes/config.php';

// Verificar se é admin
if ($_SESSION['perfil'] !== 'admin') {
    $_SESSION['mensagem_erro'] = 'Acesso negado!';
    header('Location: index.php');
    exit;
}

if ($_SESSION['perfil'] !== 'admin') {
    $_SESSION['mensagem_erro'] = 'Acesso negado!';
    header('Location: index.php');
    ob_end_flush();
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
/* ========== ESTILOS GERAIS ========== */
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f5f7fa;
    color: #333;
    margin: 0;
    padding: 0;
}

.main-container {
    display: flex;
    min-height: 100vh;
}

.content {
    flex: 1;
    padding: 20px;
    background-color: #f5f7fa;
}

/* ========== CARDS ========== */
.card {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    margin-bottom: 20px;
}

.card-header {
    padding: 15px 20px;
    border-bottom: 1px solid #eee;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-header h3 {
    margin: 0;
    font-size: 1.25rem;
    color: #333;
}

.card-body {
    padding: 20px;
}

/* ========== CABEÇALHO E AÇÕES ========== */
.header-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.header-actions {
    display: flex;
    gap: 15px;
    align-items: center;
}

/* ========== FORMULÁRIOS ========== */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 600;
    color: #555;
}

.form-control {
    width: 100%;
    padding: 10px 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.3s;
}

.form-control:focus {
    border-color: #d10101;
    outline: none;
    box-shadow: 0 0 0 3px rgba(209, 1, 1, 0.1);
}

.form-actions {
    display: flex;
    gap: 10px;
    margin-top: 25px;
}

/* ========== TABELAS ========== */
.table-responsive {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    font-size: 14px;
}

.table th {
    background-color: #f8f9fa;
    padding: 12px 15px;
    text-align: left;
    font-weight: 600;
    color: #555;
    border-bottom: 2px solid #dee2e6;
}

.table td {
    padding: 12px 15px;
    border-bottom: 1px solid #eee;
    vertical-align: middle;
}

.table tr:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

/* ========== BOTÕES ========== */
.btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 8px 16px;
    border-radius: 4px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s;
    border: none;
    gap: 5px;
    font-size: 14px;
}

.btn-primary {
    background-color: #d10101;
    color: white;
}

.btn-primary:hover {
    background-color: #b00000;
}

.btn-secondary {
    background-color: #6c757d;
    color: white;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-success {
    background-color: #28a745;
    color: white;
}

.btn-danger {
    background-color: #dc3545;
    color: white;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

/* ========== BADGES ========== */
.badge {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 600;
}

.badge-admin {
    background-color: #f8d7da;
    color: #721c24;
}

.badge-gerencia {
    background-color: #fff3cd;
    color: #856404;
}

.badge-vendedor {
    background-color: #cfe2ff;
    color: #084298;
}
.badge-estoque {
    background-color: #e2e3e5;
    color: #383d41;
}

.badge-rh {
    background-color: #d6d8db;
    color: #1b1e21;
}

.badge-financeiro {
    background-color: #cce5ff;
    color: #004085;
}

.badge-caixa {
    background-color: #d6dfe2;
    color: #4d8385;
}

.badge-recebimento {
    background-color: #d4edda;
    color: #155724;
}

.status-ativo {
    background-color: #d1e7dd;
    color: #0f5132;
}

.status-inativo {
    background-color: #f8d7da;
    color: #842029;
}

/* ========== ALERTAS ========== */
.alert {
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 20px;
    font-size: 14px;
}

.alert-success {
    background-color: #d1e7dd;
    color: #0f5132;
    border: 1px solid #badbcc;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c2c7;
}

/* ========== BARRA DE PESQUISA ========== */
.search-box {
    display: flex;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 4px;
    margin-top: 20px;
    padding: 8px 15px;
    border: 1px solid #ddd;
    width: 300px;
}

.search-box input {
    flex: 1;
    border: none;
    background: transparent;
    padding: 5px;
    outline: none;
    font-size: 11px;
    margin-left: 13px;
}

.search-btn {
    background: none;
    padding: 10px;
    border: none;
    cursor: pointer;
    color: #666;
}

/* ========== AÇÕES DA TABELA ========== */
.actions {
    display: flex;
    gap: 5px;
}

/* ========== RESPONSIVIDADE ========== */
@media (max-width: 768px) {
    .header-row {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .header-actions {
        width: 100%;
        margin-top: 10px;
    }
    
    .search-box {
        width: 100%;
    }
    
    .table th, .table td {
        padding: 8px 10px;
        font-size: 13px;
    }
    
    .btn-sm {
        padding: 3px 6px;
    }
}
</style>