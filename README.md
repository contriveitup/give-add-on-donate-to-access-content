# give-add-on-donate-to-access

An Unoffcial [Give](https://wordpress.org/plugins/give/) plugin add-on.

## Requirements & Other info

* Requires at least: 4.2
* Tested up to: 4.8
* Requires PHP: 5.3
* Stable tag: 1.0
* License: GPLv3
* License URI: http://www.gnu.org/licenses/gpl-3.0.html

## Description
This is a add-on to Give Donation plugin. With this add-on site admiminstrators can restrict the content of a post, page, etc.. using a shortcode or they can also restrict the entie page and redirect them to a Give donation form to make a donation on order to view the restricted content.

## Features

1. Shortcode to restrict content on any page or post
2. Admin settings that allow admins to choose which post, page, category, custom post types or custom taxonomy should be restrcited
3. Easy to manage and customize.
4. Localization ready.
5. Developer friendly.

## Shortcode - Use the following shortcode to restrict the content in a post or a page.

```
[give_donate_to_access form_id=1 show='form|message']
```
### Shortcode Parameters
**form_id** - is the id of the Give donation form which you wish to display to collect the donation. This is required.

**show** - what to show instead of the restrcited content. A Donation form or a message with the link to donation form. 
Options are form or message
By default it's **form**

## Changelog

#### Version 1.0 - 16th Oct 2017
* Initial Commit