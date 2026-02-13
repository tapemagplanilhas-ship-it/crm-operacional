TYPE=VIEW
query=select `u`.`id` AS `id`,`u`.`nome` AS `nome`,`u`.`perfil` AS `perfil`,count(`v`.`id`) AS `vendas_mes`,sum(`v`.`valor`) AS `faturamento_mes`,avg(`v`.`valor`) AS `ticket_medio_mes`,count(distinct `v`.`cliente_id`) AS `clientes_atendidos_mes`,(select count(0) from `crm_operacional`.`clientes` where `crm_operacional`.`clientes`.`usuario_id` = `u`.`id`) AS `total_clientes_carteira`,rank() over ( order by sum(`v`.`valor`) desc) AS `ranking_faturamento`,rank() over ( order by count(`v`.`id`) desc) AS `ranking_vendas` from (`crm_operacional`.`usuarios` `u` left join `crm_operacional`.`vendas` `v` on(`v`.`usuario_id` = `u`.`id` and month(`v`.`data_venda`) = month(curdate()))) group by `u`.`id`
md5=6f53f8a0b75b80724e8c6d04e9e90484
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770806698345224
create-version=2
source=SELECT\n    u.id,\n    u.nome,\n    u.perfil,\n\n    COUNT(v.id) AS vendas_mes,\n    SUM(v.valor) AS faturamento_mes,\n    AVG(v.valor) AS ticket_medio_mes,\n    COUNT(DISTINCT v.cliente_id) AS clientes_atendidos_mes,\n    \n    (SELECT COUNT(*) FROM clientes WHERE usuario_id = u.id) AS total_clientes_carteira,\n\n    RANK() OVER (ORDER BY SUM(v.valor) DESC) AS ranking_faturamento,\n    RANK() OVER (ORDER BY COUNT(v.id) DESC) AS ranking_vendas\n\nFROM usuarios u\nLEFT JOIN vendas v ON v.usuario_id = u.id \n    AND MONTH(v.data_venda) = MONTH(CURDATE())\nGROUP BY u.id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `u`.`id` AS `id`,`u`.`nome` AS `nome`,`u`.`perfil` AS `perfil`,count(`v`.`id`) AS `vendas_mes`,sum(`v`.`valor`) AS `faturamento_mes`,avg(`v`.`valor`) AS `ticket_medio_mes`,count(distinct `v`.`cliente_id`) AS `clientes_atendidos_mes`,(select count(0) from `crm_operacional`.`clientes` where `crm_operacional`.`clientes`.`usuario_id` = `u`.`id`) AS `total_clientes_carteira`,rank() over ( order by sum(`v`.`valor`) desc) AS `ranking_faturamento`,rank() over ( order by count(`v`.`id`) desc) AS `ranking_vendas` from (`crm_operacional`.`usuarios` `u` left join `crm_operacional`.`vendas` `v` on(`v`.`usuario_id` = `u`.`id` and month(`v`.`data_venda`) = month(curdate()))) group by `u`.`id`
mariadb-version=100432
