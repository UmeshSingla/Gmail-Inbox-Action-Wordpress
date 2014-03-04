<?php
/**
 * Plugin Name:	Gmail Inbox Action for Wordpress
 * Author: Umesh Kumar<umeshsingla05@gmail.com>
 * Author URI:	http://codechutney.com
 * Description:	Adds one click approval option for comments in Gmail
 * Version: 0.1
 * Text Domain : gia_inscub
 */
define('WP_GIA_TRANSLATION_DOMAIN', 'gia_inscub');
define( 'WP_GIA_URL', plugins_url('', __FILE__) );
define('WP_GIA_PLUGIN_FOLDER', dirname(__FILE__) );

/* Define all necessary variables first */
define( 'WP_GIA_CSS', WP_GIA_URL. "/assets/css/" );
define( 'WP_GIA_JS',  WP_GIA_URL. "/assets/js/" );
// Includes PHP files located in 'lib' folder
foreach( glob ( dirname(__FILE__). "/lib/*.php" ) as $lib_filename ) {
     require_once( $lib_filename );
}
class GmailInboxAction {
    public function __construct() {
//        add_filter('comment_moderation_text', array( $this, 'gia_modify_comment_text'), '', 2 );
//        add_action('comment_post', array( $this, 'verify_working') );
    }
    
}
add_action('init', function() { new GmailInboxAction(); } );