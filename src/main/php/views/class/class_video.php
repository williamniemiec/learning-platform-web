<div class="class_content">
	<!-- Video frame -->
	<iframe id="class_video" frameborder="0" src="https://www.youtube.com/embed/<?php echo $class->getVideoID(); ?>"></iframe>
	
	<!-- Video description -->
	<div class="content content_desc"><?php echo $class->getDescription(); ?></div>
	
	<!-- Student notes -->
	<div class="content content_notes">
    	<h3>Notebook</h3>
    	
		<div class="form-group">
			<label for="note_title">Title</label>
			<input id="note_title" type="text" name="note_title" placeholder="Title" class="form-control" required />
		</div>
		<div class="form-group">
			<label for="note_content">Content</label>
			<textarea id="note_content" placeholder="Content" name="note_content" class="form-control" required></textarea>
		</div>
		<div class="form-group">
			<button onClick="newNote(this, <?php echo $class->getModuleId(); ?>, <?php echo $class->getClassOrder(); ?>)" 
					class="btn btn-outline-primary"
			>
				Save
			</button>
		</div>
		
		<ul class="notebook">
			<?php foreach($notebook as $note): ?>
				<li class="notebook-item">
					<div class="notebook-item-header">
						<a href="<?php echo BASE_URL."notebook/open/".$note->getId(); ?>"><?php echo $note->getTitle(); ?></a>
					</div>
					<div class="notebook-item-footer">
						<div class="notebook-item-class"><?php echo $note->getClass()->getTitle(); ?></div>
						<div class="notebook-item-date"><?php echo $note->getCreationDate()->format("m/d/Y H:i:s"); ?></div>
					</div>
				</li>
			<?php endforeach; ?>
		</ul>
		<!-- Pagination -->
		<ul class="pagination pagination-sm justify-content-center">
			<li class="page-item disabled"><button class="page-link" onClick="navigate('bef', -1, <?php echo $class->getModuleId(); ?>, <?php echo $class->getClassOrder(); ?>)">Before</button></li>
			<li class="page-item active" data-index="1"><button onClick="navigate('go', 1, <?php echo $class->getModuleId(); ?>, <?php echo $class->getClassOrder(); ?>)" class="page-link">1</button></li>
			<?php for ($i=2; $i<=$totalPages; $i++): ?>
				<li class="page-item" data-index="<?php echo $i; ?>">
					<button onClick="navigate('go', <?php echo $i; ?>, <?php echo $class->getModuleId(); ?>, <?php echo $class->getClassOrder(); ?>)" class="page-link"><?php echo $i; ?></button>
				</li>
			<?php endfor; ?>
			<li class="page-item <?php echo $totalPages == 1 ? "disabled" : "" ?>">
				<button class="page-link" onClick="navigate('af', -1, <?php echo $class->getModuleId(); ?>, <?php echo $class->getClassOrder(); ?>)">After</button>
			</li>
		</ul>
	</div>
	
	<!-- Comments area -->
	<div class="content content_comments">
    	<h3>Comments</h3>
		<div class="form-group">
			<label for="question">Question</label>
			<textarea id="question" name="question" class="form-control"></textarea>
		</div>
		<div class="form-group">
			<button	class="btn btn-outline-primary" 
					onClick="newComment(<?php echo $id_course; ?>, <?php echo $class->getModuleId(); ?>, <?php echo $class->getClassOrder(); ?>)"
			>
				Send
			</button>
		</div>
		<hr />
		
    	<!-- Comments -->
		<div class="comments">
			<?php foreach ($comments as $comment): ?>
    			<div class="comment">
    				<img	class="img img-thumbnail" 
    						src="<?php echo empty($comment['comment']->getCreator()) || empty($comment['comment']->getCreator()->getPhoto()) ? 
    						     BASE_URL."src/main/web/images/default/noImage.png" : 
    						     BASE_URL."src/main/web/images/profile_photos/".$comment['comment']->getCreator()->getPhoto(); ?>" 
					/>
    				<div class="comment_content">
    					<div class="comment_info">
    						<!-- Comment info -->
        					<h5><?php echo empty($comment['comment']->getCreator()) ? "Deleted user" : $comment['comment']->getCreator()->getName(); ?></h5>
        					<p><?php echo $comment['comment']->getContent(); ?></p>
        					<button class="btn btn-small" onclick="open_reply(this)">&ldca; Reply</button>
        					<div class="comment_reply">
        						<textarea class="form-control"></textarea>
        						<div class="comment_reply_actions">
            						<button class="btn btn-primary" onclick="close_reply(this)">Cancel</button>
            						<button	class="btn btn-primary" 
            								onclick="new_reply(this, <?php echo $comment['comment']->getId(); ?>)"
    								>
    									Send
									</button>
        						</div>
        					</div>
        					
        					<!-- Comment replies -->
        					<div class="comment_replies">
        						<?php foreach ($comment['replies'] as $reply): ?>
                					<div class="comment comment_reply_content">
                						<img 	class="img img-thumbnail" 
                								src="<?php echo empty($reply->getCreator()) || empty($reply->getCreator()->getPhoto()) ? 
                        						     BASE_URL."src/main/web/images/default/noImage.png" : 
                        						     BASE_URL."src/main/web/images/profile_photos/".$reply->getCreator()->getPhoto(); ?>"
        								/>
                						<div class="comment_content">
                        					<div class="comment_info">
                            					<h5><?php echo empty($reply->getCreator()) ? "Deleted user" : $reply->getCreator()->getName(); ?></h5>
                            					<p><?php echo $reply->getContent(); ?></p>
                        					</div>
                        					<?php if (!empty($reply->getCreator()) && $reply->getCreator()->getId() == $_SESSION['s_login']): ?>
                            					<div class="comment_action">
                            						<button class="btn btn-danger" 
                            								onclick="delete_reply(this,<?php echo $reply->getId(); ?>)"
                    								>
                    									&times;
                									</button>
                            					</div>
                        					<?php endif; ?>
                        				</div>
                					</div>
            					<?php endforeach; ?>
            				</div>
    					</div>
    					<?php if (!empty($reply->getCreator()) && $comment['comment']->getCreator()->getId() == $_SESSION['s_login']): ?>
        					<div class="comment_action">
        						<button	class="btn btn-danger" 
    									onclick="deleteComment(this,<?php echo $comment['comment']->getId(); ?>)"
								>
									&times;
								</button>
        					</div>
    					<?php endif; ?>    					
    				</div>
    			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>