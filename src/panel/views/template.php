<!doctype html>
<html>
    <head>
        <?php $this->loadView("template/head", $header); ?>
    </head>

    <body class="scrollbar_light">
    	<?php $this->loadView("navbar/navbar_logged", array('adminName' => $adminName)); ?>
    	
		<main>
        	<?php $this->loadView($viewName, $viewData); ?>
        </main>
        
        <footer>&copy; Learning platform - All rights reserved</footer>
        
        <!-- Scripts -->
        <?php $this->loadViewInTemplate("template/scripts", $scripts); ?>
    </body>
</html>