<?php
if(!function_exists('wp_notify_moderator')){
    function wp_notify_moderator($comment_id) {
            global $wpdb;

            if ( 0 == get_option( 'moderation_notify' ) )
                    return true;

            $comment = get_comment($comment_id);
            $post = get_post($comment->comment_post_ID);
            //update comment secret for gmail
            $gmailinboxaction = new GmailInboxAction();
            $comment_secret = $gmailinboxaction->gia_set_comment_secret($comment_id);
            $user = get_userdata( $post->post_author );
            // Send to the administration and to the post author if the author can modify the comment.
            $emails = array( get_option( 'admin_email' ) );
            if ( user_can( $user->ID, 'edit_comment', $comment_id ) && ! empty( $user->user_email ) ) {
                    if ( 0 !== strcasecmp( $user->user_email, get_option( 'admin_email' ) ) )
                            $emails[] = $user->user_email;
            }

            $comment_author_domain = @gethostbyaddr($comment->comment_author_IP);
            $comments_waiting = $wpdb->get_var("SELECT count(comment_ID) FROM $wpdb->comments WHERE comment_approved = '0'");

            // The blogname option is escaped with esc_html on the way into the database in sanitize_option
            // we want to reverse this for the plain text arena of emails.
            $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
            $subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), $blogname, $post->post_title );
            $subject         = apply_filters( 'comment_moderation_subject',    $subject,         $comment_id );
            $notify_message = '<html>
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
                          "url": "'.  admin_url('admin-ajax.php'). '?action=gia_approve_comment&id='. $comment_id . '&token='. $comment_secret .'"
                        }
                      },
                      "description": "Approval request for John $10.13 expense for office supplies"
                    }
                    </script>';
           switch ( $comment->comment_type ) {
                    case 'trackback':
                            $notify_message .= sprintf( __('A new trackback on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
                            $notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
                            $notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                            $notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
                            $notify_message .= __('Trackback excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
                            break;
                    case 'pingback':
                            $notify_message .= sprintf( __('A new pingback on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
                            $notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
                            $notify_message .= sprintf( __('Website : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                            $notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
                            $notify_message .= __('Pingback excerpt: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
                            break;
                    default: // Comments
                            $notify_message .= sprintf( __('A new comment on the post "%s" is waiting for your approval'), $post->post_title ) . "\r\n";
                            $notify_message .= get_permalink($comment->comment_post_ID) . "\r\n\r\n";
                            $notify_message .= sprintf( __('Author : %1$s (IP: %2$s , %3$s)'), $comment->comment_author, $comment->comment_author_IP, $comment_author_domain ) . "\r\n";
                            $notify_message .= sprintf( __('E-mail : %s'), $comment->comment_author_email ) . "\r\n";
                            $notify_message .= sprintf( __('URL    : %s'), $comment->comment_author_url ) . "\r\n";
                            $notify_message .= sprintf( __('Whois  : http://whois.arin.net/rest/ip/%s'), $comment->comment_author_IP ) . "\r\n";
                            $notify_message .= __('Comment: ') . "\r\n" . $comment->comment_content . "\r\n\r\n";
                            break;
            }

            $notify_message .= sprintf( __('Approve it: %s'),  admin_url("comment.php?action=approve&c=$comment_id") ) . "\r\n";
            if ( EMPTY_TRASH_DAYS )
                    $notify_message .= sprintf( __('Trash it: %s'), admin_url("comment.php?action=trash&c=$comment_id") ) . "\r\n";
            else
                    $notify_message .= sprintf( __('Delete it: %s'), admin_url("comment.php?action=delete&c=$comment_id") ) . "\r\n";
            $notify_message .= sprintf( __('Spam it: %s'), admin_url("comment.php?action=spam&c=$comment_id") ) . "\r\n";

            $notify_message .= sprintf( _n('Currently %s comment is waiting for approval. Please visit the moderation panel:',
                    'Currently %s comments are waiting for approval. Please visit the moderation panel:', $comments_waiting), number_format_i18n($comments_waiting) ) . "\r\n";
            $notify_message .= admin_url("edit-comments.php?comment_status=moderated") . "\r\n";
            $notify_message .= '</body>
                </html>';

            $subject = sprintf( __('[%1$s] Please moderate: "%2$s"'), $blogname, $post->post_title );
            $message_headers = '';
            $emails          = apply_filters( 'comment_moderation_recipients', $emails,          $comment_id );
            $notify_message  = apply_filters( 'comment_moderation_text',       $notify_message,  $comment_id );
            $subject         = apply_filters( 'comment_moderation_subject',    $subject,         $comment_id );
            $message_headers = apply_filters( 'comment_moderation_headers',    $message_headers, $comment_id );
            add_filter( 'wp_mail_content_type', 'gia_set_html_content_type' );
            foreach ( $emails as $email ) {
                    $sent[] = wp_mail( $email, $subject, $notify_message, $message_headers );
            }
            remove_filter( 'wp_mail_content_type', 'gia_set_html_content_type' );
    }
}
function gia_set_html_content_type(){
    return 'text/html';
}
