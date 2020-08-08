<!-- Students birthdate alert -->
<?php
if (!empty($totalWatchedVideos) && !empty($totalWatchedLength))
    $this->loadView(
        'alerts/BirthdateAlert', 
        array('total_watched_videos' => $totalWatchedVideos, 
              'total_watched_length' => $totalWatchedLength)
    ); 
?>
    	
<div class="container">
	<div class="home">
        <!-- Display error message (if any) -->
        <?php if ($totalCourses == 0): ?>
            <div class="error_msg">
    			<h2>There are not registered courses for this account :/</h2>
    		</div>
        <?php else: ?>
        	<!-- Progress chart -->
        	<div class="view_panel">
            	<h1 class="view_header">Progress chart</h1>
            	<div class="view_content">
            		<canvas id="chart_progress" height="60"></canvas>
        		</div>
    		</div>
        	
        	
        	<div class="view_panel">
            	<h1 class="view_header">My Courses</h1>
            	<div class="view_content">
            	
                	<!-- Courses search -->
                	<div class="search-bar">
            			<input type="text" class="search-bar-big" placeholder="Search course" />
            			<span class="search-bar-btn">S</span>
            		</div>
                
                	<!-- Display student courses -->
                	<div class="courses">
                		<?php foreach($courses as $course): ?>
                			<a href="<?php echo BASE_URL."courses/open/".$course['course']->getId(); ?>">
                        		<div class="course">
                        			<!-- Course information -->
                    				<img class="img img-responsive" src="<?php echo empty($course['course']->getLogo()) ? BASE_URL."assets/img/noImage" : BASE_URL."assets/img/logos/".$course['course']->getLogo(); ?>" />
                        			<h2><?php echo $course['course']->getName(); ?></h2>
                        			<p><?php echo $course['course']->getDescription(); ?></p>                			
                        			
                        			<div class="course_info">
                        				<span class="course_watchedClasses">&#128435;<?php echo $course['total_classes_watched']; ?>/<?php echo $course['course']->getTotalClasses(); ?></span>
                        				<span class="course_modules">&#128455;...</span>
                        			</div>
                        			
                        			<!-- Course progress -->
                        			<div class="progress position-relative">
                        				<?php if ($course['course']->getTotalClasses() == 0): ?>
                        					<div class="progress-bar bg-success" style="width:0%"></div>
                        					<small class="justify-content-center d-flex position-absolute w-100">0%</small>
                        				<?php else: ?>
                            				<div class="progress-bar bg-success" style="width:<?php echo floor($course['total_classes_watched']/$course['course']->getTotalClasses() * 100); ?>%"></div>
                            				<small class="justify-content-center d-flex position-absolute w-100"><?php echo floor($course['total_classes_watched']/$course['course']->getTotalClasses() *100); ?>%</small>
                            			<?php endif; ?>
                            		</div>
                        		</div>
                    		</a>
                		<?php endforeach; ?>
                	</div>
            	</div>
    		</div>
    	<?php endif; ?>
    </div>
</div>