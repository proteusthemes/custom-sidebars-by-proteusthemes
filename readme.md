# ProteusThemes Custom Sidebars #
**Contributors:** capuderg, cyman  
**Tags:** custom sidebars, widgets, sidebars, custom, sidebar, widget, personalize  
**Requires at least:** 4.0  
**Tested up to:** 4.5  
**Stable tag:** 0.1  

Create your own sidebars and choose on which pages they show up.

## Description ##

If you'd like to show different widgets on sidebars or footers of any area of your site - then this is the plugin for you.

This plugin is based of the [Custom Sidebars plugin](https://wordpress.org/plugins/custom-sidebars/), which we used for a few years, but their plugin development stalled, so we created this plugin. We really appreciate the work of developers of the original plugin!

Custom Sidebars allows you to create all the widgetized areas you need, your own custom sidebars, configure them adding widgets, and replace the default sidebars on the posts or pages you want in just few clicks.

With this plugin you can customize every widget area by setting new default sidebars for a group of posts or pages easily, keeping the chance of changing them individually.

For example, you can change:

* Sidebars for all the posts that belong to a category,
* Sidebars for all the posts that belong to a post-type,
* Sidebars for the main blog page,
* Sidebars for search results.

## Installation ##

There are two ways of installing the plugin:

**From this page.**

1. Download the plugin, extract the zip file.
2. Upload the `proteusthemes-custom-sidebars` folder to your `/wp-content/plugins/` directory.
3. Active the plugin in the plugin menu panel in your administration area.

**From inside your WordPress installation, in the plugin section.**

1. Search for *ProteusThemes Custom Sidebars* plugin.
2. Download it and then active it.

Once you have the plugin activated you will find all new features inside your "Widgets" screen! There you will be able to create and manage your own sidebars.

## Frequently Asked Questions ##

### Why can't I see a widget menu? ###

This plugin requires your theme to have widget areas enabled, if you don't have widget areas enabled you probably need to use a different theme that does!

### Where do I set my sidebars up? ###

You have a sidebar box when editing a entry. Also you can define default sidebars for different posts and archives.

### Why do I get a message 'There are no replaceable sidebars selected'?  ###

You can create all the sidebars you want, but you need some sidebars of your theme to be replaced by the ones that you have created. You have to select which sidebars from your theme are suitable to be replaced in the Custom Sidebars settings page and you will have them available to switch.

### Everything is working properly on Admin area, but the custom sidebars are not displayed on the site. Why? ###

You are probably using a theme that doesn’t load dynamic sidebars properly or doesn’t use the wp_head() function in its header. The plugin replaces the sidebars inside that function, and many other plugins hook there, so it is [more than recommended to use it](http://josephscott.org/archives/2009/04/wordpress-theme-authors-dont-forget-the-wp_head-function/).

### It appears that only an Admin can choose to add a sidebar. How can Editors (or any other role) edit customs sidebars? ###

Any user that can switch themes, can create sidebars. Switch_themes is the capability needed to manage widgets, so if you can’t edit widgets you can’t create custom sidebars. There are some plugins to give capabilities to the roles, so you can make your author be able to create the sidebars. Try [User role editor](http://wordpress.org/extend/plugins/user-role-editor/)

### Does it have custom taxonomies support? ###

This plugin supports showing your posts on all different categories, post_types, ...

### Can I use the plugin in commercial projects? ###

Custom Sidebars has the same license as WordPress, so you can use it wherever you want for free.

## Screenshots ##

### 1. screenshot-1.png The WordPress Widgets section is packed with features to create and manage your sidebars. ###
![screenshot-1.png The WordPress Widgets section is packed with features to create and manage your sidebars.](http://ps.w.org/proteusthemes-custom-sidebars/assets/screenshot-1.png)

### 2. screenshot-2.png Create and edit sidebars directly inside the widgets page. Easy and fast! ###
![screenshot-2.png Create and edit sidebars directly inside the widgets page. Easy and fast!](http://ps.w.org/proteusthemes-custom-sidebars/assets/screenshot-2.png)

### 3. screenshot-4.png Or finetune the sidebars by selecting them directly for a special post or page! ###
![screenshot-4.png Or finetune the sidebars by selecting them directly for a special post or page!](http://ps.w.org/proteusthemes-custom-sidebars/assets/screenshot-3.png)


## Changelog ##

### 1.0.0 ###

* Initial plugin version.

## Contact and Credits ##

This plugin is being developed by [ProtuesThemes](https://www.proteusthemes.com/)

The original plugin: Custom sidebars is maintained and developed by [WPMU DEV](http://premium.wpmudev.org). Original development completed by [Javier Marquez](http://marquex.es/)