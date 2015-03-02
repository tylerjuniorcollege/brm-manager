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
		<div class="row">
			<div class="form-group col-sm-6">
				<label for="description" class="col-sm-4 control-label">Template Id</label>
				<div class="col-sm-8">
					<input type="text" name="templateid" placeholder="Template ID ..." class="form-control">
				</div>
			</div>
			<div class="form-group col-sm-6">
				<label for="campaigns" class="col-sm-4 control-label">Campaign</label>
				<div class="col-sm-8" id="campaign-select">
					<select name="campaigns" class="form-control" id="campaign-list" placeholder="Campaign">
						<option></option>
						<option value="new">New Campaign</option>
						<option value="none">No Assigned Campaign</option>
						<optgroup label="Existing Campaigns">
						<?php foreach($data['campaigns'] as $campaign): ?>
							<option value="<?=$campaign->id;?>"><?=$campaign->name; ?></option>
						<?php endforeach; ?>
						</optgroup>
					</select>
				</div>
			</div>
		</div>
		<div class="col-sm-10 col-sm-offset-2" style="padding: 0">
			<div class="panel panel-primary collapse" data-toggle="collapse" id="campaignCreateForm">
				<div class="panel-heading"><h3 class="panel-title">New Campaign</h3></div>
				<div class="panel-body">
					<div class="form-group">
						<label for="newCampaignName" class="col-sm-2 control-label">Campaign Name</label>
						<div class="col-sm-10">
							<input type="text" name="campaign-name" placeholder="Name" class="form-control">
						</div>
					</div>
					<div class="form-group">
						<label for="newCampaignName" class="col-sm-2 control-label">Campaign Description</label>
						<div class="col-sm-10">
							<input type="text" name="campaign-description" placeholder="Description" class="form-control">
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="col-sm-10 col-sm-offset-2 panel-group" id="aOpts" style="padding:0" role="tablist" aria-multiselectable="true">
			<div class="panel panel-default" id="requestDetails">
				<div class="panel-heading" role="tab" id="reqHeading">
					<h3 class="panel-title">
						<a data-toggle="collapse" data-parent="#aOpts" href="#reqCollapse" aria-expanded="false" aria-controls="reqCollapse">
							Request Details
						</a>
					</h3>
				</div>
				<div class="panel-collapse collapse" id="reqCollapse" role="tabpanel" aria-labelledby="reqHeading">
					<div class="panel-body">
						<div class="form-group">
							<label for="requestdate" class="col-sm-2 control-label">Requested Date</label>
							<div class="col-sm-10">
								<div class="input-group date" id="requesteddate">
									<input type="text" class="form-control" name="requestdate" placeholder="<?=date('m/d/Y g:i A'); ?>">
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="requestuser" class="col-sm-2 control-label">Requesting User</label>
							<div class="col-sm-10">
								<input type="text" name="requestuser" class="form-control" id="requestuser" data-provide="typeahead" autocomplete="off" placeholder="Email Address">
							</div>
						</div>
						<div class="form-group">
							<label for="requestdepartment" class="col-sm-2 control-label">Requesting Department</label>
							<div class="col-sm-10">
								<select name="department" class="form-control" id="departmentSelect" placeholder="Department">
									<option></option>
									<?php foreach($data['departments'] as $dept): ?>
									<option value="<?=$dept->id; ?>"><?=$dept->name; ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="panel panel-default" id="launchSettings">
				<div class="panel-heading" role="tab" id="launchHeading">
					<h3 class="panel-title">
						<a data-toggle="collapse" data-parent="#aOpts" href="#launchCollapse" aria-expanded="false" aria-controls="launchCollapse">
							Launch Details
						</a>
					</h3>
				</div>
				<div class="panel-collapse collapse" id="launchCollapse" role="tabpanel" aria-labelledby="launchHeading">
					<div class="panel-body">
						<div class="form-group">
							<label for="launchdate" class="col-sm-2 control-label">Launch Date</label>
							<div class="col-sm-10">
								<div class="input-group date" id="launchdate">
									<input type="text" class="form-control" name="launchdate" placeholder="<?=date('m/d/Y g:i A'); ?>">
									<span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span></span>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="population" class="col-sm-2 control-label">Population</label>
							<div class="col-sm-10">
								<input type="text" name="population" class="form-control" id="population" placeholder="Population">
							</div>
						</div>
						<div class="form-group">
							<label for="requestdepartment" class="col-sm-2 control-label">Email List Name</label>
							<div class="col-sm-10">
								<input type="text" name="emaillistname" class="form-control" placeholder="List Name">
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label for="content" class="col-sm-2 control-label">Content</label>
			<div class="col-sm-10">
				<textarea name="content" placeholder="BRM Content Here ..." rows="25" class="form-control" id="contentInput"></textarea>
				<iframe id="previewContent" style="display:none; height: 300px;" class="col-sm-12"></iframe>
				<span class="help-block"><button class="btn btn-primary" id="previewBtn" type="button">Render Preview</button><button class="btn btn-primary" id="editBtn" type="button" style="display:none; margin-top: 5px;">Show Edit</button></span>
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
					<ul class="list-group" id="currentUsers"></ul>
				</div>
			</div>
			<div class="col-sm-5">
				<input type="text" id="searchUsers" placeholder="Search Or Input Email Address ..." class="form-control" autocomplete="off">
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
							<input type="checkbox" id="permApprove" class="selectUserPermCheck" value="2">
							<span id="permLabelApprove" class="label label-primary">Approve</span>
						</label>
						<label class="checkbox-inline">
							<input type="checkbox" id="permEdit" class="selectUserPermCheck" value="4">
							<span id="permLabelEdit" class="label label-warning">Edit</span>
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
									<input type="checkbox" id="newPermApprove" class="selectUserPermCheck" value="2">
									<span class="label label-primary">Approve</span>
								</label>
								<label class="checkbox-inline">
									<input type="checkbox" id="newPermEdit" class="selectUserPermCheck" value="4">
									<span class="label label-warning">Edit</span>
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