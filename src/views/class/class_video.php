<div class="class_content">
	<!-- Video frame -->
	<iframe id="class_video" frameborder="0" src="http://www.youtube.com/embed/<?php echo $class->getVideoID(); ?>"></iframe>
	
	<!-- Video description -->
	<div class="content_desc"><?php echo $class->getDescription(); ?></div>
	
	<!-- Comments area -->
	<div class="content_comments">
    	<h3>Comments</h3>
		<form method="POST">
			<div class="form-group">
    			<label for="question">Question</label>
    			<textarea id="question" name="question" class="form-control"></textarea>
			</div>
			<div class="form-group">
    			<input class="btn btn-outline-primary" type="submit" value="Send" />
			</div>
		</form>
		<hr />
		
    	<!-- Comments -->
		<div class="comments">
			<?php foreach ($comments as $comment): ?>
    			<div class="comment">
    				<img class="img img-thumbnail" src="<?php echo BASE_URL."assets/img/profile_photos/".$comment['comment']->getCreator()->getPhoto(); ?>" />
    				
    				<div class="comment_content">
    					<div class="comment_info">
    						<!-- Comment info -->
        					<h5><?php echo $comment['comment']->getCreator()->getName(); ?></h5>
        					<p><?php echo $comment['comment']->getContent(); ?></p>
        					<button class="btn btn-small" onclick="open_reply(this)">&ldca; Reply</button>
        					<div class="comment_reply">
        						<textarea class="form-control"></textarea>
        						<div class="comment_reply_actions">
            						<button class="btn btn-primary" onclick="close_reply(this)">Cancel</button>
            						<button class="btn btn-primary" onclick="send_reply(this,<?php echo $comment['comment']->getId(); ?>,<?php echo $_SESSION['s_login']; ?>)">Send</button>
        						</div>
        					</div>
        					
        					<!-- Comment replies -->
        					<div class="comment_replies">
        						<?php foreach ($comment['replies'] as $reply): ?>
                					<div class="comment comment_reply_content">
                						<img class="img img-thumbnail" src="https://media.gettyimages.com/photos/colorful-powder-explosion-in-all-directions-in-a-nice-composition-picture-id890147976?s=612x612" />
                						<div class="comment_content">
                        					<div class="comment_info">
                            					<h5><?php echo $reply->getUser()->getName(); ?></h5>
                            					<p><?php echo $reply->getContent(); ?></p>
                        					</div>
                        					<?php if ($reply->getCreator()->getId() == $_SESSION['s_login']): ?>
                            					<div class="comment_action">
                            						<button class="btn btn-danger" onclick="delete_reply(this,<?php echo $reply->getId(); ?>)">&times;</button>
                            					</div>
                        					<?php endif; ?>
                        				</div>
                					</div>
            					<?php endforeach; ?>
            				</div>
    					</div>
    					<?php if ($comment['comment']->getCreator()->getId() == $_SESSION['s_login']): ?>
        					<div class="comment_action">
        						<button class="btn btn-danger" onclick="deleteComment(this,<?php echo $comment['comment']->getId(); ?>)">&times;</button>
        					</div>
    					<?php endif; ?>    					
    				</div>
    			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>