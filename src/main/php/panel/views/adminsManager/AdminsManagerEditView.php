<div class="container">
   <!-- Shows error message if an error occurs -->
	<?php if ($error): ?>
		<?php $this->load_view("alerts/alert_error", array('msg' => $msg)); ?>
	<?php endif; ?>
	
	<div class="view_panel">
		<h1 class="view_header">New admin</h1>
		<div class="view_content">
        	<form method="POST">
        		<!-- Authorization -->
        		<div class="form-group">
            		<label for="authorization">Authorization*</label>
            		<select id="authorization" name="authorization" class="form-control">
            			<?php foreach ($authorizations as $authorization): ?>
            				<option value="<?php echo $authorization->getId(); ?>" <?php echo $authorization->getId() == $admin->getAuthorization()->getId() ? "selected" : ""; ?>>
            					<?php echo ucfirst(strtolower($authorization->getName())); ?>
        					</option>
            			<?php endforeach; ?>
            		</select>
            	</div>

            	<!-- Email -->
            	<div class="form-group">
            		<label for="register_email">Email*</label>
            		<input id="register_email" type="email" name="email" placeholder="Email" value="<?php echo $admin->getEmail(); ?>" class="form-control" required />
            	</div>
            	
            	<!-- Password -->
            	<div class="form-group">
            		<label for="register_pass">Password</label>
            		<input id="register_pass" type="password" name="password" placeholder="Password" class="form-control pass_input" />
            		<div id="pass_strength_box">
            			<h5 class='pass_strength_header'>Password strengh</h5>
            			<div class='progress'>
                        	<div class='pass_strength_bar'></div>
                    	</div>
                        <ul class='pass_strength'>
                            <li id='pass_length' data-length='8'>
                                Password length (minimum: 8 characters)
                                <span class='pass_strength_icon'></span>
                            </li>
                            <li id='pass_numCharact'>
                                Numbers and Characters
                            <span class='pass_strength_icon'></span>
                            </li>
                            <li id='pass_specCharact'>
                                Special characters
                                <span class='pass_strength_icon'></span>
                            </li>
                            <li id='pass_ulCharact'>
                                Uppercase and lowercase letters
                                <span class='pass_strength_icon'></span>
                            </li>
                        </ul>
            		</div>
            	</div>
            	
            	<!-- Register button -->
            	<div class="form-group">
            		<input type="submit" value="Update" class="form-control btn_theme btn_full" />
            	</div>
            </form>
        </div>
	</div>
</div>