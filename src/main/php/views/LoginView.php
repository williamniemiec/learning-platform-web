<div class="container">
	<!-- Shows error message if an error occurs -->
	<?php if ($error): ?>
		<?php $this->load_view("alerts/alert_error", array('msg' => $msg)); ?>
	<?php endif; ?>

	<div class="view_panel login">
    	<form method="POST">
    	   <!-- Email -->
        	<div class="form-group">
        		<label for="login_email">Email</label>
        		<input id="email" type="email" name="email" placeholder="Email" class="form-control" />
        	</div>
        	
        	<!-- Password -->
        	<div class="form-group">
        		<label for="login_pass">Password</label>
        		<input id="pass" type="password" name="password" placeholder="Password" class="form-control" />
        	</div>
        	
        	<!-- Login button -->
        	<div class="form-group">
        		<input type="submit" value="Login" class="form-control btn_theme btn_full" />
        	</div>
        </form>
    </div>
</div>