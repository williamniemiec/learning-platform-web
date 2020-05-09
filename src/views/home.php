<div class="container">
	<div class="home">
        <h1>My Courses</h1>
        <?php if ($totalCourses == 0): ?>
            <div class="error_msg">
    			<h2>There are not registered courses for this account :/</h2>
    		</div>
        <?php else: ?>
        	<div class="courses">
        		<?php foreach($courses as $course): ?>
        			<a href="<?php echo BASE_URL."courses/open/".$course['id']; ?>">
                		<div class="course">
            				<img class="img img-responsive" src="<?php echo empty($course['logo']) ? BASE_URL."assets/images/noImage" : BASE_URL."assets/images/logos/".$course['logo']; ?>" />
                			<h2><?php echo $course['name']; ?></h2>
                			<p><?php echo $course['description']; ?></p>
                			<div class="course_info">
                				<span class="course_watchedClasses">&#128435;<?php echo $course['totalWatchedClasses']; ?>/<?php echo $course['totalClasses'] ?></span>
                				<span class="course_modules">&#128455;<?php echo $course['totalModules']; ?></span>
                			</div>
                			<div class="progress position-relative">
                				<?php if ($course['totalClasses'] == 0): ?>
                					<div class="progress-bar bg-success" style="width:0%"></div>
                					<small class="justify-content-center d-flex position-absolute w-100">0%</small>
                				<?php else: ?>
                    				<div class="progress-bar bg-success" style="width:<?php echo floor($course['totalWatchedClasses']/$course['totalClasses'] * 100); ?>%"></div>
                    				<small class="justify-content-center d-flex position-absolute w-100"><?php echo floor($course['totalWatchedClasses']/$course['totalClasses'] *100); ?>%</small>
                    			<?php endif; ?>
                    		</div>
                		</div>
            		</a>
        		<?php endforeach; ?>
        	</div>
    	<?php endif; ?>
    </div>
</div>