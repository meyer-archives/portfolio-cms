<div id="admin-bar">
<?php if( !empty( $current_project ) ) { ?>
	<h2><?php echo $current_project["title"]; ?></h2>
	<p>Admin Bar</p>
<?php } else {

$project_array = array();

foreach( $projects_by_id as $pid => $project ) {
	$order = $project["order"] * 100 + $pid;
	$project_array[$order] = "<li><a href='{$project["url"]}'>{$project["title"]}</a></li>";
}

echo implode( "\n", $project_array );

?>

<?php } ?>
</div>