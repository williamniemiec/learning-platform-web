<div id="addClass" class="modal fade scrollbar_light" role="alert">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<div class="modal-header">
				<h3 class="modal-title">Add class</h3>
				<button class="close" data-dismiss="modal">&times;</button>
			</div>
			<div class="modal-body">
				<div class="form-group">
					<input id="classType_video" type="radio" name="classType" value="video" checked />
					<label for="classType_video">Video</label>
					
					<input id="classType_quest" type="radio" name="classType" value="quest" />
					<label for="classType_quest">Questionnaire</label>
				</div>
				<div class="classType_content_video">
					<div class="form-group">
						<label for="classType_video_title">Title</label>
						<input id="classType_video_title" class="form-control" />
					</div>
					<div class="form-group">
						<label for="classType_video_description">Description</label>
						<textarea class="form-control" id="classType_video_description"></textarea>
					</div>
					<div class="form-group">
						<label for="classType_video_url">Youtube URL</label>
						<input id="classType_video_url" class="form-control" />
					</div>
				</div>
				<div class="classType_content_quest">
					<div class="form-group">
						<label for="classType_quest_name">Question name</label>
						<input id="classType_quest_name" class="form-control" />
					</div>
					<div class="form-group">
						<label for="classType_quest_name">Questions</label>
						<input id="classType_quest_q1" class="form-control" placeholder="Question" /><br />
						<input id="classType_quest_q2" class="form-control" placeholder="Question" /><br />
						<input id="classType_quest_q3" class="form-control" placeholder="Question" /><br />
						<input id="classType_quest_q4" class="form-control" placeholder="Question" /><br />
					</div>
					<div class="form-group">
						<label for="answer">Answer</label>
						<select name="answer" class="form-control">
							<option value="1">Question 1</option>
							<option value="2">Question 2</option>
							<option value="3">Question 3</option>
							<option value="4">Question 4</option>
						</select>
					</div>
				</div>
			</div>
			<div class="modal-footer">
				<button class="btn_theme btn_full btn_theme_outline" onclick="addClass(this)">Add</button>
			</div>
		</div>
	</div>
</div>