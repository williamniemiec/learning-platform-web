<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Courses</h1>
    	<div class="view_content">
            <?php if (count($courses) == 0): ?>
            	<h2>There are no registered courses</h2>
            <?php else: ?>
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
                	<tbody>
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
