<!doctype html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/bootstrap.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/style.css' />
    </head>

    <body>
    	<!-- Menu bar -->
    	<?php $this->loadView("menuBar/menu_logged", array('name' => $studentName)); ?>
    	
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script>var BASE_URL = "<?php echo BASE_URL; ?>"</script>
		
		<main>
        	<?php $this->loadView($viewName, $viewData); ?>
        </main>
    </body>
</html>