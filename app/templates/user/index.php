<div class="page-header"><h3>User Admin<small class="pull-right"><a href="/user/add" class="btn btn-primary">Add New User</a></small></h3></div>
<div class="row">
	<div class="col-sm-12">
		<table class="table table-striped table-hover" id="userTable">
			<colgroup>
				<col class="col-sm-1">
				<col class="col-sm-1">
				<col class="col-sm-2">
				<col class="col-sm-2">
				<col class="col-sm-3">
				<col class="col-sm-1">
				<col class="col-sm-1">
				<col class="col-sm-1">
			</colgroup>
			<thead>
				<tr>
					<th>Id</th>
					<th>Email</th>
					<th>First Name</th>
					<th>Last Name</th>
					<th>Permissions</th>
					<th># of BRMs Created</th>
					<th>Created</th>
					<th>Edit</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach ($data['users'] as $user) {
				$permissions = \BRMManager\Permissions::userCan((int) $user->permissions);
				printf("\n\t\t\t\t<tr><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td>%s</td><td><a href=\"%s\">Edit</a></td></tr>",
					$user->id,
					$user->email,
					$user->firstname,
					$user->lastname,
					implode(', ', $permissions),
					$user->brms()->count(),
					date('F j, Y g:i:s', $user->created),
					$user->editLink()
				);
			} ?>
			</tbody>
		</table>
	</div>
</div>