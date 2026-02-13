TYPE=VIEW
query=select `v`.`data_venda` AS `data_venda`,dayname(`v`.`data_venda`) AS `dia_semana`,hour(`v`.`data_registro`) AS `hora`,count(0) AS `total_vendas`,sum(`v`.`valor`) AS `faturamento_total`,avg(`v`.`valor`) AS `ticket_medio`,count(distinct `v`.`usuario_id`) AS `vendedores_ativos`,count(distinct `v`.`cliente_id`) AS `clientes_atendidos`,count(0) AS `total_atendimentos`,sum(`v`.`status` = \'concluida\') AS `atendimentos_com_venda` from `crm_operacional`.`vendas` `v` group by `v`.`data_venda`,hour(`v`.`data_registro`)
md5=ed38f78dbc8dbab07b07debb87a5cc2f
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770806407659518
create-version=2
source=SELECT\n    v.data_venda,\n    DAYNAME(v.data_venda) AS dia_semana,\n    HOUR(v.data_registro) AS hora,\n    COUNT(*) AS total_vendas,\n    SUM(v.valor) AS faturamento_total,\n    AVG(v.valor) AS ticket_medio,\n    COUNT(DISTINCT v.usuario_id) AS vendedores_ativos,\n    COUNT(DISTINCT v.cliente_id) AS clientes_atendidos,\n    COUNT(*) AS total_atendimentos,\n    SUM(v.status=\'concluida\') AS atendimentos_com_venda\nFROM vendas v\nGROUP BY v.data_venda, HOUR(v.data_registro)
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `v`.`data_venda` AS `data_venda`,dayname(`v`.`data_venda`) AS `dia_semana`,hour(`v`.`data_registro`) AS `hora`,count(0) AS `total_vendas`,sum(`v`.`valor`) AS `faturamento_total`,avg(`v`.`valor`) AS `ticket_medio`,count(distinct `v`.`usuario_id`) AS `vendedores_ativos`,count(distinct `v`.`cliente_id`) AS `clientes_atendidos`,count(0) AS `total_atendimentos`,sum(`v`.`status` = \'concluida\') AS `atendimentos_com_venda` from `crm_operacional`.`vendas` `v` group by `v`.`data_venda`,hour(`v`.`data_registro`)
mariadb-version=100432
