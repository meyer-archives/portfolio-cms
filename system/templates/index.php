<?php if( !defined( "SITE_PATH" ) ) die( "Can't touch this." );

?>

<div id="upload-form">
	<h2>Upload Form</h2>
	<form action="#">
		<input type="file" name="uploadify" id="uploadify" />
	</form>
</div>

<?php
/*
foreach( $items_by_project as $id => $item ) {

?>
	<li class="item" id="item-<?php echo $item["id"]; ?>" data-item-id="<?php echo $item["id"]; ?>" data-item-order="<?php echo $item["order"]; ?>" data-project-id="<?php echo $item["project"]; ?>" data-img-href="<?php echo $item["img_500"] ?>" data-img-thumb="<?php echo $item["img_thumb"] ?>">
		<div class="image-fullsize"><img src="<?php echo $item["img_500"] ?>"></div>
		<h3 class="item-title"><?php echo $item["title"]; ?></h3>
		<p class="item-desc"><?php echo $item["desc"]; ?></p>
	</li>
<?php

} // foreach
*/

echo "<pre>Data:\n";
htmlspecialchars( print_r( $items_by_project, 1 ) );
echo "</pre>";

?>