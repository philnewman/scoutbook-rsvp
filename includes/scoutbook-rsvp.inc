<?php
/**
 * Plugin Name: Scoutbook RSVP
 * Plugin URI: http://troop351.org/plugins/scoutbook-rsvp
 * Description:
 * Version: 0.1
 * Author: Phil Newman
 * Author URI: http://getyourphil.net
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
 **/

function ptn_scoutbook_get_rsvps($event_id){
  global $wpdb;
  $user_id = get_current_user_id();
	$query = "SELECT * 
					FROM `wp_postmeta` 
					WHERE meta_key = 'rsvp'
					AND meta_value LIKE '%event_id\";%\"$event_id\"%'";
  $posts = $wpdb->get_results($query);
  return $posts;
}


function ptn_scoutbook_get_rsvp_by_event_and_user($event_id){
  global $wpdb;

  $user_id = get_current_user_id();
	$query = "SELECT * 
					FROM `wp_postmeta` 
					WHERE meta_key = 'rsvp'
					AND meta_value LIKE '%user_id\";i:$user_id%'
					AND meta_value LIKE '%event_id\";%\"$event_id\"%'";
  $posts = $wpdb->get_results($query, OBJECT);
  return $posts[0];
}

function get_rsvp_event_ids($rsvp_type, $rsvp_data){
  global $wpdb;
  $rsvp_ids_query = 
    "SELECT a.ID
  FROM wp_posts a, wp_postmeta b ";
  if ($rsvp_type == 'rsvpEvent'){
    $rsvp_ids_query .= "WHERE b.meta_key = 'rsvpEvent' ";
  }else{
    $rsvp_ids_query .= "WHERE b.meta_key = 'rsvpUser' ";
  }
  $rsvp_ids_query .=
  "AND b.meta_value = $rsvp_data
  AND a.ID = b.post_id
  AND a.post_type = 'rsvp'";
    
  $rsvp_ids_query_results = $wpdb->get_col($rsvp_ids_query);
  return $rsvp_ids_query_results;
}

function ptn_scoutbook_add_rsvp_to_event( $content ) {
  global $post;
  if( ! $post instanceof WP_Post ) return $content;
  switch( $post->post_type ) {
    case 'ai1ec_event':
      return '[scoutbook_rsvp]' . $content;
    default:
      return $content;
  }
}
add_filter( 'the_content', 'ptn_scoutbook_add_rsvp_to_event' );

function ptn_scoutbook_get_ai1ec_dtl($event_id){
    global $wpdb;
    $event_query = "SELECT start, timezone_name FROM wp_ai1ec_events WHERE post_id = $event_id";     
    $event_query_dtl = $wpdb->get_results($event_query,OBJECT); 
    $event_query_dtl_start = '@'. $event_query_dtl[0]->start;
    $dt = new DateTime($event_query_dtl_start);
    $dt->setTimeZone(new DateTimeZone($event_query_dtl[0]->timezone_name));
    $event_query_dtl[0]->date_string =  $dt->format('F j, Y');
    $event_query_dtl[0]->date_object = $dt;
    
    return $event_query_dtl;
}

function isRSVPexpired($rsvp_ID){
    $rsvp_event_start = ptn_scoutbook_get_ai1ec_dtl($rsvp_ID);
    $today = new DateTime('America/Los_Angeles');
    $interval = $rsvp_event_start[0]->date_object->diff($today);    
    $days = $interval->format('%a');
    if (intval($days) >= 3 ){
      return TRUE;
    }else{
      return FALSE;
    }
}

?>