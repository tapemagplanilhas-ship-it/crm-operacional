<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
verificarLogin();

$tipo = $_GET['tipo'] ?? '';
$area_solicitada = $_GET['area'] ?? $_SESSION['area'] ?? 'vendas';

// Verificação de segurança para não-admins
if ($_SESSION['perfil'] !== 'admin') {
    $relatorios_permitidos = [
        'vendas' => ['comissões', 'desempenho'],
        'marketing' => ['campanhas', 'conversao'],
        'rh' => ['produtividade', 'turnover'],
        'financeiro' => ['fluxo_caixa', 'receitas_despesas']
    ];
    
    $area_usuario = $_SESSION['area'] ?? 'vendas';
    
    if (!in_array($tipo, $relatorios_permitidos[$area_usuario] ?? [])) {
        header('Location: relatorios_especializados.php?erro=acesso_negado');
        exit;
    }
}

// Configurações específicas por relatório
$config_relatorios = [
    'comissões' => ['titulo' => 'Relatório de Comissões', 'arquivo' => 'relatorios/comissoes.php'],
    'desempenho' => ['titulo' => 'Desempenho por Região', 'arquivo' => 'relatorios/desempenho.php'],
    // ... outros relatórios
];

$config = $config_relatorios[$tipo] ?? ['titulo' => 'Relatório', 'arquivo' => ''];
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h2><?= $config['titulo'] ?></h2>
        <small class="text-muted">Área: <?= ucfirst($area_solicitada) ?></small>
        <div class="dashboard-actions">
            <button class="btn-secondary" onclick="window.print()">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button class="btn-success" id="btn-exportar-excel">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
        </div>
    </div>

    <div class="conteudo-relatorio">
        <?php 
        if (file_exists($config['arquivo'])) {
            include $config['arquivo'];
        } else {
            echo '<div class="alert alert-warning">Relatório em desenvolvimento</div>';
        }
        ?>
    </div>
</div>

<script>
document.getElementById('btn-exportar-excel').addEventListener('click', function() {
    // Implemente a exportação para Excel aqui
    console.log('Exportando relatório de <?= $tipo ?>...');
});
</script>

<?php require_once 'includes/footer.php'; ?>