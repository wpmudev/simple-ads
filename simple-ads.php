<?php
/*
Plugin Name: Simple Ads
Plugin URI: http://premium.wpmudev.org/project/simple-ads
Description: This plugin does the advertising basics.
Author: S H Mohanjith (Incsub), Andrew Billits (Incsub)
Version: 1.0.5
Author URI: http://premium.wpmudev.org
WDP ID: 108
Text Domain: simple_ads
*/

/*
Copyright 2007-2009 Incsub (http://incsub.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License (Version 2 - GPLv2) as published by
the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

global $simple_ads_settings_page, $simple_ads_settings_page_long;

if (is_multisite()) {
	if ( version_compare($wp_version, '3.0.9', '>') ) {
		$simple_ads_settings_page = 'settings.php';
		$simple_ads_settings_page_long = 'network/settings.php';
	} else {
		$simple_ads_settings_page = 'ms-admin.php';
		$simple_ads_settings_page_long = 'ms-admin.php';
	}
} else {
	$simple_ads_settings_page = 'options-general.php';
	$simple_ads_settings_page_long = 'options-general.php';
}

//------------------------------------------------------------------------//
//---Classes--------------------------------------------------------------//
//------------------------------------------------------------------------//

class simple_ads_page_ads {
    var $page_ads = 0;

    function get_count() {
        return $this->page_ads;
    }

    function increase() {
	$tmp_page_ads = $this->page_ads;
	$tmp_page_ads = $tmp_page_ads + 1;
        $this->page_ads = $tmp_page_ads;
    }
}
$simple_ads_page_ads = new simple_ads_page_ads();
//------------------------------------------------------------------------//
//---Hook-----------------------------------------------------------------//
//------------------------------------------------------------------------//
add_action('init', 'simple_ads_init');
// add_action('admin_menu', 'simple_ads_pages');
if (is_multisite()) {
	add_action('network_admin_menu', 'simple_ads_network_pages');
} else {
	add_action('admin_menu', 'simple_ads_pages');
}
add_filter('the_content', 'simple_ads_output', 20, 1);

register_activation_hook( __FILE__, 'simple_ads_activate' );
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//
function simple_ads_activate() {
	/*if ( !is_multisite() )
		exit( 'The Simple Ads plugin is only compatible with WordPress Multisite.' ); */
}

function simple_ads_init() {
	/*if ( !is_multisite() )
		exit( 'The Simple Ads plugin is only compatible with WordPress Multisite.' );*/

	load_plugin_textdomain('simple_ads', false, dirname(plugin_basename(__FILE__)).'/languages');
}

function simple_ads_network_pages() {
	global $wpdb, $wp_roles, $current_user, $wp_version, $simple_ads_settings_page, $simple_ads_settings_page_long;

	if ( version_compare($wp_version, '3.0.9', '>') ) {
	    if ( is_network_admin() ) {
		add_submenu_page($simple_ads_settings_page, __('Advertising', 'simple_ads'), __('Advertising', 'simple_ads'), 'manage_network_options', 'advertising', 'simple_ads_site_output');
	    }
	} else {
	    if ( is_super_admin() ) {
		add_submenu_page($simple_ads_settings_page, __('Advertising', 'simple_ads'), __('Advertising', 'simple_ads'), 10, 'advertising', 'simple_ads_site_output');
	    }
	}
}

function simple_ads_pages() {
	global $wpdb, $wp_roles, $current_user, $wp_version, $simple_ads_settings_page, $simple_ads_settings_page_long;

	add_submenu_page($simple_ads_settings_page, __('Advertising', 'simple_ads'), __('Advertising', 'simple_ads'), 'manage_options', 'advertising', 'simple_ads_site_output');
}

function simple_ads_get_ad_code($ad_type) {
	if ( $ad_type == 'before' ) {
		$ad_code = stripslashes( get_site_option('advertising_before_code') );
	}
	if ( $ad_type == 'after' ) {
		$ad_code = stripslashes( get_site_option('advertising_after_code') );
	}
	if ( $ad_code == 'empty' ) {
		$ad_code = '';
	}
	return $ad_code;
}

