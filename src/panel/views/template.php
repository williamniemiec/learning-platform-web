<!doctype html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/bootstrap.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/style.css' />
    </head>

    <body>
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
        			</div>
        			<div class="navbar-nav ml-auto">
        				<a href="<?php echo BASE_URL; ?>settings" class="nav-item nav-link active"><?php echo $adminName; ?></a>
        				<a href="<?php echo BASE_URL; ?>home/logout" class="nav-item nav-link">Logout</a>
        			</div>
        		</div>
    		</div>
    	</nav>
    	
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script>var BASE_URL = "<?php echo BASE_URL; ?>"</script>
        <script src='<?php echo BASE_URL; ?>assets/js/script.js'></script>
		
		<main>
        	<?php $this->loadView($viewName, $viewData); ?>
        </main>
    </body>
</html>