=== Indelible Alt Tag Manager ===
Contributors: indelibleinc
Tags: alt tags, images, accessibility, seo, media library
Requires at least: 5.0
Tested up to: 6.9
Stable tag: 1.0.0
Requires PHP: 7.2
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Find images in your media library that are missing alt tags and add them through an easy-to-use admin interface.

== Description ==

Indelible Alt Tag Manager scans your WordPress media library for images that are missing alt tags and provides a simple, paginated admin interface to add them.

**Features:**

* Automatically detects images without alt tags
* Grid-based admin UI for quickly reviewing and updating alt tags
* Image preview modal for viewing full-size images
* Paginated loading for large media libraries
* Admin notice showing the count of images missing alt tags
* Cached queries for performance

**Why Alt Tags Matter:**

Alt tags (alternative text) are essential for accessibility and SEO. Screen readers use alt text to describe images to visually impaired users, and search engines use it to understand image content.

== Installation ==

1. Upload the `ind-alt-tag-manager` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the 'Alt Tag Manager' menu item in the admin sidebar.
4. Review images missing alt tags and add appropriate alt text.

== Frequently Asked Questions ==

= Who can use this plugin? =

Any user with the "upload_files" capability (typically Authors and above) can access the Alt Tag Manager admin page.

= Does this plugin modify my images? =

No. This plugin only updates the alt text metadata associated with your images. The image files themselves are never modified.

= How often is the missing alt tag count updated? =

The count is cached for 30 minutes and automatically refreshes when you update an alt tag or upload a new image.

== Screenshots ==

1. Admin page showing images missing alt tags in a grid layout.
2. Image preview modal for viewing full-size images.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
