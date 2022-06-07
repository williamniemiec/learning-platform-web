<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />

<?php if (!empty($description)): ?>
	<meta name="description" content="<?php echo $description; ?>" />
<?php endif; ?>

<?php if (!empty($keywords)): ?>
	<meta name="keywords" content="<?php echo implode(',', $keywords); ?>" />
<?php endif; ?>

<?php if (!empty($robots)): ?>
	<meta name="robots" content="<?php echo $robots; ?>" />
<?php endif; ?>

<title><?php echo $title; ?></title>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>src/main/webapp/lib/bootstrap/bootstrap.min.css' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>src/main/webapp/lib/bootstrap/bootstrap.min.css.map' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>src/main/webapp/lib/scrollbar_light/jquery.mCustomScrollbar.min.css' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>src/main/webapp/lib/scrollbar_light/scrollbar_light.css' />
<link rel='stylesheet' href='<?php echo BASE_URL."src/main/webapp/styles/colors/".THEME.".css"; ?>' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>src/main/webapp/styles/btnTheme.css' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>src/main/webapp/lib/password_strength/ps_style.css' />
<link rel='stylesheet' href='<?php echo BASE_URL; ?>src/main/webapp/styles/global.css' />

<?php if (!empty($styles)) foreach ($styles as $style): ?>
	<link rel='stylesheet' href='<?php echo BASE_URL."src/main/webapp/styles/".$style.".css"; ?>' />	
<?php endforeach; ?>

<?php if (!empty($stylesPHP)) foreach ($stylesPHP as $style): ?>
	<link rel='stylesheet' href='<?php echo BASE_URL."src/main/webapp/styles/".$style.".php"; ?>' />	
<?php endforeach; ?>
