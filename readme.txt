=== Gmail Comment Approval ===
Contributors: UmeshSingla
Donate link: http://github.com/UmeshSingla
Tags: comment moderation, gmail actions
Requires at least: 3.5
Tested up to: 3.8.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

One click comment approval for Wordpress from your Gmail account.

== Description ==

This plugin incorporates the Gmail action API for WordPress comment approval. You can directly
approve the comments from your Gmail inbox without logging into your site.

== Installation ==

1. Upload `gmail-inbox-action` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You should have https enabled for your site
3. For proper working you need to make sure that your emails are DKIM signed
4. Register your email with google: http://goo.gl/siU783

**For detailed reference:**
Visit [Gmail Comment Approval](http://codechutney.com/gmail-comment-approval-using-gmail-actions)

== Frequently Asked Questions ==

= What is DKIM signed emails? =

DKIM signatures are used to avoid spamming of mails, you can install it on
your server using this tutorial [Setup DKIM](http://goo.gl/pKwTfl).

= Why I need to register with google? =

Google keeps track of emails implementing Gmail actions to avoid spamming.

= Which email do i need to register ? =

Register the email from which your server sends the email, to make sure the action
button is displayed

== Other Notes ==
Refer the following link to know more about registration : [Registering email](http://goo.gl/K9ArOP)

== Screenshots ==

1. Gmail screenshot

== Changelog ==

= 1.0 =
First Release