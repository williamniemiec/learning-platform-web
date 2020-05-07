<div class="course_header">
	<img class="img imr-responsive course_banner" src="<?php echo BASE_URL."assets/images/logos/".$logo; ?>" />
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

<div class="row">
	<div class="col-3 course_left">
		<?php $this->loadView("course_left", array('modules' => $modules)); ?>
	</div>
	
	<div class="col course_right">
		<?php $this->loadView($view, $viewContent); ?>
	</div>
</div>
