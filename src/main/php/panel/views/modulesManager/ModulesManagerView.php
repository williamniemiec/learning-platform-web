<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Modules manager</h1>
    	<div class="view_content">
            <?php if (count($modules) == 0): ?>
            	<h2>There are no registered modules</h2>
            <?php else: ?>
            	<a class="btn_theme" href="<?php BASE_URL; ?>modules/new">New</a>
                <table class="table table-hover table-stripped text_centered">
                	<thead>
                		<tr>
                    		<th>Name</th>
                    		<th class="counter">Total classes</th>
                    		<th>Actions</th>
                		</tr>
                	</thead>
                	<tbody>
                		<?php foreach($modules as $module): ?>
                    		<tr>
                    			<td><a href="<?php echo BASE_URL."modules/edit/".$module->getId(); ?>"><?php echo $module->getName(); ?></a></td>
                    			<td><?php echo $module->getTotalClasses(); ?></td>
                    			<td class="actions">
                    				<a class="btn_theme" href="<?php echo BASE_URL."modules/edit/".$module->getId(); ?>">Edit</a>
                    				<a class="btn_theme btn_theme_danger" href="<?php echo BASE_URL."modules/delete/".$module->getId(); ?>">Delete</a>
                				</td>
                    		</tr>
                		<?php endforeach; ?>
                	</tbody>
                </table>
                <!-- Pagination -->
    			<ul class="pagination pagination-sm justify-content-center">
    				<li class="page-item <?php echo ($currentIndex - 1 <= 0) ? "disabled" : ""; ?>">
    					<a href="<?php echo BASE_URL."modules?index=".($currentIndex - 1); ?>" class="page-link">Before</a>
    				</li>
    				<?php for ($i=1; $i<=$totalPages; $i++): ?>
        				<li class="page-item <?php echo $i == $currentIndex ? "active" : "" ?>" data-index="<?php echo $i; ?>">
        					<a href="<?php echo BASE_URL."modules?index=".$i; ?>" class="page-link"><?php echo $i; ?></a>
    					</li>
    				<?php endfor; ?>
    				<li class="page-item <?php echo ($currentIndex + 1 > $totalPages) ? "disabled" : ""; ?>">
    					<a href="<?php echo BASE_URL."modules?index=".($currentIndex + 1); ?>" class="page-link">After</a>
    				</li>
    			</ul>
            <?php endif; ?>
        </div>
    </div>
</div>