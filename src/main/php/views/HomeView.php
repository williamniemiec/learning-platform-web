<div class="parallax">
	<div class="container parallax-container">
    	<div class="parallax-title">
    		<h1>Learning platform</h1>
    		</div>
    	<div class="parallax-captions">
    		<div class="parallax-caption"><?php echo $total_bundles; ?> bundles</div>
    		<div class="parallax-caption"><?php echo $total_courses; ?> courses</div>
    		<div class="parallax-caption">More than <?php echo $total_length; ?> hours of classes</div>
    	</div>
	</div>
</div>

<div class="container">
    <div id="area-central">
    	<div class="view_widget">
    		<h2 class="area-title">Explore our bundles</h2>
    		<div class="gallery">
				<div class="gallery-controls">
        			<div class="gallery-control gallery-control-left">&larr;</div>
        			<div class="gallery-control gallery-control-right">&rarr;</div>
    			</div>
    			<div class="gallery-items">
        			<div class="gallery-items-area">
        				<?php foreach ($bundles as $bundle): ?>
                			<button	class="gallery-item" 
                					onClick="window.location.href='<?php echo BASE_URL."bundle/open/".$bundle['bundle']->get_id() ?>'"
                			>
                				<img	class="gallery-item-thumbnail" 
                						src="<?php echo empty($bundle['bundle']->get_logo()) ? 
                						    BASE_URL."src/main/webapp/images/default/noImage.png" : 
                						    BASE_URL."src/main/webapp/images/logos/bundles/".$bundle['bundle']->get_logo(); ?>" 
        						/>
                				<div class="gallery-item-content">
                    				<div class="gallery-item-header"><?php echo $bundle['bundle']->get_name(); ?></div>
                    				<div class="gallery-item-body"><p><?php echo $bundle['bundle']->get_description(); ?></p></div>
                    				<div class="gallery-item-footer">
                    					<?php echo !empty($bundle['has_bundle']) ? "Purchased" : $bundle['bundle']->getPrice(); ?>
                					</div>
                				</div>
                			</button>
            			<?php endforeach; ?>
        			</div>
    			</div>
    		</div>
    	</div>
    	
    	<div class="area bundles-search">
    		<h3 class="area-title">Discover new possibilities</h3>
    		<div class="search-bar">
    			<input type="text" class="search-bar-big" placeholder="Search bundle" />
    			<button class="search-bar-btn" onClick="search(this)">&#128270;</button>
    		</div>
		</div>
		<div class="view_widget">
			<!-- Filters -->
			<div class="search-filters">
				<h3>Filters</h3>
				<div class="search-filter-options">
					<div class="form-group">
    					<input id="rdo-price" type="radio" name="filter" value="price" checked />
    					<label for="rdo-price">Price</label>
					</div>
					<div class="form-group">
    					<input id="rdo-course" type="radio" name="filter" value="courses" />
    					<label for="rdo-course">Total courses</label>
					</div>
					<div class="form-group">
    					<input id="rdo-sales" type="radio" name="filter" value="sales" />
    					<label for="rdo-sales">Best sellers</label>
					</div>
					<div class="form-group">
    					<select id="order" class="form-control">
    						<option value="asc" selected>Ascending</option>
    						<option value="desc">Descending</option>
    					</select>
					</div>
				</div>
			</div>
		</div>
		<!-- Bundles with the specified name and selected filters -->
		<div id="bundle-search-results" class="view_widget">
			<h3>Results</h3>
    		<div class="gallery">
				<div class="gallery-controls">
        			<div class="gallery-control gallery-control-left">&larr;</div>
        			<div class="gallery-control gallery-control-right">&rarr;</div>
    			</div>
    			<div class="gallery-items">
    				<div class="gallery-items-area"></div>
    			</div>
    		</div>
		</div>
    </div>
</div>