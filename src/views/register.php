<!doctype html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/bootstrap.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/style.css' />
    </head>

    <body>
    	<!-- Loads menu bar -->
    	<?php $this->loadView("menuBar/menu_noLogged"); ?>
    	
    	<main>
        	<div class="container">
        	   <!-- Shows error message if an error occurs -->
        		<?php if ($error): ?>
        			<?php $this->loadView("alerts/error", array('msg' => $msg)); ?>
        		<?php endif; ?>
        		
        		<div class="registration">
        			<h1>Registration</h1>
        		
                	<form method="POST">
                		<div class="form-group">
                    		<label for="name">Name</label>
                    		<input id="name" type="text" name="name" placeholder="Name" class="form-control" />
                    	</div>
                    	
                    	<div class="form-group">
                    		<label>Genre</label><br />
                    		
                    		<input id="male" type="radio" name="genre" value="0" checked value="Male" />
                    		<label for="male">Male</label>
                    		
                    		<input id="female" type="radio" name="genre" value="1" value="Female" />
                    		<label for="female">Female</label>
                    	</div>
                    	
                    	<div class="form-group">
                    		<label for="birthdate">Birthdate</label>
                    		<input id="birthdate" type="date" name="birthdate" class="form-control" />
                    	</div>
                    	
                    	<div class="form-group">
                    		<label for="email">Email</label>
                    		<input id="email" type="email" name="email" placeholder="Email" class="form-control" />
                    	</div>
                    	
                    	<div class="form-group">
                    		<label for="pass">Password</label>
                    		<input id="pass" type="password" name="password" placeholder="Password" class="form-control" />
                    	</div>
                    	
                    	<div class="form-group">
                    		<input type="submit" value="Register" class="form-control" />
                    	</div>
                    </form>
            	</div>
        	</div>
    	</main>
    	
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/script.js'></script>
    </body>
</html>