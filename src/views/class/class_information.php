<div class="class_info">
	<!-- Mark as watched button -->
    <?php if ($classType != "Questionnaire"): ?>
    	<button class="btn btn-outline-primary btn_mark_watch" 
    			onclick="<?php echo $wasWatched ? "markAsWatched" : "removeWatched"; ?>(<?php echo $classId; ?>)">
    		Mark as watched
    	</button>
	<?php endif; ?>
	
	<!-- Progress bar -->
	<?php if ($totalClasses > 0): ?>
    	<div id="class_course__progress" class="progress">
    		<div class="progress-bar bg-success" style="width:<?php echo floor($totalWatchedClasses / $totalClasses * 100); ?>%">
    			<?php echo $totalWatchedClasses; ?> / <?php echo $totalClasses; ?>
			</div>
    	</div>
    <?php endif; ?>
</div>