<div class="container">
	<div class="view_panel">
		<h1 class="view_header">Admins manager</h1>
		<div class="view_content">
			<a href="<?php echo BASE_URL."admins/new"; ?>" class="btn_theme">New admin</a>
        	<table class="table table-hover">
        		<thead>
        			<tr>
        				<th>Name</th>
        				<th>Authorization</th>
        				<th>Actions</th>
        			</tr>
        		</thead>
        		<tbody>
        			<?php foreach ($admins as $admin): ?>
            			<tr>
            				<td class="admin_name"><?php echo $admin->get_name(); ?></td>
            				<td class="admin_privileges"><?php echo ucfirst(strtolower($admin->getAuthorization()->get_name())); ?></td>
            				<td class="actions">
            					<a href="<?php echo BASE_URL."admins/edit/".$admin->get_id(); ?>" class="btn_theme">Edit</a>
            				</td>
            			</tr>
        			<?php endforeach; ?>
        		</tbody>
        	</table>
		</div>
	</div>
</div>
