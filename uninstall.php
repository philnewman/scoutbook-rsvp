<?php

// if uninstall.php is not called by WordPress, die
if (!defined('WP_UNINSTALL_PLUGIN')) {
    die;
}

global $wpdb;

$ptn_scoutbook_rsvp_option = get_option('scoutbook_rsvp_page');
$wpdb->delete("wp_posts", array('GUID'=> $ptn_scoutbook_rsvp_option, 'post_type'=>'page'));
$wpdb->delete("wp_posts", array('post_type'=>'rsvp'));
delete_option('scoutbook_rsvp_page');