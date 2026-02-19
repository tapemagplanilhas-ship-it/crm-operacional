<?php
session_start();
require_once 'includes/config.php';
require_once 'includes/header.php';
?>
<link rel="stylesheet" href="assets/css/ajuda.css">
<div class="main-container">
    <div class="content">
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-question-circle"></i> Ajuda do Sistema</h3>
            </div>
            <div class="card-body">
                <div class="help-section">
                    <h4>📌 Como usar o sistema</h4>
                    <p>Este sistema CRM foi desenvolvido para gerenciar clientes, vendas e operações da TAPEMAG. Abaixo estão os principais recursos e como utilizá-los.</p>
                </div>

                <div class="help-section">
                    <h4>👥 Perfis de Usuário</h4>
                    <p>O sistema possui diferentes perfis com diferentes níveis de acesso:</p>
                    
                    <div class="table-responsive">
                        <table class="table help-table">
                            <thead>
                                <tr>
                                    <th>Perfil</th>
                                    <th>Acesso</th>
                                    <th>Descrição</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge badge-admin">Admin</span></td>
                                    <td>Total</td>
                                    <td>Acesso completo a todas as funcionalidades do sistema</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-gerencia">Gerência</span></td>
                                    <td>Vendas, Clientes, Relatórios</td>
                                    <td>Gerenciamento de equipe e análise de desempenho</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-vendedor">Vendedor</span></td>
                                    <td>Vendas, Clientes</td>
                                    <td>Cadastro de clientes e registro de vendas</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-estoque">Estoque</span></td>
                                    <td>Produtos</td>
                                    <td>Gestão de inventário e produtos</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-rh">RH</span></td>
                                    <td>Colaboradores</td>
                                    <td>Gestão de equipe e usuários</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-financeiro">Financeiro</span></td>
                                    <td>Financeiro</td>
                                    <td>Controle financeiro e relatórios</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-caixa">Caixa</span></td>
                                    <td>Vendas, Caixa</td>
                                    <td>Registro de vendas e operações de caixa</td>
                                </tr>
                                <tr>
                                    <td><span class="badge badge-recebimento">Recebimento</span></td>
                                    <td>Financeiro, Contas</td>
                                    <td>Gestão de contas a receber</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="help-section">
                    <h4>📋 Funcionalidades Principais</h4>
                    <ul class="help-list">
                        <li><strong>Clientes:</strong> Cadastro completo com histórico de compras</li>
                        <li><strong>Vendas:</strong> Registro de vendas com múltiplas formas de pagamento</li>
                        <li><strong>Produtos:</strong> Gestão de estoque e cadastro de produtos</li>
                        <li><strong>Relatórios:</strong> Análise de desempenho e métricas de vendas</li>
                        <li><strong>Metas:</strong> Definição e acompanhamento de metas por vendedor</li>
                    </ul>
                </div>

                <div class="help-section">
                    <h4>❓ Problemas Comuns</h4>
                    <div class="accordion" id="helpAccordion">
                        <div class="card">
                            <div class="card-header" id="headingOne">
                                <h5 class="mb-0">
                                    <button class="btn btn-link" type="button" data-toggle="collapse" data-target="#collapseOne">
                                        Esqueci minha senha
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseOne" class="collapse show" data-parent="#helpAccordion">
                                <div class="card-body">
                                    Entre em contato com o administrador do sistema ou com o RH para redefinir sua senha.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingTwo">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseTwo">
                                        Não consigo acessar uma funcionalidade
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseTwo" class="collapse" data-parent="#helpAccordion">
                                <div class="card-body">
                                    Verifique se seu perfil tem acesso à funcionalidade. Se precisar de acesso adicional, solicite ao seu supervisor.
                                </div>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-header" id="headingThree">
                                <h5 class="mb-0">
                                    <button class="btn btn-link collapsed" type="button" data-toggle="collapse" data-target="#collapseThree">
                                        Erro ao cadastrar cliente/venda
                                    </button>
                                </h5>
                            </div>
                            <div id="collapseThree" class="collapse" data-parent="#helpAccordion">
                                <div class="card-body">
                                    Verifique se todos os campos obrigatórios foram preenchidos corretamente. Se o problema persistir, contate o suporte técnico.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-mapa">
    <h3><i class="fas fa-map-marked-alt"></i> Localização</h3>

    <div class="map-container">
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d1295.0601930824237!2d-47.85664648765965!3d-23.352616740540693!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x94c5d92430a3ffff%3A0xa390c08c435262b6!2sTapemag%20-%20Uma%20loja%20completa%20de%20solu%C3%A7%C3%B5es.!5e0!3m2!1spt-BR!2sbr!4v1770904856691!5m2!1spt-BR!2sbr" width="600" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </div>
</div>

                <div class="help-section">
                    <h4>📞 Suporte</h4>
                    <p>Para mais informações ou problemas técnicos, entre em contato com:</p>
                    <ul>
                        <li><strong>E-mail:</strong> suporte@tapemag.com.br</li>
                        <li><strong>Telefone:</strong> (15) 3451-1419</li>
                        <li><strong>Horário:</strong> Segunda a Sexta, das 7:30h às 18h</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>