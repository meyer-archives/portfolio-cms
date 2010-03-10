<!DOCTYPE HTML>
<html>
<head>
	<title><?php echo $sitename . ( $page_title ? " &rsaquo; " . $page_title : "" ); ?></title>
	<meta http-equiv="Content-Type" content="text/html;charset=utf-8">
	<link rel="stylesheet" type="text/css" href="<?php echo MEDIA_URL ?>css/site.css" media="all">

	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery-1.4.2.min.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery-ui.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.timeago.js"></script>

<?php if( logged_in() ) { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo SYS_MEDIA_URL ?>uploadify/uploadify.css" media="all">
	<link rel="stylesheet" type="text/css" href="<?php echo SYS_MEDIA_URL ?>css/admin.css" media="all">

	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.idle-timer.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.scrollTo.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.localscroll.min.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.uniform.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.timers.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.quicksand.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.dataset.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/jquery.blockUI.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>uploadify/swfobject.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>uploadify/jquery.uploadify.v2.1.0.min.js"></script>
	<script type="text/javascript" src="<?php echo SYS_MEDIA_URL ?>js/admin.js"></script>

<?php } ?>

	<script type="text/javascript" src="<?php echo MEDIA_URL ?>js/site.js"></script>

<!--
	<script type="text/javascript" src="http://use.typekit.com/bta0via.js"></script>
	<script type="text/javascript">try{Typekit.load();}catch(e){}</script>
-->
</head>
<body id="<?php echo $body_id; ?>-page" class="<?php echo logged_in() ? "logged-in" : "logged-out" ?>">
	<div id="wrapper">
		<div id="header">
			<div class="container">
				<h1>Header</h1>
			</div><!-- .container -->
		</div><!-- #header -->
		<div id="content">
			<div class="container">
				<div id="project-add">
					<form action="<?php echo API_URL ?>project/add" method="post" accept-charset="utf-8">
						<input type="text" name="project_title" value="">
						<p><button type="submit">Add New Project</button> <a class="cancel" href="#">Cancel</a></p>
					</form>
				</div>