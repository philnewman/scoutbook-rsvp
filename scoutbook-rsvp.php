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

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

add_action( 'admin_init', 'child_plugin_has_parent_plugin' );
function child_plugin_has_parent_plugin() {
    if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'scoutbook/scoutbook.php' ) ) {
        add_action( 'admin_notices', 'child_plugin_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) ); 
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
      if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'all-in-one-event-calendar/all-in-one-event-calendar.php' ) ) {
        add_action( 'admin_notices', 'child_plugin_notice' );
        deactivate_plugins( plugin_basename( __FILE__ ) ); 
        if ( isset( $_GET['activate'] ) ) {
            unset( $_GET['activate'] );
        }
    }
}
function child_plugin_notice(){
    ?><div class="error"><p>Sorry, but Child Plugin requires the Parent plugin to be installed and active.</p></div><?php
}

function ptn_scoutbook_rsvp_load_css_and_js() {
  $rsvp_file_dir = plugin_dir_url(__FILE__);
  $rsvp_style_file = $rsvp_file_dir.'css/scoutbook-rsvp-styles.css';
  $rsvp_js_file = $rsvp_file_dir.'js/rsvp.js';

   wp_enqueue_style('ptn-scoutbook-rsvp-styles', $rsvp_style_file);
   wp_enqueue_script( 'ptn-scoutbook-rsvp-js' , $rsvp_js_file, array('jquery'), '1.0', true );
   wp_localize_script('ptn-scoutbook-rsvp-js', 'scoutbookrsvp', array('ajax_url' => admin_url( 'admin-ajax.php' )));
}
add_action( 'init', 'ptn_scoutbook_rsvp_load_css_and_js' );

if (file_exists(plugin_dir_path(__FILE__)."includes/shortcodes.php")){
   include_once plugin_dir_path(__FILE__)."includes/shortcodes.php";
} 
if (file_exists(plugin_dir_path(__FILE__)."includes/scoutbook-rsvp.inc")){
   include_once plugin_dir_path(__FILE__)."includes/scoutbook-rsvp.inc";
}

