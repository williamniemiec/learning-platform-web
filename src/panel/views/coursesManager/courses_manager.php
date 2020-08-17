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
                    			<?php if (empty($course->getLogo())): ?>
                    				<td class="manager-table-logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/img/default/noImage"; ?>" /></td>
                    			<?php else: ?>
                    				<td class="manager-table-logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/img/logos/courses/".$course->getLogo(); ?>" /></td>
                    			<?php endif; ?>
                    			<td><a href="<?php echo BASE_URL."courses/edit/".$course->getId(); ?>"><?php echo $course->getName(); ?></a></td>
                    			<td><?php echo $course->getDescription(); ?></td>
                    			<td><?php echo $course->getTotalStudents(); ?></td>
                    			<td><?php echo $course->getTotalClasses(); ?></td>
                    			<td><?php echo number_format($course->getTotalLength() / 60, 2); ?>h</td>
                    			<td class="actions">
                    				<a class="btn_theme" href="<?php echo BASE_URL."courses/edit/".$course->getId(); ?>">Edit</a>
                    				<a class="btn_theme btn_theme_danger" href="<?php echo BASE_URL."courses/delete/".$course->getId(); ?>">Delete</a>
                				</td>
                    		</tr>
                		<?php endforeach; ?>
                	</tbody>
                </table>
        	<?php endif; ?>
    	</div>
    </div>
</div>
