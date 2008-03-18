<?php
/**
 * 
 * Plugin Name: Listen To
 * Plugin URI: http://www.herr-nilsson.com/listen-to
 * Description: This plugin will display the latest song you scrobbled trough Last.fm while writing a post. You can place it where you want in you template. <a href="options-general.php?page=listenTo/listenTo.php">Configure your settings here</a>.
 * Version: 1.02
 * Author: Alexander Lian
 * Author URI: http://Herr-Nilsson.com
 *
 * Copyright 2007  Alexander Lian (email : alex [at] herr-nilsson [dot] com)
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * 
 *TODO
 *
 *Streaming ?
 *
 *Update older posts?
 *
 *Dont display on posts older than the plugin?
 *
 */

define('MAGPIE_CACHE_AGE', 120); 
global $listenTo_db_version, $dB_name;
$listenTo_db_version = "1.0";
$dB_name = "listenTo";
$username = get_option('listenTo_username');

/*Load Lang*/
$listenTo_domain = 'listenTo';
$listenTo_is_setup = 0;

function listenTo_lang_setup()
{
   global $listenTo_domain, $listenTo_is_setup;

   if($listenTo_is_setup) {
      return;
   } 
   
  load_plugin_textdomain($listenTo_domain, 'wp-content/plugins/listen-to');
}
listenTo_lang_setup();

/*START*/
function listenTo ($before = '', $beforeLink = '', $after = '', $afterLink = '') {
	global $wpdb, $post, $dB_name, $username;
	$table_name = $wpdb->prefix . $dB_name;
	$title = get_option('listenTo_title');
	$noLink = get_option('listenTo_noLink');

	$content = $wpdb->get_row("SELECT * FROM ".$table_name." WHERE id = ".$post->ID."", ARRAY_A );

if($content[id]) {
		echo $before."<span class=\"listenTo\">".$beforeLink."<a href=\"" . $content[url] . "\" title=\"".$title." - ".$content[title]."\" class=\"listenTo_link\">" . $content[title] . "</a>".$afterLink."</span>".$after;
	} elseif($noLink) {
		echo $before."<span class=\"listenTo listenTo_noLink\">".$noLink."</span>".$after;
	}else {
		
	}
}


function listenTo_install () {
   global $wpdb;
   global $listenTo_db_version;
   global $dB_name;
	$table_name = $wpdb->prefix . $dB_name;
   
   if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
      
      $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL,
	  time tinytext NOT NULL,
	  title text NOT NULL,
	  url text NOT NULL,
	  UNIQUE KEY id (id)
		);";

      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      	dbDelta($sql);
 
      add_option("listenTo_db_version", $listenTo_db_version);
   }
}
register_activation_hook(__FILE__,'listenTo_install');

/*Insert latest into db*/
function listenTo_publish ($id) {
	global $wpdb, $dB_name, $username;
		include_once(ABSPATH . WPINC . '/rss.php');
 		$table_name = $wpdb->prefix . $dB_name;

		$songs = fetch_rss('http://ws.audioscrobbler.com/1.0/user/'.$username.'/recenttracks.rss');
		$songs = array_slice($songs->items, 0, 1);
		
		if(strtotime($songs[0]['pubdate']) + (10 * 60) >= time()) {
			foreach ( $songs as $song ) {
					$msg = $song['title'];
					$updated = $song['pubdate'];
					$link = $song['link'];
					
					$insert = "INSERT INTO " . $table_name .
           				 " (id, time, title, url) " .
            				"VALUES ('" . $wpdb->escape($id) . "','" . $wpdb->escape($updated) . "','" . $wpdb->escape($msg) . "','" . $wpdb->escape($link) . "')";

     						 $results = $wpdb->query( $insert );
					}
				}

}

/*insert into post*/

function listenTo_post($content) {
	if (get_option('listenTo_display') == 'true') {
		print $content.'<p>';
		listenTo();
		print '</p>';
	}
	else {
		return $content;
	}
}


/*Delete post - remove entry*/

function listenTo_delete ($id) {
	global $wpdb, $dB_name;
	
	$table_name = $wpdb->prefix . $dB_name;
	$wpdb->query(" DELETE FROM " . $table_name . " WHERE id = '" . $id . "' ");
	 
}

add_action('publish_post', 'listenTo_publish');
add_action('delete_post', 'listenTo_delete');
add_action('the_content', 'listenTo_post');


 /*Admin Page*/

