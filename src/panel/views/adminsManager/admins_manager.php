<div class="container">
	<div class="view_panel">
		<h1 class="view_header">Admins manager</h1>
		<div class="view_content">
			<a href="<?php echo BASE_URL."admins/new"; ?>" class="btn_theme">New admin</a>
        	<table class="table table-hover">
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Privileges</th>
        				<th>Actions</th>
        			</tr>
        		</thead>
        		<tbody>
        			<?php foreach ($admins as $admin): ?>
            			<tr>
            				<td class="admin_name">Admin name 1</td>
            				<td class="admin_privileges">root, supporter, manager</td>
            				<td class="actions">
            					<button class="btn_theme">Edit</button>
            					<button class="btn_theme btn_theme_danger">Delete</button>
            				</td>
            			</tr>
        			<?php endforeach; ?>
        		</tbody>
        	</table>
		</div>
	</div>
</div>
