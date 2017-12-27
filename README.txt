=== Donate to Access Content ===
Contributors: contriveitup, rupakdhiman
Tags: donate, donate to access, donate to access content, give add-on, wordpress plugin, free wordpress plugin, free give plugin add-on, contriveitup
Donate link: paypal.me/contriveitup
Requires at least: 4.3
Tested up to: 4.9.x
Requires PHP: 5.3
Stable tag: 1.0.1
License: GPLv3
License URI: http://www.gnu.org/licenses/gpl-3.0.html

An Unoffcial [Give](https://wordpress.org/plugins/give/) plugin add-on to restrict site\'s content until donation is made.

== Description ==
This is a Free add-on to Give donation plugin which allows admin to restrict the content of their website until a user has made a donation. 

Once a user has made the donation they can access the content. Admin can choose to restrict the content of the site via shortcode or they restrict the entire website or a particular page, post, category, post type, etc... with the help of plugin settings.

You can also track the development at : https://github.com/contriveitup/give-add-on-donate-to-access-content

== Installation ==
== Automatic Plugin Installation ==

1. Go to Plugins > Add New.
2. Type in the name of the WordPress Plugin or descriptive keyword, author, or tag in Search Plugins box or click a tag link below the screen.
3. Find the WordPress Plugin you wish to install.
4. Click Details for more information about the Plugin and instructions you may wish to print or save to help setup the Plugin.
5. Click Install Now to install the WordPress Plugin.

== Manual Plugin Installation ==

Note: Installation of a WordPress Plugin manually requires FTP familiarity and the awareness that you may put your site at risk if you install a WordPress Plugin incompatible with the current version or from an unreliable source.

1. Download your WordPress Plugin to your desktop.
2. If downloaded as a zip archive, extract the Plugin folder to your desktop.
3. With your FTP program, upload the Plugin folder to the wp-content/plugins folder in your WordPress directory online.
4. Go to Plugins screen and find the newly uploaded Plugin in the list.
5. Click Activate to activate it.

== Frequently Asked Questions ==

= What type of content can I restrict with this plugin? =

With this plugin, you can restrict the content on a page or post by using plugin\'s shortcode or you can restrict an entire page, post, category archive page, custom post type, custom taxonomy archive page.

= What shortcode is used to restrict the content? =

The shortcode which is used to restrict the content is:

[cip_donate_to_access_content form_id=1 show="form|message"]

It has 2 options, the first one is required which is the ID of the donation form which would appear instead of the restricted content. The second option is to either show a Donation form or a Message in place of the restricted content.

= What if I am on a single post page and a category in that post is restricted. Would a user will still be able to view that post? =

Yes, for now on the Category Archive page can be restricted. If a post is in that category a user can view that post without donation unless you restrict that entire post altogether.

== Changelog ==
= 1.0: November 9, 2017 =
* Initial Release. JMJK!!

= 1.0.1: December 20, 2017 =
* Fixed: Fatal Error - Class not found.