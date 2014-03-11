<?php
/**
 * Class GmailInboxAction
 * 
 * Handles the Ajax request and filters notification content to add script for Gmail Action, generates sha1 token.
 */
class GmailInboxAction {

    /**
     * @var string
     * contains comment secret sent in mail
     */
    var $secret;

    /**
     * register action and filters on object creation
     */
    public function __construct() {
        $this->secret = '';
        add_action( 'wp_ajax_nopriv_gia_approve_comment', array( $this, 'gia_approve_comment' ) );
        add_action( 'wp_ajax_gia_approve_comment', array( $this, 'gia_approve_comment' ) );
        add_action( 'comment_post', array( $this, 'filter_mail_content_type' ) );
        add_action( 'wp_set_comment_status', array( $this, 'remove_comment_secret' ), '', 2 );
    }

    /**
     * Calls generate_comment_secret and update_comment_secret for setting comment secret
     * @param $comment_id
     *
     * @return string, If updated return string
     */
    public function set_comment_secret( $comment_id ) {
        //if empty comment id
        if ( !$comment_id ) {
            return;
        }
        //else generate comment secret
        $comment_secret = $this->generate_comment_secret( $comment_id );
        //if comment secret, update comment meta
        if ( $comment_secret ) {
            $updated = $this->update_comment_secret( $comment_id, $comment_secret );
        }

        return $comment_secret;
    }

    /**
     * Generate comment secret for comment id
     * @param $comment_id
     *
     * @return string, hash
     */
    public function generate_comment_secret( $comment_id ) {
        $static_num = 8;
        $rand = rand( 1111, getrandmax() );
        $current_time = time();
        $str = $static_num * $comment_id . $current_time . $rand;

        return sha1( $str );
    }

    /**
     * Stores comment secret in meta
     * @param $comment_id
     * @param $comment_secret
     *
     * @return mixed
     */
    public function update_comment_secret( $comment_id, $comment_secret ) {
        if ( !$comment_id || !$comment_secret ) {
            return;
        }

        return update_comment_meta( $comment_id, 'comment_secret', $comment_secret );
    }

    /**
     * Verify comment secret
     * @param $comment_id
     * @param $comment_secret
     *
     * @return bool
     */
    public function verify_comment_secret( $comment_id, $comment_secret ) {
        if ( !$comment_id || !$comment_secret ) {
            return;
        }
        $actual_comment_secret = get_comment_meta( $comment_id, 'comment_secret', true );
        if ( $actual_comment_secret === $comment_secret ) {
            return true;
        }
    }

    /**
     * Check comment secret and set header
     */
    public function gia_approve_comment() {
        if ( empty( $_REQUEST['id'] ) || empty( $_REQUEST['token'] ) ) {
            $this->set_headers_401();
        }
        //Check Gmail headers
        if ( !$_SERVER['REQUEST_METHOD'] == 'POST' ||
                !isset( $_SERVER['HTTP_USER_AGENT'] ) ||
                $_SERVER['HTTP_USER_AGENT'] != 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/1.0 (KHTML, like Gecko; Gmail Actions)' ) {
            $this->set_headers_401();
        }

        //Check if comment is already approved
        $comment = get_comment( $_REQUEST['id'] );

        //if no object => invalid ID, or comment status changed
        if ( !$comment || $comment->comment_approved != '0' ) {
            $this->set_headers_401();
        }

        //verify token
        $verified = $this->verify_comment_secret( $_REQUEST['id'], $_REQUEST['token'] );
        if ( !$verified ) {
            $this->set_headers_401();
        }
        //approve comment
        $updated = wp_set_comment_status( $_REQUEST['id'], 'approve', FALSE );
        //if approved
        if ( $updated ) {
            //set Headers for gmail
            header( 'HTTP/1.1 200 OK', true, 200 );

            die( 1 );
        } else {
            header( 'HTTP/1.1 400 Bad Request', true, 400 );
            die;
        }
    }

    /**
     * Filter notification mail to append gmail action schema
     * @param $notify_message
     * @param $comment_id
     *
     * @return string
     */
    public function gia_modify_notification_text( $notify_message, $comment_id ) {
        $message = '<html>
            <body>
            <script type="application/ld+json">
                {
                    "@context": "http://schema.org",
                    "@type": "EmailMessage",
                    "action": {
                        "@type": "ConfirmAction",
                        "name": "Approve Comment",
                        "handler": {
                            "@type": "HttpActionHandler",
                            "url": "' . admin_url( 'admin-ajax.php' ) . '?action=gia_approve_comment&id=' . $comment_id . '&token=' . $this->secret . '",
                            "method": "POST"
                        }
                    },
                    "description": "Approval request for comment"
                }
            </script>';
        $message .= $notify_message;
        $message .= '</body>
                </html>';
        return $message;
    }

    /**
     * Hooked on 'comment_post', actually calls set_comment_secret function
     * @param $comment_id
     */
    function filter_mail_content_type( $comment_id ) {
        //update comment secret for gmail
        $this->secret = $this->set_comment_secret( $comment_id );
        add_filter( 'comment_moderation_text', array( $this, 'gia_modify_notification_text' ), 1, 2 );
        add_filter( 'wp_mail_content_type', array( $this, 'set_html_content_type' ) );
    }

    /**
     * Set mail content type
     * @return string
     */
    function set_html_content_type() {
        return 'text/html';
    }
    /**
     * Delete comment secret from meta if comment status is changed
     * @param type $comment_id
     * @param type $comment_status
     */
    function remove_comment_secret($comment_id, $comment_status){
        if( !$comment_status || !$comment_id ){
            return;
        }
        delete_comment_meta($comment_id, 'comment_secret');
    }
    /**
     * Set headers for Gmail
     */
    function set_headers_401(){
        header( 'HTTP/1.1 401 Unauthorized', true, 401 );
        exit;
    }
}
