<?php

/**
* Router class
*/
class Router{
	private static $instance;
	public $format;
	public $slug;
	public $url;
	private $routed = false;
	private $called_function;

	public static function &get_instance() {
		if( self::$instance === null )
			self::$instance = new Router();
		return self::$instance;
	}

	private function __construct(){
		$this->url = (object) array(
			"slug" => false,
			"format" => false,
			"called_function" => false,
			"matched_data" => false
		);

		$url_string = $_SERVER["REQUEST_URI"]; // /path/to/method/1.format?a=b
		$url_string = explode("?",$url_string); // /path/to/method/1.format
		$url_string = trim( $url_string[0], "/" ); // path/to/method/1.format
	
		// Trim off "index.php" if it's there
		if( substr( $url_string , 0, 9) == "index.php" )
			$url_string = trim( substr( $url_string , 10), "/" );

		// ""							catchall_route
		// "about"						catchall_route
		// "gallery/aviator-project"	project_single
		// "go/1"						project_short_url => prorject_single
		// "api"						adding/removing projects or items

		// Response format
		$url_parts = explode( ".", $url_string );

		if( sizeof( $url_parts ) == 1 ){
			$this->url->slug = strtolower( $url_parts[0] ); // path/to/method/1
			$this->url->format = "html"; // format
		} elseif( sizeof( $url_parts ) == 2 ){
			$this->url->slug = strtolower( $url_parts[0] ); // path/to/method/1
			$this->url->format = strtolower( $url_parts[1] ); // format
			if( $this->url->format == "html" ){
				// 404
			}
		} else {
			// 404
		}

		$patterns = array(
			// Project Short URL
			'project_short_url'=>'#^go\/(?P<project_id>\d+)$#i',

			// API
			// Items
			'item_list'=>'#^api/items$#i',
			'item_single'=>'#^api/item$#i',
			'item_add'=>'#^api/item/add$#i',
			'item_delete'=>'#^api/item/delete$#i',

			// Projects
			'project_list'=>'#^api/projects$#i',
			'project_single'=>'#^api/project$#i',
			'project_add'=>'#^api/project/add$#i',
			'project_delete'=>'#^api/project/delete$#i',

			// Publish (will eventually be replaced/removed)
			'publish'=>'#^api/publish$#i',

			// Miscellaneous
			'logout'=>'#^logout$#i',

			// Projects
			'project_route'=>'#^'.PROJECT_PREFIX.'(?P<project_slug>.*+)$#i',

			// Catchall
			'catchall_route'=>'#^(?P<url_string>.*+)$#i'
		);

		for(
			reset($patterns);
			current($patterns);
			next($patterns)
		){
			if( preg_match( current($patterns), $this->url->slug, $matched_data ) ) {
				$this->url->called_function = key($patterns);
				$this->url->matched_data = $matched_data;
				break; // only run until it matches
			}
		}

	}

	public function route(){
		if( !$this->routed ) {
			call_user_func(array(
				$this,
				$this->url->called_function
			), $this->url->matched_data
			);
			$this->routed = true;
		} else {
			throw new Exception("URL has already been routed", 1);
		}
	}

	private function return_data( $status, $status_msg, $data = array() ){
		switch( $this->url->format ) {
			case "json":

			header("Content-type: application/javascript");
			echo json_encode(array(
				"status" => $status,
				"status_msg" => $status_msg,
				"data" => $data
			));
			exit;

			break;

			case "html":

			if( !empty( $data ) ){
				$data_html = "";
				foreach( $data as $s => $row )
					$data_html .= $s . " => " . $row . "<br>";

				$data = print_r( $data, 1 );
			} else {
				$data = "None";
				$data_html = "None";
			}

			echo "<pre>" . htmlentities( "Status\n==========\n" . $status . " - " . $status_msg . "\n\nData\n==========\n" . $data ) . "</pre>";
//			echo $data_html;
			exit;

			break;

			default:
			echo "Format '{$this->url->format}' has not been implemented.";
			break;
		}
	}

	private function project_route( $args ){
		$p = Portfolio::get_instance();

		if( $current_project = $p->project_by_slug( $args["project_slug"] ) ) {
			$t = new Template("project-single");
			$t->set("current_project",$current_project);
			$t->render();
		} else {
			$t = new Template("error");
			$t->set( "page_title", "Project Not Found" );
			$t->set( "error_message", "Project not found" );
			$t->set( "error_details", "The project you were looking for could not be found" );
			$t->render();
		}
	}

	private function catchall_route( $args ){
		$p = Portfolio::get_instance();
		if( empty( $args["url_string"] ) ){
			$t = new Template("index");
		} else {
			$t = new Template("page-".$args["url_string"]);
		}
		$t->render();
	}

