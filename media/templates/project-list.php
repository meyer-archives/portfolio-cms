<?php if( !defined( "SITE_PATH" ) ) die( "Can't touch this." ); ?>
<ul class="project-list">
<?php

foreach( $projects_by_id as $pid => $project ){
	echo "\t<li id='project-$pid'>Project $pid - {$project["title"]}\n";
	echo "\t\t<ul class='item-list'>\n";
	if( !empty( $items_by_project[$pid] ) ) {
		foreach( $items_by_project[$pid] as $item ){
			echo "\t\t\t<li>{$item["title"]}</li>\n";
		}
	} else {
		echo "\t\t\t<li class='empty'>Project $pid ({$project["title"]}) is empty</li>";
	}
	echo "\t\t</ul>\n";
	echo "\t</li>\n";
}

?>
</ul>