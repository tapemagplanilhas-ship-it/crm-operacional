<?php
require_once 'includes/config.php';

// Apenas para desenvolvimento - remover em produção
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? 'Administrador';
    $email = $_POST['email'] ?? 'admin@tapemag.com';
    $usuario = $_POST['usuario'] ?? 'admin';
    $senha = $_POST['senha'] ?? 'admin123';
    $perfil = $_POST['perfil'] ?? 'admin';
    
    $conn = getConnection();
    if ($conn) {
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
        
        $sql = "INSERT INTO usuarios (nome, email, usuario, senha, perfil, status) 
                VALUES (?, ?, ?, ?, ?, 'ativo')";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssss", $nome, $email, $usuario, $senha_hash, $perfil);
        
        if ($stmt->execute()) {
            echo "✅ Usuário criado com sucesso!<br>";
            echo "Usuário: $usuario<br>";
            echo "Senha: $senha<br>";
            echo "Perfil: $perfil<br>";
            echo '<a href="login.php">Ir para login</a>';
        } else {
            echo "❌ Erro ao criar usuário: " . $conn->error;
        }
        
        $conn->close();
    }
} else {
    ?>
    <!DOCTYPE html>
    <html>
    <head><title>Criar Usuário Admin</title></head>
    <body>
        <h2>Criar Usuário Administrador</h2>
        <form method="POST">
            <p><strong>APENAS PARA DESENVOLVIMENTO</strong></p>
            <p>Nome: <input type="text" name="nome" value="Administrador"></p>
            <p>E-mail: <input type="email" name="email" value="admin@tapemag.com"></p>
            <p>Usuário: <input type="text" name="usuario" value="admin"></p>
            <p>Senha: <input type="text" name="senha" value="admin123"></p>
            <p>Perfil: 
                <select name="perfil">
                    <option value="admin">Administrador</option>
                    <option value="gerencia">Gerência</option>
                    <option value="vendedor">Vendedor</option>
                </select>
            </p>
            <button type="submit">Criar Usuário</button>
        </form>
    </body>
    </html>
    <?php
}
?>