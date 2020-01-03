<?php
	error_reporting(E_ALL);
	ini_set('display_errors', 1);
	
	/* Wordpress required */
	ini_set("include_path", '/home/troop/php:' . ini_get("include_path")  );
	$parse_uri = explode( 'wp-content', $_SERVER['SCRIPT_FILENAME'] );
	require_once( $parse_uri[0] . 'wp-load.php' );

  $user_id = 2;
  $event_id = 2707763;

	$query = "SELECT * 
						FROM `wp_postmeta` 
						WHERE meta_key = 'rsvp'
						AND meta_value LIKE '%user_id\";i:$user_id%'
						AND meta_value LIKE '%event_id\";%\"$event_id\"%'";
  

$posts = $wpdb->get_results($query, OBJECT);
echo gettype($posts);
echo '<pre>';
var_dump($posts);
echo '</pre>';

$phil = $posts[0];

var_dump($phil);




