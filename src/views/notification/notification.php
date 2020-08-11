<div class="notifications">
	<div class="notification_icon">
		<span class="notifications_new badge badge-danger badge-pill"><?php echo $notifications['total_unread']; ?></span>
		<i style="font-size:24px" class="fa">&#xf0f3;</i>
	</div>
	<div class="notifications_area scrollbar_light">
		<?php foreach($notifications['notifications'] as $notification): ?>
    		<div class="notification <?php echo $notification->wasRead() ? "" : "new"; ?>">
    			<div class="notification_info">
    				<div class="notification_date"><?php echo $notification->getDate()->format("m-d-Y H:m:s"); ?></div>
    				<?php if ($notification->wasRead()): ?>
    					<button class="notification_btn">Mark as unread</button>
    				<?php else: ?>
    					<button class="notification_btn">Mark as read</button>
    				<?php endif; ?>
    				<span class="caret">&times;</span>
    			</div>
    			<div class="notification_msg"><?php echo $notification->getMessage(); ?></div>
    		</div>
		<?php endforeach; ?>
	</div>
</div>