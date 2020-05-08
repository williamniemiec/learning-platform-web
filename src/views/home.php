<div class="container">
	<div class="home">
        <h1>My Courses</h1>
        <?php if ($totalCourses == 0): ?>
            <div class="error_msg">
    			<h2>There are not registered courses for this account :/</h2>
    		</div>
        <?php else: ?>
            <table class="table table-hover table-stripped table-bordered">
            	<thead class="thead-dark">
            		<tr>
            			<th></th>
                		<th>Name</th>
                		<th>Description</th>
                		<th>Progress</th>
            		</tr>
            	</thead>
            	<tbody>
            		<?php foreach($courses as $course): ?>
                		<tr>
                			<?php if (empty($course['logo'])): ?>
                				<td class="course_logo"><img class="img img-responsive" src="<?php echo BASE_URL."assets/images/noImage"; ?>" /></td>
                			<?php else: ?>
                				<td class="course_logo"><img class="img img-responsive" src="<?php echo BASE_URL."assets/images/logos/".$course['logo']; ?>" /></td>
                			<?php endif; ?>
                			<td><a href="<?php echo BASE_URL."courses/open/".$course['id']; ?>"><?php echo $course['name']; ?></a></td>
                			<td><?php echo $course['description']; ?></td>
                			<td><?php echo $course['totalWatchedClasses']; ?>/<?php echo $course['totalClasses'] ?> classes were watched</td>
                		</tr>
            		<?php endforeach; ?>
            	</tbody>
            </table>
        <?php endif; ?>
    </div>
</div>