<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Bundles manager</h1>
    	<div class="view_content">
            <?php if (count($bundles) == 0): ?>
            	<h2>There are no registered bundles</h2>
            <?php else: ?>
            	<!-- Filters -->
        		<div class="view_widget">
        			<h3>Filters</h3>
    				<form method="GET">
        				<div class="search-filter-options">
        					<div class="form-group">
        						<label for="rdo-total-courses">Total courses</label>
        						<input id="rdo-total-courses" type="radio" name="order-by" value="courses" <?php echo $selectedOrderBy == 'courses' ? "checked" : ""; ?> />
        					</div>
        					<div class="form-group">
        						<label for="rdo-total-sales">Total sales</label>
        						<input id="rdo-total-sales" type="radio" name="order-by" value="sales" <?php echo $selectedOrderBy == 'sales' ? "checked" : ""; ?> />
        					</div>
        					<div class="form-group">
        						<label for="rdo-price">Price</label>
        						<input id="rdo-price" type="radio" name="order-by" value="price" <?php echo $selectedOrderBy == 'price' ? "checked" : ""; ?> />
        					</div>
        					<div class="form-group form-group-select">
        						<label for="rdo-order-by-direction">Direction</label>
        						<select id="rdo-order-by-direction" name="order-by-direction" class="form-control">
        							<option value="asc" <?php echo $selectedOrderByDirection == 'asc' ? "selected" : ""; ?>>Ascending</option>
        							<option value="desc" <?php echo $selectedOrderByDirection == 'desc' ? "selected" : ""; ?>>Descending</option>
        						</select>
        					</div>
            			</div>
    					<div class="form-group">
    						<input type="submit" value="Filter" class="btn_theme btn_full" />
    					</div>
    				</form>
        		</div>
            
            	<a class="btn_theme" href="<?php BASE_URL; ?>bundles/new">New</a>
                <table class="table table-hover table-stripped text_centered">
                	<thead>
                		<tr>
                			<th></th>
                    		<th>Name</th>
                    		<th>Description</th>
                    		<th class="table-price">Price</th>
                    		<th>Students</th>
                    		<th>Actions</th>
                		</tr>
                	</thead>
                	<tbody>
                		<?php foreach($bundles as $bundle): ?>
                    		<tr>
                    			<?php if (empty($bundle->getLogo())): ?>
                    				<td class="manager-table-logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/img/default/noImage.png"; ?>" /></td>
                    			<?php else: ?>
                    				<td class="manager-table-logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/img/logos/bundles/".$bundle->getLogo(); ?>" /></td>
                    			<?php endif; ?>
                    			<td><a href="<?php echo BASE_URL."bundles/edit/".$bundle->getId(); ?>"><?php echo $bundle->getName(); ?></a></td>
                    			<td><?php echo $bundle->getDescription(); ?></td>
                    			<td>US$ <?php echo $bundle->getPrice(); ?></td>
                    			<td><?php echo $bundle->getTotalStudents(); ?></td>
                    			<td class="actions">
                    				<a class="btn_theme" href="<?php echo BASE_URL."bundles/edit/".$bundle->getId(); ?>">Edit</a>
                    				<a class="btn_theme btn_theme_danger" href="<?php echo BASE_URL."bundles/delete/".$bundle->getId(); ?>">Delete</a>
                				</td>
                    		</tr>
                		<?php endforeach; ?>
                	</tbody>
                </table>
                <!-- Pagination -->
    			<ul class="pagination pagination-sm justify-content-center">
    				<li class="page-item <?php echo ($currentIndex - 1 <= 0) ? "disabled" : ""; ?>">
    					<a href="<?php echo BASE_URL."bundles?index=".($currentIndex - 1); ?>" class="page-link">Before</a>
    				</li>
    				<?php for ($i=1; $i<=$totalPages; $i++): ?>
        				<li class="page-item <?php echo $i == $currentIndex ? "active" : "" ?>" data-index="<?php echo $i; ?>">
        					<a href="<?php echo BASE_URL."bundles?index=".$i; ?>" class="page-link"><?php echo $i; ?></a>
    					</li>
    				<?php endfor; ?>
    				<li class="page-item <?php echo ($currentIndex + 1 > $totalPages) ? "disabled" : ""; ?>">
    					<a href="<?php echo BASE_URL."bundles?index=".($currentIndex + 1); ?>" class="page-link">After</a>
    				</li>
    			</ul>
            <?php endif; ?>
        </div>
    </div>
</div>