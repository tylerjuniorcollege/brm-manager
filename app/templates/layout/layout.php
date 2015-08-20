<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<meta name="description" content="">
		<meta name="author" content="Tyler Junior College">

		<title>BRM Manager</title>

		<?= implode($data['css']['rendered'], "\n\t\t"); ?>

		<!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
		<!--[if lt IE 9]>
	  		<script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
	  		<script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
		<![endif]-->
	</head>
	<body class="fuelux">

		<div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
			  		<a class="navbar-brand" href="/brm">BRM Manager</a>
			  	</div>
			  	<?php if(isset($data['user'])): ?>
			  		<ul class="nav navbar-nav navbar-right">
          				<li class="dropdown">
          					<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false"><i class="fa fa-comments fa-lg fa-fw"></i><span class="badge" id="unreadCommentCount"><?= (count($data['unread_comments']) > 0 ? count($data['unread_comments']) : '' ); ?></span> <span class="caret"></span></a>
          					<ul class="dropdown-menu" role="menu">

          					</ul>
          				</li>
          			</ul>
      				<ul class="nav navbar-nav navbar-left">
      					<?php if($data['user']->hasAccess('create')): ?>
      						<li class="dropdown">
      							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">New <span class="caret"></span></a>
      							<ul class="dropdown-menu" role="menu">
      								<li><a href="/brm/create">Create a New BRM</a></li>
      								<li><a href="/image/upload">Upload a New BRM Email Image</a></li>
      							</ul>
      						</li>
      					<?php endif; ?>
      					<?php if($data['user']->hasAccess('admin')): ?>
      						<li class="dropdown">
      							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Admin <span class="caret"></span></a>
      							<ul class="dropdown-menu" role="menu">
      								<li><a href="/user">Admin Users</a></li>
      								<li><a href="/user/groups">Admin User Groups</a></li>
      								<li class="divider"></li>
      								<li><a href="/admin/er">Adminer</a></li>
      							</ul>
      					<?php endif; ?>
      					<li><a href="/logout">Logout</a></li>
      				</ul>
      			<?php endif; ?>
		  	</div>
		</div>
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<?php foreach($flash as $type => $message) {
						printf('<div class="alert alert-%s alert-dismissible fade in" role="alert"><button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>%s</div>', $type, $message);
					} ?>
				</div>
			</div>
<?= $data['content']; ?>
			<div class="row">
				<div class="col-md-12">
					<p>&copy; <?= date("Y"); ?> Tyler Junior College</p>
				</div>
		  	</div>
		</div>
		<div class="modal fade" id="hiddenModal" tabindex="-1" role="dialog" aria-labelledby="hiddenModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<h4 class="modal-title" id="hiddenModalLabel">ACTION!</h4>
					</div>
					<div class="modal-body">
						<iframe id="player" width="560" height="315" src="https://www.youtube.com/embed/ZTidn2dBYbY?autoplay=0&start=18" frameborder="0" allowfullscreen></iframe>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" style="display: none;" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
	<?= implode($data['js']['rendered'], "\n\t"); ?>
	</body>
</html>