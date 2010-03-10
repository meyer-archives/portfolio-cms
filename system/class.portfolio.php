<?php

if( !defined( "SITE_PATH" ) ) die( "Can't touch this." );

class Portfolio {
	private $sqlite;
	//Item Arrays
	private $items_by_id;
	private $items_by_project;

	// Project Arrays
	private $projects_by_id;
	private $projects_by_slug;
	private $meta;

	private static $instance;
 
	public static function &get_instance() {
		if( self::$instance === null )
			self::$instance = new Portfolio();
		return self::$instance;
	}

	private function __construct(){
		// Initiate the DB
		try {
			$this->sqlite = DB::get_handle();

			$this->sqlite->exec("CREATE TABLE IF NOT EXISTS portfolio_meta (
				meta_id INTEGER PRIMARY KEY,
				meta_key TEXT UNIQUE,
				meta_value TEXT,
				meta_category TEXT,
				meta_category_key INTEGER
			);");
			$this->sqlite->exec("CREATE TABLE IF NOT EXISTS portfolio_projects (
				project_id INTEGER PRIMARY KEY,
				project_title TEXT,
				project_title_src TEXT,
				project_slug TEXT UNIQUE,
				project_order INTEGER,
				date_added TIMESTAMP,
				last_updated TIMESTAMP
			);");
			$this->sqlite->exec("CREATE TABLE IF NOT EXISTS portfolio_items (
				item_id INTEGER PRIMARY KEY,
				item_title TEXT,
				item_desc TEXT,
				item_title_src TEXT,
				item_desc_src TEXT,
				item_order INTEGER,
				item_project INTEGER,
				date_added TIMESTAMP,
				last_updated TIMESTAMP
			);");
			$now = time();
		} catch( PDOException $e ){ 
			die( "PDO Error: " . $e->getMessage() ); 
		}

		$items = $this->sqlite->query("SELECT * FROM portfolio_items ORDER BY item_order ASC;");

		if( $items && $item_results = $items->fetchAll() ) {
			foreach( $item_results as $row ){
				$this->items_by_project[$row["item_project"]][$row["item_id"]] = &$this->items_by_id[$row["item_id"]];
				$this->items_by_id[$row["item_id"]] = array(
					"id" => $row["item_id"],
					"project" => $row["item_project"],
					"title_src" => unescape( $row["item_title_src"] ),
					"title" => unescape( $row["item_title"], false ),
					"desc_src" => unescape( $row["item_desc_src"] ),
					"desc" => unescape( $row["item_desc"], false ),
					"order" => $row["item_order"],
					"date_added" => strtotime($row["date_added"]),
					"last_updated" => strtotime($row["last_updated"]),
					"img_original" => UPLOAD_URL . "image{$row["item_id"]}_orig.jpg",
					"img_thumb" => IMAGE_URL . "image{$row["item_id"]}_50.jpg",
					"img_500" => IMAGE_URL . "image{$row["item_id"]}_500.jpg",
					"img_700" => IMAGE_URL . "image{$row["item_id"]}_700.jpg",
					"meta" => array()
				);

				if( empty( $project_count[$row["item_project"]] ) ) {
					$project_count[$row["item_project"]] = 1;
				} else {
					$project_count[$row["item_project"]]++;
				}
			}
		} else {
			// No items
		}

		$this->projects_by_slug["unpublished"] = &$this->projects_by_id[0];
		$this->projects_by_id[0] = array(
			"id" => 0,
			"title" => "Unpublished",
			"title_src" => "Unpublished",
			"slug" => "unpublished",
			"date_added" => 0,
			"last_updated" => 0,
			"meta" => array()
		);

		$project_results = $this->sqlite->query("SELECT * FROM portfolio_projects ORDER BY project_order ASC;")->fetchAll();
		foreach( $project_results as $row ){
			$this->projects_by_slug[$row["project_slug"]] = &$this->projects_by_id[$row["project_id"]];
			$this->projects_by_id[$row["project_id"]] = array(
				"id" => (int) $row["project_id"],
				"title" => unescape($row["project_title"],false),
				"title_src" => unescape($row["project_title_src"]),
				"slug" => "not-implemented-yet-".$row["project_id"],
				"date_added" => strtotime($row["date_added"]),
				"last_updated" => strtotime($row["last_updated"]),
				"meta" => array()
			);
		}

		$meta_results = $this->sqlite->query("SELECT * FROM portfolio_meta;")->fetchAll();
		foreach( $meta_results as $row ){
			if( $row["meta_category"] == "project" ) {
				if( $pid = (int) $row["meta_category_key"] && !empty( $this->projects_by_id[$pid] ) )
					$this->projects_by_id[$pid]["meta"][$row["meta_key"]] == $row["meta_value"];
			} elseif( $row["meta_category"] == "item" ) {
				if( $item_id = (int) $row["meta_category_key"] && !empty( $this->items_by_id[$item_id] ) )
					$this->items_by_id[$item_id]["meta"][$row["meta_key"]] == $row["meta_value"];
			} else {
				$this->meta[$row["meta_key"]] = $row["meta_value"];
			}
		}
	}

/*
	private function db_version_update(){
		$time_now = time();
		$this->meta("last_updated",$time_now);
		return $time_now;
	}
*/
	function meta( $k = false, $v = false ){
		if( empty( $k ) ) {
			return $this->meta;
		} else {
			if( empty($v) ) { // Get
				if( !empty( $this->meta[$k] ) )
					return $this->meta[$k];
				return false;
			} else { // Set
				$query = sprintf( "INSERT OR REPLACE INTO portfolio_meta ( meta_key, meta_value ) VALUES ( '%s', '%s' );",
					escape($k),
					escape($v)
				);
				$this->meta[$k] = $v;

				return $this->sqlite->exec($query);
			}
		}
	}

