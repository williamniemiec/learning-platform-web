<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">My support</h1>
		<div class="view_content">
        	<a class="btn_theme" href="<?php echo BASE_URL; ?>support/new">New</a>
            <table class="table table-hover table-bordered table-stripped">
            	<thead>
            		<tr class="support_header">
            			<th>Title</th>
            			<th>Category</th>
            			<th class="support_date">Date of publication</th>
            			<th>Status</th>
            		</tr>
            	</thead>
            	<tbody>
            		<?php foreach ($supportTopics as $topic): ?>
            		<tr>
            			<td><a href="<?php echo BASE_URL."support/open/".$topic->getId(); ?>"><?php echo $topic->getTitle(); ?></a></td>
            			<td><?php echo ucfirst(strtolower($topic->getCategory()->getName())); ?></td>
            			<td><?php echo $topic->getCreationDate()->format("m-d-Y H:m:s"); ?></td>
            			<td><?php echo $topic->isClosed() ? "Closed" : "Open" ?></td>
            		</tr>
            		<?php endforeach; ?>
            	</tbody>
            </table>
        </div>
    </div>
</div>|