TYPE=VIEW
query=select `c`.`id` AS `id`,`c`.`nome` AS `nome`,`c`.`telefone` AS `telefone`,`c`.`email` AS `email`,`c`.`usuario_id` AS `usuario_id`,max(`v`.`data_venda`) AS `ultima_venda`,case when max(`v`.`data_venda`) >= curdate() - interval 30 day then \'ativo\' when max(`v`.`data_venda`) >= curdate() - interval 90 day then \'recorrente\' when max(`v`.`data_venda`) is null then \'novo\' when max(`v`.`data_venda`) < curdate() - interval 90 day then \'inativo\' else \'sem_definicao\' end AS `status_cliente` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `c`.`id`
md5=5d285a98c8f721759ddee1ede186d80c
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770806247270525
create-version=2
source=SELECT \n    c.id,\n    c.nome,\n    c.telefone,\n    c.email,\n    c.usuario_id,\n\n    MAX(v.data_venda) AS ultima_venda,\n\n    CASE\n        WHEN MAX(v.data_venda) >= DATE_SUB(CURDATE(), INTERVAL 30 DAY) \n            THEN \'ativo\'\n        WHEN MAX(v.data_venda) >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)\n            THEN \'recorrente\'\n        WHEN MAX(v.data_venda) IS NULL\n            THEN \'novo\'\n        WHEN MAX(v.data_venda) < DATE_SUB(CURDATE(), INTERVAL 90 DAY)\n            THEN \'inativo\'\n        ELSE \'sem_definicao\'\n    END AS status_cliente\n\nFROM clientes c\nLEFT JOIN vendas v ON v.cliente_id = c.id\nGROUP BY c.id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `c`.`id` AS `id`,`c`.`nome` AS `nome`,`c`.`telefone` AS `telefone`,`c`.`email` AS `email`,`c`.`usuario_id` AS `usuario_id`,max(`v`.`data_venda`) AS `ultima_venda`,case when max(`v`.`data_venda`) >= curdate() - interval 30 day then \'ativo\' when max(`v`.`data_venda`) >= curdate() - interval 90 day then \'recorrente\' when max(`v`.`data_venda`) is null then \'novo\' when max(`v`.`data_venda`) < curdate() - interval 90 day then \'inativo\' else \'sem_definicao\' end AS `status_cliente` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `c`.`id`
mariadb-version=100432