	function project_short_url( $args ){
		$p = Portfolio::get_instance();
		if( $project = $p->project_by_id($args["project_id"]) ){
			header( "Location: ". $project["url"] );
			exit;
//			die( "Project {$args["project_id"]} exists" );
		} else {
			die( "Project {$args["project_id"]} does not exist" );
		}
	}

	// Miscellaneous

	function logout( $args ){
		if( logged_in() ) {
			setcookie( "pw_hash", "", time()-60*60*24*365 );
			$this->return_data(
				"success",
				"Successfully logged out"
			);
		} else {
			$this->return_data(
				"success",
				"Already logged out"
			);
		}
	}

	function publish( $args ){
		$last_updated = Cache::update();
		$this->return_data(
			"success",
			"Cache successfully updated",
			array(
				"last_updated" => $last_updated
			)
		);
	}

	function heartbeat( $args ){
		$p = Portfolio::get_instance();
		$this->return_data(
			"success",
			"Heartbeat successfully fetched",
			array(
				"last_updated" => $p->meta("last_updated")
			)
		);
	}


	function items_by_project( $args ){
		if( empty( $_GET["project_id"] ) )
			$this->return_data(
				"error",
				'$_GET["project_id"] must be set for items_by_project()'
			);
		$pid = $_GET["project_id"];

		$p = Portfolio::get_instance();
		$project = $p->project_by_id($pid);

		if( !empty( $project ) ){
			$this->return_data(
				"success",
				"All items from project $pid ({$project['title_src']}) successfully fetched",
				array(
					"items_by_project"=>$p->items_by_project($pid)
				)
			);
		} else {
			$this->return_data(
				"error",
				"No item with an ID of $pid could be found"
			);
		}
	}

	// Items

	function item_list( $args ){
		$p = Portfolio::get_instance();

		if( !empty( $_GET["project_id"] ) ) {
			$pid = (int) $_GET["project_id"];
			if( $current_project = $p->project_by_id($pid) ) {
				$this->return_data(
					"success",
					"All items from project {$pid} successfully fetched",
					array(
						"project"=>$current_project,
						"items"=>$p->items_by_project($current_project["id"])
					)
				);
			} else {
				$this->return_data(
					"error",
					"Project {$pid} does not exist"
				);
			}
		} elseif( !empty( $_GET["project_slug"] ) ) {
			$pslug = $_GET["project_slug"];

			if( $current_project = $p->items_by_slug($pslug) ) {
				$this->return_data(
					"success",
					"All items from project '$pslug' successfully fetched",
					array(
						"project"=>$current_project,
						"items"=>$p->items_by_project($current_project["id"])
					)
				);
			} else {
				$this->return_data(
					"error",
					"Project '$pslug' does not exist"
				);
			}
		} else {
			$this->return_data(
				"success",
				"All items successfully fetched",
				array(
					"items_by_id"=>$p->items_by_id(),
					"items_by_project"=>$p->items_by_project(),
					"item_count"=>count($p->items_by_id())
				)
			);
		}
	}

	function item_single( $args ){
		if( empty( $_GET["item_id"] ) )
			$this->return_data(
				"error",
				'$_GET["item_id"] must be set for item_single()'
			);

		$p = Portfolio::get_instance();
		$id = $_GET["item_id"];

		if( $data = $p->item_by_id($id) ) {
			$this->return_data(
				"success",
				"Item $id successfully fetched",
				array(
					"item"=>$data
				)
			);
		} else {
			$this->return_data(
				"error",
				"No item with the ID of $id could be found"
			);
		}
	}

