<div class="container">
	<h1>Students manager</h1>
	<button class="btn btn-primary" onclick="show_addStudent(this)">Add student</button>
	<table class="table table-hover table-bordered table-sm">
		<thead class="thead-dark">
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
    					<button class="btn btn-warning" onclick="show_editStudent(this,<?php echo $student->getId(); ?>)">Edit</button>
    					<button class="btn btn-danger" onclick="deleteStudent(this,<?php echo $student->getId(); ?>)">Delete</button>
    				</td>
    			</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
	
	<!-- Modals -->
	<?php $this->loadView("modal_addStudent"); ?>
	<?php $this->loadView("modal_editStudent"); ?>
</div>