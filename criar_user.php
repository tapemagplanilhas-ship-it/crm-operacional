<?php
require_once 'includes/config.php';
require_once 'includes/header.php';

$conn = getConnection();

if (!$conn) {
    die('Erro ao conectar ao banco de dados');
}



// Verificar se o usuário é admin
if (!verificarPermissao('admin')) {
    echo "<p>Você não tem permissão para acessar esta página.</p>";
    exit;
}

// Processar envio do formulário
$erro = '';
$sucesso = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $perfil = $_POST['perfil'] ?? '';

    if (!$usuario || !$nome || !$email || !$senha || !$perfil) {
        $erro = 'Todos os campos são obrigatórios.';
    } else {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        $sql = "INSERT INTO usuarios (usuario, nome, email, senha, perfil, data_cadastro)
                VALUES (?, ?, ?, ?, ?, NOW())";

        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "sssss", $usuario, $nome, $email, $senha_hash, $perfil);

            if (mysqli_stmt_execute($stmt)) {
                $sucesso = 'Usuário adicionado com sucesso!';
            } else {
                // Se quiser, dá pra melhorar a mensagem quando for duplicado
                $erro = 'Erro ao adicionar usuário (usuário ou e-mail já existente).';
            }

            mysqli_stmt_close($stmt);
        } else {
            $erro = 'Erro na consulta ao banco.';
        }
    }
}


// Buscar todos os usuários
$usuarios = [];
$result = mysqli_query($conn, "SELECT id, nome, email, perfil, data_cadastro FROM usuarios ORDER BY id DESC");
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $usuarios[] = $row;
    }
}
?>

<main class="container">
    <h1>Administração de Usuários</h1>

    <!-- Mensagens -->
    <?php if ($erro): ?>
        <div style="color:red; margin-bottom: 10px;"><?php echo $erro; ?></div>
    <?php endif; ?>
    <?php if ($sucesso): ?>
        <div style="color:green; margin-bottom: 10px;"><?php echo $sucesso; ?></div>
    <?php endif; ?>

    <!-- Formulário de Adicionar Usuário -->
    <section style="margin-bottom: 20px;">
        <h2>Adicionar Novo Usuário</h2>
        <form method="POST" action="">

        <div>
  <label>Usuário (login):</label>
  <input type="text" name="usuario" required>
</div>


            <div>
                <label>Nome:</label>
                <input type="text" name="nome" required>
            </div>
            <div>
                <label>Email:</label>
                <input type="email" name="email" required>
            </div>
            <div>
                <label>Senha:</label>
                <input type="password" name="senha" required>
            </div>
            <div>
                <label>Perfil:</label>
                <select name="perfil" required>
                    <option value="">Selecione</option>
                    <option value="admin">Administrador</option>
                    <option value="gerencia">Gerência</option>
                    <option value="vendedor">Vendedor</option>
                    <option value="estoque">Estoque</option>
                    <option value="rh">Recursos Humanos</option>
                    <option value="caixa">Caixa</option>
                    <option value="financeiro">Financeiro</option>
                    <option value="recebimento">Recebimento</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="margin-top: 15px; align-items: center">Adicionar Usuário</button>
        </form>
    </section>

    <!-- Lista de Usuários -->
    <section>
        <h2>Usuários Cadastrados</h2>
        <table class="data-table sortable" border="1" cellpadding="8" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                    <th>Perfil</th>
                    <th>Criado em</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuarios as $u): ?>
                <tr>
                    <td><?php echo $u['id']; ?></td>
                    <td><?php echo htmlspecialchars($u['nome']); ?></td>
                    <td><?php echo htmlspecialchars($u['email']); ?></td>
                    <td><?php echo $u['perfil']; ?></td>
                    <td><?php echo $u['data_cadastro']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>