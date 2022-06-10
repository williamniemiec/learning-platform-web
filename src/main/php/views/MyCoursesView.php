<!-- Students birthdate alert -->
<?php
if (!empty($totalWatchedVideos) && !empty($totalWatchedLength)) {
    $this->loadView(
        'alerts/BirthdateAlert', 
        array('total_watched_videos' => $totalWatchedVideos, 
              'total_watched_length' => $totalWatchedLength)
    ); 
}
?>
        
<div class="container">
    <div class="home">
        <!-- Display error message (if any) -->
        <?php if ($totalCourses == 0): ?>
            <div class="error_msg">
                <h2>
                    There are no registered courses for this account :/
                </h2>
            </div>
        <?php else: ?>
            <!-- Progress chart -->
            <div class="view_panel">
                <h1 class="view_header">
                    Progress chart
                </h1>
                <div class="view_content">
                    <canvas id="chart_progress" height="60"></canvas>
                </div>
            </div>
            
            <!-- Courses -->
            <div class="view_panel">
                <h1 class="view_header">
                    My Courses
                </h1>
                <div class="view_content">
                    <!-- Courses search -->
                    <div class="search-bar">
                        <input 
                            type="text" 
                            class="search-bar-big" 
                            placeholder="Search course" 
                        />
                        <button class="search-bar-btn"  onClick="search(this)">
                            &#128270;
                        </button>
                    </div>
                
                    <!-- Display student courses -->
                    <div id="courses">
                        <?php foreach($courses as $course): ?>
                            <button	
                                class="course" 
                                onClick="window.location.href='<?php echo BASE_URL."courses/open/".$course['course']->getId(); ?>'"
                            >
                                <!-- Course information -->
                                <img
                                    class="img img-responsive" 
                                    src="<?php echo empty($course['course']->getLogo()) 
                                        ? BASE_URL."src/main/webapp/images/default/noImage.png" 
                                        : BASE_URL."src/main/webapp/images/logos/courses/".$course['course']->getLogo(); ?>" 
                                />
                                <h2>
                                    <?php echo $course['course']->getName(); ?>
                                </h2>
                                <p>
                                    <?php echo $course['course']->getDescription(); ?>
                                </p>         
                                <div class="course_info">
                                    <span class="course_watchedClasses">
                                        &#128249;
                                        <?php echo $course['total_classes_watched']; ?> / 
                                        <?php echo $course['course']->getTotalClasses(); ?>
                                    </span>
                                    <span class="course_length">
                                        &#128337;
                                        <?php echo $course['course']->getTotalLength() == 0 
                                            ? "0/0" 
                                            : number_format($course['total_length_watched'] / 60, 2) 
                                                ."h / "
                                                .number_format($course['course']->getTotalLength()/60, 2) 
                                                ."h"; ?>
                                    </span>
                                </div>
                                
                                <!-- Course progress -->
                                <div class="progress position-relative">
                                    <?php if ($course['course']->getTotalClasses() == 0): ?>
                                        <div 
                                            class="progress-bar bg-success" 
                                            style="width:0%">
                                        </div>
                                        <small class="justify-content-center d-flex position-absolute w-100">
                                            0%
                                        </small>
                                    <?php else: ?>
                                        <div
                                            class="progress-bar bg-success" 
                                            style="width:<?php echo floor($course['total_classes_watched'] / $course['course']->getTotalClasses() * 100); ?>%"
                                        >
                                        </div>
                                        <small class="justify-content-center d-flex position-absolute w-100">
                                            <?php echo floor($course['total_classes_watched']/$course['course']->getTotalClasses() *100); ?>%
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </button>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            
            <!-- Student notes -->
            <div class="view_panel">
                <h1 class="view_header">
                    Notebook
                </h1>
                <div class="view_content">
                    <ul class="notebook">
                        <?php foreach($notebook as $note): ?>
                            <li class="notebook-item">
                                <div class="notebook-item-header">
                                    <a href="<?php echo BASE_URL.'notebook/open/'.$note->getId(); ?>">
                                        <?php echo $note->getTitle(); ?>
                                    </a>
                                </div>
                                <div class="notebook-item-footer">
                                    <div class="notebook-item-class">
                                        <?php echo $note->getClass()->getTitle(); ?>
                                    </div>
                                    <div class="notebook-item-date">
                                        <?php echo $note->getCreationDate()->format("m-d-Y H:m:s"); ?>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; ?>
                    </ul>

                    <!-- Pagination -->
                    <ul class="pagination pagination-sm justify-content-center">
                        <li class="page-item disabled">
                            <button 
                                class="page-link" 
                                onClick="navigate('bef')"
                            >
                                Before
                            </button>
                        </li>
                        <li class="page-item active" data-index="1">
                            <button 
                                onClick="navigate('go', 1)" 
                                class="page-link"
                            >
                                1
                            </button>
                        </li>
                        <?php for ($i=2; $i<=$totalPages; $i++): ?>
                            <li class="page-item" data-index="<?php echo $i; ?>">
                                <button 
                                    onClick="navigate('go', <?php echo $i; ?>)" 
                                    class="page-link"
                                >
                                <?php echo $i; ?>
                            </button>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?php echo $totalPages == 1 ? "disabled" : "" ?>">
                            <button 
                                class="page-link" 
                                onClick="navigate('af')"
                            >
                                After
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>