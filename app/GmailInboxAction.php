<?php

//Gmail Inbox Action class to handle ajax request
class   GmailInboxAction {
        var $secret;
	public function    __construct() {
                $this->secret = '';
		add_action( 'wp_ajax_nopriv_gia_approve_comment', array( $this, 'gia_approve_comment' ) );
		add_action( 'wp_ajax_gia_approve_comment', array( $this, 'gia_approve_comment' ) );
                add_action('comment_post', array($this, 'gia_filter_mail_content_type') );
                add_filter('comment_moderation_text', array($this, 'gia_modify_notification_text'), '', 2);
	}

	//Generates and updates comment secret
	public function gia_set_comment_secret( $comment_id ) {
		//if empty comment id
		if ( ! $comment_id ) {
			return;
		}
		//else generate comment secret
		$comment_secret = $this->gia_generate_comment_secret( $comment_id );
		//if comment secret, update comment meta
		if ( $comment_secret ) {
			$updated = $this->gia_update_comment_secret( $comment_id, $comment_secret );
		}

		return $comment_secret;
	}

	public function gia_generate_comment_secret( $comment_id ) {
		$static_num   = 8;
		$rand         = rand( 1111, getrandmax() );
		$current_time = time();
		$str          = $static_num * $comment_id . $current_time . $rand;

		return sha1( $str );
	}

	public function    gia_update_comment_secret( $comment_id, $comment_secret ) {
		if ( ! $comment_id || ! $comment_secret ) {
			return;
		}

		return update_comment_meta( $comment_id, 'comment_secret', $comment_secret );
	}

	//verify comment secret
	public function    gia_verify_comment_secret( $comment_id, $comment_secret ) {
		if ( ! $comment_id || ! $comment_secret ) {
			return;
		}
		$actual_comment_secret = get_comment_meta( $comment_id, 'comment_secret', true );
		if ( $actual_comment_secret === $comment_secret ) {
			return true;
		}
	}

	public function gia_approve_comment() {
		if ( empty( $_REQUEST[ 'id' ] ) || empty( $_REQUEST[ 'token' ] ) ) {
			header('HTTP/1.1 401 Unauthorized', true, 401);
			die;
		}
		//uncomment when implementing
		//        if(   !isset($_SERVER['HTTP_USER_AGENT']) ||  (strpos($_SERVER['HTTP_USER_AGENT'],    'Gmail Actions')    ==  false) || (strpos($_SERVER['HTTP_USER_AGENT'],'gecko') == false)    )   {   header('HTTP/1.1 401 Unauthorized', true, 401);   die;}
		//verify token
		$verified = $this->gia_verify_comment_secret( $_REQUEST[ 'id' ], $_REQUEST[ 'token' ] );
		if ( ! $verified ) {
			header('HTTP/1.1 401 Unauthorized', true, 401);
			die;
		}
		//approve comment
		$updated = wp_set_comment_status( $_REQUEST[ 'id' ], 'approve' );
		if ( ob_get_contents() ) {
			ob_get_clean();
		}
		if ( ! $updated ) {
			header('HTTP/1.1 400 Bad Request', true, 400);
			die;
		}
		//if approved
		header('HTTP/1.1 200 OK', true, 200);
		die( 1 );
	}
        public function gia_modify_notification_text($notify_message, $comment_id){
            $message = '<html>
            <body>
            <script type="application/ld+json">
                {
                    "@context": "http://schema.org",
                    "@type": "Comment",
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
        function gia_filter_mail_content_type($comment_id){
            //update comment secret for gmail
            $this->secret   = $this->gia_set_comment_secret( $comment_id );
            add_filter( 'wp_mail_content_type', array($this, 'gia_set_html_content_type' ) );
        }
        function    gia_set_html_content_type() {
            return 'text/html';
        }
}