<?php
/**
 * Plugin Name: Scoutbook RSVP
 * Plugin URI: http://troop351.org/plugins/scoutbook-rsvp
 * Description:
 * Version: 0.1
 * Author: Phil Newman
 * Author URI: http://getouourphil.net
 * License: GPL3
 * License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
 **/

function ptn_scoutbook_rsvp_link(){
    global $post;
    session_start();

    $rsvp_links = TRUE; 
    // Is the user logged in
    if (!is_user_logged_in()){
      $rsvp_links = FALSE;
    }
    // Is today between the RSVP start and end dates?
    $today = date("Y-m-d");
    $today_time = strtotime($today);
    $start_time = strtotime(CFS()->get('rsvp_start'));
    if (is_null($start_time)){
     $start_time =  $today_time;
    }
    $end_time = strtotime(CFS()->get('rsvp_end'));  
    if (($today_time <= $start_time) || ($today_time > $end_time)){
      $rsvp_links = FALSE;
      $rsvp_msg = 'RSVP has expired.<p>';
    }
    // Is the RSVP quota already reached?
    $rsvp_limit = CFS()->get( 'rsvp_limit' );  
    // get rsvp_count here         $rsvp_count = count($rsvp_events);  
    $rsvp_count = count(ptn_scoutbook_get_rsvps($post->ID));
    if (($rsvp_limit) && ($rsvp_count >= $rsvp_limit)){
      $rsvp_links = FALSE;
      $rsvp_msg .= 'Event quota has been reached.<p>';
    }
    
  $rsvp_status = ptn_scoutbook_get_rsvp_by_event_and_user($post->ID);
  
  if ($rsvp_links){
  if (!is_null($rsvp_status)){
    $user_rsvped = TRUE;
    $rsvp_status_arr = unserialize($rsvp_status->meta_value);
    $rsvp_msg .= "You are RSVP'ed for ".$rsvp_status_arr['guests'];
    $part = $rsvp_status_arr['part'];

    $hide_rsvp = 'hidden';
    $hide_rsvp_del = '';
  } else {
    $rsvp_msg .= "You are not RSVP'ed";
        $hide_rsvp = '';
    $hide_rsvp_del = 'hidden';
  }
  }
   if ($rsvp_links){
 
  $rsvp_ui = '<div id="rsvp_links">';
    $rsvp_ui .= '<p hidden id="test">'.$post->ID.'</p>';
    $rsvp_ui .= '<img '. $hide_rsvp_del .' class="delete-post-on button" src="'. plugins_url( 'assets/rsvp-red.png', dirname(__FILE__) ) . '" > '; 
    $rsvp_ui .= '<img '. $hide_rsvp .' class="delete-post-off" src="'. plugins_url( 'assets/rsvp-red-gray.png', dirname(__FILE__) ) . '" > ';
    $rsvp_ui .= '<img '. $hide_rsvp . ' class="create-post-on button" src="'. plugins_url( 'assets/rsvp-green.png', dirname(__FILE__) ) . '" > '; 
    $rsvp_ui .= '<img '. $hide_rsvp_del .' class="create-post-off" src="'. plugins_url( 'assets/rsvp-green-gray.png', dirname(__FILE__) ) . '" > ';
  
    if ($post->post_title == 'Cascade Adventures') {
      $ca_parts =array('All', 'Hiking','Rafting', 'Climbing', 'Rafting and Climbing');
      foreach($ca_parts as $ca_part){
       if(strcmp($ca_part, $part) == 0){
          $rsvp_ui .=  '<input type="radio" name="part" value="'.$ca_part. '" checked>'.$ca_part.'<br>';
        } else {
          $rsvp_ui .=  '<input type="radio" name="part" value="'.$ca_part. '">'.$ca_part.'<br>';
        }
      }  
    }

    $rsvp_ui .= '<select name="rsvpnumberlist" id="additional_guests">';
    for ($x = 1; $x <= 5; $x++) {  
      if (($user_rsvped) && ($x == $rsvp_status_arr['guests'])){
        $guest_selected = 'selected';
      }else{
        $guest_selected = '';
      }
      $rsvp_ui .= '<option '.$guest_selected.'>'.$x.'</option>';
    }
    $rsvp_ui .= '</select>';
    $rsvp_ui .= '<p><div class="rsvp_status">'.$rsvp_msg.'</div>';
  }else{
    $rsvp_ui .= '<p><div class="rsvp_status">'.$rsvp_msg.'</div>';
  }
  if (is_user_logged_in()){
    $rsvp_page = get_option(scoutbook_rsvp_page); 
    $_SESSION["sb_event"]=$post->ID;
    $rsvp_ui .= '<a href="'.$rsvp_page.'" class="button">View RSVPs</a>';
  
  $rsvp_ui .= '</div>';
  }
  return $rsvp_ui;
}
add_shortcode( 'scoutbook_rsvp', 'ptn_scoutbook_rsvp_link' );


