<!doctype html>
<html>
    <head>
        <?php $this->load_view("template/head", $header); ?>
    </head>

    <body class="scrollbar_light">
    	<!-- Menu -->
    	<?php $this->load_view("navbar/navbar_no_logged"); ?>
    	
    	<!-- Content -->
		<main>
        	<?php $this->load_view($viewName, $viewData); ?>
        </main>
        
        <!-- Footer -->
        <footer>
        	<?php $this->load_view("template/footer"); ?>
    	</footer>
        
        <!-- Scripts -->
        <?php if (!empty($scripts)): ?>
        	<?php $this->load_view("template/scripts", array('scripts' => $scripts)); ?>
    	<?php else: ?>
    		<?php $this->load_view("template/scripts"); ?>
    	<?php endif; ?>
    </body>
</html>