	// Nearly identical to meta()
	function project_meta( $id, $k = false, $v = false ){
		$id = (int) $id;
		if( $id > 0 ){
			if( empty( $k ) ) {
				return !empty( $this->projects_by_id[$id]["meta"] ) ? $this->projects_by_id[$id]["meta"] : false;
			} else {
				if( empty($v) ) { // Get
					return !empty( $this->projects_by_id[$id]["meta"][$k] ) ? $this->projects_by_id[$id]["meta"][$k] : false;
				} else { // Set
					$query = sprintf( "INSERT OR REPLACE INTO portfolio_meta ( meta_key, meta_value, meta_category, meta_category_key ) VALUES ( '%s', '%s', 'project', '%s' );",
						escape($k),
						escape($v),
						$id
					);
					$this->projects_by_id[$id]["meta"][$k] = $v;

					return $this->sqlite->exec($query);
				}
			}
		} else {
			show_error( '$id must be numeric and greater than zero in project_meta()', E_USER_ERROR );
		}
	}

	// Nearly identical to project_meta()
	function item_meta( $id, $k = false, $v = false ){
		$id = (int) $id;
		if( $id > 0 ){
			if( empty( $k ) ) {
				return !empty( $this->items_by_id[$id]["meta"] ) ? $this->items_by_id[$id]["meta"] : false;
			} else {
				if( empty($v) ) { // Get
					return !empty( $this->items_by_id[$id]["meta"][$k] ) ? $this->items_by_id[$id]["meta"][$k] : false;
				} else { // Set
					$query = sprintf( "INSERT OR REPLACE INTO portfolio_meta ( meta_key, meta_value, meta_category, meta_category_key ) VALUES ( '%s', '%s', 'item', '%s' );",
						escape($k),
						escape($v),
						$id
					);
					$this->items_by_id[$id]["meta"][$k] = $v;

					return $this->sqlite->exec($query);
				}
			}
		} else {
			show_error( '$id must be numeric and greater than zero in item_meta()', E_USER_ERROR );
		}
	}

	///////////////////////////////////////////////////////
	//  PORTFOLIO ITEMS - ADD, MODIFY, AND DELETE
	///////////////////////////////////////////////////////

	function item_add( $title, $desc, $project_id = "0" ){
		$project_id = (int) $project_id;

		$title_src = escape($title);
		$title = escape_typogrify($title);
		$desc_src = escape($desc);
		$desc = escape_typogrify( $desc );

		if( !$this->projects_by_id() ) {
			$new_project = $this->project_add("New Project");
			$project_id = $new_project["id"];
		}

		if( !$this->project($project_id) )
			die( "Project {$project_id} doesn't exist" );

		$query = sprintf( "INSERT INTO portfolio_items (
			item_title,
			item_title_src,
			item_desc,
			item_desc_src,
			item_project,
			item_order,
			date_added,
			last_updated
		) VALUES (
			'%s',
			'%s',
			'%s',
			'%s',
			'%s',
			'0',
			DATETIME('NOW'),
			DATETIME('NOW')
		);",
			$title,
			$title_src,
			$desc,
			$desc_src,
			$project_id
		);
		$this->sqlite->exec( $query );

		$id = $this->sqlite->lastInsertId();

		$this->items_by_project[$project_id][] = &$this->items_by_id[$id];
		$this->items_by_id[$id] = array(
			"id" => $id,
			"project" => $project_id,
			"title_src" => $title_src,
			"title" => $title,
			"desc_src" => $desc_src,
			"desc" => $desc,
			"order" => $order,
			"date_added"=>false,
			"last_updated"=>false,
			"img_original" => UPLOAD_URL . "image{$id}_orig.jpg",
			"img_thumb" => IMAGE_URL . "image{$id}_50.jpg",
			"img_500" => IMAGE_URL . "image{$id}_500.jpg",
			"img_700" => IMAGE_URL . "image{$id}_700.jpg"
		);

