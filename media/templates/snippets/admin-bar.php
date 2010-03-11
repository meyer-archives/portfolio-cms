<div id="admin-bar">
<?php if( !empty( $current_project ) ) { ?>
	<h2><?php echo $current_project["title"]; ?></h2>
	<ul>
<?php

if( !empty( $items_by_project[$current_project["id"]] ) ) {

foreach( $items_by_project[$current_project["id"]] as $item ) {

?>

<?php

}
}

?>
	</ul>
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