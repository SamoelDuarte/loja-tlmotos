<?php $this->load->view("partial/header"); ?>

<div id="page_title" style="margin-bottom:8px;">
    <?php echo "Nota Fiscal Gerada com Sucesso!"; ?>
</div>

<div id="content_area_wrapper">
    <div id="content_area" style="padding: 20px;">
        
        <div style="text-align: center; margin-bottom: 30px;">
            <h2 style="color: #4CAF50;">✓ NFC-e Gerada com Sucesso!</h2>
            <p style="font-size: 16px; color: #666;">
                Sua Nota Fiscal de Consumidor Eletrônica foi gerada e está disponível abaixo.
            </p>
        </div>

        <div style="border: 2px solid #4CAF50; border-radius: 10px; padding: 20px; margin-bottom: 20px; background-color: #f9f9f9;">
            <h3 style="color: #333; margin-top: 0;">Informações da Venda:</h3>
            <table style="width: 100%; font-size: 14px;">
                <tr>
                    <td width="30%"><strong>Número da Venda:</strong></td>
                    <td><?php echo $sale_id; ?></td>
                </tr>
                <tr>
                    <td><strong>Data/Hora:</strong></td>
                    <td><?php echo $transaction_time; ?></td>
                </tr>
                <tr>
                    <td><strong>Vendedor:</strong></td>
                    <td><?php echo $employee; ?></td>
                </tr>
                <?php if(isset($customer)): ?>
                <tr>
                    <td><strong>Cliente:</strong></td>
                    <td><?php echo $customer; ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td><strong>Total:</strong></td>
                    <td style="font-size: 16px; color: #4CAF50;"><strong><?php echo to_currency($total); ?></strong></td>
                </tr>
            </table>
        </div>

        <?php if(isset($nota_fiscal_path)): ?>
        <div style="text-align: center; margin: 30px 0;">
            <h3>Sua Nota Fiscal:</h3>
            <div style="margin: 20px 0;">
                <a href="<?php echo base_url($nota_fiscal_path); ?>" 
                   target="_blank" 
                   style="background-color: #4CAF50; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block; margin: 10px;">
                    📄 Ver NFC-e (PDF)
                </a>
                <a href="<?php echo base_url($nota_fiscal_path); ?>" 
                   download 
                   style="background-color: #008CBA; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-size: 16px; display: inline-block; margin: 10px;">
                    💾 Download NFC-e
                </a>
            </div>
        </div>

        <!-- Embed da nota fiscal -->
        <div style="text-align: center; margin-top: 30px;">
            <h4>Visualização da Nota Fiscal:</h4>
            <iframe src="<?php echo base_url($nota_fiscal_path); ?>" 
                    width="100%" 
                    height="600px" 
                    style="border: 1px solid #ddd; border-radius: 5px;">
            </iframe>
        </div>
        <?php endif; ?>

        <div style="text-align: center; margin-top: 40px;">
            <p style="color: #666; font-size: 12px;">
                Esta Nota Fiscal de Consumidor Eletrônica (NFC-e) possui validade jurídica<br/>
                e pode ser consultada no site da Receita Federal.
            </p>
        </div>

        <!-- Botões de ação -->
        <div style="text-align: center; margin-top: 30px; border-top: 1px solid #ddd; padding-top: 20px;">
            <a href="<?php echo site_url('sales'); ?>" 
               style="background-color: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 5px;">
                🏠 Nova Venda
            </a>
            <a href="<?php echo site_url('sales/listar_notas'); ?>" 
               style="background-color: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 5px;">
                📋 Ver Todas as Notas
            </a>
            <button onclick="window.print()" 
                    style="background-color: #9C27B0; color: white; padding: 10px 20px; border: none; border-radius: 4px; margin: 5px; cursor: pointer;">
                🖨️ Imprimir
            </button>
        </div>
    </div>
</div>

<script>
// Auto-abrir a nota fiscal em nova aba
<?php if(isset($nota_fiscal_path)): ?>
setTimeout(function() {
    if(confirm('Deseja abrir a Nota Fiscal em nova aba para visualização?')) {
        window.open('<?php echo base_url($nota_fiscal_path); ?>', '_blank');
    }
}, 1000);
<?php endif; ?>
</script>

<?php $this->load->view("partial/footer"); ?>
