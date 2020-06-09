<div class="course_header">
	<div class="course_info">
        <h1><a href="<?php echo BASE_URL."courses/open/".$id_course; ?>"><?php echo $name; ?></a></h1>
        <h6><?php echo $description; ?></h6>
        <?php if ($totalClasses > 0): ?>
            <div class="course_progress">
            	<p>Watched classes: <?php echo $totalWatchedClasses; ?> / <?php echo $totalClasses; ?></p>
            	<div class="progress">
            		<div class="progress-bar bg-success" style="width:<?php echo floor($totalWatchedClasses/$totalClasses * 100); ?>%"><?php echo floor($totalWatchedClasses/$totalClasses *100); ?>%</div>
            	</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<div class="course_content">
	<div class="course_left_action">
    	<div id="course_left_button" class="active">
    		<div class="trace"></div>
    		<div class="trace"></div>
    		<div class="trace"></div>
    	</div>
	</div>
	
	
    <div class="course_left">
    	<?php $this->loadView("course_left", array('modules' => $modules, 'logo' => $logo)); ?>
    </div>
    
    <div class="course_right">
    	<?php $this->loadView($view, $viewContent); ?>
    </div>
</div>

<!-- Scripts -->
<script src='<?php echo BASE_URL; ?>assets/js/course.js'></script>
