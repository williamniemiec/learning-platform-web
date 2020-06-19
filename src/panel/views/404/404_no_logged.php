<!doctype html>
<html>
    <head>
        <?php $this->loadView("template/head", $header); ?>
    </head>

    <body>
    	<!-- Menu -->
    	<?php $this->loadView("navbar/navbar_no_logged"); ?>
    	
    	<!-- Content -->
		<main>
        	<div class="container">
    			<h1>Error! Page not found</h1>
    		</div>
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