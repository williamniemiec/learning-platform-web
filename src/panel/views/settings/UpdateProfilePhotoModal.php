<div id="changeProfilePhoto" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Profile photo</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div id="changePhoto_error" class="alert alert-danger" role="alert">
                	<button class="close" aria-label="close" onclick="changePhoto_error()">
                		<span aria-hidden="true">&times;</span>
                	</button>
                	<h4 class="alert-heading">Error</h4>
                	Invalid photo
				</div>
				
				<div class="form-group">
					<label for="profile_photo">Profile photo</label>
					<input id="profile_photo" type="file" class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full" onclick="update_profilePhoto(this)">Save</button>
			</div>
		</div>
	</div>
</div>