function simple_ads_output($content) {
	global $wpdb, $simple_ads_page_ads, $current_site;
	$advertising_ads_per_page = get_site_option('advertising_ads_per_page');
	$advertising_main_blog = get_site_option('advertising_main_blog', 'hide');
	$display_ads = 'yes';

	if ( $wpdb->blogid == $current_site->id && $advertising_main_blog == 'hide' ) {
		$display_ads = 'no';
	}
	if ( $display_ads == 'yes' ) {
		if ( is_page() ) {
			if ( get_site_option('advertising_location_before_page_content') == '1' ) {
				$page_ads = $simple_ads_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = simple_ads_get_ad_code('before') . $content;
					$simple_ads_page_ads->increase();
				}
			}
			if ( get_site_option('advertising_location_after_page_content') == '1' ) {
				$page_ads = $simple_ads_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $content . simple_ads_get_ad_code('after');
					$simple_ads_page_ads->increase();
				}
			}
		} else {
			if ( get_site_option('advertising_location_before_post_content') == '1' ) {
				$page_ads = $simple_ads_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = simple_ads_get_ad_code('before') . $content;
					$simple_ads_page_ads->increase();
				}
			}
			if ( get_site_option('advertising_location_after_post_content') == '1' ) {
				$page_ads = $simple_ads_page_ads->get_count();
				if ( $page_ads < $advertising_ads_per_page ) {
					$content = $content . simple_ads_get_ad_code('after');
					$simple_ads_page_ads->increase();
				}
			}
		}
	}
	return $content;
}

//------------------------------------------------------------------------//
//---Page Output Functions------------------------------------------------//
//------------------------------------------------------------------------//

