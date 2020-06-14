<div class="container">
	<div class="view_panel">
		<h1 class="view_header">Admins manager</h1>
		<div class="view_content">
			<button class="btn_theme">New admin</button>
        	<table class="table table-hover">
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Privileges</th>
        				<th>Actions</th>
        			</tr>
        		</thead>
        		<tbody>
        			<tr data-id_admin="1">
        				<td class="admin_name">Admin name 1</td>
        				<td class="admin_privileges">root, supporter, manager</td>
        				<td class="actions">
        					<button class="btn_theme">Edit</button>
        					<button class="btn_theme btn_theme_danger">Delete</button>
        				</td>
        			</tr>
        			<tr data-id_admin="2">
        				<td class="admin_name">Admin name 2</td>
        				<td class="admin_privileges">supporter</td>
        				<td class="actions">
        					<button class="btn_theme">Edit</button>
        					<button class="btn_theme btn_theme_danger">Delete</button>
        				</td>
        			</tr>
        		</tbody>
        	</table>
        	
        	<!-- Modals -->
		</div>
	</div>
</div>