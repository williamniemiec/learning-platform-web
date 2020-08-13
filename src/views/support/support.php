<div class="container">
	<div class="view_panel">
    	<h1 class="view_header">My support</h1>
		<div class="view_content">
        	<!-- Search -->
        	<div class="search-bar">
    			<input type="text" class="search-bar-big" placeholder="Search course" />
    			<button class="search-bar-btn" onClick="search(this)">&#128270;</button>
    		</div>
    		
			<div class="view_widget">
			    <!-- Filters -->
				<div class="search-filters">
    				<h3>Filters</h3>
    				<div class="search-filter-options">
    					<div class="form-group">
        					<input id="rd-price" type="radio" name="filter" />
        					<label for="rd-price">All</label>
    					</div>
    					<div class="form-group">
        					<input id="rd-course" type="radio" name="filter" />
        					<label for="rd-course">Only answered</label>
    					</div>
    					<div class="form-group">
        					<select id="category" class="form-control">
        						<option value="0">All</option>
        						<option value="2">Cat2</option>
        					</select>
        					<label for="order"></label>
    					</div>
    				</div>
				</div>
			</div>
        	
        	<!-- Topics -->
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