function simple_ads_site_output() {
	global $wpdb, $wp_roles, $current_user, $simple_ads_settings_page;

	if(!current_user_can('manage_options')) {
		echo "<p>" . __('Nice Try...', 'simple_ads') . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e(urldecode($_GET['updatedmsg']), 'simple_ads') ?></p></div><?php
	}
	echo '<div class="wrap">';
	switch( $_GET[ 'action' ] ) {
		//---------------------------------------------------//
		default:
			$advertising_before_code = stripslashes( get_site_option('advertising_before_code') );
			if ($advertising_before_code == 'empty') {
				$advertising_before_code = '';
			}
			$advertising_after_code = stripslashes( get_site_option('advertising_after_code') );
			if ($advertising_after_code == 'empty') {
				$advertising_after_code = '';
			}
			?>
			<h2><?php _e('Advertising', 'simple_ads') ?></h2>
            <form method="post" action="<?php print $simple_ads_settings_page; ?>?page=advertising&action=process">
            <table class="form-table">
            <tr valign="top">
            <th scope="row"><?php _e('Ad Locations', 'simple_ads') ?></th>
            <td>
            <label for="advertising_location_before_post_content">
            <input name="advertising_location_before_post_content" id="advertising_location_before_post_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_before_post_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('Before Post Content', 'simple_ads'); ?></label>
            <br />
            <label for="advertising_location_after_post_content">
            <input name="advertising_location_after_post_content" id="advertising_location_after_post_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_after_post_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('After Post Content', 'simple_ads'); ?></label>
            <br />
            <label for="advertising_location_before_page_content">
            <input name="advertising_location_before_page_content" id="advertising_location_before_page_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_before_page_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('Before Page Content', 'simple_ads'); ?></label>
            <br />
            <label for="advertising_location_after_page_content">
            <input name="advertising_location_after_page_content" id="advertising_location_after_page_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_after_page_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('After Page Content', 'simple_ads'); ?></label>
            </td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Ads per page', 'simple_ads') ?></th>
            <td>
            <?php
            $advertising_ads_per_page = get_site_option('advertising_ads_per_page', 3);
			?>
            <select name="advertising_ads_per_page" id="advertising_ads_per_page" >
                <option value="1" <?php if ( $advertising_ads_per_page == '1' ) { echo 'selected="selected"'; } ?> ><?php _e('1', 'simple_ads'); ?></option>
                <option value="2" <?php if ( $advertising_ads_per_page == '2' ) { echo 'selected="selected"'; } ?> ><?php _e('2', 'simple_ads'); ?></option>
                <option value="3" <?php if ( $advertising_ads_per_page == '3' ) { echo 'selected="selected"'; } ?> ><?php _e('3', 'simple_ads'); ?></option>
                <option value="4" <?php if ( $advertising_ads_per_page == '4' ) { echo 'selected="selected"'; } ?> ><?php _e('4', 'simple_ads'); ?></option>
                <option value="5" <?php if ( $advertising_ads_per_page == '5' ) { echo 'selected="selected"'; } ?> ><?php _e('5', 'simple_ads'); ?></option>
                <option value="6" <?php if ( $advertising_ads_per_page == '6' ) { echo 'selected="selected"'; } ?> ><?php _e('6', 'simple_ads'); ?></option>
                <option value="7" <?php if ( $advertising_ads_per_page == '7' ) { echo 'selected="selected"'; } ?> ><?php _e('7', 'simple_ads'); ?></option>
                <option value="8" <?php if ( $advertising_ads_per_page == '8' ) { echo 'selected="selected"'; } ?> ><?php _e('8', 'simple_ads'); ?></option>
                <option value="9" <?php if ( $advertising_ads_per_page == '9' ) { echo 'selected="selected"'; } ?> ><?php _e('9', 'simple_ads'); ?></option>
                <option value="10" <?php if ( $advertising_ads_per_page == '10' ) { echo 'selected="selected"'; } ?> ><?php _e('10', 'simple_ads'); ?></option>
            </select>
            <br /><?php _e('Maximum number of ads to be shown on a single page. For Google Adsense set this to "3".', 'simple_ads') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('"Before" Ad Code', 'simple_ads') ?></th>
            <td>
            <textarea name="advertising_before_code" type="text" rows="5" wrap="soft" id="advertising_before_code" style="width: 95%"/><?php echo $advertising_before_code; ?></textarea>
            <br /><?php _e('Used before post and page content.', 'simple_ads') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('"After" Ad Code', 'simple_ads') ?></th>
            <td>
            <textarea name="advertising_after_code" type="text" rows="5" wrap="soft" id="advertising_after_code" style="width: 95%"/><?php echo $advertising_after_code; ?></textarea>
            <br /><?php _e('Used after post and page content.', 'simple_ads') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Main Blog', 'simple_ads') ?></th>
            <td>
            <?php
            $advertising_main_blog = get_site_option('advertising_main_blog', 'hide');
			?>
            <select name="advertising_main_blog" id="advertising_main_blog" >
                <option value="hide" <?php if ( $advertising_main_blog == 'hide' ) { echo 'selected="selected"'; } ?> ><?php _e('Hide Ads', 'simple_ads'); ?></option>
                <option value="show" <?php if ( $advertising_main_blog == 'show' ) { echo 'selected="selected"'; } ?> ><?php _e('Show Ads', 'simple_ads'); ?></option>
            </select>
            <br /><?php //_e('') ?></td>
            </tr>
            </table>

            <p class="submit">
            <input class="button button-primary" type="submit" name="Submit" value="<?php _e('Save Changes', 'simple_ads') ?>" />
			<input class="button button-secondary" type="submit" name="Reset" value="<?php _e('Reset', 'simple_ads') ?>" />
            </p>
            </form>
			<?php
		break;
		//---------------------------------------------------//
		case "process":
			if ( isset( $_POST[ 'Reset' ] ) ) {
				update_site_option( "advertising_before_code", "empty" );
				update_site_option( "advertising_after_code", "empty" );
				update_site_option( "advertising_ads_per_page", "1" );
				update_site_option( "advertising_location_before_post_content", "0" );
				update_site_option( "advertising_location_after_post_content", "0" );
				update_site_option( "advertising_location_before_page_content", "0" );
				update_site_option( "advertising_location_after_page_content", "0" );
				update_site_option( "advertising_main_blog", "hide" );
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='{$simple_ads_settings_page}?page=advertising&updated=true&updatedmsg=" . urlencode(__('Changes saved.', 'simple_ads')) . "';
				</script>
				";
			} else {
				if ( empty($_POST[ 'advertising_before_code' ]) ) {
					$advertising_before_code = 'empty';
				} else {
					$advertising_before_code = $_POST[ 'advertising_before_code' ];
				}
				if ( empty($_POST[ 'advertising_after_code' ]) ) {
					$advertising_after_code = 'empty';
				} else {
					$advertising_after_code = $_POST[ 'advertising_after_code' ];
				}

				if ( empty($_POST[ 'advertising_location_before_post_content' ]) ) {
					$advertising_location_before_post_content = 'empty';
				} else {
					$advertising_location_before_post_content = $_POST[ 'advertising_location_before_post_content' ];
				}
				if ( empty($_POST[ 'advertising_location_after_post_content' ]) ) {
					$advertising_location_after_post_content = 'empty';
				} else {
					$advertising_location_after_post_content = $_POST[ 'advertising_location_after_post_content' ];
				}
				if ( empty($_POST[ 'advertising_location_before_page_content' ]) ) {
					$advertising_location_before_page_content = 'empty';
				} else {
					$advertising_location_before_page_content = $_POST[ 'advertising_location_before_page_content' ];
				}
				if ( empty($_POST[ 'advertising_location_after_page_content' ]) ) {
					$advertising_location_after_page_content = 'empty';
				} else {
					$advertising_location_after_page_content = $_POST[ 'advertising_location_after_page_content' ];
				}

				update_site_option( "advertising_before_code", $advertising_before_code );
				update_site_option( "advertising_after_code", $advertising_after_code );
				update_site_option( "advertising_ads_per_page", $_POST[ 'advertising_ads_per_page' ] );
				update_site_option( "advertising_location_before_post_content", $advertising_location_before_post_content );
				update_site_option( "advertising_location_after_post_content", $advertising_location_after_post_content );
				update_site_option( "advertising_location_before_page_content", $advertising_location_before_page_content );
				update_site_option( "advertising_location_after_page_content", $advertising_location_after_page_content );
				update_site_option( "advertising_main_blog", $_POST[ 'advertising_main_blog' ] );
				echo "
				<SCRIPT LANGUAGE='JavaScript'>
				window.location='{$simple_ads_settings_page}?page=advertising&updated=true&updatedmsg=" . urlencode(__('Changes saved.', 'simple_ads')) . "';
				</script>
				";
			}
		break;
		//---------------------------------------------------//
		case "temp":
		break;
		//---------------------------------------------------//
	}
	echo '</div>';
}

if ( !function_exists( 'wdp_un_check' ) ) {
	add_action( 'admin_notices', 'wdp_un_check', 5 );
	add_action( 'network_admin_notices', 'wdp_un_check', 5 );

	function wdp_un_check() {
		if ( !class_exists( 'WPMUDEV_Update_Notifications' ) && current_user_can( 'edit_users' ) )
			echo '<div class="error fade"><p>' . __('Please install the latest version of <a href="http://premium.wpmudev.org/project/update-notifications/" title="Download Now &raquo;">our free Update Notifications plugin</a> which helps you stay up-to-date with the most stable, secure versions of WPMU DEV themes and plugins. <a href="http://premium.wpmudev.org/wpmu-dev/update-notifications-plugin-information/">More information &raquo;</a>', 'wpmudev') . '</a></p></div>';
	}
}
