<?php use models\enum\GenreEnum; ?>

<div class="container">
    <div class="view_panel">
    	<h1 class="view_header">Settings - Edit</h1>
    	<div class="view_content">
        	<form method="POST">
        		<!-- User name -->
        		<div class="form-group">
            		<label for="name">Name*</label>
            		<input id="name" type="text" name="name" placeholder="Name" class="form-control" value="<?php echo $user->get_name(); ?>" required />
            	</div>
            	
            	<!-- Genre -->
            	<div class="form-group">
            		<label>Genre*</label><br />
            		
            		<input id="male" type="radio" name="genre" value="0" <?php echo $user->getGenre()->get() == GenreEnum::MALE ? "checked" : ""; ?> />
            		<label for="male">Male</label>
            		
            		<input id="female" type="radio" name="genre" value="1" <?php echo $user->getGenre()->get() == GenreEnum::FEMALE ? "checked" : ""; ?> />
            		<label for="female">Female</label>
            	</div>
            	
            	<!-- Birthdate -->
            	<div class="form-group">
            		<label for="birthdate">Birthdate*</label>
            		<input id="birthdate" type="date" name="birthdate" class="form-control" value="<?php echo $user->get_birthdate()->format("Y-m-d"); ?>" required />
            	</div>
            	
            	<!-- Email -->
            	<div class="form-group" data-toggle="tooltip" data-placement="bottom" title="Email cannot be changed">
            		<label for="email">Email*</label>
            		<input id="email" class="form-control" value="<?php echo $user->getEmail(); ?>" readonly />
            	</div>
            	
            	<!-- Save button -->
            	<div class="form-group">
            		<input class="btn_theme btn_full" type="submit" value="Save" class="form-control" />
            	</div>
            </form>
        </div>
    </div>
</div>