function ptn_scoutbook_rsvp_activate() {
    flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ptns_scoutbook_rsvp_activate' );


/****************************************************************************/
/* Create custom post type of rsvp                                          */
/***************************************************************************/
function create_scoutbook_rsvp() {
    register_post_type( 'rsvp',
        array(
            'labels' => array(
                'name' => 'rsvp',
                'singular_name' => 'RSVP',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New RSVP',
                'edit' => 'Edit',
                'edit_item' => 'Edit RSVP',
                'new_item' => 'New RSVP',
                'view' => 'View',
                'view_item' => 'View RSVP',
                'search_items' => 'Search RSVP',
                'not_found' => 'No RSVPs found',
                'not_found_in_trash' => 'No RSVPs found in Trash',
                'parent' => 'Parent'
            ),
            'public' => true,
            'menu_position' => 15,
            'supports' => ('title'),
           // 'supports' => false,
            'taxonomies' => array( '' ),
            'menu_icon' => plugins_url( 'assets/icon-20x20.png', __FILE__ ),
            'has_archive' => true
        )
    );
}
add_action( 'init', 'create_scoutbook_rsvp' );
add_action( 'add_meta_boxes', 'ptn_scoutbook_rsvp_add_meta_boxes');
add_action( 'save_post', 'ptn_scoutbook_rsvp_save_rsvp', 10, 2 );

function ptn_scoutbook_rsvp_add_meta_boxes(){

   add_meta_box(
    'rsvp-event',
    'RSVP',
    'ptn_scoutbook_rsvp_event_init',
    'rsvp');
}

function ptn_scoutbook_rsvp_event_init(){
	    global $post;
	    // Use nonce for verification
	    wp_nonce_field( plugin_basename( __FILE__ ), 'gear_nonce' );
  ?>
      <div id="rsvp_event">
  <!--      Event: <input type="text" name="rsvpEvent" value="<?php //$the_event_ID =  get_post_meta($post->ID, 'rsvpEvent', true); echo $the_event_ID;?>" />
  -->
        <strong>Event: </strong><?php $the_event_ID =  get_post_meta($post->ID, 'rsvpEvent', true); echo $the_event_ID. ' - ';?>
        <?php echo get_the_title($the_event_ID); ?>
        <!-- Attendee: <input type="text" name="rsvpUser" value="<?php //$the_user_ID =  get_post_meta($post->ID, 'rsvpUser', true); echo $the_user_ID; ?>" />
        -->
        <p></-><strong>Attendee: </strong> <?php $the_user_ID =  get_post_meta($post->ID, 'rsvpUser', true); echo $the_user_ID.' -'; ?>
        <?php $the_user = get_userdata($the_user_ID); echo $the_user->display_name; ?>
        </p>
      </div>
      <?php
}
function ptn_scoutbook_rsvp_save_rsvp( $post_id, $post) {
    global $wpdb;

    // check to make sure this is RSVP post
    if ( $post->post_type == 'rsvp' ) {
        //$where = array( 'ID' => $post_id );
        //$wpdb->update( $wpdb->posts, array( 'post_title' => $post_id ), $where );
      
        // Store data in post meta table if present in post data
        if ( isset( $_POST['rsvpEvent'] ) && $_POST['rsvpEvent'] != '' ) {
            update_post_meta( $post_id, 'rsvpEvent', $_POST['rsvpEvent'] );
        }
        if ( isset( $_POST['rsvpUser'] ) && $_POST['rsvpUser'] != '' ) {
            update_post_meta( $post_id, 'rsvpUser', $_POST['rsvpUser'] );
        }
      }
    }

function ptn_scoutbook_rsvp_type_template($single_template) {
     global $post;

     if ($post->post_type == 'rsvp') {
          $single_template = dirname( __FILE__ ) . '/templates/single-rsvp.php';
     }
     return $single_template;
}
add_filter( 'single_template', 'ptn_scoutbook_rsvp_type_template' );

function ptn_scoutbook_rsvp_type_archive_template($archive_template) {
     global $post;
     if ($post->post_type == 'rsvp') {
          $archive_template = dirname( __FILE__ ) . '/templates/archive-rsvp.php';
     }
     return $archive_template;
}
add_filter( 'archive_template', 'ptn_scoutbook_rsvp_type_archive_template' );

function ptn_scoutbook_add_rsvp_page() {
    $my_post = array(
      'post_title'    => wp_strip_all_tags( 'Event RSVPs' ),
      'post_content'  => '[scoutbook_rsvp_event_list]',
      'post_status'   => 'publish',
      'post_author'   => 1,
      'post_type'     => 'page',
    );
    $rsvp_page_post_id = wp_insert_post( $my_post );
    $rsvp_page_permalink = get_permalink($rsvp_page_post_id);
    update_option('scoutbook_rsvp_page', $rsvp_page_permalink);
    
}
register_activation_hook(__FILE__, 'ptn_scoutbook_add_rsvp_page');


add_action( 'wp_ajax_add_rsvp', 'scoutbook_add_rsvp' ); 
function scoutbook_add_rsvp() {
  global $wpdb;
  
  $event_id = $_POST['rsvp_event'];
  $addtl_guests = $_POST['guest_count'];
  $cascade_adventure_part = $_POST['part'];
  
  //echo 'CAP: '.$cascade_adventure_part;
  

  $user_id = get_current_user_id();
  $title = $event_id.'-'.$user_id;
    
  $rsvp_post_id = wp_insert_post(
        array(
            'comment_status'    =>  'closed',
            'ping_status'       =>  'closed',
            'post_author'       =>  'scoutbook_rsvp',                
            'post_type'         =>  'rsvp',
            'post_status'       =>  'publish',
        )
    );
    
  $rsvp_arr = array(
    "user_id" => $user_id,
    "event_id" => $event_id,
    "guests" => $addtl_guests,
    "part" => $cascade_adventure_part,
    
  // add radio button stuff here  
    
  );
  update_post_meta($rsvp_post_id, 'rsvp', $rsvp_arr);
	wp_die();
}

add_action( 'wp_ajax_delete_rsvp', 'scoutbook_delete_rsvp' ); 
function scoutbook_delete_rsvp() {
   global $wpdb;
    $event_id = $_POST['rsvp_event'];
    $rsvp_post = ptn_scoutbook_get_rsvp_by_event_and_user($event_id);
    delete_post_meta($rsvp_post->meta_id);
    wp_delete_post($rsvp_post->post_id);

  wp_die();
}