<nav class="navbar navbar-expand-lg">
	<div class="container">
		<a class="navbar-brand" href="<?php echo BASE_URL; ?>">Learning Platform</a>
		<button class="navbar-toggler" data-toggle="collapse" data-target="#navbarMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div id="navbarMenu" class="navbar-collapse collapse">
			<div class="navbar-nav ml-auto">
				<div class="notifications">
					<i style="font-size:24px" class="fa notification_icon">&#xf0f3;</i>
					<div class="notifications_area">
						<div class="notification">
							<div class="notification_info">
    							<div class="notification_date">25/05/2020</div>
    							<span class="caret">&times;</span>
							</div>
							<div class="notification_msg">Donec id diam at nulla sagittis lobortis. Phasellus pellentesque vehicula massa id ultricies. Fusce ac mauris ac sapien blandit sodales non nec augue. Nullam odio mi, faucibus ac dolor non, ornare feugiat erat.</div>
						</div>
						<div class="notification">
							Maecenas dapibus faucibus tortor quis posuere. Phasellus porta rhoncus neque in euismod. Morbi sit amet mauris aliquet, dictum lorem a, elementum purus.
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
						<?php echo $name; ?>
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