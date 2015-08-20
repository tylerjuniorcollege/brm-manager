<div class="page-header">
	<h3>BRM Emails 	<small class="pull-right"><button type="button" class="btn btn-default" data-toggle="collapse" data-target="#filters" aria-expanded="false" aria-controls="filters"><i class="fa fa-gear"></i></button></small></h3>
</div>
<div class="row collapse" id="filters">
	<div class="col-sm-12">
		<h4>Filters</h4>
	</div>
	<div class="col-sm-12 well">
		<div class="row">
			<div class="col-sm-3">
				<label class="checkbox checkbox-custom checkbox-inline"><input type="checkbox" id="filterHidden" class="filter" value="1">Hidden BRMs</label><br />
				<label class="checkbox checkbox-custom checkbox-inline"><input type="checkbox" id="filterUser" class="filter" value="1">Show My Created BRMs</label>
			</div>
			<div class="col-sm-9">
				
			</div>
		</div>
	</div>
</div>
<div class="row">
	<table class="table table-striped table-hover" id="list-table" style="width: 100%">
		<colgroup>
			<col class="col-md-1">
			<col class="col-md-3">
			<col class="col-md-1">
			<col class="col-md-1">
			<col class="col-md-1">
			<col class="col-md-1">
			<col class="col-md-1">
			<col class="col-md-1">
			<col class="col-md-1">
			<col class="col-md-1">
		</colgroup>
		<thead>
			<tr>
				<th rowspan="2">Id</th>
				<th rowspan="2">Title</th>
				<th rowspan="2">State</th>
				<th rowspan="2">Creator</th>
				<th rowspan="2">Launch Date</th>
				<th rowspan="2">Current Version</th>
				<th colspan="3">Approval Stats</th>
				<th rowspan="2">Delete</th>
			</tr>
			<tr>
				<th>Approval Needed</th>
				<th>Approved</th>
				<th>Denied</th>
			</tr>
		</thead>
		<tbody>
		</tbody>
	</table>
</div>
<form class="form modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelled="deleteModalLabel" aria-hidden="true" method="POST" action="/admin/brm/delete">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4>Are You Sure?</h4>
			</div>
			<div class="modal-body">
				<div class="alert alert-danger" role="alert">
					<strong>Danger!</strong> Choosing the red button below will completely remove the BRM Email from the system.<br>
					No takebacks, No recovery*, Nothing.<br>
					By clicking the red button, you agree to re-enter the data if you want the same BRM Email again.<br>
					<hr>
					<button type="submit" name="deleteoption" value="delete" class="btn btn-danger" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-delay="250" data-content="This will completely remove the BRM from the system">
						Delete The BRM</button>
					<button type="submit" name="deleteoption" value="changestate" class="btn btn-default" data-toggle="popover" data-placement="bottom" data-trigger="hover" data-delay="250" data-html="true" data-content="This will hide the BRM Email but can be found by using the &quot;Show Hidden&quot; filter option">
						Change the BRM State to "Hidden"</button>
				</div>
			</div>
			<div class="modal-footer">
				<button type="button" id="closeDelete" class="btn btn-default" data-dismiss="modal">Cancel</button>
			</div>		
		</div>
	</div>
</form>