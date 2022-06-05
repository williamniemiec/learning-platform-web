<div class="container">
	<div class="view_panel">
	<h1 class="view_header"><?php echo $bundle->get_name(); ?></h1>
	<div class="view_content">
    	<div class="bundle-info">
    		<img	class="bundle-info-logo" 
					src="<?php echo empty($bundle->get_logo()) ? 
					    BASE_URL."src/main/webapp/images/default/noImage.png" : 
					    BASE_URL."src/main/webapp/images/logos/bundles/".$bundle->get_logo(); ?>" 
			/>
    		<div class="bundle-info-content">
    			<p><?php echo $bundle->get_description(); ?></p>
    		</div>
    	</div>
    	
    	<table class="bundle-details">
    		<tr>
    			<th>Name</th>
    			<td><?php echo $bundle->get_name(); ?></td>
    		</tr>
    		<tr>
    			<th>Description</th>
    			<td><?php echo $bundle->get_description(); ?></td>
    		</tr>
    		<tr>
    			<th>Total classes</th>
    			<td><?php echo $total_classes; ?></td>
    		</tr>
    		<tr>
    			<th>Total hours</th>
    			<td><?php echo number_format($total_length / 60, 2); ?>h</td>
    		</tr>
    	</table>
    	
    	<!-- Included courses -->
    	<div class="view_widget">
        	<h2>Included courses</h2>
        	<div class="mosaic">
        		<?php foreach ($courses as $course): ?>
            		<div class="mosaic-item">
                		<img	class="mosaic-item-thumbnail" 
                				src="<?php echo empty($course->get_logo()) ?
                				    BASE_URL."src/main/webapp/images/default/noImage.png" :
                				    BASE_URL."src/main/webapp/images/logos/courses/".$course->get_logo(); ?>" 
        				/>
                		<div class="mosaic-item-content">
                    		<div class="mosaic-item-header"><?php echo $course->get_name(); ?></div>
                    		<div class="mosaic-item-caption">
                    			<div class="mosaic-item-caption-split">
                    				<p>&#128249; <?php echo $course->get_total_classes(); ?></p>
                    				<p>&#128337; <?php echo number_format($course->get_total_length()/60, 2); ?>h</p>
                    			</div>
                    		</div>
                		</div>
            		</div>
        		<?php endforeach; ?>
        	</div>
    	</div>
    	
    	<div class="purchase-option">
        	<!-- Price -->
    		<div class="purchase-price">US$ <?php echo number_format($bundle->getPrice(), 2); ?></div>
        	<!-- Buy option (if student does not have it) -->
        	<?php if ($has_bundle): ?>
        		<div class="purchase-btn">Purchased</div>
        	<?php else: ?>
    			<button class="purchase-btn" onClick="buy(<?php echo $bundle->get_id(); ?>)">Buy</button>
			<?php endif; ?>
    	</div>
    	
    	<!-- Extension bundles -->
    	<div class="view_widget">
    		<h2 class="area-title">Extension bundles</h2>
    		<?php if (empty($extensionBundles)): ?>
    			<h3 class="view_widget_central">There are no bundles</h3>
    		<?php else: ?>
        		<div class="gallery">
    				<div class="gallery-controls">
            			<div class="gallery-control gallery-control-left">&larr;</div>
            			<div class="gallery-control gallery-control-right">&rarr;</div>
        			</div>
        			<div class="gallery-items">
            			<div class="gallery-items-area">
            				<?php foreach ($extensionBundles as $bundle): ?>
                    			<button	class="gallery-item" 
                    					onClick="window.location.href='<?php echo BASE_URL."bundle/open/".$bundle->get_id() ?>'"
                    			>
                    				<img	class="gallery-item-thumbnail" 
                    						src="<?php echo empty($bundle->get_logo()) ? 
                    						    BASE_URL."src/main/webapp/images/default/noImage.png" : 
                    						    BASE_URL."src/main/webapp/images/logos/bundles/".$bundle->get_logo(); ?>" 
            						/>
                    				<div class="gallery-item-content">
                        				<div class="gallery-item-header"><?php echo $bundle->get_name(); ?></div>
                        				<div class="gallery-item-body"><p><?php echo $bundle->get_description(); ?></p></div>
                        				<div class="gallery-item-footer">
                        					<?php echo $bundle->getPrice(); ?>
                    					</div>
                    				</div>
                    			</button>
                			<?php endforeach; ?>
            			</div>
        			</div>
        		</div>
    		<?php endif; ?>
    	</div>
		
    	<!-- Try something new -->
    	<div class="view_widget">
    		<h2 class="area-title">Try something new</h2>
    		<?php if (empty($unrelatedBundles)): ?>
    			<h3>There are no bundles</h3>
    		<?php else: ?>
        		<div class="gallery">
    				<div class="gallery-controls">
            			<div class="gallery-control gallery-control-left">&larr;</div>
            			<div class="gallery-control gallery-control-right">&rarr;</div>
        			</div>
        			<div class="gallery-items">
            			<div class="gallery-items-area">
            				<?php foreach ($unrelatedBundles as $bundle): ?>
                    			<button	class="gallery-item" 
                    					onClick="window.location.href='<?php echo BASE_URL."bundle/open/".$bundle->get_id() ?>'"
                    			>
                    				<img	class="gallery-item-thumbnail" 
                    						src="<?php echo empty($bundle->get_logo()) ? 
                    						    BASE_URL."src/main/webapp/images/default/noImage.png" : 
                    						    BASE_URL."src/main/webapp/images/logos/bundles/".$bundle->get_logo(); ?>" 
            						/>
                    				<div class="gallery-item-content">
                        				<div class="gallery-item-header"><?php echo $bundle->get_name(); ?></div>
                        				<div class="gallery-item-body"><p><?php echo $bundle->get_description(); ?></p></div>
                        				<div class="gallery-item-footer">
                        					<?php echo $bundle->getPrice(); ?>
                    					</div>
                    				</div>
                    			</button>
                			<?php endforeach; ?>
            			</div>
        			</div>
        		</div>
    		<?php endif; ?>
    	</div>
    </div>
	</div>
</div>