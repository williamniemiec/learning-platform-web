<script src='<?php echo BASE_URL; ?>lib/jquery/jquery-3.4.1.min.js'></script>
<script src='<?php echo BASE_URL; ?>lib/bootstrap/bootstrap.bundle.min.js'></script>
<script src='<?php echo BASE_URL; ?>lib/scrollbar_light/jquery.mCustomScrollbar.concat.min.js'></script>
<script src='<?php echo BASE_URL; ?>lib/scrollbar_light/scrollbar_light.js'></script>
<script src='<?php echo BASE_URL; ?>assets/scripts/notifications.js'></script>
<script>var BASE_URL = "<?php echo BASE_URL; ?>"</script>
<script src='<?php echo BASE_URL; ?>assets/scripts/global.js'></script>

<?php if (!empty($scripts)) foreach ($scripts as $script): ?>
	<script src='<?php echo BASE_URL."assets/scripts/".$script.".js"; ?>'></script>	
<?php endforeach; ?>