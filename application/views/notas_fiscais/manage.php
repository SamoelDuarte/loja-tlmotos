<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Gerenciar Notas Fiscais - TL Motos</title>
    <link href="<?php echo base_url(); ?>css/general.css" rel="stylesheet" type="text/css" />
    <style>
        .nf-container { margin: 20px; }
        .nf-section { margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px; }
        .nf-section h3 { color: #333; margin-bottom: 15px; }
        .nf-list { list-style: none; padding: 0; }
        .nf-list li { padding: 10px; margin: 5px 0; background: #f9f9f9; border-radius: 3px; display: flex; justify-content: space-between; align-items: center; }
        .nf-actions { display: flex; gap: 10px; }
        .btn { padding: 8px 15px; text-decoration: none; border-radius: 3px; font-size: 12px; }
        .btn-primary { background: #007cba; color: white; }
        .btn-success { background: #28a745; color: white; }
        .btn-info { background: #17a2b8; color: white; }
        .alert { padding: 15px; margin: 20px 0; border-radius: 5px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        .stats { display: flex; gap: 20px; margin-bottom: 20px; }
        .stat-card { padding: 20px; background: #f8f9fa; border-radius: 5px; text-align: center; flex: 1; }
        .stat-number { font-size: 24px; font-weight: bold; color: #007cba; }
        .manual-generate { margin-top: 20px; padding: 20px; background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="nf-container">
        <h1>Gerenciar Notas Fiscais</h1>
        
        <?php if ($this->session->flashdata('success')): ?>
            <div class="alert alert-success">
                <?php echo $this->session->flashdata('success'); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($this->session->flashdata('error')): ?>
            <div class="alert alert-error">
                <?php echo $this->session->flashdata('error'); ?>
            </div>
        <?php endif; ?>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number"><?php echo count($nfes); ?></div>
                <div>NF-e Geradas</div>
                <small>(Vendas WooCommerce)</small>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo count($nfces); ?></div>
                <div>NFC-e Geradas</div>
                <small>(Vendas Balcão)</small>
            </div>
        </div>
        
        <div class="nf-section">
            <h3>📧 NF-e - Notas Fiscais Eletrônicas (WooCommerce)</h3>
            <p>Notas fiscais modelo 55 geradas automaticamente para vendas do WooCommerce e enviadas por email.</p>
            
            <?php if (empty($nfes)): ?>
                <p><em>Nenhuma NF-e encontrada. As notas são geradas automaticamente quando pedidos são processados no WooCommerce.</em></p>
            <?php else: ?>
                <ul class="nf-list">
                    <?php foreach ($nfes as $nfe): ?>
                        <li>
                            <div>
                                <strong><?php echo $nfe; ?></strong>
                                <br><small>Gerada automaticamente via WooCommerce</small>
                            </div>
                            <div class="nf-actions">
                                <a href="<?php echo site_url('NotasFiscais/download/' . $nfe); ?>" class="btn btn-primary">
                                    📄 Download
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="nf-section">
            <h3>🏪 NFC-e - Notas Fiscais de Consumidor (Balcão)</h3>
            <p>Notas fiscais modelo 65 geradas automaticamente para vendas realizadas no balcão.</p>
            
            <?php if (empty($nfces)): ?>
                <p><em>Nenhuma NFC-e encontrada. As notas são geradas automaticamente quando vendas são finalizadas no PDV.</em></p>
            <?php else: ?>
                <ul class="nf-list">
                    <?php foreach ($nfces as $nfce): ?>
                        <li>
                            <div>
                                <strong><?php echo $nfce; ?></strong>
                                <br><small>Gerada automaticamente via PDV</small>
                            </div>
                            <div class="nf-actions">
                                <a href="<?php echo site_url('NotasFiscais/download/' . $nfce); ?>" class="btn btn-primary">
                                    📄 Download
                                </a>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        
        <div class="manual-generate">
            <h3>🔧 Geração Manual de NFC-e</h3>
            <p>Para gerar uma NFC-e manualmente para uma venda específica, use o formulário abaixo:</p>
            
            <form action="<?php echo site_url('NotasFiscais/gerar_nfce'); ?>" method="post" style="display: flex; gap: 10px; align-items: center;">
                <label for="sale_id">ID da Venda:</label>
                <input type="number" name="sale_id" id="sale_id" placeholder="Ex: 123" required style="padding: 8px; border: 1px solid #ccc; border-radius: 3px;">
                <button type="submit" class="btn btn-success">Gerar NFC-e</button>
            </form>
            
            <small style="color: #666; margin-top: 10px; display: block;">
                💡 Dica: O ID da venda pode ser encontrado no sistema de vendas ou relatórios.
            </small>
        </div>
        
        <div style="margin-top: 30px; padding: 20px; background: #e9f7ff; border-radius: 5px;">
            <h4>📋 Como Funciona:</h4>
            <ul>
                <li><strong>NF-e (Modelo 55):</strong> Gerada automaticamente para pedidos do WooCommerce e enviada por email para o cliente</li>
                <li><strong>NFC-e (Modelo 65):</strong> Gerada automaticamente para vendas realizadas no PDV/Balcão</li>
                <li><strong>Armazenamento:</strong> Todas as notas são salvas em formato PDF para consulta posterior</li>
                <li><strong>Email:</strong> NF-e são enviadas automaticamente para o email do cliente (somente WooCommerce)</li>
            </ul>
        </div>
        
        <div style="margin-top: 20px; text-align: center;">
            <a href="<?php echo site_url('sales'); ?>" class="btn btn-info">🔙 Voltar ao PDV</a>
            <a href="<?php echo site_url('reports'); ?>" class="btn btn-info">📊 Relatórios</a>
        </div>
    </div>
</body>
</html>
