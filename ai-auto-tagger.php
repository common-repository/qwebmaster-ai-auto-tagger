<?php
/*
Plugin Name: Qwebmaster AI Auto Tagger
Plugin URI: http://qwebmaster.com/qwebmaster-ai-auto-tagger
Description: Automatically tags all posts using Artificial intelligence
Author: Aleksander Spasovski
Version: 1.0
Author URI: http://www.qwebmaster.com/
*/

if (!defined('WPINC')) {
    die;
}

/**
 *
 * Woocommerce AI Auto Tagger Load Admin Settings
 *
 * @since v1 Initial Public Release.
 */
if ( is_admin() ) {
    require_once('admin/settings.php');
    // $options = get_option( 'qaiau_option' );
    // $options['option']
}

/**
 *
 * Woocommerce AI Auto Tagger Shortcode
 *
 * @since v1 Initial Public Release.
 */
add_shortcode('qwebmaster_ai_auto_tagger_shortcode', 'qwebmaster_ai_auto_tagger_shortcode');
function qwebmaster_ai_auto_tagger_shortcode( $atts = array(), $content = null ) {
qwebmaster_ai_auto_tagger_api();
}

/**
 *
 *
 * Woocommerce AI Auto Tagger Get Keywords
 *
 * @since v1.0 Initial Public Release.
 */
function qwebmaster_ai_auto_tagger_api($post_id) {
  $url = 'https://api.qwebmaster.com/keywords';
  $content= strip_tags(get_post_field('post_content', $post_id));
  $response = wp_remote_post($url, array(
          'method' => 'POST',
          'headers' => array('Content-Type' => 'multipart/form-data; boundary=----WebKitFormBoundary7MA4YWxkTrZu0gW'),
          'httpversion' => '1.0',
          'sslverify' => false,
          'body' => "------WebKitFormBoundary7MA4YWxkTrZu0gW\r\nContent-Disposition: form-data; name=\"content\"\r\n\r\n".$content."\r\n------WebKitFormBoundary7MA4YWxkTrZu0gW--"
      ));
      if ( is_wp_error( $response ) ) {
         $error_message = $response->get_error_message();
         echo "Something went wrong: $error_message";
      } else {
        $response = json_decode($response['body'],true);
        foreach ($response['keywords'] as $key => $keyword) {
            // If match has over 4 weight add it to the post tag list.
            if ($keyword[1]  >= 4 ) {
              wp_set_post_tags( $post_id, array( $keyword[0] ), true );
            }
        }
      }
}

/**
 *
 *
 * Woocommerce AI Auto Tagger AJAX
 *
 * @since v1.0 Initial Public Release.
 */
 function qaiau_scan_ajax_admin() {
  if (isset($_POST['status']) && isset($_POST['id']) && is_numeric($_POST['id'])) {
    $status = sanitize_text_field($_POST['status']);
    $post_id = sanitize_text_field($_POST['id']);
    if($status == 'true') {
        update_post_meta( $post_id, '_qwebmaster_ai_auto_tagger', 'on' );
    } else {
        update_post_meta( $post_id, '_qwebmaster_ai_auto_tagger', 'off' );
    }
  }
}
add_action('wp_ajax_qaiau_ajax_request', 'qaiau_scan_ajax_admin');

/**
 *
 *
 * Woocommerce AI Auto Tagger AJAX Scan and Tag All Posts
 *
 * @since v1.0 Initial Public Release.
 */
function qaiau_scan_ajax_scan() {
   qwebmaster_ai_auto_tag_all_posts();
}
add_action('wp_ajax_qaiau_scan_posts', 'qaiau_scan_ajax_scan');

/**
 *
 *
 * Woocommerce AI Auto Tagger Load required styles and scripts
 *
 * @since v1.0 Initial Public Release.
 */
add_action( 'admin_enqueue_scripts', 'qwebmaster_ai_auto_tagger_enqueue' );
function qwebmaster_ai_auto_tagger_enqueue($hook) {
  global $post;
    //if( 'index.php' != $hook ) return;  // Only applies to dashboard panel
    wp_enqueue_script( 'qaiau_ajax_script', plugins_url( '/js/qaiau.js', __FILE__ ), array('jquery'));
    wp_enqueue_style( 'qaiau_style', plugins_url( '/css/qaiau.css', __FILE__ ));
    wp_localize_script( 'qaiau_ajax_script', 'qaiau_ajax_object',
            array( 'ajax_url' => admin_url( 'admin-ajax.php' ), 'post_id' => $post->ID ) );
}

