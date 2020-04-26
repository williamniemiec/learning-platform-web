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
    		<h1>Error! Page not found</h1>
    	</div>
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/script.js'></script>
    </body>
</html>