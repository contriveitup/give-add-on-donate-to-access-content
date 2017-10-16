# give-add-on-donate-to-access
A [Give](https://wordpress.org/plugins/give/) plugin add-on
## Description
This is a add-on to Give Donation plugin. With this add-on site admiminstrators can restrict the content of a post, page, etc.. using a shortcode or they can also restrict the entie page and redirect them to a Give donation form to make a donation on order to view the restricted content.

## Features
## Shortcode - Use the following shortcode to restrict the content in a post or a page.

```
[give_donate_to_access form_id=1 show='form']
```
### Shortcode Parameters
**form_id** - is the id of the Give donation form which you wish to display to collect the donation. This is required.
**show** - what to show instead of the restrcited content. A Donation form or a message with the link to donation form. By default it's **form**

## Redirection - In the admin settings of this add-on you can specify the post, page and categories id's and based upon that the whole page, post or category page will be restrcited and the user will be redirect to a donation form. 

The donation to which a user will be redirected can be set in the admin settings as well.

### Note
This is right now a very basic version of the plugin with only one shortcode and the ability to restrict only posts, pages & category pages.
