=== Collabora Online WP ===
Contributors: hubcp
Tags: collaboration, pdf, presentation, spreadsheet, document
Requires at least: 6.6
Tested up to: 6.7.1
Stable tag: 0.9.9
Requires PHP: 8.0
License: MPL-2.0
License URI: http://mozilla.org/MPL/2.0/

The Collabora Online WP plugin allows collaborative editing of office files with other users.

== Description ==

The Collabora Online WP plugin allows collaborative editing of office files with other users.

This requires a working setup of Collabora Online.

For more information about Collabora Online, see https://collaboraonline.com/

Attaching an office document to a post is simple.

1. Upload the document using the media manager
2. Insert the `collabora_online` shortcode or insert a COOL block in the editor.

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

= 0.9.9 =

* Fix permissions to allow saving document in reviewer mode.

= 0.9.8 =

* Added a better message if the user needs to login to access documents.
* Added a role for reviewers.
* If user can edit document then open as edit even in reviewer mode.
* Fix missing review mode in COOL block.

= 0.9.7 =

* Fix a regression for the collaboration mode. Only "view" would work.
* Added a better error check if SimpleXML isn't available.

= 0.9.6 =

* Fix a fatal syntax error (broken plugin).

= 0.9.5 =

* Added support for the "review" mode, ie only allowing comments in the document.

= 0.9.4 =

* Improved namespace to limit potential conflict with other plugins
* Require nonce for the COOL frame.

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
