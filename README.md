#Gmail Inbox Actions

Allows to approve WordPress comment from your Gmail inbox, without logging into your site.
## Requirements:

- Emails must be [authenticated via DKIM or SPF](https://support.google.com/mail/answer/180707?hl=en)
	- If you are unable to set DKIM settings on your server, you can opt for [Postmark] (https://postmarkapp.com) or Google Apps service
- Your domain of the DKIM or SPF signatures must match the domain of your From: email address exactly. eg for From: foo@bar.com the DKIM must be for the bar.com domain and not a subdomain such as email.bar.com.
- Emails must come from a static email address, eg foo@bar.com
- Emails must follow [Google's general email guidelines](https://support.google.com/mail/answer/81126?hl=en)
- [register with Google](https://developers.google.com/gmail/actions/registering-with-google) so you can see the Approve comment button in your inbox.


[Full list of requirements from Google](https://developers.google.com/gmail/actions/registering-with-google)


## Installation
Follows the normal installation, you can upload the gmail-inbox-action folder to `wp-content/plugins` folder

## Configuration

Update your Gmail account email in general settings.
