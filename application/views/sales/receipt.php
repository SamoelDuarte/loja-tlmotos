<?php $this->load->view("partial/header"); ?>
<?php
if (isset($error_message))
{
	echo '<h1 style="text-align: center;">'.$error_message.'</h1>';
	exit;
}
?>
<div id="receipt_wrapper">
	<div id="receipt_header">
		<div id="company_name"><?php echo $this->config->item('company'); ?></div>
		<div id="company_address"><?php echo nl2br($this->config->item('address')); ?></div>
		<div id="company_phone"><?php echo $this->config->item('phone'); ?></div>
		<div id="sale_receipt"><?php echo $receipt_title; ?></div>
		<div id="sale_time"><?php echo $transaction_time ?></div>
	</div>
	<div id="receipt_general_info">
		<?php if(isset($customer))
		{
		?>
			<div id="customer"><?php echo $this->lang->line('customers_customer').": ".$customer; ?></div>
		<?php
		}
		?>
		<div id="sale_id"><?php echo $this->lang->line('sales_id').": ".$sale_id; ?></div>
		<div id="employee"><?php echo $this->lang->line('employees_employee').": ".$employee; ?></div>
	</div>

	<table id="receipt_items">
	<tr>
	<th style="width:25%;"><?php echo $this->lang->line('sales_item_number'); ?></th>
	<th style="width:25%;"><?php echo $this->lang->line('items_item'); ?></th>
	<th style="width:17%;"><?php echo $this->lang->line('common_price'); ?></th>
	<th style="width:16%;text-align:center;"><?php echo $this->lang->line('sales_quantity'); ?></th>
	<th style="width:16%;text-align:center;"><?php echo $this->lang->line('sales_discount'); ?></th>
	<th style="width:17%;text-align:right;"><?php echo $this->lang->line('sales_total'); ?></th>
	</tr>
	<?php
	foreach(array_reverse($cart, true) as $line=>$item)
	{
	?>
		<tr>
		<td><?php echo $item['item_number']; ?></td>
		<td><span class='long_name'><?php echo $item['name']; ?></span><span class='short_name'><?php echo character_limiter($item['name'],10); ?></span></td>
		<td><?php echo to_currency($item['price']); ?></td>
		<td style='text-align:center;'><?php echo $item['quantity']; ?></td>
		<td style='text-align:center;'><?php echo $item['discount']; ?></td>
		<td style='text-align:right;'><?php echo to_currency($item['price']*$item['quantity']-$item['price']*$item['quantity']*$item['discount']/100); ?></td>
		</tr>

	    <tr>
	    <td colspan="2" align="center"><?php echo $item['description']; ?></td>
		<td colspan="2" ><?php echo $item['serialnumber']; ?></td>
		<td colspan="2"><?php echo '&nbsp;'; ?></td>
	    </tr>

	<?php
	}
	?>
	<tr>
	<td colspan="4" style='text-align:right;border-top:2px solid #000000;'><?php echo $this->lang->line('sales_sub_total'); ?></td>
	<td colspan="2" style='text-align:right;border-top:2px solid #000000;'><?php echo to_currency($subtotal); ?></td>
	</tr>

	<?php foreach($taxes as $name=>$value) { ?>
		<tr>
			<td colspan="4" style='text-align:right;'><?php echo $name; ?>:</td>
			<td colspan="2" style='text-align:right;'><?php echo to_currency($value); ?></td>
		</tr>
	<?php }; ?>

	<tr>
	<td colspan="4" style='text-align:right;'><?php echo $this->lang->line('sales_total'); ?></td>
	<td colspan="2" style='text-align:right'><?php echo to_currency($total); ?></td>
	</tr>

    <tr><td colspan="6">&nbsp;</td></tr>

	<?php
		foreach($payments as $payment_id=>$payment)
	{ ?>
		<tr>
		<td colspan="2" style="text-align:right;"><?php echo $this->lang->line('sales_payment'); ?></td>
		<td colspan="2" style="text-align:right;"><?php $splitpayment=explode(':',$payment['payment_type']); echo $splitpayment[0]; ?> </td>
		<td colspan="2" style="text-align:right"><?php echo to_currency( $payment['payment_amount'] * -1 ); ?>  </td>
	    </tr>
	<?php
	}
	?>

    <tr><td colspan="6">&nbsp;</td></tr>

	<tr>
		<td colspan="4" style='text-align:right;'><?php echo $this->lang->line('sales_change_due'); ?></td>
		<td colspan="2" style='text-align:right'><?php echo  $amount_change; ?></td>
	</tr>

	</table>

	<?php if (isset($nota_fiscal_gerada) && $nota_fiscal_gerada === true): ?>
	<div style="border: 3px solid #4CAF50; background-color: #f0fdf4; padding: 15px; margin: 20px 0; text-align: center;">
		<h3 style="color: #4CAF50; margin: 0 0 10px 0;">✅ NOTA FISCAL GERADA</h3>
		<p style="margin: 5px 0; font-weight: bold;">NFC-e foi emitida com sucesso para esta venda!</p>
		<?php if (isset($nota_fiscal_path)): ?>
		<p style="margin: 10px 0;">
			<a href="<?php echo base_url($nota_fiscal_path); ?>" target="_blank" 
			   style="background-color: #4CAF50; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; font-weight: bold;">
				📄 Ver NFC-e
			</a>
			<a href="<?php echo base_url($nota_fiscal_path); ?>" download 
			   style="background-color: #008CBA; color: white; padding: 8px 15px; text-decoration: none; border-radius: 4px; margin-left: 10px; font-weight: bold;">
				💾 Download
			</a>
		</p>
		<?php endif; ?>
		<p style="font-size: 11px; color: #666; margin-top: 10px;">
			A Nota Fiscal será aberta automaticamente para impressão
		</p>
	</div>
	<?php endif; ?>

	<div id="sale_return_policy">
	<?php echo nl2br($this->config->item('return_policy')); ?>
	</div>
	<div id='barcode'>
	<?php echo "<img src='index.php/barcode?barcode=$sale_id&text=$sale_id&width=250&height=50' />"; ?>
	</div>
</div>
<?php $this->load->view("partial/footer"); ?>

<?php if ($this->Appconfig->get('print_after_sale'))
{
?>
<script type="text/javascript">
$(window).load(function()
{
	window.print();
});
</script>
<?php
}
?>

<?php 
// Se uma nota fiscal foi gerada, abrir automaticamente para impressão
if (isset($nota_fiscal_gerada) && $nota_fiscal_gerada === true && isset($nota_fiscal_path))
{
?>
<script type="text/javascript">
$(window).load(function()
{
	// Aguardar um pouco e abrir a nota fiscal
	setTimeout(function() {
		// Abrir nota fiscal em nova janela otimizada para impressão
		var notaWindow = window.open('<?php echo base_url($nota_fiscal_path); ?>', 'nota_fiscal', 'width=800,height=600,scrollbars=yes,resizable=yes');
		
		// Após carregar, focar na impressão
		notaWindow.onload = function() {
			notaWindow.focus();
			notaWindow.print();
		};
		
		// Se não conseguir abrir, mostrar link direto
		if (!notaWindow) {
			if (confirm('Pop-up bloqueado! Deseja abrir a Nota Fiscal em nova aba?')) {
				window.open('<?php echo base_url($nota_fiscal_path); ?>', '_blank');
			}
		}
	}, 1000);
});
</script>
<?php
}
?>