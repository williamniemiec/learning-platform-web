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
    
    <h1>Course - edit</h1>
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
    		<input class="btn btn-success" type="submit" value="Save" class="form-control" />
    	</div>
    </form>
    
    <hr />
    
    <div class="modules" data-idCourse = "<?php echo $course['id']; ?>">
    	<h2>Modules</h2>
    	<button class="btn btn-primary" data-toggle="modal" data-target="#addModule">Add module</button>
    	
    	<?php foreach ($modules as $module): ?>
    		<div class="module" data-moduleId="<?php echo $module['id']; ?>">
    			<button class="btn btn-primary" onclick="show_addClass(this,<?php echo $module['id']; ?>)">Add class</button>
    			<button class="btn btn-warning" onclick="show_editModule(this,<?php echo $module['id']; ?>)">Edit Module</button>
    			<button class="btn btn-danger" onclick="deleteModule(this,<?php echo $module['id']; ?>)">Delete module</button>
        		<h3 class="moduleName"><?php echo $module['name']; ?></h3>
        		<div class="classes">
        			<?php foreach ($module['classes'] as $class): ?>
            			<div class="class">
            				<?php if ($class['type'] == 'video'): ?>
                				<h5><?php echo $class['video']['title']; ?></h5>
            				<?php else: ?>
            					<h5><?php echo $class['quest']['question']; ?></h5>
            				<?php endif; ?>
            				<button class="btn btn-danger" onclick="deleteClass(this,<?php echo $class['id']; ?>)">Delete</button>
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
</div>