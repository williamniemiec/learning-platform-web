<div id="editStudent" class="modal fade" role="alert">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Edit Student</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
            		<label for="edit_student_name">Name</label>
            		<input id="edit_student_name" type="text" placeholder="Name" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label>Genre</label><br />
            		
            		<input id="edit_student_male" type="radio" name="genre" value="0" value="Male" />
            		<label for="edit_student_male">Male</label>
            		
            		<input id="edit_student_female" type="radio" name="genre" value="1" value="Female" />
            		<label for="edit_student_female">Female</label>
            	</div>
            	
            	<div class="form-group">
            		<label for="edit_student_birthdate">Birthdate</label>
            		<input id="edit_student_birthdate" type="date" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label for="edit_student_email">Email</label>
            		<input id="edit_student_email" type="email" placeholder="Email" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label for="edit_student_pass">Password</label>
            		<input id="edit_student_pass" type="password" placeholder="Password" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label>Courses</label><br />
            		
            		<div id="edit_courses">
            		</div>
            	</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary btn-block" onclick="editStudent(this)">Save</button>
			</div>
		</div>
	</div>
</div>