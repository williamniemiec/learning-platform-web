<div id="includeModules" class="modal fade">
	<div class="modal-dialog modal-dialog-centered modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Include modules</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div id="includeModules_error" class="alert alert-danger" role="alert">
                	<button class="close" aria-label="close" onclick="includeModules_error()">
                		<span aria-hidden="true">&times;</span>
                	</button>
                	<h4 class="alert-heading">Error</h4>
                	<span id="error-msg"></span>
				</div>
				
				<div class="form-group">
            		<label>Modules</label><br />
            		
            		<div id="include_modules" class="cbx-list"></div>
            	</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full" onclick="update_course(<?php echo $id_course; ?>)">Save</button>
			</div>
		</div>
	</div>
</div>