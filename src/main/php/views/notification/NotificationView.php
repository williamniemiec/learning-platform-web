<?php use models\enum\NotificationTypeEnum; ?>

<div class="notifications">
	<!-- Icon -->
	<div class="notification_icon">
		<span	id="notifications_new" 
				class="notifications_new badge badge-danger badge-pill 
				      <?php echo $notifications['total_unread'] == 0 ? "no_notifications" : ""; ?>"
		>
				<?php echo $notifications['total_unread']; ?>
		</span>
		<i style="font-size:24px" class="fa">&#xf0f3;</i>
	</div>
	
	<!-- Notifications -->
	<div class="notifications_area scrollbar_light">
		<?php foreach($notifications['notifications'] as $notification): ?>
    		<div class="notification <?php echo $notification->wasRead() ? "" : "new"; ?>">
    			<div class="notification_info">
    				<!-- Creation date -->
    				<div class="notification_date">
    					<?php echo $notification->getDate()->format("m-d-Y H:i:s"); ?>
					</div>
					
					<!-- Read / unread button -->
    				<?php if ($notification->wasRead()): ?>
    					<button	onClick="unread(this, <?php echo $notification->get_id(); ?>)" 
    							class="notification_btn"
						>
    						Mark as unread
						</button>
    				<?php else: ?>
    					<button	onClick="read(this, <?php echo $notification->get_id(); ?>)" 
    							class="notification_btn"
						>
							Mark as read
						</button>
    				<?php endif; ?>
    				
    				<!-- Remove button -->
    				<button	onClick="remove(this, <?php echo $notification->get_id(); ?>)" 
    						class="close caret"
					>
    					&times;
    				</button>
    			</div>
    			
    			<!-- Content -->
    			<a	class="notification_msg"
					href="<?php if ($notification->getReferenceType()->get() == NotificationTypeEnum::COMMENT): ?>
    			          <?php echo BASE_URL."courses/open/".$notification->getReference()->getCourseId()
                                     ."/".$notification->getReference()->get_module_id()
                                     ."/".$notification->getReference()->get_class_order(); ?>"
    			          <?php else: ?> 
    			          <?php echo BASE_URL."support/open/".$notification->getReference()->get_id(); ?>"
			              <?php endif; ?>
				>
					<?php echo $notification->getMessage(); ?>
				</a>
    		</div>
		<?php endforeach; ?>
	</div>
</div>