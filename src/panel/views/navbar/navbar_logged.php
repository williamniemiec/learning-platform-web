<nav class="navbar navbar-expand-lg">
	<div class="container">
		<a class="navbar-brand" href="<?php echo BASE_URL; ?>">Learning Platform</a>
		<button class="navbar-toggler" data-toggle="collapse" data-target="#navbarMenu">
			<span class="navbar-toggler-icon"></span>
		</button>
		<div id="navbarMenu" class="navbar-collapse collapse">
			<div class="navbar-nav">
				<ul class="nav-pages">
    				<li><a class="nav-item nav-link" href="<?php echo BASE_URL; ?>bundles">Bundles</a></li>
    				<li><a class="nav-item nav-link" href="<?php echo BASE_URL; ?>courses">Courses</a></li>
    				<li><a class="nav-item nav-link" href="<?php echo BASE_URL; ?>modules">Modules</a></li>
    				<li><a class="nav-item nav-link" href="<?php echo BASE_URL; ?>classes">Classes</a></li>
    				<li><a class="nav-item nav-link" href="<?php echo BASE_URL; ?>students">Students</a></li>
    				<li><a class="nav-item nav-link" href="<?php echo BASE_URL; ?>support">Support</a></li>
    				<li><a class="nav-item nav-link" href="<?php echo BASE_URL; ?>admins">Admins</a></li>
				</ul>
			</div>
			<div class="navbar-nav ml-auto">
				<a href="<?php echo BASE_URL; ?>settings" class="nav-item nav-link active"><?php echo $username; ?></a>
				<a href="<?php echo BASE_URL; ?>home/logout" class="nav-item nav-link">Logout</a>
			</div>
		</div>
	</div>
</nav>