	function item_add( $args ){
		if( !empty( $_FILES ) ) {

			try{
				$image = WideImage::load('image_original');
			} catch (Exception $e) {
				$this->return_data(
					"error",
					"File uploaded is not a valid image"
				);
			}
			
			$p = Portfolio::get_instance();

			$title = "New Portfolio Item";
			$desc = "";
			$project = 0;

			if( !empty($_FILES["image_original"]["name"]) )
				$title = array_shift(explode( ".", $_FILES["image_original"]["name"] ) );

			if( !empty($_POST["item_project"] ) )
				$project = escape( $_POST["item_project"] );

			$inserted_item = $p->item_add(
				$title,
				$desc,
				$project
			);


			$original = $image->saveToFile(IMAGE_PATH . "image{$inserted_item["id"]}_orig.jpg");
			$clear_img = WideImage::load(SYS_MEDIA_PATH . "images/clear.png");
			if( ADD_WATERMARK ) $watermark = WideImage::load(MEDIA_PATH . "images/" . WATERMARK_IMG);

			$orig_h = $image->getHeight();
			$orig_w = $image->getWidth();
			$orig_hyp = sqrt( $orig_h*$orig_h + $orig_w*$orig_w );

			// Generate thumbnails
			$thumb_ratio = THUMBNAIL_MAX/$orig_hyp;
			$thumb_h = round($orig_h * $thumb_ratio);
			$thumb_w = round($orig_w * $thumb_ratio);
			$thumb_hyp = THUMBNAIL_MAX;

			$thumb_outside = $image->resize(THUMBNAIL_MAX, THUMBNAIL_MAX, "outside")->crop("50%-".(THUMBNAIL_MAX/2),"50%-".(THUMBNAIL_MAX/2),THUMBNAIL_MAX,THUMBNAIL_MAX);
			$thumb_inside = $image->resize($thumb_w, $thumb_h, "inside");

			$thumb_w_offset = (THUMBNAIL_MAX/2)-($thumb_w/2);
			$thumb_h_offset = (THUMBNAIL_MAX/2)-($thumb_h/2);

			$thumb_bkg = $clear_img->resize(THUMBNAIL_MAX,THUMBNAIL_MAX, "outside");
			$thumb_bkg->merge( $thumb_outside, 0, 0)->saveToFile(IMAGE_PATH . "image{$inserted_item["id"]}_thumb_o.jpg");
			$thumb_bkg->merge( $thumb_inside, $thumb_w_offset, $thumb_h_offset)->saveToFile(IMAGE_PATH . "image{$inserted_item["id"]}_thumb_i.jpg");

			// Generate full size images
			$fs_bkg = $image->resize(FULLSIZE_MAX,FULLSIZE_MAX, "outside")->saveToFile(IMAGE_PATH . "image{$inserted_item["id"]}_full.jpg",12);;

			$fs_ratio = FULLSIZE_MAX/$orig_hyp;
			$fs_h = round($orig_h * $fs_ratio);
			$fs_w = round($orig_w * $fs_ratio);
			$fs_hyp = FULLSIZE_MAX;

			if( ADD_WATERMARK ){
				$fs_inside = $image->resize($fs_w, $fs_h)->merge($watermark, '0', '100%-300');
			} else {
				$fs_inside = $image->resize($fs_w, $fs_h);
			}

			$fs_w_offset = (FULLSIZE_MAX/2)-($fs_w/2);
			$fs_h_offset = (FULLSIZE_MAX/2)-($fs_h/2);

//			$fs_bkg = $clear_img->resize(FULLSIZE_MAX,FULLSIZE_MAX, "outside");
//			$fs_bkg->merge( $fs_inside, $fs_w_offset, $fs_h_offset)->saveToFile(IMAGE_PATH . "image{$inserted_item["id"]}_full_s.jpg");
			$fs_inside->saveToFile(IMAGE_PATH . "image{$inserted_item["id"]}_full_s.jpg");

			if( ADD_WATERMARK ){
				$full_size = $image->resize(FULLSIZE_MAX,FULLSIZE_MAX)->merge($watermark, '0', '100%-300');
			} else {
				$full_size = $image->resize(FULLSIZE_MAX,FULLSIZE_MAX);
			}
			$full_size->saveToFile(IMAGE_PATH . "image{$inserted_item["id"]}_full.jpg");

			$this->return_data(
				"success",
				"Item {$inserted_item["id"]} successfully added",
				array(
					"added_item" => $insert_id
				)
			);
		}
	}

	function item_save( $args ){
		if(
			!empty( $_POST["item_id"] ) &&
			$id = (int) $_POST["item_id"] &&
			$id > 0 &&
			!empty( $_POST["item_title"] ) &&
			!empty( $_POST["item_desc"] ) &&
			!empty( $_POST["item_project"] )
		){
			$p = Portfolio::get_instance();
			$saved_item = $p->item_update(
				$id,
				$_POST["item_title"],
				$_POST["item_desc"],
				$_POST["item_project"]
			);
			$this->return_data(
				"success",
				"Item $id successfully saved",
				$saved_item
			);
		} else {
			$this->return_data(
				"error",
				"item_title, item_desc, and item_project must be POSTed to this URL"
			);
		}
	}

	function item_delete( $args ){
		if( empty( $_GET["item_id"] ) )
			$this->return_data(
				"error",
				'$_GET["item_id"] must be set for item_delete()'
			);
		$id = $_GET["item_id"];

		$p = Portfolio::get_instance();

		if( $deleted_item = $p->item_delete($id) ){
			@unlink(IMAGE_PATH . "image{$id}_full.jpg");
			@unlink(IMAGE_PATH . "image{$id}_full_s.jpg");
			@unlink(IMAGE_PATH . "image{$id}_thumb_i.jpg");
			@unlink(IMAGE_PATH . "image{$id}_thumb_o.jpg");
			@unlink(IMAGE_PATH . "image{$id}_orig.jpg");

			$this->return_data(
				"success",
				"Item $id deleted",
				array(
					"deleted_item" => $deleted_item
				)
			);
		} else {
			$this->return_data(
				"error",
				"Item $id could not be deleted because it does not exist"
			);
		}
	}

