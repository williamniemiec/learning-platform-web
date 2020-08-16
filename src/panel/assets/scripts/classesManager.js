$(() => {	
	$("input:radio[name='rdo-class-type']").change((e) => {
		let formHTML = ''
		
		$("#class-form-info").fadeOut('fast')

		if (e.target.value == 'v') {
			formHTML = `
				<input type="hidden" name="type" value="v" />
            	<div class="form-group">
            		<label for="title">Title*</label>
            		<input id="title" type="text" name="title" placeholder="Title" class="form-control" required />
            	</div>
            	
            	<div class="form-group">
            		<label for="description">Description</label>
            		<textarea id="description" name="description" placeholder="Description" class="form-control"></textarea>
            	</div>
            	
            	<div class="form-group">
            		<label for="videoID">VideoID*</label>
            		<input id="videoID" type="text" name="videoID" placeholder="VideoID (YouTube URL - content to the right of 'v=')" class="form-control" pattern="[0-9A-z]{11}" required />
            	</div>
            	
            	<div class="form-group">
            		<label for="length">Length</label>
            		<input id="length" name="length" type="number" placeholder="Video length (integer)" class="form-control" />
            	</div>
			`
		}
		else if (e.target.value == 'q') {
			formHTML = `
				<input type="hidden" name="type" value="q" />
            	<div class="form-group">
            		<label for="question">Question*</label>
            		<input id="name" type="text" name="question" placeholder="Question" class="form-control" required />
            	</div>
            	
            	<div class="form-group">
            		<label for="description">Question 1</label>
            		<input id="q1" type="text" name="q1" placeholder="Question 1" class="form-control" required />
            	</div>
            	
            	<div class="form-group">
            		<label for="description">Question 2</label>
            		<input id="q2" type="text" name="q2" placeholder="Question 2" class="form-control" required />
            	</div>
            	
            	<div class="form-group">
            		<label for="description">Question 3</label>
            		<input id="q3" type="text" name="q3" placeholder="Question 3" class="form-control" required />
            	</div>
            	
            	<div class="form-group">
            		<label for="description">Question 4</label>
            		<input id="q4" type="text" name="q4" placeholder="Question 4" class="form-control" required />
            	</div>
            	
            	<div class="form-group">
            		<label for="answer">Answer*</label>
            		<select class="form-control">
            			<option value='1' selected>1</option>
            			<option value='2'>2</option>
            			<option value='3'>3</option>
            			<option value='4'>4</option>
            		</select>
            	</div>
			`
		}
		
		$("#class-form-info").html(formHTML).fadeIn('fast')
	})
	
	$("input:radio[name='rdo-class-type']:checked").trigger('change')
})