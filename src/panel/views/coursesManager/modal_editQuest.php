<div id="modal_editQuest" class="modal fade scrollbar_light" role="alert">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Edit class</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<label for="edit_classType_quest_name">Question name</label>
					<input id="edit_classType_quest_name" class="form-control" />
				</div>
				<div class="form-group">
					<label>Questions</label>
					<input id="edit_classType_quest_q1" class="form-control" placeholder="Question" /><br />
					<input id="edit_classType_quest_q2" class="form-control" placeholder="Question" /><br />
					<input id="edit_classType_quest_q3" class="form-control" placeholder="Question" /><br />
					<input id="edit_classType_quest_q4" class="form-control" placeholder="Question" /><br />
				</div>
				<div class="form-group">
					<label for="edit_answer">Answer</label>
					<select id="edit_answer" class="form-control">
						<option value="1">Question 1</option>
						<option value="2">Question 2</option>
						<option value="3">Question 3</option>
						<option value="4">Question 4</option>
					</select>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full btn_theme_outline" onclick="editQuest(this)">Save</button>
			</div>
		</div>
	</div>
</div>