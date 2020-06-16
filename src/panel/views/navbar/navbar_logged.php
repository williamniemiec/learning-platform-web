<nav class="navbar navbar-expand-lg">
	<div class="container">
		<a class="navbar-brand" href="<?php echo BASE_URL; ?>">Learning Platform</a>
		<button class="navbar-toggler" data-toggle="collapse" data-target="#navbarMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div id="navbarMenu" class="navbar-collapse collapse">
			<div class="navbar-nav">
				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>">Courses</a>
				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>students">Students</a>
				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>support">Support</a>
				<a class="nav-item nav-link" href="<?php echo BASE_URL; ?>admins">Admins</a>
			</div>
			<div class="navbar-nav ml-auto">
				<a href="<?php echo BASE_URL; ?>settings" class="nav-item nav-link active"><?php echo $adminName; ?></a>
				<a href="<?php echo BASE_URL; ?>home/logout" class="nav-item nav-link">Logout</a>
			</div>
		</div>
	</div>
</nav>