<?php

$permissoes = [

    'admin' => [
        'dashboard',
        'dashboard_geral',
        'clientes',
        'vendas',
        'relatorios',
        'relatorios_especializados',
        'usuarios',
        'gestao',
        'configuracoes',
        'permissoes'
    ],

    'gerencia' => [
        'dashboard',
        'dashboard_geral',
        'clientes',
        'vendas',
        'relatorios_especializados',
        'gestao',
        'configuracoes'
    ],

    'vendedor' => [
        'dashboard',
        'clientes',
        'vendas',
        'relatorios_especializados',
        'configuracoes'
    ],

    'financeiro' => [
        'dashboard',
        'relatorios',
        'relatorios_especializados',
        'configuracoes'
    ],

    'caixa' => [
        'dashboard',
        'vendas',
        'configuracoes'
    ],

    'recebimento' => [
        'dashboard',
        'clientes',
        'vendas',
        'configuracoes'
    ],

    'estoque' => [
        'dashboard',
        'gestao',
        'configuracoes'
    ],

    'rh' => [
        'dashboard',
        'configuracoes'
    ]
];

?>