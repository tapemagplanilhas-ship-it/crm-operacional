<?php
require_once "includes/config.php";
verificarLogin();
if ($_SESSION['perfil'] !== 'admin') {
    exit("Apenas administradores podem acessar este painel.");
}

require_once "includes/permissoes.php";
?>
<link rel="stylesheet" href="assets/css/perm.css">
<a href="configuracoes.php" class="btn-voltar-config">
    <i class="fas fa-arrow-left"></i> Voltar para Configurações
</a>

<h2>Gerenciar Permissões</h2>
<form method="POST" action="salvar_permissoes.php">

<table border="1" cellpadding="10" cellspacing="0">
<tr>
    <th>Perfil</th>
    <th>Permissões</th>
</tr>

<?php 
$todas = array_unique(array_merge(...array_values($permissoes)));
sort($todas);

foreach ($permissoes as $perfil => $lista): ?>
<tr>
    <td><strong><?= ucfirst($perfil) ?></strong></td>
    <td>
        <?php foreach ($todas as $item): ?>
            <label style="display:block;">
                <input type="checkbox" name="permissoes[<?= $perfil ?>][]" value="<?= $item ?>"
                       <?= in_array($item, $lista) ? 'checked' : '' ?>>
                <?= $item ?>
            </label>
        <?php endforeach; ?>
    </td>
</tr>
<?php endforeach; ?>

</table>

<br>

<button type="submit" style="padding:10px 20px;">Salvar Alterações</button>

</form>