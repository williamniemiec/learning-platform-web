<title><?php echo $title; ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>resources/styles/bootstrap.min.css' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>resources/styles/jquery.mCustomScrollbar.min.css' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>resources/styles/scrollbar_light.css' />
<link rel='stylesheet' href='<?php echo BASE_URL."resources/colors/".THEME.".css"; ?>' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>resources/styles/global.css' />
<?php if (!empty($styles)) foreach ($styles as $style): ?>
	<link rel='stylesheet' href='<?php echo BASE_URL."resources/styles/".$style.".css"; ?>' />	
<?php endforeach; ?>