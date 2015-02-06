<div id="currUser" style="display: none;"></div>
<div class="row page-header">
	<h3>Creating a new BRM Email</h3>
</div>
<div class="row">
	<form class="form-horizontal" id="create-brm" method="POST" enctype="multipart/form-data">
		<div class="form-group">
			<label for="name" class="col-sm-2 control-label">Name for BRM</label>
			<div class="col-sm-10">
				<input type="text" name="name" placeholder="Name ..." class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="description" class="col-sm-2 control-label">Description</label>
			<div class="col-sm-10">
				<input type="text" name="description" placeholder="Description ..." class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="description" class="col-sm-2 control-label">Template Id</label>
			<div class="col-sm-10">
				<input type="text" name="templateid" placeholder="Template ID ..." class="form-control">
			</div>
		</div>
		<div class="form-group">
			<label for="content" class="col-sm-2 control-label">Content</label>
			<div class="col-sm-10">
				<textarea name="content" placeholder="BRM Content Here ..." rows="25" class="form-control"></textarea>
			</div>
		</div>
		<!--<div class="form-group">
			<label for="header_imgs" class="col-sm-2 control-label">Header Images</label>
			<div class="col-sm-10">
				
			</div>
		</div>-->
		<div class="form-group">
			<label for="approval_list" class="col-sm-2 control-label">Approval/View List</label>
			<div class="col-sm-5">
				<div class="panel panel-success">
					<div class="panel-heading"><h3 class="panel-title">Current Users Tied To The BRM</h3></div>
					<ul class="list-group" id="currentUsers">

					</ul>
				</div>
			</div>
			<div class="col-sm-5">
				<input type="text" id="searchUsers" placeholder="Search Or Input Email Address ..." class="form-control">
				<br />
				<div class="panel panel-default" id="commonUsers">
					<div class="panel-heading"><h3 class="panel-title" id="searchResHeading">Common users specified before</h3></div>
					<div id="commonUserResults" class="list-group">
						<?php foreach($data['users'] as $user) {
							printf('<a href="#" class="user_action list-group-item" id="user-%s">%s &lt;%s&gt;</a>', $user->userid, $user->firstname . " " . $user->lastname, $user->email);
						} ?>
					</div>
				</div>
				<div class="panel panel-default" id="searchUsersResults" style="display:none;">
					<div class="panel-heading"><h3 class="panel-title">Search Results</h3></div>
					<div id="searchResults" class="list-group"></div>
				</div>
				<div class="panel panel-default" id="selectUserPermissions" style="display:none;">
					<div class="panel-heading"><h3 class="panel-title">Permissions for User: <span id="permUserSelect"></span></h3></div>
					<div class="panel-body">
						<label class="checkbox-inline">
							<input type="checkbox" id="permView" class="selectUserPermCheck" value="1">
							<span id="permLabelView" class="label label-info">View</span>
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" id="permApprove" class="selectUserPermCheck" value="2">
							<span id="permLabelApprove" class="label label-primary">Approve</span>
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" id="permEdit" class="selectUserPermCheck" value="4">
							<span id="permLabelEdit" class="label label-danger">Edit</span>
						</label>
					</div>
					<div class="panel-footer">
						<button type="button" id="cancelUserPerm" class="btn btn-danger pull-left">Cancel</button>
						<button type="button" id="userPermissions" class="btn btn-default pull-right">Add User to List</button>
						<div class="clearfix"></div>
					</div>
				</div>
				<div class="panel panel-default" id="addNewUser" style="display:none;">
					<div class="panel-heading"><h3 class="panel-title">Add A New User</h3></div>
					<div class="panel-body">
						<div class="form-group">
							<label for="firstName">First Name</label>
							<input type="text" id="newFirst" class="form-control" placeholder="First Name ...">
						</div>
						<div class="form-group">
							<label for="lastName">Last Name</label>
							<input type="text" id="newLast" class="form-control" placeholder="Last Name ...">
						</div>
						<div class="form-group">
							<label for="newPerm">Permissions</label>
							<div>
								<label class="checkbox-inline">
									<input type="checkbox" id="newPermView" class="selectUserPermCheck" value="1">
									<span class="label label-info">View</span>
								</label>
								<label class="checkbox-inline">
									<input type="checkbox" id="newPermApprove" class="selectUserPermCheck" value="2">
									<span class="label label-primary">Approve</span>
								</label>
								<label class="checkbox-inline">
									<input type="checkbox" id="newPermEdit" class="selectUserPermCheck" value="4">
									<span class="label label-danger">Edit</span>
								</label>
							</div>
						</div>
					</div>
					<div class="panel-footer">
						<button type="button" id="cancelAddUser" class="btn btn-danger pull-left">Cancel</button>
						<button type="button" id="addUserSubmit" class="btn btn-default pull-right">Add User</button>
						<div class="clearfix"></div>
					</div>
				</div>
			</div>
		</div>
		<hr />
		<div class="form-group">
			<div class="col-sm-3 col-sm-offset-9">
				<button type="submit" name="submit" value="save" class="btn btn-default">Save BRM</button>
				<button type="submit" name="submit" value="send" class="btn btn-primary">Submit BRM to List</button>
			</div>
		</div>
		<hr />
	</form>
</div>