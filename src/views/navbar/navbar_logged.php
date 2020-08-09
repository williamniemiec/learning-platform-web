<nav class="navbar navbar-expand-lg">
	<div class="container">
		<a class="navbar-brand" href="<?php echo BASE_URL; ?>">Learning Platform</a>
		<button class="navbar-toggler" data-toggle="collapse" data-target="#navbarMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div id="navbarMenu" class="navbar-collapse collapse">
			<div class="nav-left">
				<ul class="nav-pages">
					<li><a href="<?php echo BASE_URL; ?>courses">My courses</a></li>
					<li><a href="<?php echo BASE_URL; ?>purchases">Purchases</a></li>
				</ul>
			</div>
			<div class="navbar-nav nav-right">
				<div class="notifications">
					<div class="notification_icon">
    					<span class="notifications_new badge badge-danger badge-pill">1</span>
    					<i style="font-size:24px" class="fa">&#xf0f3;</i>
					</div>
					<div class="notifications_area scrollbar_light">
						<div class="notification">
							<div class="notification_info">
    							<div class="notification_date">25/05/2020</div>
    							<button class="notification_btn">Mark as unread</button>
    							<span class="caret">&times;</span>
							</div>
							<div class="notification_msg">Donec id diam at nulla sagittis lobortis. Phasellus pellentesque vehicula massa id ultricies. Fusce ac mauris ac sapien blandit sodales non nec augue. Nullam odio mi, faucibus ac dolor non, ornare feugiat erat.</div>
						</div>
						<div class="notification new">
							<div class="notification_info">
    							<div class="notification_date">21/12/2019</div>
    							<button class="notification_btn">Mark as read</button>
    							<span class="caret">&times;</span>
							</div>
							<div class="notification_msg">Maecenas dapibus faucibus tortor quis posuere. Phasellus porta rhoncus neque in euismod. Morbi sit amet mauris aliquet, dictum lorem a, elementum purus.</div>
						</div>
						<div class="notification">
							...
						</div>
						<div class="notification">
							...
						</div>
						<div class="notification">
							...
						</div>
						<div class="notification">
							...
						</div>
						<div class="notification">
							...
						</div>
					</div>
				</div>
				
				
				
				<div class="dropdown">
					<a class="btn btn-link dropdown-toggle" data-toggle="dropdown">
						<?php echo $username; ?>
						<span class="caret"></span>
					</a>
					<ul class="dropdown-menu dropdown-menu-right">
						<li class="dropdown-item"><a href="<?php echo BASE_URL; ?>settings">Profile</a></li>
						<li class="dropdown-item"><a href="<?php echo BASE_URL; ?>settings/edit">Edit profile</a></li>
						<li class="dropdown-divider"></li>
						<li class="dropdown-item"><a href="<?php echo BASE_URL; ?>support">Support</a></li>
						<li class="dropdown-divider"></li>
						<li class="dropdown-item"><a href="<?php echo BASE_URL; ?>home/logout">Logout</a></li>						
					</ul>
				</div>
			</div>
		</div>
	</div>
</nav>