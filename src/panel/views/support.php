<div class="container">
	<div class="view_panel">
		<h1 class="view_header">
			Support
		</h1>
		<div class="view_content">
			<div class="support_actions">
			<div class="form-group">
					<label for="support_search">Search</label>
					<input name="support_search" type="text" class="form-control" placeholder="Search" />
				</div>
				<div class="form-group">
					<label for="support_date">Date</label>
    				<input name="support_date" type="datetime-local" class="form-control" />
				</div>
				<div class="form-group">
    				<label for="support_status">Status</label>
    				<select id="support_status" class="form-control">
    					<option value="1" selected>Open</option>
    					<option value="0">Closed</option>
    				</select>
				</div>
			</div>
            <table class="table table-hover table-stripped">
            	<thead>
            		<tr">
            			<th>Title</th>
            			<th>Category</th>
            			<th class="support_date">Date of publication</th>
            			<th class="support_date_lastUpdate">Last update</th>
            			<th>Created by</th>
            			<th>Status</th>
            		</tr>
            	</thead>
            	<tbody>
            		<tr>
            			<td><a href="<?php echo BASE_URL; ?>support/open">Some title</a></td>
            			<td>Sugestion</td>
            			<td>25/05/2020</td>
            			<td>25/05/2020 22:52</td>
            			<td>Creator's name</td>
            			<td>Open</td>
            		</tr>
            		<tr>
            			<td>Some title</td>
            			<td>Sugestion</td>
            			<td>25/05/2020</td>
            			<td>25/05/2020 10:21</td>
            			<td>Creator's name</td>
            			<td>Closed</td>
            		</tr>
            	</tbody>
            </table>
        </div>
    </div>
</div>|