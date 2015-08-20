<div class="page-header"><h3>Migration Admin</h3></div>
<div class="row">
	<div class="col-sm-12">
		<table class="table table-striped table-hover" id="userTable">
			<thead>
				<tr>
					<th>Migration</th>
					<th>Run Previously</th>
					<th>Run</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach($data['table'] as $table_elements): ?>
				<tr>
					<td><?= $table_elements['name']; ?></td>
					<td><?= ($table_elements['count'] != false ? '<i class="fa fa-check-circle"></i>' : '<i class="fa fa-times-circle"></i>'); ?></td>
					<td><?= ($table_elements['count'] != false ? '' : $table_elements['link']); ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	</div>
</div>