/*--------------------------------------------------------------------------------
/
/
/
/--------------------------------------------------------------------------------*/
function ptn_scoutbook_rsvp_event_list(){
  global $post;
  session_start();
  
  $adult_rsvp_count = 0;
  $youth_rsvp_count = 0;
  $total_rsvp_count = 0;
 
  $event_id =  $_SESSION["sb_event"];
  $event = get_post($event_id);
  
  $rsvp_event_list_ui = '<h1 class="entry-title">'.$event->post_title.'</h1>';
//  echo 'Post title: '.$event->post_title;
  if ($event->post_title == 'Cascade Adventures'){
    $ca_flag = TRUE;
  }
  $link = $_SERVER['HTTP_REFERER'];
  $current_page = $_SERVER['REQUEST_URI'];
  $last_end = '/'.basename($link).'/';
  if ($last_end != $current_page){
    $rsvp_event_list_ui .= '<a id="rsvp_back_link" href="'.$link.'">Return to event &#8626;</a>';
  }
  $rsvp_list =  ptn_scoutbook_get_rsvps($event_id);

  foreach ($rsvp_list as $rsvp_meta){
    $rsvp_meta_arr = unserialize($rsvp_meta->meta_value);
    $rsvp_userdata = get_userdata($rsvp_meta_arr['user_id']);
    
    if (is_null($rsvp_meta_arr['part']) && ($ca_flag)){
      $rsvp_meta_arr['part'] = "All";
    }
    
    $youth_or_adult = get_user_meta($rsvp_userdata->ID, 'MemberType', TRUE);
    if (empty($youth_or_adult)){
      $youth_or_adult ="adult";
    }
    if ($youth_or_adult == "youth"){
      if ($ca_flag){
     $new_scout_list[] = '<tr><td>'.$rsvp_userdata->first_name.' '.$rsvp_userdata->last_name.'<!--'.$rsvp_userdata->ID.'--></td><td>'.$rsvp_meta_arr['guests']. '</td><td> '.$rsvp_meta_arr['part'].'</td></tr>';
      } else {
      $new_scout_list[] = '<tr><td>'.$rsvp_userdata->first_name.' '.$rsvp_userdata->last_name.'<!--'.$rsvp_userdata->ID.'--></td><td>'.$rsvp_meta_arr['guests'].'</td></tr>';
      }
     
     $youth_rsvp_count += $rsvp_meta_arr['guests'];
    }else{
     $adult_rsvp_count += $rsvp_meta_arr['guests'];
     if ($ca_flag){
       $new_adult_list[] = '<tr line-height=".1"><td>'.$rsvp_userdata->first_name.' '.$rsvp_userdata->last_name.'</td><td>'.$rsvp_meta_arr['guests']. '<!--'.$rsvp_userdata->ID.'--></td><td> '.$rsvp_meta_arr['part'].'</td></tr>';
     }else {
      $new_adult_list[] = '<tr line-height=".1"><td>'.$rsvp_userdata->first_name.' '.$rsvp_userdata->last_name.'</td><td>'.$rsvp_meta_arr['guests']. '<!--'.$rsvp_userdata->ID.'--></td></tr>';

     }
    }
    $total_rsvp_count += $rsvp_meta_arr['guests'];
  }
  $rsvp_event_list_ui .= '<p></p>';
  $rsvp_event_list_ui .= '<h4>SCOUT RSVPs</h4>';
  asort($new_scout_list);
  $rsvp_event_list_ui .=  '<table><th>Name</th><th>Attendees</th>';
//    $rsvp_event_list_ui .=  '<table><th>Name</th><th>Attendees</th><th>Cascade Adventure Intenerary</th>';
  if ($ca_flag){
    $rsvp_event_list_ui .= '<th>Cascade Adventure Itenerary</th>';
  }
  foreach ($new_scout_list as $scout){
    $rsvp_event_list_ui .= $scout;
  }
  $rsvp_event_list_ui .= '</table>';
  $rsvp_event_list_ui .= '<h4>Total Scouts: '.$youth_rsvp_count.'</h4><br/>';
  $rsvp_event_list_ui .= '<p></p>';
  
  $rsvp_event_list_ui .= '<h4>ADULT RSVPs</h4>';
  asort($new_adult_list);
  $rsvp_event_list_ui .=  '<table><th>Name</th><th>Attendees</th>';
//    $rsvp_event_list_ui .=  '<table><th>Name</th><th>Attendees</th><th>Cascade Adventure Intenerary</th>';

  if ($ca_flag){
    $rsvp_event_list_ui .= '<th>Cascade Adventure Itenerary</th>';
  }
  foreach ($new_adult_list as $adult){
    $rsvp_event_list_ui .= $adult;
  }
    $rsvp_event_list_ui .= '</table>';
  $rsvp_event_list_ui .= '<h4>Total Adults: '.$adult_rsvp_count.'</h4><br/>';
  
 $rsvp_event_list_ui .= '<p></p>';
 $rsvp_event_list_ui .= '<h4>TOTAL RSVPs: '.$total_rsvp_count.'</h4><br/>';
 return $rsvp_event_list_ui;
}
add_shortcode( 'scoutbook_rsvp_event_list', 'ptn_scoutbook_rsvp_event_list' );

/*--------------------------------------------------------------------------------
/
/
/
/--------------------------------------------------------------------------------*/
function ptn_scoutbook_rsvp_user_list(){
  
  $user_id = get_current_user_id();
  $rsvp_ids_query_results = get_rsvp_event_ids('rsvpUser', $user_id);
  
  foreach ($rsvp_ids_query_results as $rsvp_id){
    $rsvp_event_id = get_post_meta($rsvp_id, 'rsvpEvent', true);
    $rsvp_event_title = get_the_title($rsvp_event_id);
    $rsvp_post_obj = get_post($rsvp_event_id);
    $event_query_start = ptn_scoutbook_get_ai1ec_dtl($rsvp_event_id);
    echo $event_query_start[0]->date_string.' '.$rsvp_post_obj->post_name.'</br>';

  }
}
add_shortcode( 'scoutbook_rsvp_user_list', 'ptn_scoutbook_rsvp_user_list' );

  ?>