function set_listenTo_options () {
     add_option("listenTo_username", "");
     add_option("listenTo_noLink", "");
     add_option("listenTo_title","When I wrote this post I was listening to");
     add_option("listenTo_display","");
}
function unset_listenTo_options () {
     delete_option("listenTo_username");
     delete_option("listenTo_noLink");
     delete_option("listenTo_title");
     delete_option("listenTo_display");
}
 function print_listenTo_form () {
 global $listenTo_domain;
      $defaultUsername = get_option('listenTo_username');
      $defaultnoLink = get_option('listenTo_noLink');
      $defaultTitle = get_option('listenTo_title');
      if(get_option("listenTo_display") == "true") {
      $defaultDisplay = 'checked="checked"';
      } else { $defaultDisplay = ''; }
      echo '
 <form method="post">
 <fieldset class="options">
      <p>
      <label>';
      _e('Your Last.fm Username', $listenTo_domain);
      echo '
      </label>
      <br />
      <input type="text" name="listenTo_username" value="'.$defaultUsername.' " size="60" />
      </p>
      <p>
      <label>';
      	_e('Display this when there is no link to display', $listenTo_domain);
      echo '
      </label>
      <br />
      <input type="text" name="listenTo_noLink" value="'.$defaultnoLink.' " size="60" />
      </p>
      <p>
      <label>';
      _e('The Link Title (displayed on mouseover)', $listenTo_domain);
      echo '
      </label>
      <br />
      <input type="text" name="listenTo_title" value="'.$defaultTitle.' " size="60" />
      </p>
      <p>
      <label>';
      _e('Display Listen To after every post(if not you need to place " listenTo(); " in yout template)?', $listenTo_domain);
      echo '
      </label>
      &nbsp;
      <input type="checkbox" name="listenTo_display" value="true" ' . $defaultDisplay . ' />
      </p>
 </fieldset>
      <input type="submit" name="submit" value="'; _e('Update Settings', $listenTo_domain); echo '" class="button" />
 </form>
 <br />
 <form method="post">
 <p>
 <label><strong>';
      _e('Delete Database Table', $listenTo_domain);
      echo '
      </strong></label>
      </p>
      </p>';
      _e('You can delete the database entry here if you do not wish to use this plugin anymore.', $listenTo_domain);
      echo '
      </p>
      <p>
 <input type="hidden" name="listenTo_delete" value="listenTo_delete" />
 <input type="submit" name="submit_delete" value="'; _e('Delete DB Table', $listenTo_domain); echo '" 
 onClick="javascript: var ans = confirm(\'';_e('Are you shure you want to delete the database table? Press OK for yes and Cancel if you dont want to do this.', $listenTo_domain); echo '\'); if(!ans) {return false}" class="button delete" />
 </p>
 </form>';

 }
function listenTo_options () {
global $listenTo_domain;
     echo '<div class="wrap"><h2>'; _e('Listen To Options', $listenTo_domain); echo '</h2>';
     if ($_REQUEST['submit']) {
          update_listenTo_options();
     }elseif ($_REQUEST['submit_delete']) {
     	delete_listenTo_DB();
     }
     print_listenTo_form();
     echo '</div>';
}
function delete_listenTo_DB() {
	$deleted = false;
	if ($_REQUEST['listenTo_delete']) {
			global $wpdb, $dB_name, $listenTo_domain;
			
	$table_name = $wpdb->prefix . $dB_name;
   
   		if($wpdb->get_var("show tables like '$table_name'") == $table_name) {
   
			$wpdb->query(" DROP TABLE " . $table_name . " ");
          $deleted = true;
          
          delete_option("listenTo_db_version");
          
          } else { $noTable = true; }
          
     }
     if ($deleted) {
           echo '<div id="message" class="updated fade">';
           echo "<p>" . __('Database table was deleted', $listenTo_domain) .  "</p>";
           echo '</div>';
      } elseif ($noTable) {
      		echo '<div id="message" class="error fade">';
           echo "<p>" . __('The database table is already deleted', $listenTo_domain) . "</p>";
           echo '</div>';
      	} else {
           echo '<div id="message" class="error fade">';
           echo "<p>" . __('Unable to delete database table', $listenTo_domain) . "</p>";
           echo '</div>';
      }
     
}

function update_listenTo_options() {
global $listenTo_domain;
     $updated = false;
     if ($_REQUEST['listenTo_username']) {
          update_option('listenTo_username', $_REQUEST['listenTo_username']);
          $updated = true;
     }
     if ($_REQUEST['listenTo_noLink']) {
          update_option('listenTo_noLink', $_REQUEST['listenTo_noLink']);
          $updated = true;
     }
     if ($_REQUEST['listenTo_title']) {
          update_option('listenTo_title', $_REQUEST['listenTo_title']);
          $updated = true;
     }
     if ($_REQUEST['listenTo_display']) {
          update_option('listenTo_display', $_REQUEST['listenTo_display']);
          $updated = true;
     } else {
     	update_option('listenTo_display', '');
     }
     if ($updated) {
           echo '<div id="message" class="updated fade">';
           echo "<p>" . __('Options Updated', $listenTo_domain) . "</p>";
           echo '</div>';
      } else {
           echo '<div id="message" class="error fade">';
           echo "<p>" . __e('Unable to update options', $listenTo_domain) . "</p>";
           echo '</div>';
      }
 }
function modify_menu_for_listenTo () {
     add_options_page(
                      'Listen To',         //Title
                      'Listen To',         //Sub-menu title
                      'manage_options', //Security
                      __FILE__,         //File to open
                      'ListenTo_options'  //Function to call
                     );  
}
add_action('admin_menu','modify_menu_for_listenTo');
register_activation_hook(__FILE__,"set_listenTo_options");
register_deactivation_hook(__FILE__,"unset_listenTo_options");


?>