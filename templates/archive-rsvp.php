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
?>
<?php wp_head(); ?>
<?php get_header(); ?>
<?php
$eventID = $_GET["event"];
//$paged = ( get_query_var( 'paged' ) ) ? absint( get_query_var( 'paged' ) ) : 1;

$args = array(
   'post_type' => 'rsvp',
   'meta_query' => array(
       array(
           'meta_key' => 'rsvpEvent',
           'meta_value' => $eventID,
           'compare' => '=',
       ) 
   ) 
);

$rsvp_query = new WP_Query($args);

// EVENT TITLE 
/* If RSVP is for a scout or adult - how can I get the display name?
    Options:
      1) When creating Scouts / Adults - also create a user.
      2) When creating Scouts / Adults - instead create users -- w/ user meta_data
      3) Logic to try to read User, Scout, Adults
      Issues:
        - Accounts needed to login to RSVP - therefore option 1 or 2 is best.
      Option 1 is fastest and can transition to option 2 which is cleanest.
*/
?>
<ol>
<?php
if ( $rsvp_query->have_posts() ) {
    while ( $rsvp_query->have_posts() ) {
        $rsvp_query->the_post();
        $rsvp_meta_id =  $rsvp_query->post->ID;
        $rsvp_user_id = get_post_meta($rsvp_meta_id, 'rsvpUser', TRUE);
        $rsvpUser = get_user_by('id', $rsvp_user_id); 
    ?><li><?php echo $rsvpUser->display_name;?></li><?php
    }
}
  ?></ol>
<?php get_sidebar(); ?>
<?php get_footer(); ?>
