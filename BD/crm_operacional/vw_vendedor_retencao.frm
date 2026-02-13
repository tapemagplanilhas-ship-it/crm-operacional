TYPE=VIEW
query=select `u`.`id` AS `vendedor_id`,`u`.`nome` AS `vendedor`,count(distinct `c`.`id`) AS `total_clientes_carteira`,sum(case when `v`.`data_venda` >= curdate() - interval 90 day then 1 end) AS `clientes_retidos_90dias`,sum(case when `v`.`data_venda` >= curdate() - interval 60 day then 1 end) AS `clientes_retidos_60dias`,sum(case when `v`.`data_venda` >= curdate() - interval 30 day then 1 end) AS `clientes_retidos_30dias`,sum(case when `v`.`data_venda` >= curdate() - interval 90 day then 1 end) / nullif(count(distinct `c`.`id`),0) * 100 AS `taxa_retencao_90dias`,sum(`v`.`valor`) AS `faturamento_carteira`,avg(`v`.`valor`) AS `ticket_medio_carteira` from ((`crm_operacional`.`usuarios` `u` left join `crm_operacional`.`clientes` `c` on(`c`.`usuario_id` = `u`.`id`)) left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `u`.`id`
md5=fe7efb8260fcb9d293b74619ca30c5d5
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770806723780937
create-version=2
source=SELECT\n    u.id AS vendedor_id,\n    u.nome AS vendedor,\n\n    COUNT(DISTINCT c.id) AS total_clientes_carteira,\n\n    SUM(CASE WHEN v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 1 END) AS clientes_retidos_90dias,\n    SUM(CASE WHEN v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 60 DAY) THEN 1 END) AS clientes_retidos_60dias,\n    SUM(CASE WHEN v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) THEN 1 END) AS clientes_retidos_30dias,\n\n    (\n        SUM(CASE WHEN v.data_venda >= DATE_SUB(CURDATE(), INTERVAL 90 DAY) THEN 1 END)\n        /\n        NULLIF(COUNT(DISTINCT c.id),0)\n    ) * 100 AS taxa_retencao_90dias,\n\n    SUM(v.valor) AS faturamento_carteira,\n    AVG(v.valor) AS ticket_medio_carteira\n\nFROM usuarios u\nLEFT JOIN clientes c ON c.usuario_id = u.id\nLEFT JOIN vendas v ON v.cliente_id = c.id\nGROUP BY u.id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `u`.`id` AS `vendedor_id`,`u`.`nome` AS `vendedor`,count(distinct `c`.`id`) AS `total_clientes_carteira`,sum(case when `v`.`data_venda` >= curdate() - interval 90 day then 1 end) AS `clientes_retidos_90dias`,sum(case when `v`.`data_venda` >= curdate() - interval 60 day then 1 end) AS `clientes_retidos_60dias`,sum(case when `v`.`data_venda` >= curdate() - interval 30 day then 1 end) AS `clientes_retidos_30dias`,sum(case when `v`.`data_venda` >= curdate() - interval 90 day then 1 end) / nullif(count(distinct `c`.`id`),0) * 100 AS `taxa_retencao_90dias`,sum(`v`.`valor`) AS `faturamento_carteira`,avg(`v`.`valor`) AS `ticket_medio_carteira` from ((`crm_operacional`.`usuarios` `u` left join `crm_operacional`.`clientes` `c` on(`c`.`usuario_id` = `u`.`id`)) left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `u`.`id`
mariadb-version=100432
