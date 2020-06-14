<!doctype html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/bootstrap.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>../assets/css/jquery.mCustomScrollbar.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/scrollbar_light.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL."assets/css/".THEME.".css"; ?>' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/global.css' />
        <?php foreach ($styles as $style): ?>
        	<link rel='stylesheet' href='<?php echo BASE_URL."assets/css/".$style.".css"; ?>' />	
        <?php endforeach; ?>
    </head>

    <body  class="scrollbar_light">
    	<nav class="navbar navbar-expand-lg">
    		<div class="container">
        		<a class="navbar-brand" href="<?php echo BASE_URL; ?>">Learning Platform</a>
        		<button class="navbar-toggler" data-toggle="collapse" data-target="#navbarMenu">
        			<span class="navbar-toggler-icon"></span>
        		</button>
        		<div id="navbarMenu" class="navbar-collapse collapse">
        			<div class="navbar-nav">
        				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>">Courses</a>
        				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>students">Students</a>
        				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>support">Support</a>
        				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>admins">Admins</a>
        			</div>
        			<div class="navbar-nav ml-auto">
        				<a href="<?php echo BASE_URL; ?>settings" class="nav-item nav-link active"><?php echo $adminName; ?></a>
        				<a href="<?php echo BASE_URL; ?>home/logout" class="nav-item nav-link">Logout</a>
        			</div>
        		</div>
    		</div>
    	</nav>
		<main>
        	<?php $this->loadView($viewName, $viewData); ?>
        </main>
        <footer>&copy; Learning platform - All rights reserved</footer>
        
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script src='<?php echo BASE_URL; ?>../assets/js/jquery.mCustomScrollbar.concat.min.js'></script>
        <script src='<?php echo BASE_URL; ?>../assets/js/scrollbar_light.js'></script>
        <script>var BASE_URL = "<?php echo BASE_URL; ?>"</script>
        <script src='<?php echo BASE_URL; ?>assets/js/script.js'></script>
    </body>
</html>