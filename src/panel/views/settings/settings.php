<div class="container">
	<div class="view_panel">
		<h1 class="view_header">Settings</h1>
		<div class="view_content">
			<div class="user_info">
        		<div class="user_photo">
        			<img class="img rounded-circle" src="<?php echo empty($profilePhoto) ? BASE_URL."resources/images/user.png" : BASE_URL."resources/images/profile_photos/".$profilePhoto; ?>" />
        			<button id="btn_photo_update" class="btn_theme" data-toggle="modal" data-target="#changeProfilePhoto">&#8634;</button>
    			</div>
        		
        		<div class="form-group">
            		<label for="name">Name</label>
            		<input id="name" type="text" name="name" placeholder="Name" class="form-control" value="<?php echo $adminName; ?>" readonly />
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
</div>

<!-- Modals -->
<?php $this->loadView('settings/modal_updateProfilePhoto'); ?>
<?php $this->loadView('settings/modal_changePassword'); ?>

<!-- Scripts -->
<script src="<?php echo BASE_URL; ?>resources/scripts/settings.js"></script>