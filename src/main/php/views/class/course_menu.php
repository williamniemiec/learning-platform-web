<?php use models\Video; ?>

<div class="course_menu scrollbar_light">
	<div class="course_banner_area">
    	<img	class="img imr-responsive course_banner" 
    			src="<?php echo empty($logo) ? 
    			    BASE_URL."src/main/webapp/images/default/noImage" : 
		            BASE_URL."src/main/webapp/images/logos/courses/".$logo; ?>" 
		/>
    </div>
    
    <?php foreach ($modules as $module): ?>
    	<div class="module">
    		<h4><?php echo $module->get_name(); ?></h4>
    		<div class="module_classes">
    			<?php $id = 0; ?>
    			<?php foreach ($module->getClasses() as $class): ?>
    				<div class="module_class" data-class="<?php echo $class->get_module_id()."/".$class->get_class_order(); ?>">
    					<?php if ($class instanceof Video): ?>
    						<div class="module_title">
        						<a href="<?php echo BASE_URL."courses/open/".$id_course."/".$class->getModuleId()."/".$class->getClassOrder(); ?>">
        							<?php echo ++$id.". ".$class->getTitle(); ?>
        						</a>
    						</div>
    						<?php if (!empty($watched_classes[$class->getModuleId()][$class->getClassOrder()])): ?>
    							<small class="class_watched">Watched</small>
    						<?php endif; ?>
    					<?php else: ?>
    						<a href="<?php echo BASE_URL."courses/open/".$id_course."/".$class->get_module_id()."/".$class->get_class_order(); ?>">
    							<?php echo ++$id.". Questionnaire"; ?>
    						</a>
    						<?php if (!empty($watched_classes[$class->get_module_id()][$class->get_class_order()])): ?>
    							<small class="class_watched">Watched</small>
    						<?php endif; ?>
    					<?php endif; ?>
    				</div>
    			<?php endforeach; ?>
    		</div>
    	</div>
    <?php endforeach; ?>
</div>