<?php
use models\enum\GenreEnum;
?>

<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">Settings</h1>
    	<div class="view_content">
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
        	
        	<div class="form-group">
        		<a class="btn_theme btn_full" href="<?php echo BASE_URL."settings/edit"; ?>">Change</a>
        	</div>
    	</div>
	</div>
	
	<!-- Modals -->
	<?php $this->loadView('settings/ChangePasswordModal'); ?>
</div>
