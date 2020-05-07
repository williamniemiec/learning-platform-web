<div class="course_header">
	<img class="img imr-responsive course_banner" src="<?php echo BASE_URL."assets/images/logos/".$logo; ?>" />
	<div class="course_info">
        <h1><a href="<?php echo BASE_URL."courses/open/".$id_course; ?>"><?php echo $name; ?></a></h1>
        <h6><?php echo $description; ?></h6>
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
