<div class="modules" data-id_course = "<?php echo $course['id']; ?>">
            	<h2>Modules</h2>
            	<button class="btn_theme" data-toggle="modal" data-target="#addModule">Add module</button>
            	
            	<?php foreach ($modules as $module): ?>
            		<div class="module view_widget" data-id_module="<?php echo $module['id']; ?>">
            			<div class="module_actions">
                			<button class="btn_theme" onclick="show_addClass(this,<?php echo $module['id']; ?>)">Add class</button>
                			<button class="btn_theme" onclick="show_editModule(this,<?php echo $module['id']; ?>)">Edit Module</button>
                			<button class="btn_theme btn_theme_danger" onclick="deleteModule(this,<?php echo $module['id']; ?>)">Delete module</button>
            			</div>
                		<h3 class="moduleName"><?php echo $module['name']; ?></h3>
                		<div class="classes">
                			<?php foreach ($module['classes'] as $class): ?>
                    			<div class="class">
                    				<?php if ($class['type'] == 'video'): ?>
                        				<h5 class="class_title" data-id_video="<?php echo $class['video']['id']; ?>"><?php echo $class['video']['title']; ?></h5>
                        				<div class="class_actions">
                        					<button class="btn_theme" onclick="show_editVideo(this,<?php echo $class['video']['id']; ?>)">Edit</button>
                        					<button class="btn_theme btn_theme_danger" onclick="deleteClass(this,<?php echo $class['id']; ?>)">Delete</button>
                    					</div>
                    				<?php else: ?>
                    					<h5 class="class_title" data-id_quest="<?php echo $class['quest']['id']; ?>"><?php echo $class['quest']['question']; ?></h5>
                    					<div class="class_actions">
                        					<button class="btn_theme" onclick="show_editQuest(this,<?php echo $class['quest']['id']; ?>)">Edit</button>
                        					<button class="btn_theme btn_theme_danger" onclick="deleteClass(this,<?php echo $class['id']; ?>)">Delete</button>
                    					</div>
                    				<?php endif; ?>
                    			</div>
                			<?php endforeach; ?>
                		</div>
            		</div>
        		<?php endforeach; ?>
            </div>
            
            <!-- Modals -->
        	<?php $this->loadView("coursesManager/modal_addModule"); ?>
        	<?php $this->loadView("coursesManager/modal_editModule"); ?>
        	<?php $this->loadView("coursesManager/modal_addClass"); ?>
        	<?php $this->loadView("coursesManager/modal_editVideo"); ?>
        	<?php $this->loadView("coursesManager/modal_editQuest"); ?>
    	</div>