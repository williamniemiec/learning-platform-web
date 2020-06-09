<div class="course_content">
    <div class="course_left">
    	<?php $this->loadView("course_left", array('modules' => $modules, 'logo' => $logo)); ?>
    </div>
    
    <div class="course_right">
    	<?php if ($view != "noClasses"): ?>
        	<div class="content_info">
            	<div class="course_left_action">
                	<div id="course_left_button" class="active">
                		<div class="trace"></div>
                		<div class="trace"></div>
                		<div class="trace"></div>
                	</div>
            	</div>
            	<h1 class="content_title"><?php echo $viewContent['content_title']; ?></h1>
            	<?php if ($viewContent['content_embed']['watched'] > 0): ?>
            		<small class="class_watched">Watched</small>
            	<?php endif; ?>
            </div>
        <?php endif; ?>
        
        <div class="class_info">
        <?php if ($viewContent['content_title'] != "Questionnaire"): ?>
        	<button class="btn btn-outline-primary btn_mark_watch" 
        			onclick="<?php echo $viewContent['content_embed']['watched'] == 0 ? "markAsWatched" : "removeWatched"; ?>(<?php echo $viewContent['content_embed']['id_class']; ?>)">
        		Mark as watched
        	</button>
    	<?php endif; ?>
    	<?php if ($viewContent['totalClasses'] > 0): ?>
        	<div id="class_course__progress" class="progress">
        		<div class="progress-bar bg-success" style="width:<?php echo floor($viewContent['totalWatchedClasses']/$viewContent['totalClasses'] * 100); ?>%">
        			<?php echo $viewContent['totalWatchedClasses']; ?> / <?php echo $viewContent['totalClasses']; ?>
    			</div>
        	</div>
        <?php endif; ?>
	</div>
        
    	<?php $this->loadView($view, $viewContent); ?>
    </div>
</div>

<!-- Scripts -->
<script src='<?php echo BASE_URL; ?>assets/js/course.js'></script>
