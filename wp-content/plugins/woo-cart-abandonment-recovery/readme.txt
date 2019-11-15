=== WooCommerce Cart Abandonment Recovery ===
Contributors: brainstormforce, wpcrafter
Donate link: https://www.paypal.me/BrainstormForce
Tags: woocommerce, cart abandonment, cart recovery
Requires at least: 4.4
Tested up to: 5.2
Stable tag: 1.1.5
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Recover your lost revenue. Capture email address of users on the checkout page and send follow up emails if they don't complete the purchase.

== Description ==
WooCommerce Cart Abandonment Recovery plugin is best plugin to capture email address of users on the checkout page and send follow up emails if they don't complete the purchase. Ultimately, it will recover your lost revenue.


== Installation ==

1. Upload `woocommerce-cart-abandonment-recovery.zip` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= Does this plugin work with CartFlows? =

Yes, of course. We have built it in a way that it will work with CartFlows as well as with WooCommerce.

== Screenshots ==

== Changelog ==
= Version 1.1.5 - Tuesday, 9th July 2019
* Fix: Other crons disappearing issue.

= Version 1.1.4 - Tuesday, 9th July 2019
* Fix: Follow up emails were getting sent even after the completion of the order.
* Fix: Email template variable 'Abandoned Product Names' warning issue.

= Version 1.1.3 - Thursday, 27th June 2019
* Improvement: Added checkout link for abandoned cart inside the admin section.
* Fix: Added pagination for reports.
* Fix: Recover report calculations before campaign triggers.
* Fix: Empty cart notice when CartFlows checkout is set global.

= Version 1.1.2 - Wednesday, 12th June 2019 =
* Fix: Issue of timezone while sending mail through cron.
* Fix: Delete single cart abandonment order.
* Fix: MySql 5.5 support for CURRENT_TIMESTAMP.

= Version 1.1.1 - Thursday, 06th June 2019 =
* New: Added feature to reschedule emails for Admin.
* Fix: Coupon expiry time issue.
* Fix: Email issue for a user who has an already purchased order.
* Fix: Translatable strings updated.

= Version 1.1.0 - Thursday, 30th May 2019 =
* Added a view for admin to check email status specific to the particular abandoned user.

= Version 1.0.0 - Monday, 27th May 2019 =
* Initial Release

== Upgrade Notice ==