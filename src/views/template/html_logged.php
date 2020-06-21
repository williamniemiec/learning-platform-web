<!doctype html>
<html>
    <head>
        <?php $this->loadView("template/head", $header); ?>
    </head>

    <body class="scrollbar_light">
    	<!-- Menu -->
    	<?php $this->loadView("navbar/navbar_logged", array('username' => $username)); ?>
    	
    	<!-- Content -->
		<main>
        	<?php $this->loadView($viewName, $viewData); ?>
        </main>
        
        <!-- Footer -->
        <footer>
        	<?php $this->loadView("template/footer"); ?>
    	</footer>
        
        <!-- Scripts -->
        <?php if (!empty($scripts)): ?>
        	<?php $this->loadView("template/scripts", array('scripts' => $scripts)); ?>
    	<?php else: ?>
    		<?php $this->loadView("template/scripts"); ?>
    	<?php endif; ?>
    </body>
</html>