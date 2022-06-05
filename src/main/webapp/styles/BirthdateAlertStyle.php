<?php 
header("Content-type: text/css; charset: UTF-8");
require('../../../../src/main/php/config.php');
?>

.alert-birthdate {
	background-color:				var(--color-primary);
	background-image:				url(<?php echo BASE_URL; ?>src/main/web/images/alerts/birthdate.png);
	background-size:				cover;
	color:							white;
	font-weight:					bold;
}