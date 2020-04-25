<!doctype html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/bootstrap.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/style.css' />
    </head>

    <body>
    	<nav class="navbar navbar-expand-lg  bg-dark">
    		<div class="container">
        		<a class="navbar-brand" href="">Learning Platform</a>
        		<button class="navbar-toggler" data-toggle="collapse" data-target="#navbarMenu">
        			<span class="navbar-toggler-icon"></span>
        		</button>
        		<div id="navbarMenu" class="navbar-collapse collapse">
        			<div class="navbar-nav">
        				<a href="<?php echo BASE_URL; ?>login" class="nav-item nav-link active">Login</a>
        				<a href="<?php echo BASE_URL; ?>login/register" class="nav-item nav-link">Register</a>
        			</div>
        		</div>
    		</div>
    	</nav>
    	
    	<div class="container">
    		<?php if ($error): ?>
    			<div class="alert alert-danger fade show" role="alert">
    				<button class="close" data-dismiss="alert" aria-label="close">
    					<span aria-hidden="true">&times;</span>
    				</button>
    				<h4 class="alert-heading">Error</h4>
    				<?php echo $msg; ?>
    			</div>
    		<?php endif; ?>
    		
        	<form method="POST">
            	<div class="form-group">
            		<label for="email">Email</label>
            		<input id="email" type="email" name="email" placeholder="Email" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<label for="pass">Password</label>
            		<input id="pass" type="password" name="password" placeholder="Password" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<input type="submit" value="Login" class="form-control" />
            	</div>
            </form>
    	</div>
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/script.js'></script>
    </body>
</html>