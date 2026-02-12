TYPE=VIEW
query=select `c`.`id` AS `cliente_id`,`c`.`nome` AS `cliente`,count(distinct `v`.`usuario_id`) AS `total_vendedores`,group_concat(distinct `v`.`usuario_id` order by `v`.`usuario_id` ASC separator \',\') AS `vendedores_que_atendeu`,case when count(distinct `v`.`usuario_id`) = 1 then \'alta\' when count(distinct `v`.`usuario_id`) = 2 then \'media\' else \'baixa\' end AS `nivel_dependencia`,max(`v`.`data_venda`) AS `ultima_compra`,sum(case when `v`.`status` = \'concluida\' then `v`.`valor` end) AS `total_gasto` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `c`.`id`
md5=5340533bd8e87a99fe74d7aac8745fcb
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770806284668228
create-version=2
source=SELECT\n    c.id AS cliente_id,\n    c.nome AS cliente,\n\n    COUNT(DISTINCT v.usuario_id) AS total_vendedores,\n\n    GROUP_CONCAT(DISTINCT v.usuario_id ORDER BY v.usuario_id SEPARATOR \',\') AS vendedores_que_atendeu,\n\n    CASE \n        WHEN COUNT(DISTINCT v.usuario_id) = 1 THEN \'alta\'\n        WHEN COUNT(DISTINCT v.usuario_id) = 2 THEN \'media\'\n        ELSE \'baixa\'\n    END AS nivel_dependencia,\n\n    MAX(v.data_venda) AS ultima_compra,\n\n    SUM(CASE WHEN v.status = \'concluida\' THEN v.valor END) AS total_gasto\n\nFROM clientes c\nLEFT JOIN vendas v ON v.cliente_id = c.id\nGROUP BY c.id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `c`.`id` AS `cliente_id`,`c`.`nome` AS `cliente`,count(distinct `v`.`usuario_id`) AS `total_vendedores`,group_concat(distinct `v`.`usuario_id` order by `v`.`usuario_id` ASC separator \',\') AS `vendedores_que_atendeu`,case when count(distinct `v`.`usuario_id`) = 1 then \'alta\' when count(distinct `v`.`usuario_id`) = 2 then \'media\' else \'baixa\' end AS `nivel_dependencia`,max(`v`.`data_venda`) AS `ultima_compra`,sum(case when `v`.`status` = \'concluida\' then `v`.`valor` end) AS `total_gasto` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `c`.`id`
mariadb-version=100432
