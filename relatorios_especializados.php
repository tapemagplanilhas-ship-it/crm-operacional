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

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
async function carregarGrafico() {
    const response = await fetch("api/relatorios_dashboard.php");
    const result = await response.json();

    if (!result.success) return;

    const dados = result.data;

    const labels = [
        "Vendas",
        "Clientes",
        "Concluídas",
        "Perdidas",
        "Orçamentos"
    ];

    const valores = [
        dados.total_vendas,
        dados.total_clientes,
        dados.vendas_status.concluida ?? 0,
        dados.vendas_status.perdida ?? 0,
        dados.vendas_status.orcamento ?? 0
    ];

    const ctx = document.getElementById("graficoRelatorios").getContext("2d");

    new Chart(ctx, {
        type: "bar",
        data: {
            labels,
            datasets: [{
                data: valores,
                backgroundColor: "#fc0000",
                borderRadius: 10,
                barThickness: 28, /* FINO E ELEGANTE */
                hoverBackgroundColor: "#a00000"
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 12 }, color: "#6B7280" }
                },
                y: {
                    grid: { color: "rgba(0,0,0,0.05)" },
                    ticks: {
                        beginAtZero: true,
                        precision: 0,
                        color: "#9CA3AF",
                        font: { size: 11 }
                    }
                }
            }
        }
    });
}

document.addEventListener("DOMContentLoaded", carregarGrafico);
</script>

<div class="dashboard">
    <div class="dashboard-header">
        <h2><i class="fas fa-chart-pie"></i> Relatórios Especializados</h2>
    </div>

    <!-- Gráfico de Pizza -->
    <div class="grafico-container">
        <canvas id="graficoRelatorios"></canvas>
    </div>

    <h3 class="subtitulo">Escolha um relatório:</h3>

    <div class="botoes-relatorios">
        <?php if ($mostrar_todos): ?>
            <!-- Admin vê TODOS os relatórios -->
            <?php foreach ($todas_areas_relatorios as $area => $relatorios): ?>
                <?php foreach ($relatorios as $codigo => $titulo): ?>
                    <a href="gerar_relatorio_especializado.php?tipo=<?= $codigo ?>&area=<?= $area ?>" 
                       class="botao-relatorio">
                        <i class="fas fa-file-alt"></i> <?= $titulo ?>
                    </a>
                <?php endforeach; ?>
            <?php endforeach; ?>
        <?php else: ?>
            <!-- Usuário normal vê apenas a área dele -->
            <?php foreach ($todas_areas_relatorios[$area_usuario] as $codigo => $titulo): ?>
                <a href="gerar_relatorio_especializado.php?tipo=<?= $codigo ?>" 
                   class="botao-relatorio">
                    <i class="fas fa-file-alt"></i> <?= $titulo ?>
                </a>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<style>
.grafico-container {
    width: 100%;
    max-width: 520px;
    height: 260px; /* ALTURA MENOR */
    margin: 25px auto;
    padding: 15px 25px;
    background: white;
    border-radius: 14px;
    box-shadow: 0 2px 8px rgb(0 0 0 / 6%);
}
.subtitulo {
    text-align: center;
    margin-bottom: 20px;
    color: #4a5568;
}

.botoes-relatorios {
    display: flex;
    flex-direction: column;
    gap: 15px;
    max-width: 500px;
    margin: 0 auto 40px;
}

.botao-relatorio {
    background: #eb0000;
    color: white;
    padding: 14px 18px;
    border-radius: 8px;
    text-align: center;
    font-size: 1rem;
    text-decoration: none;
    transition: .2s;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.botao-relatorio:hover {
    background: #a30000;
    transform: translateY(-2px);
}
</style>

<?php require_once 'includes/footer.php'; ?>