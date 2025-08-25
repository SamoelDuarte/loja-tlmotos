<?php $this->load->view("partial/header"); ?>

<div id="page_title" style="margin-bottom: 8px;">
	Notas Fiscais Geradas
</div>

<div id="content_area_wrapper">
	<div id="content_area">
		
		<!-- Debug Info (apenas em desenvolvimento) -->
		<?php if (isset($debug_info) && !empty($debug_info)): ?>
		<div style="background-color: #f0f8ff; border: 1px solid #ccc; padding: 10px; margin-bottom: 20px; font-size: 12px;">
			<h4>Informações de Debug:</h4>
			<ul>
				<?php foreach ($debug_info as $info): ?>
				<li><?php echo $info; ?></li>
				<?php endforeach; ?>
			</ul>
		</div>
		<?php endif; ?>

		<?php if (empty($notas)): ?>
			<div style="text-align: center; padding: 50px;">
				<h3>Nenhuma nota fiscal encontrada</h3>
				<p>As notas fiscais geradas aparecerão aqui.</p>
				<p><strong>Dica:</strong> Faça uma venda e escolha "Emitir Nota Fiscal" para gerar sua primeira NFC-e.</p>
			</div>
		<?php else: ?>
			<div style="margin-bottom: 20px; padding: 10px; background-color: #e8f5e8; border: 1px solid #4CAF50; border-radius: 5px;">
				<h4 style="color: #4CAF50; margin: 0;">✓ <?php echo count($notas); ?> nota(s) fiscal(is) encontrada(s)</h4>
			</div>
			
			<table id="sortable_table" class="tablesorter" style="width: 100%; border-collapse: collapse;">
				<thead>
					<tr style="background-color: #f0f0f0;">
						<th style="border: 1px solid #ddd; padding: 10px;">Nome do Arquivo</th>
						<th style="border: 1px solid #ddd; padding: 10px;">Tipo</th>
						<th style="border: 1px solid #ddd; padding: 10px;">Data de Criação</th>
						<th style="border: 1px solid #ddd; padding: 10px;">Tamanho</th>
						<th style="border: 1px solid #ddd; padding: 10px;">Ações</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($notas as $nota): ?>
					<tr>
						<td style="border: 1px solid #ddd; padding: 8px;"><?php echo $nota['nome']; ?></td>
						<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
							<span style="background-color: #007cba; color: white; padding: 2px 8px; border-radius: 3px; font-size: 11px;">
								<?php echo $nota['tipo']; ?>
							</span>
						</td>
						<td style="border: 1px solid #ddd; padding: 8px;"><?php echo $nota['data_criacao']; ?></td>
						<td style="border: 1px solid #ddd; padding: 8px; text-align: right;"><?php echo $nota['tamanho']; ?></td>
						<td style="border: 1px solid #ddd; padding: 8px; text-align: center;">
							<a href="<?php echo $nota['url']; ?>" target="_blank" 
							   style="background-color: #4CAF50; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 11px; margin: 2px;">
								👁️ Ver
							</a>
							<a href="<?php echo $nota['url']; ?>" download 
							   style="background-color: #008CBA; color: white; padding: 5px 10px; text-decoration: none; border-radius: 3px; font-size: 11px; margin: 2px;">
								💾 Download
							</a>
							<button onclick="window.open('<?php echo $nota['url']; ?>', 'print', 'width=800,height=600').print();" 
							        style="background-color: #9C27B0; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer; font-size: 11px; margin: 2px;">
								🖨️ Imprimir
							</button>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		<?php endif; ?>
		
		<div style="margin-top: 20px; text-align: center; border-top: 1px solid #ddd; padding-top: 20px;">
			<a href="<?php echo site_url('sales'); ?>" 
			   style="background-color: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 5px;">
				🏠 Voltar para Vendas
			</a>
			<a href="<?php echo site_url('teste_nf'); ?>" 
			   style="background-color: #FF9800; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px; margin: 5px;">
				🧪 Teste NFC-e
			</a>
			<button onclick="location.reload();" 
			        style="background-color: #607D8B; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; margin: 5px;">
				🔄 Atualizar Lista
			</button>
		</div>
	</div>
</div>

<script type="text/javascript">
$(document).ready(function() {
	$("#sortable_table").tablesorter({
		headers: {
			4: { sorter: false } // Desabilita ordenação na coluna "Ações"
		}
	});
});
</script>

<?php $this->load->view("partial/footer"); ?>
