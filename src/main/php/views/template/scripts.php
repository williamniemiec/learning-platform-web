<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/lib/jquery/jquery-3.4.1.min.js'></script>
<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/lib/bootstrap/bootstrap.bundle.min.js'></script>
<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/lib/scrollbar_light/jquery.mCustomScrollbar.concat.min.js'></script>
<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/lib/scrollbar_light/scrollbar_light.js'></script>
<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/lib/password_strength/ps_script.js'></script>
<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/lib/chartjs/Chart.min.js'></script>
<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/scripts/global.js'></script>
<script>var BASE_URL = "<?php echo BASE_URL; ?>"</script>
<script type="text/javascript" src='<?php echo BASE_URL; ?>src/main/webapp/scripts/NotificationScript.js'></script>

<?php if (!empty($scripts)) foreach ($scripts as $script): ?>
	<script type="text/javascript" src='<?php echo BASE_URL."src/main/webapp/scripts/".$script.".js"; ?>'></script>	
<?php endforeach; ?>

<?php if (!empty($scriptsModule)) foreach ($scriptsModule as $script): ?>
	<script type='module' src='<?php echo BASE_URL."src/main/webapp/scripts/".$script.".js"; ?>'></script>	
<?php endforeach; ?>
