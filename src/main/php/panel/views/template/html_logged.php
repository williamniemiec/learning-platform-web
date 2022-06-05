<!doctype html>
<html>
    <head>
        <?php $this->load_view("template/head", $header); ?>
    </head>

    <body class="scrollbar_light">
    	<!-- Menu -->
    	<?php $this->load_view("navbar/navbar_logged", array('username' => $username, 'authorization' => $authorization)); ?>
    	
    	<!-- Content -->
		<main>
        	<?php $this->load_view($viewName, $viewData); ?>
        </main>
        
        <footer>&copy; Learning platform - All rights reserved</footer>
        
        <!-- Scripts -->
        <?php if (!empty($scripts)): ?>
        	<?php $this->load_view("template/scripts", array('scripts' => $scripts)); ?>
    	<?php else: ?>
    		<?php $this->load_view("template/scripts"); ?>
    	<?php endif; ?>
    </body>
</html>