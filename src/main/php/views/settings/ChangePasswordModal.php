<div id="changePassword" class="modal fade scrollbar_light">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Change password</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div id="changePassword_error" class="alert alert-danger" role="alert">
                	<button class="close" aria-label="close" onclick="changePassword_error()">
                		<span aria-hidden="true">&times;</span>
                	</button>
                	<h4 class="alert-heading">Error</h4>
                	<span id='error-msg'></span>
				</div>
				
				<div class="form-group">
					<label for="current_password">Current password</label>
					<input id="current_password" type="password" class="form-control" />
				</div>
				
				<div class="form-group">
					<label for="new_password">New password</label>
					<input id="new_password" type="password" class="form-control pass_input" />
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
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full submit" onclick="update_password(this)">Save</button>
			</div>
		</div>
	</div>
</div>