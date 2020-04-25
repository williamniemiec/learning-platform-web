<?php foreach ($modules as $module): ?>
	<div class="module">
		<h4><?php echo $module['name']; ?></h4>
		<div class="module_classes">
			<?php foreach ($module['classes'] as $class): ?>
				<div class="module_class">
					<?php if ($class['type'] == 'video'): ?>
						<a href="<?php echo BASE_URL."courses/class/".$class['id']; ?>">
							<?php echo $class['video']['title']; ?>
						</a>
						<?php if ($class['watched'] > 0): ?>
							<small class="class_watched">Watched</small>
						<?php endif; ?>
					<?php else: ?>
						<a href="<?php echo BASE_URL."courses/class/".$class['id']; ?>">
							Questionnaire
						</a>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
<?php endforeach; ?>