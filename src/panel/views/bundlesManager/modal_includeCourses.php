<div id="includeCourses" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Include courses</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div id="includeCourses_error" class="alert alert-danger" role="alert">
                	<button class="close" aria-label="close" onclick="includeCourses_error()">
                		<span aria-hidden="true">&times;</span>
                	</button>
                	<h4 class="alert-heading">Error</h4>
				</div>
				
				<div class="form-group">
            		<label>Courses</label><br />
            		
            		<div id="include_courses" class="cbx-list"></div>
            	</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full" onclick="update_bundle(<?php echo $id_bundle; ?>)">Save</button>
			</div>
		</div>
	</div>
</div>