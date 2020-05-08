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
        
        <div class="container">
        	<div class="error_404">
        		<h1>Error! Page not found</h1>
        	</div>
        </div>
    	
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/script.js'></script>
    </body>
</html>