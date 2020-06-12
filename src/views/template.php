<!doctype html>
<html>
    <head>
        <title><?php echo $title; ?></title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/bootstrap.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/jquery.mCustomScrollbar.min.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>../assets/css/scrollbar_light.css' />
        <link rel='stylesheet' href='<?php echo BASE_URL; ?>assets/css/style.css' />
    </head>

    <body class="scrollbar_light">
    	<!-- Menu bar -->
    	<?php $this->loadView("menuBar/menu_logged", array('name' => $studentName)); ?>
    	
        <!-- Scripts -->
        <script src='<?php echo BASE_URL; ?>assets/js/jquery-3.4.1.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/bootstrap.bundle.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/jquery.mCustomScrollbar.concat.min.js'></script>
        <script src='<?php echo BASE_URL; ?>assets/js/scrollbar_light.js'></script>
        <script>var BASE_URL = "<?php echo BASE_URL; ?>"</script>
        <script src='<?php echo BASE_URL; ?>assets/js/script.js'></script>
		
		<main>
        	<?php $this->loadView($viewName, $viewData); ?>
        </main>
        <footer>&copy; Learning platform - All rights reserved</footer>
    </body>
</html>