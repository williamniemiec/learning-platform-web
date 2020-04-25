<div class="container">
    <h1>Courses</h1>
    <?php if ($totalCourses == 0): ?>
    	<h2>There are not registered courses for this account :/</h2>
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
            			<td class="course_logo"><img class="img img-responsive" src="<?php echo BASE_URL."assets/images/logos/".$course['logo']; ?>" /></td>
            			<td><a href="<?php echo BASE_URL."courses/open/".$course['id']; ?>"><?php echo $course['name']; ?></a></td>
            			<td><?php echo $course['description']; ?></td>
            			<td>/<?php echo $course['totalClasses'] ?> classes were watched</td>
            		</tr>
        		<?php endforeach; ?>
        	</tbody>
        </table>
    <?php endif; ?>
</div>