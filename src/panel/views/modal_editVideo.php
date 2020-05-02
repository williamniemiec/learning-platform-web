<div id="modal_editVideo" class="modal fade" role="alert">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Edit class</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="edit_classType_video_title">Title</label>
					<input id="edit_classType_video_title" class="form-control" />
				</div>
				<div class="form-group">
					<label for="edit_classType_video_description">Description</label>
					<textarea class="form-control" id="edit_classType_video_description"></textarea>
				</div>
				<div class="form-group">
					<label for="edit_classType_video_url">Youtube URL</label>
					<input id="edit_classType_video_url" class="form-control" />
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn btn-primary btn-block" onclick="editVideo(this)">Save</button>
			</div>
		</div>
	</div>
</div>