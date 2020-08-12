<div class="container">
	<div class="view_panel">
		<h1 class="view_header">Purchases</h1>
		<div class="view_content">
			<table class="full-table">
				<thead>
					<tr>
    					<th>Bundle name</th>
    					<th>Purchase date</th>
    					<th>Price</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ($purchases as $purchase): ?>
					<tr>
						<td><?php echo $purchase->getBundle()->getName(); ?></td>
						<td><?php echo $purchase->getDate()->format("m/d/Y H:m:s"); ?></td>
						<td><?php echo number_format($purchase->getPrice(), 2); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>