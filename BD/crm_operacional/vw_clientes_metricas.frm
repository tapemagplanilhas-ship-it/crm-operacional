TYPE=VIEW
query=select `c`.`id` AS `id`,`c`.`nome` AS `nome`,`c`.`telefone` AS `telefone`,`c`.`email` AS `email`,`c`.`empresa` AS `empresa`,`c`.`documento` AS `documento`,`c`.`observacoes` AS `observacoes`,`c`.`usuario_id` AS `usuario_id`,max(`v`.`data_venda`) AS `ultima_venda`,coalesce(sum(case when `v`.`status` = \'concluida\' then `v`.`valor` end),0) AS `total_gasto`,coalesce(avg(case when `v`.`status` = \'concluida\' then `v`.`valor` end),0) AS `media_gastos`,coalesce(sum(`v`.`status` = \'concluida\'),0) / nullif(count(`v`.`id`),0) * 100 AS `taxa_fechamento` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `c`.`id`
md5=a8bd41e9f0e952efd852a8f9b9b156b9
updatable=0
algorithm=0
definer_user=root
definer_host=localhost
suid=2
with_check_option=0
timestamp=0001770805976140326
create-version=2
source=SELECT \n    c.id,\n    c.nome,\n    c.telefone,\n    c.email,\n    c.empresa,\n    c.documento,\n    c.observacoes,\n    c.usuario_id,\n\n    MAX(v.data_venda) AS ultima_venda,\n\n    COALESCE(SUM(CASE WHEN v.status = \'concluida\' THEN v.valor END), 0) AS total_gasto,\n\n    COALESCE(AVG(CASE WHEN v.status = \'concluida\' THEN v.valor END), 0) AS media_gastos,\n\n    (\n      COALESCE(SUM(v.status = \'concluida\'), 0)\n      /\n      NULLIF(COUNT(v.id), 0)\n    ) * 100 AS taxa_fechamento\n\nFROM clientes c\nLEFT JOIN vendas v ON v.cliente_id = c.id\nGROUP BY c.id
client_cs_name=utf8mb4
connection_cl_name=utf8mb4_unicode_ci
view_body_utf8=select `c`.`id` AS `id`,`c`.`nome` AS `nome`,`c`.`telefone` AS `telefone`,`c`.`email` AS `email`,`c`.`empresa` AS `empresa`,`c`.`documento` AS `documento`,`c`.`observacoes` AS `observacoes`,`c`.`usuario_id` AS `usuario_id`,max(`v`.`data_venda`) AS `ultima_venda`,coalesce(sum(case when `v`.`status` = \'concluida\' then `v`.`valor` end),0) AS `total_gasto`,coalesce(avg(case when `v`.`status` = \'concluida\' then `v`.`valor` end),0) AS `media_gastos`,coalesce(sum(`v`.`status` = \'concluida\'),0) / nullif(count(`v`.`id`),0) * 100 AS `taxa_fechamento` from (`crm_operacional`.`clientes` `c` left join `crm_operacional`.`vendas` `v` on(`v`.`cliente_id` = `c`.`id`)) group by `c`.`id`
mariadb-version=100432
