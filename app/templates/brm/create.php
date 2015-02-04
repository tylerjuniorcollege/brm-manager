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
		<div class="form-group">
			<label for="header_imgs" class="col-sm-2 control-label">Header Images</label>
			<div class="col-sm-10">
				<div class="fileinput fileinput-new" data-provides="fileinput">
					<div class="fileinput-btns">
						<span class="btn btn-default btn-file">
							<span class="fileinput-new">Select Image</span>
							<span class="fileinput-exists">Change</span>
							<input type="file" name="header_image">
						</span>
						<a href="#" class="btn btn-danger fileinput-exists" data-dismiss="fileinput">Remove Image</a>
					</div>
					<div class="fileinput-preview thumbnail" data-trigger="fileinput" style="width:300px; height: 150px; margin-top:5px;"></div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<label for="approval_list" class="col-sm-2 control-label">Approval/View List</label>
			<div class="col-sm-4">
				<ul class="list-group">

				</ul>
			</div>
			<div class="col-sm-6">
				
			</div>
		</div>
		<hr />
		<div class="form-group">
			<div class="col-sm-2 col-sm-offset-10">
				<button type="submit" class="btn btn-primary">Submit New BRM</button>
			</div>
		</div>
		<hr />
	</form>
</div>