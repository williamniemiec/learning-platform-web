<script src='<?php echo BASE_URL; ?>resources/scripts/jquery-3.4.1.min.js'></script>
<script src='<?php echo BASE_URL; ?>resources/scripts/bootstrap.bundle.min.js'></script>
<script src='<?php echo BASE_URL; ?>resources/scripts/jquery.mCustomScrollbar.concat.min.js'></script>
<script src='<?php echo BASE_URL; ?>resources/scripts/scrollbar_light.js'></script>
<script>var BASE_URL = "<?php echo BASE_URL; ?>"</script>
<script src='<?php echo BASE_URL; ?>resources/scripts/global.js'></script>

<?php if (!empty($scripts)) foreach ($scripts as $script): ?>
	<script src='<?php echo BASE_URL."resources/scripts/".$script.".js"; ?>'></script>	
<?php endforeach; ?>