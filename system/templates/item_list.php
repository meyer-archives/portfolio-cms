<?php if( !defined( "SITE_PATH" ) ) die( "Can't touch this." ); ?>
<div id="secondary">
	<ul id="project-link-list">
	<?php

	foreach( $projects as $pid => $project ) {
		if( $pid > 0 || ( $pid == 0 && !empty( $items[$pid] ) ) ) {

	?>
		<li id="goto-project-<?php echo $pid; ?>">
			<a href="#project-<?php echo $pid; ?>">
				<span class="title"><?php echo $project["title"]; ?></span>
				<span class="count"><span><?php echo !empty( $items[$pid] ) ? count( $items[$pid] ) : '0'; ?></span></span>
			</a>
		</li>
	<?php
		}
	}
	?>
	</ul>
	<div id="project-add">
		<form action="<?php echo ADMIN_URL ?>project/add" method="post" accept-charset="utf-8">
			<input type="text" name="project_title" value="">
			<p><button type="submit">Add New Project</button> <a class="cancel" href="#">Cancel</a></p>
		</form>
	</div>
</div><!-- #secondary -->
<div id="primary">
	<ul id="project-list">
<?php

$oddeven = "";

foreach( $projects as $pid => $project ) {
if( $pid > 0 || ( $pid == 0 && !empty( $items[$pid] ) ) ) {

$oddeven = empty($oddeven) ? " odd" : "";

?>
	<li class="project<?php echo $oddeven; ?>" id="project-<?php echo $pid; ?>">
		<div class="project-container">
			<div class="project-header">
				<h2><?php echo $project["title"]; ?></h2>
				<p class="project-options">
					<a class="edit" href="<?php echo ADMIN_URL . "project/$pid/edit.html" ?>">Edit</a>
					<a class="delete" href="<?php echo ADMIN_URL . "project/$pid/delete.html" ?>">Delete</a>
				</p>
			</div>
		<?php if( $pid > 0 ) { ?>
		<?php } ?>
			<ul class="item-list">
<?php
if( !empty( $items[$pid] ) ) {
foreach( $items[$pid] as $item ){

?>
				<li class="item" id="item-<?php echo $item["id"]; ?>">
					<div class="thumbnail" style="background-image: url(<?php echo IMAGE_URL."image".$item["id"]."_50.jpg"; ?>)"></div>
					<div class="item-details">
						<h3><?php echo $item["title"] . "(" . $item["order"] . ")"; ?></h3>
						<?php if( !empty($item["desc"]) ){ echo "<p class='desc'>".$item["desc"]."</p>"; } ?>
						<p class="item-options">
							<a class="edit" href="<?php echo ADMIN_URL ."item/". $item["id"] ."/edit.html" ?>">Edit</a>
							<a class="delete" href="<?php echo ADMIN_URL ."item/". $item["id"] ."/delete.html"; ?>">Delete</a>
						</p>
					</div>
				</li>
<?php }
} ?>
			</ul>
<?php if( $pid > 0 ) { ?>
			<div class="item-add">
				<form enctype="multipart/form-data" action="<?php echo ADMIN_URL; ?>item/add" method="POST">
					<h4>Add a new item to this project</h4>
					<input type="hidden" name="item_project" value="<?php echo $pid; ?>">
					<input name="image_original" type="file" />
					<p><button type="submit">Upload</button> <a class="cancel" href="#">Cancel</a></p>
				</form>
			</div>
<?php
}
?>
		</div><!-- .project-container -->
	</li><!-- #project-<?php echo $pid; ?>.project -->
<?php }
} // foreach( $p ) ?>
	</ul>
</div><!-- #primary -->