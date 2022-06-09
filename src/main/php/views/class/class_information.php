<?php
use domain\Video;
?>

<div class="class_info">
    <!-- Mark as watched button -->
    <?php if ($class instanceof Video): ?>
        <button 
            class="btn btn-outline-primary btn_mark_watch" 
            onclick="<?php echo $wasWatched ? "removeWatched" : "markAsWatched"; ?>(<?php echo $class->getModuleId().','.$class->getClassOrder(); ?>)">
            <?php echo $wasWatched ? "Remove watched" : "Mark as watched"; ?>
        </button>
    <?php endif; ?>
    
    <!-- Progress bar -->
    <?php if ($total['total_classes'] > 0): ?>
        <div id="class_course__progress" class="progress">
            <div 
                class="progress-bar bg-success" 
                style="width:<?php echo floor($totalWatchedClasses / $total['total_classes'] * 100); ?>%
            ">
                <?php echo $totalWatchedClasses; ?> / <?php echo $total['total_classes']; ?>
            </div>
        </div>
    <?php endif; ?>
</div>