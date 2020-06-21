<div class="container">
   <!-- Shows error message if an error occurs -->
	<?php if ($error): ?>
		<?php $this->loadView("alerts/error", array('msg' => $msg)); ?>
	<?php endif; ?>
	
	<div class="view_panel">
		<h1 class="view_header">Registration</h1>
		<div class="view_content">
        	<form method="POST">
        		<!-- Name -->
        		<div class="form-group">
            		<label for="register_name">Name</label>
            		<input id="register_name" type="text" name="name" placeholder="Name" class="form-control" />
            	</div>
            	
            	<!-- Genre -->
            	<div class="form-group">
            		<label>Genre</label><br />
            		
            		<input id="register_male" type="radio" name="genre" value="0" checked value="Male" />
            		<label for="register_male">Male</label>
            		
            		<input id="register_female" type="radio" name="genre" value="1" value="Female" />
            		<label for="register_female">Female</label>
            	</div>
            	
            	<!-- Birthdate -->
            	<div class="form-group">
            		<label for="register_birthdate">Birthdate</label>
            		<input id="register_birthdate" type="date" name="birthdate" class="form-control" />
            	</div>
            	
            	<!-- Email -->
            	<div class="form-group">
            		<label for="register_email">Email</label>
            		<input id="register_email" type="email" name="email" placeholder="Email" class="form-control" />
            	</div>
            	
            	<!-- Password -->
            	<div class="form-group">
            		<label for="register_pass">Password</label>
            		<input id="register_pass" type="password" name="password" placeholder="Password" class="form-control" />
            	</div>
            	
            	<!-- Register button -->
            	<div class="form-group">
            		<input type="submit" value="Register" class="form-control btn_theme btn_full" />
            	</div>
            </form>
        </div>
	</div>
</div>