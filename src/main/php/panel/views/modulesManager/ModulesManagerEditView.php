<?php use models\Video; ?>

<div class="container">
	<!-- Module information -->
	<div class="view_panel">
		<h1 class="view_header">Module edit</h1>
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
           
            <h2>Module info</h2>
            
            <form method="POST" enctype="multipart/form-data">
            	<div class="form-group">
            		<label for="name">Module name</label>
            		<input id="name" type="text" name="name" placeholder="Name" class="form-control" required value="<?php echo $module->get_name(); ?>" />
            	</div>
            	<div class="form-group">
            		<input class="btn_theme btn_full" type="submit" value="Save" class="form-control" />
            	</div>
            </form>
        </div>
    </div>
    
    <!-- Module classes -->
    <div class="view_panel">
		<h1 class="view_header">Module classes</h1>
		<div class="view_content">
			<table class="table table-hover table-stripped text_centered">
            	<thead>
            		<tr>
                		<th>Name</th>
                		<th>Type</th>
                		<th>Order</th>
            		</tr>
            	</thead>
            	<tbody>
            		<?php foreach($classes as $class): ?>
                		<tr>
                			<td><?php echo $class instanceof Video ? $class->getTitle() : $class->getQuestion(); ?></td>
                			<td><?php echo $class instanceof Video ? "Video" : "Questionnaire"; ?></td>
                			<td><?php echo $class->get_class_order(); ?></td>
                		</tr>
            		<?php endforeach; ?>
            	</tbody>
            </table>
            <button class="btn_theme" onclick="show_updateModules(<?php echo $module->get_id(); ?>)">Include classes</button>
		</div>
	</div>
	
	<!-- Modals -->
	<?php $this->load_view("modulesManager/IncludeClassesModal", array('id_module' => $module->get_id())); ?>
</div>