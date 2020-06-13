<div id="editadmin" class="modal fade scrollbar_light" role="alert">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Edit admin</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
            		<label for="edit_admin_name">Name</label>
            		<input id="edit_admin_name" type="text" placeholder="Name" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label>Genre</label><br />
            		
            		<input id="edit_admin_male" type="radio" name="genre" value="0" value="Male" />
            		<label for="edit_admin_male">Male</label>
            		
            		<input id="edit_admin_female" type="radio" name="genre" value="1" value="Female" />
            		<label for="edit_admin_female">Female</label>
            	</div>
            	
            	<div class="form-group">
            		<label for="edit_admin_birthdate">Birthdate</label>
            		<input id="edit_admin_birthdate" type="date" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label for="edit_admin_email">Email</label>
            		<input id="edit_admin_email" type="email" placeholder="Email" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label for="edit_admin_pass">Password</label>
            		<input id="edit_admin_pass" type="password" placeholder="Password" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label>Privileges</label><br />
            		
            		<div id="edit_privileges">
            		</div>
            	</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full btn_theme_light" onclick="editAdmin(this)">Save</button>
			</div>
		</div>
	</div>
</div>