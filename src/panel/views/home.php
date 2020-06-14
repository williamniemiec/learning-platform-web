<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Courses</h1>
    	<div class="view_content">
            <?php if (count($courses) == 0): ?>
            	<h2>There are not registered courses for this account :/</h2>
            <?php else: ?>
            	<a class="btn_theme" href="<?php BASE_URL; ?>courses/add">Add</a>
                <table class="table table-hover table-stripped text_centered">
                	<thead>
                		<tr>
                			<th></th>
                    		<th>Name</th>
                    		<th>Description</th>
                    		<th>Students</th>
                    		<th>Actions</th>
                		</tr>
                	</thead>
                	<tbody>
                		<?php foreach($courses as $course): ?>
                    		<tr>
                    			<?php if (empty($course['logo'])): ?>
                    				<td class="course_logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/images/noImage"; ?>" /></td>
                    			<?php else: ?>
                    				<td class="course_logo"><img class="img img-responsive" src="<?php echo BASE_URL."../assets/images/logos/".$course['logo']; ?>" /></td>
                    			<?php endif; ?>
                    			<td><a href="<?php echo BASE_URL."courses/edit/".$course['id']; ?>"><?php echo $course['name']; ?></a></td>
                    			<td><?php echo $course['description']; ?></td>
                    			<td><?php echo $course['total_students']; ?></td>
                    			<td class="actions">
                    				<a class="btn_theme" href="<?php echo BASE_URL."courses/edit/".$course['id']; ?>">Edit</a>
                    				<a class="btn_theme btn_theme_danger" href="<?php echo BASE_URL."courses/delete/".$course['id']; ?>">Delete</a>
                				</td>
                    		</tr>
                		<?php endforeach; ?>
                	</tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>