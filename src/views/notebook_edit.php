<div class="container">
    <div class="view_panel">
    	<h1 class="view_header"><?php echo $note->getTitle(); ?> - Edit</h1>
    	<div class="view_content">
    		<form method="POST">
        		<div class="form-group">
        			<label for="note_title">Title</label>
        			<input id="note_title" type="text" name="note_title" value="<?php echo $note->getTitle(); ?>" placeholder="Title" class="form-control" required />
    			</div>
    			<div class="form-group">
        			<label for="note_content">Content</label>
        			<textarea id="note_content" placeholder="Content" name="note_content" class="form-control" required><?php echo $note->getContent(); ?></textarea>
        		</div>
        		<div class="form-group">
        			<input type="submit" class="btn_theme btn_theme_full form-control" />
        		</div>
    		</form>
		</div>
    </div>
</div>