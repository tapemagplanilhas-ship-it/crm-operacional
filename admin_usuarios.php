<?php
session_start();
require_once 'includes/header.php';
require_once 'includes/config.php';


// Verificar se usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// Apenas usuários com perfil 'admin' podem acessar
if ($_SESSION['perfil'] !== 'admin') {
    echo "Acesso negado!";
    exit;
}

$conn = getConnection();
$usuarios = [];
if ($conn) {
    $result = $conn->query("SELECT id, nome, usuario, email, perfil, status, ultimo_login FROM usuarios");
    if ($result) {
        $usuarios = $result->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<div class="page-header">
    <h2><i class="fas fa-user-cog"></i> Administração de Usuários</h2>
    <div class="page-actions">
        <button class="btn-primary" onclick="window.location.href='criar_user.php'">
            <i class="fas fa-plus"></i> Adicionar Usuário
        </button>
    </div>
</div>

<div class="table-responsive">
    <table class="data-table sortable">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nome</th>
                <th>Usuário</th>
                <th>E-mail</th>
                <th>Perfil</th>
                <th>Status</th>
                <th>Último Login</th>
                <th class="text-center">Ações</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($usuarios as $u): ?>
            <tr>
                <td><?= $u['id'] ?></td>
                <td><?= htmlspecialchars($u['nome']) ?></td>
                <td><?= htmlspecialchars($u['usuario']) ?></td>
                <td><?= htmlspecialchars($u['email']) ?></td>
                <td><?= htmlspecialchars($u['perfil']) ?></td>
                <td class="<?= $u['status'] === 'ativo' ? 'status-ativo' : 'status-inativo' ?>"><?= ucfirst($u['status']) ?></td>
                <td><?= $u['ultimo_login'] ?></td>
                <td class="text-center">
                    <div class="actions">
  <button 
    class="action-btn edit"
    title="Editar usuário"
    onclick="abrirModal('includes/modals/modal_editar_usuario.php?id=<?= $u['id'] ?>')">
    <i class="fas fa-pen"></i>
  </button>

  <button 
    class="action-btn toggle"
    title="Ativar / Inativar usuário"
    onclick="if(confirm('Tem certeza?')) window.location.href='usuario_toggle.php?id=<?= $u['id'] ?>'">
    <i class="fas fa-power-off"></i>
  </button>
</div>

                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php require_once 'includes/footer.php'; ?>
