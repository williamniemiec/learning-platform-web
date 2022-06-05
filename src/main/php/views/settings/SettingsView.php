<?php use models\enum\GenreEnum; ?>

<div class="container">
	<?php if (!empty($msg)): ?>
    	<div class="alert alert-success fade show" role="alert">
    		<button class="close" data-dismiss="alert" aria-label="close">
    			<span aria-hidden="true">&times;</span>
    		</button>
    		<h4 class="alert-heading">Success!</h4>
    		<?php echo $msg; ?>
    	</div>
    <?php endif; ?>
	
	<div class="view_panel">
    	<h1 class="view_header">Settings</h1>
    	<div class="view_content">
    		<div class="user_photo">
    			<img	class="img rounded-circle" 
    					src="<?php echo empty($user->getPhoto()) ? 
    					    BASE_URL."assets/img/default/user.png" : 
    					    BASE_URL."assets/img/profile_photos/".$user->getPhoto(); ?>"
			    />
    			<button id="btn_photo_update" class="btn_theme" data-toggle="modal" data-target="#changeProfilePhoto">&#8634;</button>
			</div>
    		
    		<div class="form-group">
        		<label for="name">Name</label>
        		<input id="name" type="text" name="name" placeholder="Name" class="form-control" value="<?php echo $user->getName(); ?>" readonly />
        	</div>
        	
        	<div class="form-group">
        		<label>Genre</label><br />
        		
        		<input id="male" type="radio" name="genre" value="0" value="Male" <?php echo $user->getGenre()->get() == GenreEnum::MALE ? "checked" : ""; ?> disabled />
        		<label for="male">Male</label>
        		
        		<input id="female" type="radio" name="genre" value="1" value="Female" <?php echo $user->getGenre()->get() == GenreEnum::FEMALE ? "checked" : ""; ?> disabled />
        		<label for="female">Female</label>
        	</div>
        	
        	<div class="form-group">
        		<label for="birthdate">Birthdate</label>
        		<input id="birthdate" type="date" name="birthdate" class="form-control" value="<?php echo $user->getBirthdate()->format("Y-m-d"); ?>" readonly />
        	</div>
        	
        	<div class="form-group">
        		<label for="email">Email</label>
        		<input id="email" type="email" name="email" placeholder="Email" class="form-control" value="<?php echo $user->getEmail(); ?>" readonly />
        	</div>
        	
        	<button class="btn_theme" data-toggle="modal" data-target="#changePassword">Change password</button>
        	<a href="<?php echo BASE_URL."settings/clear"; ?>" class="btn_theme btn_theme_danger">Clear history</a>
        	<a href="<?php echo BASE_URL."settings/delete"; ?>" class="btn_theme btn_theme_danger">Delete account</a>
        	
        	<div class="form-group">
        		<a class="btn_theme btn_full" href="<?php echo BASE_URL."settings/edit"; ?>">Change</a>
        	</div>
    	</div>
	</div>
</div>

<!-- Modals -->
<?php $this->loadView('settings/UpdateProfilePhotoModal'); ?>
<?php $this->loadView('settings/ChangePasswordModal'); ?>