<div class="container">
	<div class="view_content theme_light">
        <?php if ($error): ?>
        	<div class="alert alert-danger fade show" role="alert">
        		<button class="close" data-dismiss="alert" aria-label="close">
        			<span aria-hidden="true">&times;</span>
        		</button>
        		<h4 class="alert-heading">Error</h4>
        		<?php echo $msg; ?>
        	</div>
        <?php endif; ?>
        
        <h1>Course editing</h1>
        <h2>Course info</h2>
        
        <form method="POST" enctype="multipart/form-data">
        	<div class="form-group">
        		<label for="name">Course name</label>
        		<input id="name" type="text" name="name" placeholder="Name" class="form-control" required value="<?php echo $course['name']; ?>" />
        	</div>
        	
        	<div class="form-group">
        		<label for="description">Description name</label>
        		<textarea id="description" name="description" class="form-control"><?php echo $course['description']; ?></textarea>
        	</div>
        	
        	<div class="form-group">
        		<label for="logo">Current logo</label><br />
        		<img class="img img-responsive img-thumbnail img_courseEdit" src="<?php echo BASE_URL."../assets/images/logos/".$course['logo']; ?>" /><br /><br />
        		<input id="logo" name="logo" type="file" accept=".jpeg,.png,.jpg" class="form-control" />
        	</div>
        	
        	<div class="form-group">
        		<input class="btn_theme" type="submit" value="Save" class="form-control" />
        	</div>
        </form>
        
        <hr />
        
        <div class="modules" data-id_course = "<?php echo $course['id']; ?>">
        	<h2>Modules</h2>
        	<button class="btn_theme" data-toggle="modal" data-target="#addModule">Add module</button>
        	
        	<?php foreach ($modules as $module): ?>
        		<div class="module" data-id_module="<?php echo $module['id']; ?>">
        			<div class="module_actions">
            			<button class="btn_theme" onclick="show_addClass(this,<?php echo $module['id']; ?>)">Add class</button>
            			<button class="btn_theme" onclick="show_editModule(this,<?php echo $module['id']; ?>)">Edit Module</button>
            			<button class="btn_theme btn_theme_danger" onclick="deleteModule(this,<?php echo $module['id']; ?>)">Delete module</button>
        			</div>
            		<h3 class="moduleName"><?php echo $module['name']; ?></h3>
            		<div class="classes">
            			<?php foreach ($module['classes'] as $class): ?>
                			<div class="class">
                				<?php if ($class['type'] == 'video'): ?>
                    				<h5 class="class_title" data-id_video="<?php echo $class['video']['id']; ?>"><?php echo $class['video']['title']; ?></h5>
                    				<div class="class_actions">
                    					<button class="btn_theme" onclick="show_editVideo(this,<?php echo $class['video']['id']; ?>)">Edit</button>
                    					<button class="btn_theme btn_theme_danger" onclick="deleteClass(this,<?php echo $class['id']; ?>)">Delete</button>
                					</div>
                				<?php else: ?>
                					<h5 class="class_title" data-id_quest="<?php echo $class['quest']['id']; ?>"><?php echo $class['quest']['question']; ?></h5>
                					<div class="class_actions">
                    					<button class="btn_theme" onclick="show_editQuest(this,<?php echo $class['quest']['id']; ?>)">Edit</button>
                    					<button class="btn_theme btn_theme_danger" onclick="deleteClass(this,<?php echo $class['id']; ?>)">Delete</button>
                					</div>
                				<?php endif; ?>
                			</div>
            			<?php endforeach; ?>
            		</div>
        		</div>
    		<?php endforeach; ?>
        </div>
        
        <!-- Modals -->
    	<?php $this->loadView("modal_addModule"); ?>
    	<?php $this->loadView("modal_editModule"); ?>
    	<?php $this->loadView("modal_addClass"); ?>
    	<?php $this->loadView("modal_editVideo"); ?>
    	<?php $this->loadView("modal_editQuest"); ?>
	</div>
</div>