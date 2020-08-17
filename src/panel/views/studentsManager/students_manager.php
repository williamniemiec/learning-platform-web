<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Students manager</h1>
    	<div class="view_content">
        	<table class="table table-hover table-sm">
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Bundles</th>
        				<th>Total bundles</th>
        				<th>Actions</th>
        			</tr>
        		</thead>
        		<tbody>
        			<?php foreach ($students as $student): ?>
            			<tr data-id_student="<?php echo $student->getId(); ?>">
            				<td class="student_name"><?php echo $student->getName(); ?></td>
            				<td class="student_bundles">
            					<?php
                					foreach($student->getBundles() as $bundle) {
                					    echo $bundle->getName()." | ";
                					}
            				    ?>	
            				</td>
            				<td class="student_totalBundles"><?php echo count($student->getBundles()); ?></td>
            				<td class="actions">
            					<button class="btn_theme" onclick="show_editStudent(<?php echo $student->getId(); ?>)">Edit</button>
            					<button class="btn_theme btn_theme_danger" onclick="deleteStudent(this,<?php echo $student->getId(); ?>)">Delete</button>
            				</td>
            			</tr>
        			<?php endforeach; ?>
        		</tbody>
        	</table>
        	
        	<!-- Modals -->
        	<?php $this->loadView("studentsManager/modal_editStudent"); ?>
    	</div>
	</div>
</div>