<div class="course_content">
    <!-- Course menu -->
    <div class="course_left">
    	<?php $this->loadView("class/course_menu", $info_menu); ?>
    	
    </div>
    
    <div class="course_right">
        <?php if ($view != "noClasses"): ?>
            <!-- Course information -->
        	<?php $this->loadView("class/course_information", $info_course); ?>
			
            <!-- Class information -->
            <?php $this->loadView("class/class_information", $info_class); ?>
        <?php endif; ?>
        
        <!-- Class view -->
    	<?php $this->loadView($view, $classContent); ?>
    </div>
</div>
