<?php

//Gmail Inbox Action class to handle ajax request
class   GmailInboxAction {
	public function    __construct() {
		add_action( 'wp_ajax_nopriv_gia_approve_comment', array( $this, 'gia_approve_comment' ) );
		add_action( 'wp_ajax_gia_approve_comment', array( $this, 'gia_approve_comment' ) );
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
			return 'verified';
		}
	}

	public function gia_approve_comment() {
		if ( empty( $_REQUEST[ 'id' ] ) || empty( $_REQUEST[ 'token' ] ) ) {
			echo '401 (Unauthorized)';
			die;
		}
		//uncomment when implementing
		//        if(   !isset($_SERVER['HTTP_USER_AGENT']) ||  (strpos($_SERVER['HTTP_USER_AGENT'],    'Gmail Actions')    ==  false) || (strpos($_SERVER['HTTP_USER_AGENT'],'gecko') == false)    )   {   echo    '401 (Unauthorized)';   die;}
		//verify token
		$verified = $this->gia_verify_comment_secret( $_REQUEST[ 'id' ], $_REQUEST[ 'token' ] );
		if ( ! $verified || $verified != 'verified' ) {
			echo '401 (Unauthorized)';
			die;
		}
		//approve comment
		$updated = wp_set_comment_status( $_REQUEST[ 'id' ], 'approve' );
		if ( ob_get_contents() ) {
			ob_get_clean();
		}
		if ( ! $updated ) {
			echo '400 (Bad Request)';
			die;
		}
		//if approved
		echo '200 (OK)';
		die( 1 );
	}
}