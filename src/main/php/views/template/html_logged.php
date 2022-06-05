<!doctype html>
<html>
    <head>
        <?php $this->load_view("template/head", $header); ?>
    </head>

    <body class="scrollbar_light">
    	<!-- Menu -->
    	<?php $this->load_view("navbar/navbar_logged", array('username' => $username, 'notifications' => $notifications)); ?>
    	
    	<!-- Content -->
		<main>
        	<?php $this->load_view($viewName, $viewData); ?>
        </main>
        
        <!-- Footer -->
        <footer>
        	<?php $this->load_view("template/footer"); ?>
    	</footer>
        
        <!-- Scripts -->
        <?php 
            if (!empty($scripts)) {
                if (!empty($scriptsModule))
                    $this->load_view("template/scripts", array('scripts' => $scripts, 'scriptsModule' => $scriptsModule));
                else
                    $this->load_view("template/scripts", array('scripts' => $scripts));
            }
            else {
                if (!empty($scriptsModule))
                    $this->load_view("template/scripts", array('scriptsModule' => $scriptsModule));
                else
                    $this->load_view("template/scripts");
            }
        ?>
    </body>
</html>