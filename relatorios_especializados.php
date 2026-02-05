<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
verificarLogin();

// Definir todas as áreas e relatórios disponíveis
$todas_areas_relatorios = [
    'vendas' => [
        'comissões' => 'Relatório de Comissões',
        'desempenho' => 'Desempenho por Região'
    ],
    'marketing' => [
        'campanhas' => 'Eficácia de Campanhas',
        'conversao' => 'Taxa de Conversão'
    ],
    'rh' => [
        'produtividade' => 'Produtividade da Equipe',
        'turnover' => 'Índice de Turnover'
    ],
    'financeiro' => [
        'fluxo_caixa' => 'Fluxo de Caixa',
        'receitas_despesas' => 'Receitas vs Despesas'
    ]
];

// Se for admin, mostra todos os relatórios agrupados por área
if ($_SESSION['perfil'] === 'admin') {
    $mostrar_todos = true;
    $area_usuario = 'admin';
} else {
    $mostrar_todos = false;
    $area_usuario = $_SESSION['area'] ?? 'vendas'; // Valor padrão
}

// Verificar se a área existe, senão usa 'vendas'
if (!array_key_exists($area_usuario, $todas_areas_relatorios) && !$mostrar_todos) {
    $area_usuario = 'vendas';
}
?>

<div class="dashboard">
    <div class="dashboard-header">
        <h2><i class="fas fa-chart-pie"></i> Relatórios Especializados</h2>
        <?php if ($mostrar_todos): ?>
            <div class="dashboard-actions">
                <button class="btn-secondary" id="btn-exportar-todos">
                    <i class="fas fa-file-export"></i> Exportar Todos
                </button>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($mostrar_todos): ?>
        <!-- Visualização para Admin (todos os relatórios) -->
        <?php foreach ($todas_areas_relatorios as $area => $relatorios): ?>
            <div class="area-section">
                <h3 class="area-title">
                    <i class="fas <?= 
                        $area === 'vendas' ? 'fa-dollar-sign' : 
                        ($area === 'marketing' ? 'fa-bullseye' : 
                        ($area === 'rh' ? 'fa-users' : 'fa-coins')) 
                    ?>"></i>
                    Área <?= ucfirst($area) ?>
                </h3>
                <div class="grid-cards">
                    <?php foreach ($relatorios as $codigo => $titulo): ?>
                        <div class="card-relatorio">
                            <div class="card-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <h4><?= $titulo ?></h4>
                            <a href="gerar_relatorio_especializado.php?tipo=<?= $codigo ?>&area=<?= $area ?>" class="btn btn-primary">
                                <i class="fas fa-eye"></i> Visualizar
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <!-- Visualização normal (apenas para a área do usuário) -->
        <div class="grid-cards">
            <?php foreach ($todas_areas_relatorios[$area_usuario] as $codigo => $titulo): ?>
                <div class="card-relatorio">
                    <div class="card-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4><?= $titulo ?></h4>
                    <a href="gerar_relatorio_especializado.php?tipo=<?= $codigo ?>" class="btn btn-primary">
                        <i class="fas fa-eye"></i> Visualizar
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.area-section {
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 1px solid #e2e8f0;
}

.area-title {
    color: #4a5568;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.grid-cards {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.card-relatorio {
    background: white;
    border-radius: 8px;
    padding: 20px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: transform 0.3s;
}

.card-relatorio:hover {
    transform: translateY(-5px);
}

.card-icon {
    font-size: 1.8rem;
    color: #4a5568;
    margin-bottom: 10px;
}
</style>

<script>
// Script para exportar todos (apenas para admin)
document.getElementById('btn-exportar-todos')?.addEventListener('click', function() {
    // Implemente a lógica de exportação aqui
    alert('Exportando todos os relatórios...');
});
</script>

<?php require_once 'includes/footer.php'; ?>