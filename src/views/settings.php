<div class="container">
	<div class="settings">
    	<h1>Settings</h1>
    	<div class="user_info">
    		<div class="user_photo">
    			<img class="img rounded-circle" src="<?php echo empty($profilePhoto) ? BASE_URL."assets/images/user.png" : BASE_URL."assets/images/profile_photos/".$profilePhoto; ?>" />
    			<button id="btn_photo_update" class="btn_theme" data-toggle="modal" data-target="#changeProfilePhoto">&#8634;</button>
			</div>
    		
    		<div class="form-group">
        		<label for="name">Name</label>
        		<input id="name" type="text" name="name" placeholder="Name" class="form-control" value="<?php echo $studentName; ?>" readonly />
        	</div>
        	
        	<div class="form-group">
        		<label>Genre</label><br />
        		
        		<input id="male" type="radio" name="genre" value="0" value="Male" <?php echo $genre == 0 ? "checked" : ""; ?> disabled />
        		<label for="male">Male</label>
        		
        		<input id="female" type="radio" name="genre" value="1" value="Female" <?php echo $genre == 1 ? "checked" : ""; ?> disabled />
        		<label for="female">Female</label>
        	</div>
        	
        	<div class="form-group">
        		<label for="birthdate">Birthdate</label>
        		<input id="birthdate" type="date" name="birthdate" class="form-control" value="<?php echo $birthdate; ?>" readonly />
        	</div>
        	
        	<div class="form-group">
        		<label for="email">Email</label>
        		<input id="email" type="email" name="email" placeholder="Email" class="form-control" value="<?php echo $email; ?>" readonly />
        	</div>
        	
        	<button class="btn_theme" data-toggle="modal" data-target="#changePassword">Change password</button>
        	
        	<div class="form-group">
        		<a class="btn_theme btn_full" href="<?php echo BASE_URL."settings/edit"; ?>">Change</a>
        	</div>
    	</div>
	</div>
</div>

<div id="changeProfilePhoto" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Profile photo</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="profile_photo">Profile photo</label>
					<input id="profile_photo" type="file" class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary btn-block" onclick="update_profilePhoto(this)">Save</button>
			</div>
		</div>
	</div>
</div>

<div id="changePassword" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Change password</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div id="changePassword_error" class="alert alert-danger" role="alert">
                	<button class="close" aria-label="close" onclick="changePassword_error()">
                		<span aria-hidden="true">&times;</span>
                	</button>
                	<h4 class="alert-heading">Error</h4>
                	Current password is incorrect
				</div>
				
				<div class="form-group">
					<label for="current_password">Current password</label>
					<input id="current_password" type="password" class="form-control" />
				</div>
				
				<div class="form-group">
					<label for="new_password">New password</label>
					<input id="new_password" type="password" class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full" onclick="update_password(this)">Save</button>
			</div>
		</div>
	</div>
</div>

<script src="<?php echo BASE_URL; ?>assets/js/settings.js"></script>