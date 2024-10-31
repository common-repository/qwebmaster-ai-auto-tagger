<?php
/**
 *  QAIAU Admin Settings Class
 *
 * @since 1.0
 * @debug
 */

class QAIAUsettingsPage
{
 /**
  * Holds the values to be used in the fields callbacks
  */
 private $options;

 /**
  * Start up
  */
 public function __construct()
 {
     add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
     add_action( 'admin_init', array( $this, 'page_init' ) );
     // Footer Hook
     add_action('admin_head',  array( $this, 'qaiau_admin_footer' ) );
}

 /**
  * Add options page
  */
 public function add_plugin_page()
 {
     add_submenu_page(
     'edit.php',
     'AI Auto Tagger',
     'AI Auto Tagger',
     'manage_options',
     'qaiau-auto-tagger',
     array( $this, 'create_admin_page')
   );
 }


 /**
  * Options page callback
  */
 public function create_admin_page()
 {
     // Set class property
     $this->options = get_option( 'qaiau_option' );
     ?>
     <div class="qaiau_settings_wrapper">
         <h1>Qwebmaster AI Auto Tagger</h1>
         <form method="post" action="options.php">
         <?php
             // This prints out all hidden setting fields
             settings_fields( 'qaiau_option_group' );
             do_settings_sections( 'qaiau-auto-tagger' );
             //submit_button();
         ?>
         </form>
     </div>
     <?php
 }


 /**
  * Register and add settings
  */
 public function page_init()
 {

     register_setting(
         'qaiau_option_group', // Option group
         'qaiau_option', // Option name
         array( $this, 'sanitize' ) // Sanitize
     );


     //
     // How It Works Section
     //
     add_settings_section(
         'qaiau_general_how_it_works', // ID
         '', // Title
         array( $this, 'print_section_how_it_works' ), // Callback
         'qaiau-auto-tagger' // Page
     );


 }



 /**
  * Sanitize each setting field as needed
  *
  * @param array $input Contains all settings fields as array keys
  */
 public function sanitize( $input )
 {
     return $input;
 }




 /**
  * Section title
  */
 public function qwebmaster_qaiau_general_settings_title($title)
 {
 }


 /**
  * Blank
  */
 public function qaiau_print_section_blank()
 {

 }



 /**
  *
  *  How It Works Callbacks
  *
  */

 /**
  * How it Works
  */
 public function print_section_how_it_works()
 {
     //print 'Enter your settings below:';
echo '<div class="qaiau_how_it_works">';
echo '<h2>How It Works</h2>';
echo '<div class ="qaiau_padding_20">';
echo '<p>Using Artificial Antelligence, AI Auto Tagger will scan your post/s content and get the most relevant "Keywords" and add them as post tags. Accuracy is around 90%.<p>';
echo '<p>Each time you save or edit a post the tags will be updated. This can be turned on or off for individual posts.<p>';
echo '<p>To scan and tag all your posts click the button below.<p>';
echo '<input type="button" id="qaiau_scan" class="button button-primary" value="Scan & Tag All Posts">';
echo '<div class="aiau_scan_loading">';
echo '</div>';
echo '</div>';
echo '</div>';
echo '
 <style>
 </style>
 <script>
 jQuery( "#qaiau_scan" ).click(function() {
   jQuery(".aiau_scan_loading").html("<center><h2>Scanning Pease Wait...</h2></center>");
   var data = {
       action:"qaiau_scan_posts"
   };
 jQuery.post(qaiau_ajax_object.ajax_url, data, function(response) {
   jQuery(".aiau_scan_loading").html("<center><h2>DONE!</h2></center>");
 });

 });
 </script>
     ';
 }




 /**
  * Load Admin Styles and JS
  */
 public function qaiau_admin_footer() {
   global $hook_suffix;
   // Only load on plugin settings page
   if ($hook_suffix == 'posts_page_qaiau-auto-tagger') {
   ?>
    <style>
        .qaiau_settings_wrapper {
          padding-right: 20px;
        }
        #wpcontent {
          margin-left: 140px;
        }
        #wpbody {
          /* background-color: #ffffff; */
          padding-left: 20px;
        }
        .qaiau_how_it_works {
         padding: 0px;
         background: #fff;
         margin: 30px 0px;
       }
       .qaiau_settings_wrapper h2 {
         width: auto;
         padding: 16px;
         background: #2196f3;
         color: #ffffff;
         text-transform: uppercase;
       }
       .qaiau_option_wrap, .qaiau_input_wrap {
         margin-top: 20px;
       }
       .qaiau_option_wrap p {
         font-weight: bold;
       }
       .qaiau_sub_section {
         padding: 20px;
         background: #ffffff;
       }
       .qaiau_padding_20 {
         padding: 20px;
       }
    </style>
  <script>
  (function( $ ) {
    $(function() {
        // Add Color Picker to all inputs that have 'color-field' class
        $( '.qaiau-color-picker' ).wpColorPicker();
    });
  })( jQuery );
  </script>
   <?php
   } // Only load on plugin settings page
 }


}

if( is_admin() )
 $wsrr_settings_page = new QAIAUsettingsPage();
