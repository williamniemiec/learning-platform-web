<?php 
header("Content-type: text/css; charset: UTF-8");
require('../../resources/config.php');
?>

.alert-birthdate {
	background-color:				var(--color-primary);
	background-image:				url(<?php echo BASE_URL; ?>assets/img/alerts/birthdate.png);
	background-size:				cover;
	color:							white;
	font-weight:					bold;
}