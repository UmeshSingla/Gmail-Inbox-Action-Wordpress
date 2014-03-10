<?php
/**
 * Plugin Name:    Gmail Inbox Action for Wordpress
 * Author: Umesh Kumar<umeshsingla05@gmail.com>
 * Author URI:    http://codechutney.com
 * Description:    Adds one click approval option for comments in Gmail
 * Version: 0.1
 * Text Domain : gia_inscub
 */
/**
 * Used for Tranlation
 */
define( 'WP_GIA_TRANSLATION_DOMAIN', 'gia_inscub' );

foreach ( glob( dirname( __FILE__ ) . '/app/*.php' ) as $lib_filename ) {
    require_once( $lib_filename );
}
/**
 *Create a instance of GmailInboxAction class
 */
function gia_initate_class() {
	global $gmailinboxaction;
	$gmailinboxaction = new GmailInboxAction();
}
add_action( 'init', 'gia_initate_class' );