/**
 *
 *
 * Woocommerce AI Auto Tagger Add field to the post tag's meta box
 *
 * @since v1.0 Initial Public Release.
 */
add_filter( 'register_taxonomy_args', function( $args, $taxonomy )
{
    // Replace the original (post_tag) metabox callback with our wrapper
    if( 'post_tag' === $taxonomy || 'product_tag_beta' === $taxonomy )
        $args['meta_box_cb'] = 'qwebmaster_ai_auto_tagger_post_tags_meta_box';
    return $args;
}, 10, 2 );
function qwebmaster_ai_auto_tagger_post_tags_meta_box( $post, $box )
{
    // Custom action
    do_action( 'qwebmaster_ai_auto_tagger_before_post_tags_meta_box', $post, $box );
    // Original callback. Note it will echo the stuff, not return it
    post_tags_meta_box( $post, $box );
}
add_action( 'qwebmaster_ai_auto_tagger_before_post_tags_meta_box', 'qwebmaster_ai_auto_tagger_save_post2', 10, 3 );
function qwebmaster_ai_auto_tagger_save_post2() {
  global $post;
  $class = '';
  $let_active = 'false';
  if (get_post_meta( $post->ID, '_qwebmaster_ai_auto_tagger', true ) == 'on') {
    $class = 'active';
    $let_active = 'true';
  }
  //var_dump(qwebmaster_ai_auto_tagger_api($post->ID));
?>
<div class="qwebmaster-ai-on-off">
<h2>AI Auto Tagger</h2>
  <h3 class="heading <?php echo $class ?>">Click to turn
    <span class="on">
      <span>
        on
      </span>
    </span>
    <span class="off">
      <span>
        off
      </span>
    </span>
  </h3>
  <button type="button" class="btn <?php echo $class ?>">
    <span>
      <b></b>
      <svg viewBox="-5.5 -5.5 71 71" id="circle">
        <circle cx="30" cy="30" r="30" stroke="white" stroke-width="11" fill="transparent"></circle>
      </svg>
    </span>
  </button>
</div>
<script>
const btn = document.querySelector('.btn');
const heading = document.querySelector('.heading');
let active = <?php echo $let_active; ?>;
function clickHandler() {
  active = !active;
  btn.classList.add('animating');
}
btn.addEventListener('animationend', () => {
    btn.classList.remove('animating');
    if (active === true) {
      btn.classList.add('active');
      heading.classList.add('active');
    } else {
      btn.classList.remove('active');
      heading.classList.remove('active');
    }
    var data = {
        action:'qaiau_ajax_request',
        id:qaiau_ajax_object.post_id,
        status:active
    };
  jQuery.post(qaiau_ajax_object.ajax_url, data, function(response) {
  });
});
btn.addEventListener('click', clickHandler);
</script>
<?php
}

/**
 *
 *
 * Woocommerce AI Auto Tagger Actions
 *
 * @since v1.0 Initial Public Release.
 */
add_action( 'save_post', 'qwebmaster_ai_auto_tagger_save_post', 10, 3 );
function qwebmaster_ai_auto_tagger_save_post($post_id, $post, $update ) {
 if( empty( get_post_meta( $post_id, '_qwebmaster_ai_auto_tagger', true ) ) ) : update_post_meta( $post_id, '_qwebmaster_ai_auto_tagger', 'on' ); endif;
   if (get_post_meta( $post_id, '_qwebmaster_ai_auto_tagger', true ) == 'on') {
     qwebmaster_ai_auto_tagger_api($post_id);
   }
}

/**
 *
 *
 * Woocommerce AI Auto Loop and tag all posts
 *
 * @since v1.0 Initial Public Release.
 */
 function qwebmaster_ai_auto_tag_all_posts() {
    $args = array(
       'posts_per_page' => -1,
       //'cat'=>1955,
       'post_status'=>'publish',
       'meta_query' => array(
                      array(
                         'key' => '_qwebmaster_ai_auto_tagger',
                         'compare' => 'NOT EXISTS'
                      ),
       ));
       query_posts($args);
       if ( have_posts() ) :
         while ( have_posts() ) : the_post();
            qwebmaster_ai_auto_tagger_api(get_the_ID());
            update_post_meta( get_the_ID(), '_qwebmaster_ai_auto_tagger', 'on' );
         endwhile;
       endif;
      wp_reset_query();
}
