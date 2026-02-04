<?php
http_response_code(404);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>404 - Página não encontrada</title>

  <!-- Se você já usa seu CSS global -->
  <link rel="stylesheet" href="assets/css/styles.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

  <main class="container" style="padding: 40px 20px;">
    <div class="stat-card" style="max-width: 720px; margin: 60px auto; gap: 16px;">
      <div class="stat-icon" style="background:#b90000;">
        <i class="fa-solid fa-triangle-exclamation"></i>
      </div>

      <div class="stat-content">
        <h1 style="margin:0; font-size: 22px; color:#2c3e50;">Erro 404</h1>
        <p style="margin:8px 0 0; color:#666;">
          A página que você tentou acessar não existe ou foi movida.
        </p>

        <div style="margin-top: 18px; display:flex; gap:10px; flex-wrap:wrap;">
          <a class="btn btn-primary" href="index.php">
            <i class="fa-solid fa-house"></i> Voltar ao Dashboard
          </a>

          <button class="btn btn-secondary" type="button" onclick="history.back()">
            <i class="fa-solid fa-arrow-left"></i> Voltar
          </button>
        </div>

        <div style="margin-top: 14px; font-size: 12px; color:#888;">
          <div><strong>URL:</strong> <?php echo htmlspecialchars($_SERVER['REQUEST_URI'] ?? ''); ?></div>
        </div>
      </div>
    </div>
  </main>

</body>
</html>
