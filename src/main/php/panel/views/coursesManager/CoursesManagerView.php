<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Courses manager</h1>
    	<div class="view_content">
            <?php if (count($courses) == 0): ?>
            	<h2>There are no registered courses</h2>
            <?php else: ?>
            	<!-- Filters -->
            		<div class="search-bar">
            			<input type="text" class="search-bar-big" placeholder="Search course" />
            			<button class="search-bar-btn" onClick="search(this)">&#128270;</button>
            		</div>
        		<div class="view_widget">
        			<!-- Filters -->
        			<div class="search-filters">
        				<h3>Filters</h3>
        				<div class="search-filter-options">
        					<div class="form-group">
            					<input id="rdo-price" type="radio" name="filter" value="name" checked />
            					<label for="rdo-price">Name</label>
        					</div>
        					<div class="form-group">
            					<input id="rdo-course" type="radio" name="filter" value="sales" />
            					<label for="rdo-course">Total students</label>
        					</div>
        					<div class="form-group">
            					<select id="order" class="form-control">
            						<option value="asc" selected>Ascending</option>
            						<option value="desc">Descending</option>
            					</select>
        					</div>
        				</div>
        			</div>
        		</div>
            
            	<a class="btn_theme" href="<?php BASE_URL; ?>courses/new">New</a>
                <table class="table table-hover table-stripped text_centered">
                	<thead>
                		<tr>
                			<th></th>
                    		<th>Name</th>
                    		<th>Description</th>
                    		<th>Students</th>
                    		<th>Total classes</th>
                    		<th>Total length</th>
                    		<th>Actions</th>
                		</tr>
                	</thead>
                	<tbody id="courses">
                		<?php foreach($courses as $course): ?>
                    		<tr>
                    			<?php if (empty($course->get_logo())): ?>
                    				<td class="manager-table-logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/img/default/noImage.png"; ?>" /></td>
                    			<?php else: ?>
                    				<td class="manager-table-logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/img/logos/courses/".$course->get_logo(); ?>" /></td>
                    			<?php endif; ?>
                    			<td><a href="<?php echo BASE_URL."courses/edit/".$course->get_id(); ?>"><?php echo $course->get_name(); ?></a></td>
                    			<td><?php echo $course->get_description(); ?></td>
                    			<td><?php echo $course->getTotalStudents(); ?></td>
                    			<td><?php echo $course->get_total_classes(); ?></td>
                    			<td><?php echo number_format($course->get_total_length() / 60, 2); ?>h</td>
                    			<td class="actions">
                    				<a class="btn_theme" href="<?php echo BASE_URL."courses/edit/".$course->get_id(); ?>">Edit</a>
                    				<a class="btn_theme btn_theme_danger" href="<?php echo BASE_URL."courses/delete/".$course->get_id(); ?>">Delete</a>
                				</td>
                    		</tr>
                		<?php endforeach; ?>
                	</tbody>
                </table>
                <!-- Pagination -->
    			<ul class="pagination pagination-sm justify-content-center">
    				<li class="page-item <?php echo ($currentIndex - 1 <= 0) ? "disabled" : ""; ?>">
    					<a href="<?php echo BASE_URL."courses?index=".($currentIndex - 1); ?>" class="page-link">Before</a>
    				</li>
    				<?php for ($i=1; $i<=$totalPages; $i++): ?>
        				<li class="page-item <?php echo $i == $currentIndex ? "active" : "" ?>" data-index="<?php echo $i; ?>">
        					<a href="<?php echo BASE_URL."courses?index=".$i; ?>" class="page-link"><?php echo $i; ?></a>
    					</li>
    				<?php endfor; ?>
    				<li class="page-item <?php echo ($currentIndex + 1 > $totalPages) ? "disabled" : ""; ?>">
    					<a href="<?php echo BASE_URL."courses?index=".($currentIndex + 1); ?>" class="page-link">After</a>
    				</li>
    			</ul>
        	<?php endif; ?>
    	</div>
    </div>
</div>
