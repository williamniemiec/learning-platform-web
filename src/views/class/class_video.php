<div class="class_content">
	<!-- Video frame -->
	<iframe id="class_video" frameborder="0" src="http://www.youtube.com/embed/<?php echo $video['url']; ?>"></iframe>
	
	<!-- Video description -->
	<div class="content_desc"><?php echo $video['description'] ?></div>
	
	<!-- Comments -->
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
		<div class="comments">
			<?php foreach ($doubts as $doubt): ?>
    			<div class="comment">
    				<img class="img img-thumbnail" src="https://media.gettyimages.com/photos/colorful-powder-explosion-in-all-directions-in-a-nice-composition-picture-id890147976?s=612x612" />
    				<div class="comment_content">
    					<div class="comment_info">
        					<h5><?php echo $doubt['name']; ?></h5>
        					<p><?php echo $doubt['text']; ?></p>
        					<button class="btn btn-small" onclick="open_reply(this)">&ldca; Reply</button>
        					<div class="comment_reply">
        						<textarea class="form-control"></textarea>
        						<div class="comment_reply_actions">
            						<button class="btn btn-primary" onclick="close_reply(this)">Cancel</button>
            						<button class="btn btn-primary" onclick="send_reply(this,<?php echo $doubt['id']; ?>,<?php echo $_SESSION['s_login']; ?>)">Send</button>
        						</div>
        					</div>
        					<div class="comment_replies">
        						<?php foreach ($doubt['replies'] as $reply): ?>
                					<div class="comment comment_reply_content">
                						<img class="img img-thumbnail" src="https://media.gettyimages.com/photos/colorful-powder-explosion-in-all-directions-in-a-nice-composition-picture-id890147976?s=612x612" />
                						<div class="comment_content">
                        					<div class="comment_info">
                            					<h5><?php echo $reply['name']; ?></h5>
                            					<p><?php echo $reply['text']; ?></p>
                        					</div>
                        					<?php if ($reply['id_user'] == $_SESSION['s_login']): ?>
                            					<div class="comment_action">
                            						<button class="btn btn-danger" onclick="delete_reply(this,<?php echo $reply['id']; ?>)">&times;</button>
                            					</div>
                        					<?php endif; ?>
                        				</div>
                					</div>
            					<?php endforeach; ?>
            				</div>
    					</div>
    					<?php if ($doubt['id_user'] == $_SESSION['s_login']): ?>
        					<div class="comment_action">
        						<button class="btn btn-danger" onclick="deleteComment(this,<?php echo $doubt['id']; ?>)">&times;</button>
        					</div>
    					<?php endif; ?>
    				</div>
    			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>