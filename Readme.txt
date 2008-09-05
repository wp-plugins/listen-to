=== Plugin Name ===
Contributors: eelay
Donate link: http://www.herr-nilsson.com/listen-to
Tags: music, last.fm
Requires at least: 2.0.1
Tested up to: 2.6
Stable tag:1.04

This plugin will display the latest song you scrobbled trough Last.fm while writing a post.

== Description ==

This plugin uses the last scrobbled song on Last.fm as the basis for dsplaying the song you listend to while you where writing your post. It will not display anything if the last 
scrobbled song in last.fm, is older than 10 mins. when you publish your post.

Styling the link:
1. The link is sourounded by a span with a class = listenTo
2. When there is no link the span has an aditional class = listenTo_noLink
3. The link has a class = listenTo_link

You need to add the template tag in the content part of your template(or where you wish for it it apear.) when you place it you have some options if you are an advaced user; 

`<?php listenTo($before, $beforeLink, $after, $afterLink); ?>`

1. $before will apear before a `<span>`
2. $beforeLink will apear before the `<a href...`
3. $after will apear after `</span>`
4. $afterLink will apear after `</a>`


Included languages

1. English
2. Norwegian

History

* 1.04 Bugfix; use of htmlspecialchars for xml safe characters.
* 1.03 Added feature : Abillity to update listen to when you update a post. Now working on 2.6.
* 1.02 Changed the admin area to look better in 2.5->
* 1.01 Bugfix
* 1.00 Initial Release

== Installation ==

1. Upload the listenTo folder to the "/wp-content/plugins/" directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Configure the plugin in Settings->Listen To
4. Place `<?php listenTo(); ?>` in your template (recomended around you post.)






