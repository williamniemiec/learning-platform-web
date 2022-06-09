<!doctype html>
<html>
    <head>
        <?php $this->loadView("template/head", $header); ?>
    </head>

    <body class="scrollbar_light">
        <!-- Menu -->
        <?php $this->loadView("navbar/navbar_logged", array('username' => $username, 'notifications' => $notifications)); ?>
        
        <!-- Content -->
        <main>
            <?php $this->loadView($viewName, $viewData); ?>
        </main>
        
        <!-- Footer -->
        <footer>
            <?php $this->loadView("template/footer"); ?>
        </footer>
        
        <!-- Scripts -->
        <?php 
            if (!empty($scripts)) {
                if (!empty($scriptsModule)) {
                    $this->loadView("template/scripts", array('scripts' => $scripts, 'scriptsModule' => $scriptsModule));
                }
                else {
                    $this->loadView("template/scripts", array('scripts' => $scripts));
                }
            }
            else {
                if (!empty($scriptsModule)) {
                    $this->loadView("template/scripts", array('scriptsModule' => $scriptsModule));
                }
                else {
                    $this->loadView("template/scripts");
                }
            }
        ?>
    </body>
</html>