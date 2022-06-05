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
						<td><?php echo $purchase->getBundle()->get_name(); ?></td>
						<td><?php echo $purchase->getDate()->format("m/d/Y H:m:s"); ?></td>
						<td><?php echo number_format($purchase->getPrice(), 2); ?></td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			<!-- Pagination -->
			<ul class="pagination pagination-sm justify-content-center">
				<li class="page-item <?php echo ($currentIndex - 1 <= 0) ? "disabled" : ""; ?>">
					<a href="<?php echo BASE_URL."purchases?index=".($currentIndex - 1); ?>" class="page-link">Before</a>
				</li>
				<?php for ($i=1; $i<=$totalPages; $i++): ?>
    				<li class="page-item <?php echo $i == $currentIndex ? "active" : "" ?>" data-index="<?php echo $i; ?>">
    					<a href="<?php echo BASE_URL."purchases?index=".$i; ?>" class="page-link"><?php echo $i; ?></a>
					</li>
				<?php endfor; ?>
				<li class="page-item <?php echo ($currentIndex + 1 > $totalPages) ? "disabled" : ""; ?>">
					<a href="<?php echo BASE_URL."purchases?index=".($currentIndex + 1); ?>" class="page-link">After</a>
				</li>
			</ul>
		</div>
	</div>
</div>