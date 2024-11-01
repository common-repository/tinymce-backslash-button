=== TinyMCE Backslash Button ===
Contributors: redcocker
Donate link: http://www.near-mint.com/blog/donate
Tags: backslash, japanese, korean, tinymce, quicktag, button
Requires at least: 2.8
Tested up to: 3.3.1
Stable tag: 0.2.6

This plugin provides buttons to enter backslash. Even when using Japanese or Korean font, backslash doesn't appear as Yen or Won sign.

== Description ==

When using Japanese or Korean font, backlash appear as Yen or Won sign, beacuse Yen and Won sign have the same ASCII code as backslash.

This plugin provides a TinyMCE button and Quicktag to enter backslash. Even when using Japanese or Korean font, backslash doesn't appear as Yen or Won sign.

= Features =

* Easy to enter backslashes even when using Japanese or Korean font.
* Localization: English(Default), 日本語(Japanese, UTF-8).

= Notes =

* This plugin requires the default visual editor(TinyMCE). Other visual editors are not supported.
* On some clients(Visitor's PC, mobile), backslash may appear as Yen or Won sign.

== Installation ==

= Installation =

1. Upload plugin folder to the `/wp-content/plugins/` directory.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. If you need, go to "Settings" -> "TinyMCE Backslash" to configure.

= Usage =

Click "backslash" button to enter backslashes.

== Screenshots ==

1. This is the button on Visual editor(TinyMCE).
2. This is the button on HTML editor.
3. This is entered backslashes.
4. This is setting panel.

== Changelog ==

= 0.2.6 =
* Fix a bug: Using bloginfo() in the wrong way.
* Fix a bug: A missing textdomain.
* Fix a bug: The function name which may conflict with "SyntaxHighlighter TinyMCE Button" someday.

= 0.2.5 =
* Modified Quicktag processing to be compliant with WordPress 3.3.
* Added Quicktag button into the Comment editor.

= 0.2 =
* Added the button into HTML editor. The button makes easy to enter backslashes even when using Japanese or Korean font.
* Rewrote the code using class.

= 0.1 =
* This is the initial release.

== Upgrade Notice ==

= 0.2.6 =
This version has some bug fixes.

= 0.2.5 =
This plugin can support WordPress 3.3. This version has a new feature.

= 0.2 =
This version has a new feature and change.

= 0.1 =
This is the initial release.
