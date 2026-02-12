TYPE=VIEW
query=with faturamento as (select `c`.`id` AS `id`,`c`.`nome` AS `nome`,coalesce(sum(`v`.`valor`),0) AS `total_gasto` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id` and `v`.`status` = \'concluida\')) group by `c`.`id`), ordenado as (select `faturamento`.`id` AS `id`,`faturamento`.`nome` AS `nome`,`faturamento`.`total_gasto` AS `total_gasto`,ntile(3) over ( order by `faturamento`.`total_gasto` desc) AS `categoria` from `faturamento`)select case `ordenado`.`categoria` when 1 then \'A\' when 2 then \'B\' else \'C\' end AS `categoria_abc`,count(0) AS `quantidade_clientes`,sum(`ordenado`.`total_gasto`) AS `total_faturamento`,avg(`ordenado`.`total_gasto`) AS `media_por_cliente`,group_concat(`ordenado`.`nome` order by `ordenado`.`total_gasto` DESC separator \', \') AS `clientes` from `ordenado` group by `ordenado`.`categoria`
md5=c18294f6e607a34ca1f834c326f187b6
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770806363710451
create-version=2
source=WITH faturamento AS (\n    SELECT \n        c.id,\n        c.nome,\n        COALESCE(SUM(v.valor), 0) AS total_gasto\n    FROM clientes c\n    LEFT JOIN vendas v \n        ON v.cliente_id = c.id \n        AND v.status = \'concluida\'\n    GROUP BY c.id\n),\nordenado AS (\n    SELECT \n        id,\n        nome,\n        total_gasto,\n        NTILE(3) OVER (ORDER BY total_gasto DESC) AS categoria\n    FROM faturamento\n)\nSELECT \n    CASE categoria\n        WHEN 1 THEN \'A\'\n        WHEN 2 THEN \'B\'\n        ELSE \'C\'\n    END AS categoria_abc,\n\n    COUNT(*) AS quantidade_clientes,\n    SUM(total_gasto) AS total_faturamento,\n    AVG(total_gasto) AS media_por_cliente,\n\n    GROUP_CONCAT(nome ORDER BY total_gasto DESC SEPARATOR \', \') AS clientes\n\nFROM ordenado\nGROUP BY categoria
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=with faturamento as (select `c`.`id` AS `id`,`c`.`nome` AS `nome`,coalesce(sum(`v`.`valor`),0) AS `total_gasto` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id` and `v`.`status` = \'concluida\')) group by `c`.`id`), ordenado as (select `faturamento`.`id` AS `id`,`faturamento`.`nome` AS `nome`,`faturamento`.`total_gasto` AS `total_gasto`,ntile(3) over ( order by `faturamento`.`total_gasto` desc) AS `categoria` from `faturamento`)select case `ordenado`.`categoria` when 1 then \'A\' when 2 then \'B\' else \'C\' end AS `categoria_abc`,count(0) AS `quantidade_clientes`,sum(`ordenado`.`total_gasto`) AS `total_faturamento`,avg(`ordenado`.`total_gasto`) AS `media_por_cliente`,group_concat(`ordenado`.`nome` order by `ordenado`.`total_gasto` DESC separator \', \') AS `clientes` from `ordenado` group by `ordenado`.`categoria`
mariadb-version=100432
