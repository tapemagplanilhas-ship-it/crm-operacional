<?php
require_once 'includes/config.php';
require_once 'includes/header.php';
verificarLogin();

// Verificar permissões
$area_usuario = $_SESSION['area'] ?? null;
$perfil_usuario = $_SESSION['perfil'] ?? null;
$tipo_relatorio = $_GET['tipo'] ?? null;
$area_relatorio = $_GET['area'] ?? $area_usuario;

// Se não for admin e tentar acessar área diferente da sua
if ($perfil_usuario !== 'admin' && $area_relatorio !== $area_usuario) {
    header('Location: relatorios_especializados.php');
    exit();
}

// Definir todas as queries de relatório
$queries_relatorios = [
    'vendas' => [
        'comissões' => [
            'query' => "SELECT v.id, v.data_venda, v.valor, 
                       c.nome as cliente, u.nome as vendedor,
                       (v.valor * 0.1) as comissao
                       FROM vendas v
                       JOIN clientes c ON v.cliente_id = c.id
                       JOIN usuarios u ON v.usuario_id = u.id
                       WHERE v.status = 'concluida'
                       ORDER BY v.data_venda DESC",
            'titulo' => 'Relatório de Comissões'
        ],
        'desempenho' => [
            'query' => "SELECT r.nome as regiao, 
                       COUNT(v.id) as total_vendas,
                       SUM(v.valor) as valor_total
                       FROM vendas v
                       JOIN clientes c ON v.cliente_id = c.id
                       JOIN regioes r ON c.regiao_id = r.id
                       GROUP BY r.nome
                       ORDER BY valor_total DESC",
            'titulo' => 'Desempenho por Região'
        ]
    ],
    'marketing' => [
        'campanhas' => [
            'query' => "SELECT c.nome as campanha, 
                       COUNT(v.id) as conversoes,
                       c.investimento,
                       (SUM(v.valor)/c.investimento) as roi
                       FROM campanhas c
                       LEFT JOIN vendas v ON v.campanha_id = c.id
                       GROUP BY c.id
                       ORDER BY c.data_inicio DESC",
            'titulo' => 'Eficácia de Campanhas'
        ],
        'conversao' => [
            'query' => "SELECT fonte, 
                       COUNT(*) as leads,
                       SUM(CASE WHEN convertido THEN 1 ELSE 0 END) as conversoes,
                       (SUM(CASE WHEN convertido THEN 1 ELSE 0 END)/COUNT(*))*100 as taxa_conversao
                       FROM leads
                       GROUP BY fonte
                       ORDER BY taxa_conversao DESC",
            'titulo' => 'Taxa de Conversão'
        ],
        'estoque' => [
            'query' => "SELECT 
                    cab.nunota       AS \"Número Único\",
                    cab.dtneg        AS \"Data\",
                    ven.apelido      AS \"Vendedor\",
                    tpo.descroper    AS \"Tipo Operação\",
                    pro.codprod      AS \"Código Produto\",
                    pro.descrprod    AS \"Descrição\",
                    pro.compldesc    AS \"Complemento\",
                    pro.marca        AS \"Marca\",
                    ite.qtdneg       AS \"Quantidade\",
                    ite.codlocalorig AS \"Local Origem\"
                FROM 
                    TGFCAB cab,
                    TGFITE ite,
                    TGFTOP tpo,
                    TGFPRO pro,
                    TGFPAR par,
                    TGFVEN ven
                WHERE 
                    cab.nunota = ite.nunota
                    AND cab.codtipoper = tpo.codtipoper
                    AND cab.dhtipoper  = tpo.dhalter
                    AND pro.codprod    = ite.codprod
                    AND ven.codvend    = cab.codvend
                    AND par.codparc    = cab.codparc
                    AND ite.usoprod    = 'R'
                    AND cab.pendente   = 'S'
                    AND ite.pendente   = 'S'
                    AND tpo.descroper LIKE '%COM%RESER%'
                ORDER BY 
                    ite.codlocalorig, pro.descrprod",
            'titulo' => 'Itens Área de Separação'
        ]
    ]
    // Adicione outras áreas conforme necessário
];

// Verificar se o relatório existe
if (!isset($queries_relatorios[$area_relatorio][$tipo_relatorio])) {
    header('Location: relatorios_especializados.php');
    exit();
}

$relatorio = $queries_relatorios[$area_relatorio][$tipo_relatorio];
$titulo = $relatorio['titulo'];

// Executar a query
$stmt = $conn->prepare($relatorio['query']);
$stmt->execute();
$resultado = $stmt->get_result();
$dados = $resultado->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-relatorio">
    <div class="header-relatorio">
        <h2><?= $titulo ?></h2>
        <div class="acoes-relatorio">
            <button onclick="window.print()" class="btn-imprimir">
                <i class="fas fa-print"></i> Imprimir
            </button>
            <button onclick="exportarParaExcel()" class="btn-excel">
                <i class="fas fa-file-excel"></i> Excel
            </button>
        </div>
    </div>

    <!-- Filtros (opcional) -->
    <form method="get" class="filtros-relatorio">
        <input type="hidden" name="tipo" value="<?= $tipo_relatorio ?>">
        <input type="hidden" name="area" value="<?= $area_relatorio ?>">
        
        <div class="form-group">
            <label>Período:</label>
            <input type="date" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>">
            <span>até</span>
            <input type="date" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>">
        </div>
        
        <button type="submit" class="btn-filtrar">
            <i class="fas fa-filter"></i> Filtrar
        </button>
    </form>

    <!-- Tabela de Resultados -->
    <div class="tabela-relatorio">
        <table>
            <thead>
                <tr>
                    <?php if (!empty($dados)): ?>
                        <?php foreach (array_keys($dados[0]) as $coluna): ?>
                            <th><?= ucfirst(str_replace('_', ' ', $coluna)) ?></th>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($dados as $linha): ?>
                    <tr>
                        <?php foreach ($linha as $valor): ?>
                            <td><?= htmlspecialchars($valor) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Gráfico (opcional) -->
    <div class="grafico-relatorio">
        <canvas id="graficoRelatorio"></canvas>
    </div>
</div>

<script>
function exportarParaExcel() {
    // Implementar lógica de exportação
    // Pode usar bibliotecas como SheetJS ou enviar para outro script PHP
    alert('Exportação para Excel será implementada aqui');
}

// Configuração do gráfico (opcional)
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('graficoRelatorio').getContext('2d');
    
    // Exemplo básico - adapte conforme seus dados
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?= json_encode(array_column($dados, 'nome')) ?>,
            datasets: [{
                label: 'Valores',
                data: <?= json_encode(array_column($dados, 'valor')) ?>,
                backgroundColor: '#fc0000'
            }]
        }
    });
});
</script>

<style>
.container-relatorio {
    max-width: 1200px;
    margin: 20px auto;
    padding: 20px;
    background: white;
    box-shadow: 0 0 10px rgba(0,0,0,0.1);
}

.header-relatorio {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.acoes-relatorio button {
    padding: 8px 15px;
    margin-left: 10px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

.btn-imprimir { background: #6c757d; color: white; }
.btn-excel { background: #28a745; color: white; }

.tabela-relatorio {
    overflow-x: auto;
}

.tabela-relatorio table {
    width: 100%;
    border-collapse: collapse;
}

.tabela-relatorio th, 
.tabela-relatorio td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #ddd;
}

.tabela-relatorio th {
    background-color: #f8f9fa;
}

.grafico-relatorio {
    margin-top: 40px;
    height: 400px;
}
</style>

<?php require_once 'includes/footer.php'; ?>