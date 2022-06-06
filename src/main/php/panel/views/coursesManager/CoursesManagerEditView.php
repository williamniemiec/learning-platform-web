<div class="container">
	<!-- Bundle information -->
	<div class="view_panel">
		<h1 class="view_header">Course editing</h1>
		<div class="view_content">
            <?php if ($error): ?>
            	<div class="alert alert-danger fade show" role="alert">
            		<button class="close" data-dismiss="alert" aria-label="close">
            			<span aria-hidden="true">&times;</span>
            		</button>
            		<h4 class="alert-heading">Error</h4>
            		<?php echo $msg; ?>
            	</div>
            <?php endif; ?>
           
            <h2>Course info</h2>
            
            <form method="POST" enctype="multipart/form-data">
            	<div class="form-group">
            		<label for="name">Course name</label>
            		<input id="name" type="text" name="name" placeholder="Name" class="form-control" required value="<?php echo $course->getName(); ?>" />
            	</div>
            	
            	<div class="form-group">
            		<label for="description">Description name</label>
            		<textarea id="description" name="description" class="form-control"><?php echo $course->getDescription(); ?></textarea>
            	</div>
            	
            	<div class="form-group">
            		<label for="logo">Current logo</label><br />
            		<img	class="img img-responsive img-thumbnail manager-logo" 
            				src="<?php echo empty($course->getLogo()) ? 
            				    BASE_URL."../assets/img/default/noImage.png" : 
            				    BASE_URL."../assets/img/logos/courses/".$course->getLogo(); ?>" 
    				/><br /><br />
            		<input id="logo" name="logo" type="file" accept=".jpeg,.png,.jpg" class="form-control" />
            	</div>
            	
            	<div class="form-group">
            		<input class="btn_theme" type="submit" value="Save" class="form-control" />
            	</div>
            </form>
        </div>
    </div>
    
    <!-- Bundle courses -->
    <div class="view_panel">
		<h1 class="view_header">Course modules</h1>
		<div class="view_content">
			<table class="table table-hover table-stripped text_centered">
            	<thead>
            		<tr>
                		<th>Name</th>
                		<th>Total classes</th>
            		</tr>
            	</thead>
            	<tbody>
            		<?php foreach($modules as $module): ?>
                		<tr>
                			<td><?php echo $module->getName(); ?></td>
                			<td><?php echo $module->getTotalClasses(); ?></td>
                		</tr>
            		<?php endforeach; ?>
            	</tbody>
            </table>
            <button class="btn_theme" onclick="show_updateCourses(<?php echo $course->getId(); ?>)">Include modules</button>
		</div>
	</div>
	
	<!-- Modals -->
	<?php $this->loadView("coursesManager/IncludeModulesModal", array('id_course' => $course->getId())); ?>
</div>