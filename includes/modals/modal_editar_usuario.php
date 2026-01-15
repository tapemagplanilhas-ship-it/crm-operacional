<?php
session_start();
require_once __DIR__ . '/../config.php';

if (!isset($_SESSION['usuario_id']) || $_SESSION['perfil'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    exit('Usuário não informado');
}

$conn = getConnection();
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_GET['id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

if (!$usuario) exit('Usuário não encontrado');
?>

<div class="modal active">
  <div class="modal-content modal-user">

    <!-- HEADER -->
    <div class="modal-header user-header modal-user-header">
      <div class="user-head">
        <div class="user-avatar">
          <?= strtoupper(substr($usuario['nome'], 0, 2)) ?>
        </div>
        <div>
          <h2><?= htmlspecialchars($usuario['nome']) ?></h2>
          <span>@<?= htmlspecialchars($usuario['usuario']) ?></span>
        </div>
      </div>

      <button type="button"
        class="modal-close"
        onclick="window.closeModal()"
        aria-label="Fechar modal">
  &times;
</button>
      <!-- Fechar header do modal -->
    </div>

    <!-- TABS -->
    <div class="user-tabs">
      <button class="tab active" data-tab="geral">Geral</button>
      <button class="tab" data-tab="acesso">Acesso</button>
      <button class="tab" data-tab="senha">Senha</button>
    </div>

    <form id="form-editar-usuario" class="modal-form">

      <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

      <!-- TAB GERAL -->
      <div class="tab-content active" id="tab-geral">
        <div class="form-row">
          <div class="form-group">
            <label>Nome</label>
            <input type="text" name="nome" value="<?= htmlspecialchars($usuario['nome']) ?>" required>
          </div>
          <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>
          </div>
        </div>

        <div class="form-group">
          <label>Usuário</label>
          <input type="text" name="usuario" value="<?= htmlspecialchars($usuario['usuario']) ?>" required>
        </div>
      </div>

      <!-- TAB ACESSO -->
      <div class="tab-content" id="tab-acesso">
        <div class="form-group">
          <label>Perfil</label>
          <select name="perfil">
            <option value="vendedor" <?= $usuario['perfil']=='vendedor'?'selected':'' ?>>Vendedor</option>
            <option value="gerencia" <?= $usuario['perfil']=='gerencia'?'selected':'' ?>>Gerência</option>
            <option value="admin" <?= $usuario['perfil']=='admin'?'selected':'' ?>>Administrador</option>
          </select>
        </div>
      </div>

      <!-- TAB SENHA -->
      <div class="tab-content" id="tab-senha">
        <button type="button" class="btn-senha" onclick="toggleSenha()">
          Alterar senha
        </button>

        <div id="senha-box" hidden>
          <div class="form-group">
            <label>Nova senha</label>
            <input type="password" name="nova_senha">
          </div>
          <div class="form-group">
            <label>Confirmar senha</label>
            <input type="password" name="confirmar_senha">
          </div>
        </div>
      </div>

      <!-- FOOTER -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="window.closeModal()">Cancelar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>

    </form>
  </div>
</div>

<script>
document.querySelectorAll('.tab').forEach(tab => {
  tab.onclick = () => {
    document.querySelectorAll('.tab, .tab-content')
      .forEach(el => el.classList.remove('active'));

    tab.classList.add('active');
    document.getElementById('tab-' + tab.dataset.tab).classList.add('active');
  };
});

function toggleSenha() {
  document.getElementById('senha-box').hidden =
    !document.getElementById('senha-box').hidden;
}
</script>

