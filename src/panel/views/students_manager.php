<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Students manager</h1>
    	<div class="view_content">
        	<button class="btn_theme" onclick="show_addStudent(this)">Add student</button>
        	<table class="table table-hover table-sm">
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Courses</th>
        				<th>Total courses</th>
        				<th>Actions</th>
        			</tr>
        		</thead>
        		<tbody>
        			<?php foreach ($students as $student): ?>
            			<tr data-id_student="<?php echo $student->getId(); ?>">
            				<td class="student_name"><?php echo $student->getName(); ?></td>
            				<td class="student_courses"><?php echo implode(", ", $student->getCoursesName()); ?></td>
            				<td class="student_totalCourses"><?php echo count($student->getCoursesName()); ?></td>
            				<td class="actions">
            					<button class="btn_theme" onclick="show_editStudent(this,<?php echo $student->getId(); ?>)">Edit</button>
            					<button class="btn_theme btn_theme_danger" onclick="deleteStudent(this,<?php echo $student->getId(); ?>)">Delete</button>
            				</td>
            			</tr>
        			<?php endforeach; ?>
        		</tbody>
        	</table>
        	
        	<!-- Modals -->
        	<?php $this->loadView("modal_addStudent"); ?>
        	<?php $this->loadView("modal_editStudent"); ?>
    	</div>
	</div>
</div>