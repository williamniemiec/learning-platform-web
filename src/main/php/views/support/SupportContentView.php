<?php 
use domain\Admin; 
?>

<div class="container">
    <div class="view_panel">
        <h1 class="view_header">
            Support - <?php echo $topic->getTitle(); ?>
        </h1>
        <div class="view_content">
            <!-- Topic actions -->
            <?php if ($topic->isClosed()): ?>
                <a 
                    href="<?php echo BASE_URL."support/unlock/".$topic->getId(); ?>" 
                    class="btn_theme"
                >
                    Open
                </a>
            <?php else: ?>
                <a 
                    href="<?php echo BASE_URL."support/lock/".$topic->getId(); ?>" 
                    class="btn_theme"
                >
                    Close
                </a>
            <?php endif; ?>
            
            <!-- Topic first message -->
            <div class="message view_widget">
                <div class="message_info">
                    <div class="message_author">
                        <?php echo $topic->getCreator()->getName(); ?>
                        <?php if ($topic->getCreator() instanceof Admin): ?>
                            <span class="privilege_admin">
                                Admin
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="view_widget_info">
                        <?php echo $topic->getCreationDate()->format("m-d-Y H:i:s"); ?>
                    </div>
                </div>
                <div class="message_content">
                    <?php echo $topic->getContent(); ?>
                </div>
            </div>
            
            <!-- Topic replies -->
            <?php foreach ($topic->getReplies() as $reply): ?>
                <div class="message view_widget">
                    <div class="message_info">
                        <div class="message_author">
                            <?php echo $reply->getCreator()->getName(); ?>
                            <?php if ($reply->getCreator() instanceof Admin): ?>
                                <span class="privilege_admin">
                                    Admin
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="view_widget_info">
                            <?php echo $reply->getDate()->format("m-d-Y H:m:s"); ?>
                        </div>
                    </div>
                    <div class="message_content">
                        <?php echo $reply->getContent(); ?>
                    </div>
                </div>
            <?php endforeach; ?>
            
            <!-- Send form -->
            <form method="POST">
                <div class="form-group">
                    <textarea 
                        name="topic_message" 
                        class="form-control" <?php if ($topic->isClosed()) echo "disabled"; ?>
                    >
                    </textarea>
                </div>
                <div class="form-group">
                    <input 
                        class="btn_theme btn_full" 
                        type="submit" 
                        value="Send" 
                        <?php if ($topic->isClosed()) echo "disabled"; ?> 
                    />
                </div>
            </form>
        </div>
    </div>
</div>