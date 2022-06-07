<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Students manager</h1>
    	<div class="view_content">
    		<!-- Filters -->
			<div class="view_widget">
				<h3>Filter by course</h3>
				<form method="GET">
    				<div class="search-filter-options">
    					
        					<div class="form-group">
            					<select id="sel-course" name="filter-course" class="form-control">
            						<option value="0" <?php echo $selectedCourse == 0 ? "selected" : "" ?>>All</option>
            						<?php foreach ($courses as $course): ?>
            							<option value="<?php echo $course->getId(); ?>" <?php echo $selectedCourse == $course->getId() ? "selected" : ""; ?>>
            								<?php echo $course->getName(); ?>
        								</option>
            						<?php endforeach; ?>
            					</select>
        					</div>
    						<input type="submit" value="Filter" class="btn_theme" />
    				</div>
				</form>
			</div>
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
        	<?php $this->loadView("studentsManager/EditStudentModal"); ?>
    	</div>
	</div>
</div>