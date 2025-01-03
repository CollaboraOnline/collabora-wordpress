=== Collabora Online WP ===
Contributors: hubcp
Tags: collaboration, pdf, presentation, spreadsheet, document
Requires at least: 6.6
Tested up to: 6.7.1
Stable tag: 0.9.3
Requires PHP: 8.0
License: MPL-2.0
License URI: http://mozilla.org/MPL/2.0/

The Collabora Online WP plugin allow attaching office files to your content for a collaborative editing with other user.

== Description ==

The Collabora Online WP plugin allow attaching office files to your content for a collaborative editing with other user.

This require a working setup of Collabora Online.

For more information about Collabora Online, see https://collaboraonline.com/

Attaching an office document to a post is simple.

1. Upload the document using the media manager
2. Insert the `cool` shortcode or insert a COOL block in the editor.

== Frequently Asked Questions ==

= Do I need Collabora Online ? =

Yes. It needs to be up and running. It is the sole existence of that plugin.

= Which version of Collabora Online shall I use ? =

If you are already a paid customer for Collabora Online, you can use your existing setup. Otherwise you can use[CODE](https://sdk.collaboraonline.com/docs/installation/CODE_Docker_image.html) (Collabora Online Developer Edition). There is no functional difference.

== Screenshots ==

1. Add a COOL Block to your post.
2. Select a file to view or edit.
3. In the post click on view or edit.
4. Enjoy Collaboration with Collabora Online.

== Changelog ==

= 0.9.3 =

* Fix improper version in plugin file.

= 0.9.2 =

* Fix reviewing issues.
* Use more template for the frontend.
* Change the localization text domain.
* More sanitization of parameters.

= 0.9.1 =

* Fixing validation issues.
* Added French localization (test quality).
* Fix packaging of localization.
* Return 401 from WOPI if authentication failed.

= 0.9.0 =

* Initial release.
