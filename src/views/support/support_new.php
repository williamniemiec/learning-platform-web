<div class="container">
	<div class="view_panel">
        <h1 class="view_header">Support - new</h1>
        <div class="view_content">
            <form class="POST" method="POST">
            	<div class="form-group">
            		<label for="topic_title">Title</label>
            		<input id="topic_title" name="topic_title" type="text" class="form-control" />
            	</div>
            	<div class="form-group">
            		<label for="topic_category">Category</label>
            		<select name="topic_category" class="form-control">
            			<?php foreach ($categories as $category): ?>
                			<option value="<?php echo $category->getId(); ?>"><?php echo ucfirst(strtolower($category->getName())); ?></option>
            			<?php endforeach; ?>
            		</select>
            	</div>
            	<div class="form-group">
            		<label for="topic_message">Message</label>
            		<textarea id="topic_message" name="topic_message" class="form-control"></textarea>
            	</div>
            	<div class="form-group">
            		<input type="submit" value="Send" class="btn_theme btn_full form-control" />
            	</div>
        	</form>
    	</div>
	</div>
</div>