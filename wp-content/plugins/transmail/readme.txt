=== ZeptoMail ===
Contributors: ZeptoMail
Tags: mail,mailer,phpmailer,wp_mail,transactional email,zoho,zoho zeptomail,zoho transmail
Donate link: none
Requires at least: 4.8
Tested up to: 6.3.1
Requires PHP: 5.6
Stable tag: 2.2.3
License: BSD
License URI: http://www.gnu.org/licenses/gpl-2.0.html

ZeptoMail Plugin lets you configure your ZeptoMail account on your WordPress site enabling you to send transactional emails of your site via ZeptoMail API.

== Description ==

= ZeptoMail for WordPress =

ZeptoMail (formerly TransMail) Plugin helps you to configure your ZeptoMail account in your WordPress site, to send notification emails from your website.
It is recommended to use authorized servers for sending transactional/ notification emails from websites, instead of using generic hosting servers. It is possible to misuse unauthorized and unauthenticated configuration and harm the reputation of your domain/ website when using generic servers. 
This plugin can help to ensure that the transactional emails are sent from your account using ZeptoMail API's and do not end up in the Spam.

== PRE-REQUISITES ==
- A ZeptoMail Account
- A self-hosted WordPress site
- PHP 5.6 or later

== ADVANTAGES OF ZeptoMail PLUGIN ==
- ZeptoMail plugin has customized the **PHPMailer’s** code library, used in WordPress for sending email.
- By using **’wp_mail’** function of WordPress, ZeptoMail plugin handles the custom send mail action anywhere from the entire site, without having to change/ configure at every occurrence.

== ZeptoMail API ==
You can use the Send Mail token of any Mail Agent in your ZeptoMail account to send transactional emails from your site using ZeptoMail API. 

== INSTALLATION ==
1) Login to your self-hosted WordPress account and navigate to the ZeptoMail plugin Account Configuration page.
2) From the **Domain** section, pick the region where your ZeptoMail account is hosted. 
3) Login to your ZeptoMail account and access the relevant **Mail Agent** .
4) Copy your existing **Send Mail token** or generate a new one from the **SMTP & API Info** section.
5) Enter the **Send Mail token** in the plugin configuration page. 
6) Enter the From **Email Address** and From **Name** .
7) Select the default **Email Format** you wish to send your transactional emails in.
6) Click **Save** .
7) Once the configuration is saved, the Plugin will be able to send emails from your website using ZeptoMail.

== ZeptoMail PLUGIN PARAMETERS ==
- **Domain** :The domain where your Zoho Account data resides.
- **Send Mail token** :Send Mail token generated in the ZeptoMail Mail Agent you wish to configure in WordPress.
- **Bounce Return path** : Bounce email address configured for the relevant Mail Agent.
- **From Email Address** :The Email address that will be used to send all the outgoing transactional emails from your website.
- **From Name** :The Name that will be shown as the display name while sending all emails from your website.
- **Email format** :Emails from the plugin will be sent in the chosen format by default.

== ZeptoMail PLUGIN TEST EMAIL ==

After configuration, you can test the plugin. Navigate to the ZeptoMail plugin - Test Email page in your Website settings.
- **To** : Email address of the recipient.
- **Subject** : Subject of the email.
- **Content** :The message or body of the email.

For in detail instructions on how to set up ZeptoMail plugin, visit [ZeptoMail plugin page](https://www.zoho.com/zeptomail/help/wordpress-plugin.html) .
**Note** :
Sending emails through ZeptoMail is subjective to our Usage Policy restrictions. Please refer to our Usage Policy details [here](https://www.zoho.com/zeptomail/terms.html).

== Frequently Asked Questions ==

1) **What is ZeptoMail?**

 ZeptoMail is a transactional email sending service by Zoho Mail. This includes emails triggered by user action on your website or application like password reset emails, welcome emails, order confirmation emails etc. Having installed WordPress, if the PHP wp_mail() function isn't working or if your notification emails are sent to spam, ZeptoMail is the service to fix these issues. 

2) **Is ZeptoMail free?**
 ZeptoMail service and this plugin are free to get started with. We provide you with 10000 free emails on sign up. If you need to send more emails, you can buy credits from your account. The pay-as-you-go plan and ensures you only pay for what you use. Find out more ZeptoMail pricing.

3) **Why aren't my HTML emails being sent?**

In order for your emails to be sent in the HTML format, you need to choose HTML from the Mail format dropdown in the ZeptoMail plugin configuration page. [Learn more](https://www.zoho.com/zeptomail/help/wordpress-plugin.html#alink3) 

4) **Can I link more than one Mail Agent with the plugin?**

As of now, you can only configure one ZeptoMail Mail Agent with the plugin. You can only send emails through the chosen Mail Agent and its associated domain, using the plugin. 

5) **Is Bounce address same as From address?**

No. During the plugin configuration, you will need to enter the From address and bounce address. From address can be any email address belonging to the domain you have associated with the Mail Agent in the plugin. Bounce address is the bounce email address you have configured for the chosen Mail Agent in ZeptoMail. [Learn more](https://www.zoho.com/zeptomail/help/bounce-address.html)

6) **Where do I go for more assistance with ZeptoMail plugin?**

You can refer our help documentation for detailed instruction about ZeptoMail and the plugin. If you require further assistance, feel free to contact support@zeptomail.com with your questions. 

== Screenshots ==
1. Configure Account(screenshot-1.png)
2. Test Mail(screenshot-2.png)

== Changelog ==
= 1.0.1 =
* Handled replyTo error in WooCommerce cases
= 1.0.2 =
* Handled email address field
= 1.0.3 = 
* Sanitizing to address
= 1.0.4 =
* Updated FAQs
= 2.0.0 =
* TransMail is now ZeptoMail, bug fix on warning messages
= 2.0.1 =
* Tested with Wordpress 5.8
= 2.0.2 =
* Domain configuration changed for better understanding.
= 2.0.3 =
* Wordpress 6.1 update.
= 2.1.0 =
* Now dynamic from address is supported. Wordpress 6.1.1 update
= 2.2.0 =
* Removed bounce address, bug fix on warning messages
= 2.2.1 =
* bug fix on warning messages
= 2.2.2 =
* bug fix on warning messages
= 2.2.3 =
* Wordpress 6.3.1 update and bug fix on notice

== Upgrade Notice ==
none


