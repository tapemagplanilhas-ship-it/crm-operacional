TYPE=VIEW
query=select year(`v`.`data_venda`) AS `ano`,month(`v`.`data_venda`) AS `mes`,count(distinct case when `v`.`status` = \'concluida\' then `v`.`cliente_id` end) AS `clientes_que_compraram`,sum(case when `v`.`status` = \'concluida\' then `v`.`valor` end) AS `faturamento_novos_clientes`,(select count(0) from `crm_operacional`.`clientes` where `crm_operacional`.`clientes`.`data_cadastro` < makedate(year(`v`.`data_venda`),1) - interval month(`v`.`data_venda`) - 1 month) AS `clientes_mes_anterior`,0 AS `novos_clientes`,0 AS `crescimento_percentual` from `crm_operacional`.`vendas` `v` group by year(`v`.`data_venda`),month(`v`.`data_venda`)
md5=3f9a9b6d2d1f9c053bd1ea94c2d7dd71
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770806389831572
create-version=2
source=SELECT\n    YEAR(v.data_venda) AS ano,\n    MONTH(v.data_venda) AS mes,\n\n    COUNT(DISTINCT CASE WHEN v.status=\'concluida\' THEN v.cliente_id END) AS clientes_que_compraram,\n\n    SUM(CASE WHEN v.status=\'concluida\' THEN v.valor END) AS faturamento_novos_clientes,\n\n    (SELECT COUNT(*) FROM clientes WHERE data_cadastro < DATE_SUB(MAKEDATE(YEAR(v.data_venda), 1), INTERVAL (MONTH(v.data_venda)-1) MONTH))\n        AS clientes_mes_anterior,\n\n    0 AS novos_clientes, -- impossível sem tabela historica\n    0 AS crescimento_percentual\n\nFROM vendas v\nGROUP BY ano, mes
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select year(`v`.`data_venda`) AS `ano`,month(`v`.`data_venda`) AS `mes`,count(distinct case when `v`.`status` = \'concluida\' then `v`.`cliente_id` end) AS `clientes_que_compraram`,sum(case when `v`.`status` = \'concluida\' then `v`.`valor` end) AS `faturamento_novos_clientes`,(select count(0) from `crm_operacional`.`clientes` where `crm_operacional`.`clientes`.`data_cadastro` < makedate(year(`v`.`data_venda`),1) - interval month(`v`.`data_venda`) - 1 month) AS `clientes_mes_anterior`,0 AS `novos_clientes`,0 AS `crescimento_percentual` from `crm_operacional`.`vendas` `v` group by year(`v`.`data_venda`),month(`v`.`data_venda`)
mariadb-version=100432
