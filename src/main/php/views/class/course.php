<div class="course_content">
    <!-- Course menu -->
	<?php $this->load_view("class/course_menu", $info_menu); ?>
    
    <div class="class_area">
        <?php if ($view != "class/noClasses"): ?>
            <!-- Course information -->
        	<?php $this->load_view("class/course_information", $info_course); ?>
			
            <!-- Class information -->
            <?php $this->load_view("class/class_information", $info_class); ?>
        <?php endif; ?>
        
        <!-- Class view -->
    	<?php $this->load_view($view, $classContent); ?>
    </div>
</div>
