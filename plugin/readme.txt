=== Google Drive gallery ===
Contributors: skaut, genabitu, kalich5
Tags: skaut, google drive, google drive gallery, team drive, gallery, multisite, shortcode
Requires at least: 4.9.6
Tested up to: 5.0
Requires PHP: 5.6
Stable tag: 1.2.1
License: MIT
License URI: https://github.com/skaut/skaut-google-drive-gallery/blob/master/license.txt
Donate link: https://www.skaut.cz/podporte-nas/

A WordPress gallery using Google Drive as file storage.

== Description ==

<h2> Minimal requirements</h2>
- WordPress 4.9.6 and higher
- PHP 5.6 and higher

To show a Google Drive gallery add the shortcode [sgdg path="Folder name"] to the page where "Folder name" is a folder in the root directory of the plugin.
It is also possible to use subdirectories with the shortcode [sgdg path="Folder name/subfolder/subsubfolder"].
If no path is provided, then the root directory is used.

**GitHub**
[https://github.com/skaut/skaut-google-drive-gallery/](https://github.com/skaut/skaut-google-drive-gallery/)

== Installation ==
1. Download and install the plugin
2. Create a Google app at [https://console.developers.google.com](https://console.developers.google.com) and enable OAuth2 and the Drive API (necessary info is in plugin settings page)
3. Select a root directory for the plugin to use
4. Add a shortcode

== Frequently Asked Questions ==
**How to configure this plugin?**
See Installation
[Česká Nápověda](https://napoveda.skaut.cz/dobryweb/skaut-google-drive-gallery)

== Screenshots ==


== Changelog ==
 = 1.2.1 =
* Fixed error with Gutenberg block overwriting its configuration

 = 1.2.0 =
* Added proper support for dynamic columns
* Added a Gutenberg block

 = 1.1.0 =
* Added the option to order images and directories (separately) - by time or name, ascending or descending

 = 1.0.1 =
* Added graphical editor for TinyMCE

 = 1.0.0 =
* The first version
* Required WordPress 4.9.6 and higher
* Required PHP 5.6 and higher
