<div id="addStudent" class="modal fade scrollbar_light" role="alert">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Add Student</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
            		<label for="add_student_name">Name</label>
            		<input id="add_student_name" type="text" placeholder="Name" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label>Genre</label><br />
            		
            		<input id="add_student_male" type="radio" name="genre" value="0" checked value="Male" />
            		<label for="add_student_male">Male</label>
            		
            		<input id="add_student_female" type="radio" name="genre" value="1" value="Female" />
            		<label for="add_student_female">Female</label>
            	</div>
            	
            	<div class="form-group">
            		<label for="add_student_birthdate">Birthdate</label>
            		<input id="add_student_birthdate" type="date" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label for="add_student_email">Email</label>
            		<input id="add_student_email" type="email" placeholder="Email" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label for="add_student_pass">Password</label>
            		<input id="add_student_pass" type="password" placeholder="Password" class="form-control" />
            	</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full btn_theme_outline" onclick="addStudent(this)">Register</button>
			</div>
		</div>
	</div>
</div>