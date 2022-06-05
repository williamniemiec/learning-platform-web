<div class="container">
	<div class="view_panel">
		<h1 class="view_header">New class</h1>
    	<div class="view_content">
    		<!-- Error message -->
            <?php if ($error): ?>
            	<div class="alert alert-danger fade show" role="alert">
            		<button class="close" data-dismiss="alert" aria-label="close">
            			<span aria-hidden="true">&times;</span>
            		</button>
            		<h4 class="alert-heading">Error</h4>
            		<?php echo $msg; ?>
            	</div>
            <?php endif; ?>
            
            <!-- Class type -->
            <div class="class-type-section">
                <h2>Class type</h2>
                <div class="class-type-rdo">
                    <div class="form-group">
                		<label for="class-type">Video</label>
            			<input id="class-type" type="radio" name="rdo-class-type" value="v" checked />
                	</div>
                	<div class="form-group">
                		<label for="class-type">Questionnaire</label>
            			<input id="class-type" type="radio" name="rdo-class-type" value="q" />
                	</div>
            	</div>
        	</div>
            
            <!-- Class information -->
            <form method="POST" enctype="multipart/form-data">
            	<div class="form-group">
            		<label for="id_module">Module (class order will be the highets class order in use + 1)</label>
            		<select name="id_module" class="form-control">
            			<?php foreach ($modules as $k => $module): ?>
            				<option value='<?php echo $module->getId(); ?>' <?php $k == 1 ? "selected" : "" ?>>
            					<?php echo $module->getName(); ?>
        					</option>
        				<?php endforeach; ?>
            		</select>
            	</div>
            	<div id="class-form-info"></div>
            	<div class="form-group">
            		<input type="submit" value="Create" class="form-control btn_theme btn_theme_full" />
            	</div>
            </form>
        </div>
    </div>
</div>