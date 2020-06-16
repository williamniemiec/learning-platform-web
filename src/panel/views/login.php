<!doctype html>
<html>
    <head>
        <?php $this->loadView("template/head", $header); ?>
    </head>

    <body>
    	<?php $this->loadView("navbar/navbar_no_logged"); ?>
    	
    	<div class="container">
    		<?php if ($error): ?>
    			<div class="alert alert-danger fade show" role="alert">
    				<button class="close" data-dismiss="alert" aria-label="close">
    					<span aria-hidden="true">&times;</span>
    				</button>
    				<h4 class="alert-heading">Error</h4>
    				<?php echo $msg; ?>
    			</div>
    		<?php endif; ?>
    		
    		<div class="view_panel login">
            	<form method="POST">
                	<div class="form-group">
                		<label for="email">Email</label>
                		<input id="email" type="email" name="email" placeholder="Email" class="form-control" />
                	</div>
                	
                	<div class="form-group">
                		<label for="pass">Password</label>
                		<input id="pass" type="password" name="password" placeholder="Password" class="form-control" />
                	</div>
                	
                	<div class="form-group">
                		<input type="submit" value="Login" class="form-control btn_theme btn_theme_full" />
                	</div>
                </form>
            </div>
    	</div>
    	
    	<div class="watermark">
    		Admin area
    	</div>
    	
        <!-- Scripts -->
        <?php $this->loadView("template/scripts"); ?>
    </body>
</html>