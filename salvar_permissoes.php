<?php
session_start();

if ($_SESSION['perfil'] !== 'admin') {
    exit("Acesso negado");
}

$dados = $_POST['permissoes'];

// Gerar novo arquivo permissoes.php automaticamente
$conteudo = "<?php\n\n\$permissoes = " . var_export($dados, true) . ";\n\n?>";

file_put_contents("includes/permissoes.php", $conteudo);

echo "<h2>Permissões atualizadas!</h2>";
echo "<a href='permissoes.php'>Voltar</a>";