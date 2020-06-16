<!doctype html>
<html>
    <head>
        <?php $this->loadView("template/head", $header); ?>
    </head>

    <body class="scrollbar_light">
    	<!-- Menu -->
    	<?php $this->loadView("navbar/navbar_logged", array('adminName' => $adminName)); ?>
    	
    	<!-- View -->
		<main>
        	<?php $this->loadView($viewName, $viewData); ?>
        </main>
        
        <footer>&copy; Learning platform - All rights reserved</footer>
        
        <!-- Scripts -->
        <?php if (!empty($scripts)): ?>
        	<?php $this->loadView("template/scripts", array('scripts' => $scripts)); ?>
    	<?php else: ?>
    		<?php $this->loadView("template/scripts"); ?>
    	<?php endif; ?>
    </body>
</html>