<div id="admin-bar">
	<ul id="ab-project-list">
<?php

$project_array = array();

foreach( $projects_by_id as $pid => $project ) {
	$order = $project["order"] * $project["order"] + $pid;
	$project_array[$order] = "\t\t<li><a data-project-id='{$project["id"]}' href='{$project["url"]}'>{$project["title"]}</a></li>";
}

echo implode( "\n", $project_array );

?>
	<ul>
</div>