		return $this->items_by_id[$id];
	}

	function item_reorder( $project_id, $order = array() ){
		if( !empty( $order ) ){
			$query1 = "UPDATE portfolio_items SET item_order = '%s' WHERE item_id = '%s';";
			$query2 = "UPDATE portfolio_items SET item_project = '%s' WHERE item_id = '%s';";
			$this->sqlite->beginTransaction();

			foreach( $order as $item_order => $item_id ){
//				$item_order += 100;
				$this->sqlite->exec(sprintf( $query1, (int) $item_order, (int) $item_id ));
				$this->sqlite->exec(sprintf( $query2, (int) $project_id, (int) $item_id ));
			}

			$this->sqlite->commit();

			return true;
		} else {
			return false;
		}
	}

	function item_update( $id, $title, $desc, $pid, $order = false ){
		$id = (int) $id;
		if( $id > 0 ) {
			$query = "UPDATE portfolio_items SET %s = '%s' WHERE item_id = '$id';";

			$title_src = escape($title);
			$title = escape_typogrify($title);

			$this->sqlite->beginTransaction();

			$this->sqlite->exec(sprintf($query,"item_title_src",$title_src));
			$this->sqlite->exec(sprintf($query,"item_title",$title));

			$desc_src = escape($desc);
			$desc = escape_typogrify($desc);

			$this->sqlite->exec(sprintf($query,"item_desc_src",$desc_src));
			$this->sqlite->exec(sprintf($query,"item_desc",$desc));

			$pid = (int) $pid;
			$this->sqlite->exec(sprintf($query,"item_project",$pid));

			$this->sqlite->exec(sprintf($query,"last_updated","DATETIME('NOW')"));

			$this->sqlite->commit();
//			$this->db_version_update();

			$this->items_by_project[$pid][] = &$this->items_by_id[$id];
			$this->items_by_id[$id] = array(
				"id" => $id,
				"project" => $pid,
				"title_src" => $title_src,
				"title" => $title,
				"desc_src" => $desc_src,
				"desc" => $desc,
				"order" => $order,
				"date_added"=>false,
				"last_updated"=>false,
				"img_original" => UPLOAD_URL . "image{$id}_orig.jpg",
				"img_thumb" => IMAGE_URL . "image{$id}_50.jpg",
				"img_500" => IMAGE_URL . "image{$id}_500.jpg",
				"img_700" => IMAGE_URL . "image{$id}_700.jpg"
			);

			return $this->items_by_id[$id];
		} else {
			return false;
		}
	}

	function item_delete( $id ){
		$query = "DELETE FROM portfolio_items WHERE item_id = '$id'";
//		$this->db_version_update();
		if( $this->sqlite->exec($query) ){
			return $this->item( $id );
		} else {
			return false;
		}
	}


	///////////////////////////////////////////////////////
	//  PORTFOLIO CATEGORIES - ADD, MODIFY, AND DELETE
	///////////////////////////////////////////////////////

	function project_add( $title ){
		$title_src = escape($title);
		$title = escape_typogrify($title);

		$query = sprintf(
			"INSERT INTO portfolio_projects (
				project_title,
				project_title_src,
				project_order
			) VALUES ( '%s', '%s', '0' );",
			$title, $title_src
		);

//		$this->db_version_update();
		$this->sqlite->exec( $query );
		return array(
			"id" => $this->sqlite->lastInsertId(),
			"title" => $title,
			"title_src" => $title_src
		);
	}

	function project_update( $id, $title ){
		$id = (int) $id;
		if( $id > 0 ) {
			$query = false;
			if( !empty($title) ){
				$query = "UPDATE portfolio_projects SET %s = '%s' WHERE project_id = '%d';";

				$title_src = escape($title);
				$title = escape_typogrify($title);

				$this->sqlite->exec(sprintf($query,"project_title",$title,$id));
				$this->sqlite->exec(sprintf($query,"project_title_src",$title_src,$id));

//				$this->db_version_update();
				return array(
					"id" => $id,
					"title" => $title,
					"title_src" => $title_src
				);
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function project_delete( $id ){
		$id = (int) $id;
		if( $id > 0 ){
			$query = "DELETE FROM portfolio_projects WHERE project_id = '$id';";
			$query2 = "UPDATE portfolio_items SET item_project = '0' WHERE item_project = '$id';";
//			$this->db_version_update();
			if(
				$this->sqlite->exec($query) &&
				$this->sqlite->exec($query2)
			){
				return $this->project( $id );
			} else {
				return false;
			}
		} else {
			show_error( "You can't delete project 0", E_USER_ERROR );
		}
	}

	///////////////////////////////////////////////////////
	//  CATEGORIES & ITEMS - GET
	///////////////////////////////////////////////////////

	function item($id){
		return !empty( $this->items_by_id[$id] ) ? $this->items_by_id[$id] : array();
	}

	function items_by_id(){
		return $this->items_by_id;
	}

	function items_by_project($id=false){
		if( $id ) {
			return !empty( $this->items_by_project[id] ) ? $this->items_by_project[$id] : array();
		} else {
			return $this->items_by_project;
		}
	}

	function project($id){
		return !empty( $this->projects_by_id[$id] ) ? $this->projects_by_id[$id] : array();
	}

	function projects_by_id(){
		return $this->projects_by_id;
	}

	function projects_by_slug(){
		return $this->projects_by_slug;
	}
}

?>