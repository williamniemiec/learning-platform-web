<div class="container">
    <div class="view_panel">
    	<h1 class="view_header"><?php echo $note->getTitle(); ?></h1>
    	<div class="view_content">
    		<a href="<?php echo BASE_URL."notebook/edit/".$note->getId(); ?>" class="btn_theme">Edit</a>
    		<a href="<?php echo BASE_URL."notebook/delete/".$note->getId(); ?>" class="btn_theme btn_theme_danger">Remove</a>
    		<div class="message view_widget"><?php echo $note->getContent(); ?></div>
    	</div>
    </div>
</div>