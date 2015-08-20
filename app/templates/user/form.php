<div class="page-header"><h3>Add/Edit User</h3></div>
<div class="row">
	<form class="form-horizontal" id="user-form" method="POST" enctype="multipart/form-data">
		<div class="form-group">
			<label class="col-sm-2 control-label" for="id">Id</label>
			<div class="col-sm-10">
				<p class="form-control-static"><?=(is_null($data['user_data']->id) ? '&lt;Not Set&gt;' : $data['user_data']->id); ?></p>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="email">Email</label>
			<div class="col-sm-10">
				<input type="email" name="email" class="form-control" placeholder="Email Address" value="<?=(!is_null($data['user_data']->email) ? $data['user_data']->email : ''); ?>" required>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="firstname">First Name</label>
			<div class="col-sm-10">
				<input type="text" name="firstname" class="form-control" placeholder="First Name" value="<?=(!is_null($data['user_data']->firstname) ? $data['user_data']->firstname : ''); ?>" required>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="lastname">Last Name</label>
			<div class="col-sm-10">
				<input type="text" name="lastname" class="form-control" placeholder="Last Name" value="<?=(!is_null($data['user_data']->lastname) ? $data['user_data']->lastname : ''); ?>" required>
			</div>
		</div>
		<div class="form-group">
			<label class="col-sm-2 control-label" for="permissions">Permissions</label>
			<div class="col-sm-10">
				<?php if(!is_null($data['user_data']->permissions)) {
					$perm_arr = \BRMManager\Permissions::userCan((int) $data['user_data']->permissions);
				} else {
					$perm_arr = array();
				} ?>
				<label class="checkbox-inline checkbox-custom" data-initialize="checkbox">
					<input type="checkbox" class="userPermission" value="16"<?=(in_array('admin', $perm_arr) ? ' checked' : ''); ?>><span class="label label-danger">Admin</span>
				</label>
				<label class="checkbox-inline checkbox-custom" data-initialize="checkbox">
					<input type="checkbox" class="userPermission" value="8"<?=(in_array('create', $perm_arr) ? ' checked' : ''); ?>><span class="label label-info">Create</span>
				</label>
				<label class="checkbox-inline checkbox-custom" data-initialize="checkbox">
					<input type="checkbox" class="userPermission" value="4"<?=(in_array('edit', $perm_arr) ? ' checked' : ''); ?>><span class="label label-warning">Edit</span>
				</label>
				<label class="checkbox-inline checkbox-custom" data-initialize="checkbox">
					<input type="checkbox" class="userPermission" value="2"<?=(in_array('approve', $perm_arr) ? ' checked' : ''); ?>><span class="label label-primary">Approve</span>
				</label>
				<input type="hidden" name="permissions" id="userPermissions">
			</div>
		</div>
		<div class="form-group">
			<div class="col-sm-10 col-sm-offset-2">
				<button type="submit" class="btn btn-primary">Save</button>
			</div>
		</div>
	</form>
</div>