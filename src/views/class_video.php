<div class="content_info">
	<h1 class="content_title"><?php echo $content_title; ?></h1>
	<?php if ($content_embed['watched'] > 0): ?>
		<small class="class_watched">Watched</small>
	<?php endif; ?>
</div>
<div class="content_embed">
	<button class="btn btn-outline-primary btn_mark_watch" 
			onclick="<?php echo $content_embed['watched'] == 0 ? "markAsWatched" : "removeWatched"; ?>(<?php echo $content_embed['id_class']; ?>)">
		Mark as watched
	</button>
	
	<iframe id="class_video" frameborder="0" src="http://www.youtube.com/embed/<?php echo $content_embed['video']['url']; ?>"></iframe>
	
	<div class="content_desc"><?php echo $content_embed['video']['description'] ?></div>
	
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
			<?php foreach ($content_embed['doubts'] as $doubt): ?>
    			<div class="comment">
    				<img class="img img-thumbnail" src="https://media.gettyimages.com/photos/colorful-powder-explosion-in-all-directions-in-a-nice-composition-picture-id890147976?s=612x612" />
    				<div class="comment_content">
    					<h5><?php echo $doubt['name']; ?></h5>
    					<p><?php echo $doubt['text']; ?></p>
    				</div>
    			</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>