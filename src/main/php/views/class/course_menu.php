<?php use models\Video; ?>

<div class="course_menu scrollbar_light">
	<div class="course_banner_area">
    	<img	class="img imr-responsive course_banner" 
    			src="<?php echo empty($logo) ? 
    			    BASE_URL."assets/img/default/noImage" : 
		            BASE_URL."assets/img/logos/courses/".$logo; ?>" 
		/>
    </div>
    
    <?php foreach ($modules as $module): ?>
    	<div class="module">
    		<h4><?php echo $module->getName(); ?></h4>
    		<div class="module_classes">
    			<?php $id = 0; ?>
    			<?php foreach ($module->getClasses() as $class): ?>
    				<div class="module_class" data-class="<?php echo $class->getModuleId()."/".$class->getClassOrder(); ?>">
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
    						<a href="<?php echo BASE_URL."courses/open/".$id_course."/".$class->getModuleId()."/".$class->getClassOrder(); ?>">
    							<?php echo ++$id.". Questionnaire"; ?>
    						</a>
    						<?php if (!empty($watched_classes[$class->getModuleId()][$class->getClassOrder()])): ?>
    							<small class="class_watched">Watched</small>
    						<?php endif; ?>
    					<?php endif; ?>
    				</div>
    			<?php endforeach; ?>
    		</div>
    	</div>
    <?php endforeach; ?>
</div>