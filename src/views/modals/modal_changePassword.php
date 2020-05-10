<div id="changePassword" class="modal fade">
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
                	Current password is incorrect
				</div>
				
				<div class="form-group">
					<label for="current_password">Current password</label>
					<input id="current_password" type="password" class="form-control" />
				</div>
				
				<div class="form-group">
					<label for="new_password">New password</label>
					<input id="new_password" type="password" class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full" onclick="update_password(this)">Save</button>
			</div>
		</div>
	</div>
</div>