	function item_reorder( $args ){
		if(
			!empty( $_POST["project_id"] ) &&
			!empty( $_POST["item"] ) &&
			!empty( $_POST["last_updated"] )
		){
			$p = Portfolio::get_instance();

			// Trim "project-" off the ID
			$project_id = substr( $_POST["project_id"], 8 );

			// Check if we're current
			if( $_POST["last_updated"] == $p->meta("last_updated") ){
				if( $data = $p->item_reorder( $project_id, $_POST["item"] ) ){
					$this->return_data(
						"success",
						"Items successfully reordered",
						array(
							"new_order" => $data
						)
					);
				} else {
					$this->return_data(
						"error",
						"There was a problem reordering the items"
					);
				}
			} else {
				$this->return_data(
					"outdated",
					"Page is out of dated: {$_POST['last_updated']} != {$p->meta("last_updated")}"
				);
			}
		} else {
			$this->return_data(
				"error",
				"project_id, item, and last_updated must be POSTed to this URL"
			);
		}
	}

	// Projects
	function project_list( $args ){
		$p = Portfolio::get_instance();
		$projects = array();
		$items_by_project = $p->items_by_project();

		foreach( $p->projects_by_id() as $pid => $project ){
			$item_count = !empty( $items_by_project ) ? count( $items_by_project[$pid] ) : 0;
			$projects[$pid] = $project + array( "item_count" => $item_count );
		}

		$this->return_data(
			"success",
			"All projects (and corresponding items) successfully fetched",
			array(
				"projects"=>$projects,
			)
		);
	}

	function project_single( $args ){
		$p = Portfolio::get_instance();

		$id = false;
		$slug = false;

		if( !empty( $_GET["project_id"] ) ) {
			$id = (int) $_GET["project_id"];
			$project = $p->project_by_id($id);
		} elseif( !empty( $_GET["project_slug"] ) ) {
			$slug = $_GET["project_slug"];
			$project = $p->project_by_slug($slug);
		} else {
			$this->return_data(
				"error",
				'$_GET["project_id"] or $_GET["project_slug"] must be set for project_single()'
			);
		}

		if( !empty( $project ) ) {

			$items = $p->items_by_project( $project["id"] );

			if( empty( $items ) )
				$items = array();

			$this->return_data(
				"success",
				"Project {$project["id"]} ({$project['title_src']}) successfully fetched",
				array(
					"project" => $project,
					"items" => $items
				)
			);
		} else {
			$error = "id of ".( (int) $id );
			if( $slug ) $error = "slug of '" . htmlentities($slug,ENT_QUOTES) . "'";

			$this->return_data(
				"error",
				"No project with the $error could be found"
			);
		}
	}

	function project_add( $args ){
		$p = Portfolio::get_instance();
		if(
			!empty( $_POST["project_title"] ) &&
			$added_project = $p->project_add( $_POST["project_title"] )
		){
			$this->return_data(
				"success",
				"Project successfully added",
				array(
					"added_project" => $added_project
				)
			);
		} else {
			$this->return_data(
				"error",
				"No data posted"
			);
		}
	}

	function project_delete( $args ){
		$p = Portfolio::get_instance();

		if( empty( $_GET["project_id"] ) )
			$this->return_data(
				"error",
				'$_GET["project_id"] must be set for project_delete()'
			);

		$id = (int) $_GET["project_id"];

		if( $p->project_by_id( $id ) ) {
			if( $deleted_project = $p->project_delete($id) ){
				$this->return_data(
					"success",
					"Project $id successfully deleted",
					array(
						"deleted_project" => $deleted_project
					)
				);
			} else {
				$this->return_data(
					"error",
					"There was an error deleting item $id"
				);
			}
		} else {
			$this->return_data(
				"error",
				"Project $id does not exist"
			);
		}
	}

	function project_save( $args ){
		if( empty( $_GET["project_id"] ) )
			$this->return_data(
				"error",
				'$_GET["project_id"] must be set for project_save()'
			);
		$id = $_GET["project_id"];
		if( empty( $_POST["project_title"] ) ){
			$this->return_data(
				"error",
				"project_title must be POSTed to this URL"
			);
		} else {
			$saved_project = $portfolio->project_update( $id, $_POST["project_title"] );
			$this->return_data(
				"success",
				"Project $id successfully saved",
				array(
					"saved_project" => $saved_project
				)
			);
		}
	}

	function project_reorder( $args ){
	}
}


?>