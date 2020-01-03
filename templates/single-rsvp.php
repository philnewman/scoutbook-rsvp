<?php
/**
 * Plugin Name: Scoutbook RSVP
 * Plugin URI: http://troop351.org/plugins/scoutbook-rsvp
 * Description:
 * Version: 0.1
 * Author: Phil Newman
 * Author URI: http://getyourphil.net
 * License: GPL3$classes[] = $slug;
 * License URI: http://www.gnu.org/licenses/gpl-3.0.en.html
 **/


global $post;
$rsvp = get_post();

  wp_head();
  $rsvpeventid = get_post_meta($rsvp->ID, 'rsvpEvent', true);
  $rsvpuserid = get_post_meta($rsvp->ID, 'rsvpUser', true);

  // Lookup event & user by ids
  $rsvpEvent = get_post($rsvpeventid);
  $rsvpUser = get_user_by('id', $rsvpuserid);

?>
 <body <?php body_class(); ?>>
   <div id="content" class="site-content">
      <div class="container main-content-area">
          <div class="row side-pull-left">
            <div class="main-content-inner col-sm-12 col-md-8">
              <div class="content-area" id="primary">
                <main id="main" class="site-main" role="main">
                  <?php $post_class_id = 'post-'.$rsvp->ID; ?>
                  <article id="<?php echo 'post-'.$rsvp->ID;?>"  class="<?php echo 'post-'.$rsvp->ID;?> post-type-rsvp">
                    <div class="post-inner-content">
                      <header class="entry-header page-header">
                        <h1 class="entry-title">
                          <?php echo $rsvpEvent->post_title;?>
                        </h1>
                        <h2>
                          <?php echo $rsvpUser->display_name;?>
                        </h2>
                        <div class="entry-meta">
                          
                        </div>
                      </header>  
                      </div> <!--gear_history-->
                      </div><!--entry-content-->                  
                    </div><!--post-inner-content-->
                  </article>
                </main>
              </div><!--primary-->  
            </div><!--main-content-inner-->
        </div><!--row-->
       </div><!--container-->
   </div><!--content-->
  <?php get_sidebar(); get_footer();
  ?></body><?php
?>
