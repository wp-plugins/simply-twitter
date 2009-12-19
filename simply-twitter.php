<?php 
/*  
Plugin Name: Simply Twitter
Plugin URI: http://www.zaikos.com/wp-plugins/simply-twitter/
Version: 1.0
Author: <a href="http://www.zaikos.com/blog/">Dave Zaikos</a>
Description: Adds a simple Twitter widget to the sidebar.
*/

/*  Copyright 2009  Dave Zaikos  (email : http://www.zaikos.com/blog/contact/)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( !class_exists('wcc_wp_twitter_plugin') ) {
	class wcc_wp_twitter_plugin {
		function wcc_twitter_widget_init() {
			if ( !function_exists('register_sidebar_widget') || !function_exists('register_widget_control') )
				return;

			function wcc_twitter_widget($args) {
				if ( get_option('wcc_twitter_widget') ) {
					$options = unserialize(get_option('wcc_twitter_widget'));
				} else {
?>
<!-- Configure Twitter to use the sidebar widget. -->

<?php
					return;
				}

				extract($args);

				echo $before_widget . $before_title . "Twitter" . $after_title . "\n<ul id=\"twitter_update_list\">\n\t<li>Loading...</li>\n</ul>\n<div class=\"twitter_follow_link\"><a href=\"http://twitter.com/{$options['username']}\" title=\"Follow " . get_bloginfo('name') . " on Twitter\">Follow Us</a></div>\n" . $after_widget;
			}

			function wcc_twitter_widget_control() {
				if ( get_option('wcc_twitter_widget') ) {
					$options = unserialize(get_option('wcc_twitter_widget'));
				} else {
					$options = array(
						'username'=>NULL,
						'count'=>3
					);
				}

				if ( isset($_POST['wcc-twitter-widget-submit']) ) {
					if( get_magic_quotes_gpc() ) {
						$options['username'] = trim(strip_tags(stripslashes($_POST['username'])));
						$options['count'] = trim(strip_tags(stripslashes((int)$_POST['count'])));
					} else {
						$options['username'] = trim(strip_tags($_POST['username']));
						$options['count'] = trim(strip_tags((int)$_POST['count']));
					}

					if ( empty($options['username']) ) {
						delete_option('wcc_twitter_widget');
					} elseif ( get_option('wcc_twitter_widget') ) {
						update_option('wcc_twitter_widget', serialize($options));
					} else {
						add_option('wcc_twitter_widget', serialize($options), '', 'yes');
					}
				}
?>
<input type="hidden" name="wcc-twitter-widget-submit" value="1" />
<p>
	<label for="username">Username:
		<input class="widefat" name="username" type="text" maxlength="15" value="<?php echo esc_attr($options['username']); ?>" />
	</label>
</p>
<p>
	<label for="count">Display Count:
		<select class="widefat" name="count">
<?php
	for ($i = 1; $i < 21; $i++) {
		$echo = "			<option value=" . $i;
		if ( $options['count'] == $i ) $echo .= " selected";
		$echo .= ">$i</option>\n";
		echo $echo;
	}
?>
		</select>
	</label>
</p>
<?php
			}

			register_sidebar_widget('Twitter', 'wcc_twitter_widget');
			register_widget_control(array('Twitter','widgets'), 'wcc_twitter_widget_control');
		}

		function wcc_twitter_js() {
			if ( !is_admin() && is_active_widget('wcc_twitter_widget') ) {
				if ( get_option('wcc_twitter_widget') ) {
					$options = unserialize(get_option('wcc_twitter_widget'));
				} else {
					return;
				}

				wp_enqueue_script('twitter-blogger', 'http://twitter.com/javascripts/blogger.js', '', '1', TRUE);
				wp_enqueue_script('twitter-timeline', 'http://twitter.com/statuses/user_timeline/' . $options['username'] . '.json?callback=twitterCallback2&amp;count=' . $options['count'], array('twitter-blogger'), '1', TRUE);
			}
		}
	}
}

if ( class_exists('wcc_wp_twitter_plugin') ) {
	$wpsb_twitter = new wcc_wp_twitter_plugin();
	add_action('widgets_init', array(&$wpsb_twitter, 'wcc_twitter_widget_init'));
	add_action('wp_print_scripts', array(&$wpsb_twitter, 'wcc_twitter_js'));
}