<?php
/*
Plugin Name: Simple Ads
Plugin URI: 
Description:
Author: Andrew Billits (Incsub)
Version: 1.0.1
Author URI:
WDP ID: 108
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
add_action('admin_menu', 'simple_ads_plug_pages');
add_filter('the_content', 'simple_ads_output', 20, 1);
//------------------------------------------------------------------------//
//---Functions------------------------------------------------------------//
//------------------------------------------------------------------------//

function simple_ads_plug_pages() {
	global $wpdb, $wp_roles, $current_user;
	if ( is_site_admin() ) {
		add_submenu_page('ms-admin.php', 'Advertising', 'Advertising', 10, 'advertising', 'simple_ads_site_output');
	}
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
	global $wpdb, $simple_ads_page_ads;
	$advertising_ads_per_page = get_site_option('advertising_ads_per_page');
	$advertising_main_blog = get_site_option('advertising_main_blog', 'hide');
	$display_ads = 'yes';
	if ( $wpdb->blogid == 1 && $advertising_main_blog == 'hide' ) {
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
	global $wpdb, $wp_roles, $current_user;
	
	if(!current_user_can('manage_options')) {
		echo "<p>" . __('Nice Try...') . "</p>";  //If accessed properly, this message doesn't appear.
		return;
	}
	if (isset($_GET['updated'])) {
		?><div id="message" class="updated fade"><p><?php _e('' . urldecode($_GET['updatedmsg']) . '') ?></p></div><?php
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
			<h2><?php _e('Advertising') ?></h2>
            <form method="post" action="ms-admin.php?page=advertising&action=process">
            <table class="form-table">
            <tr valign="top">
            <th scope="row"><?php _e('Ad Locations') ?></th>
            <td>
            <label for="advertising_location_before_post_content">
            <input name="advertising_location_before_post_content" id="advertising_location_before_post_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_before_post_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('Before Post Content'); ?></label>
            <br />
            <label for="advertising_location_after_post_content">
            <input name="advertising_location_after_post_content" id="advertising_location_after_post_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_after_post_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('After Post Content'); ?></label>
            <br />
            <label for="advertising_location_before_page_content">
            <input name="advertising_location_before_page_content" id="advertising_location_before_page_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_before_page_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('Before Page Content'); ?></label>
            <br />
            <label for="advertising_location_after_page_content">
            <input name="advertising_location_after_page_content" id="advertising_location_after_page_content" value="1" type="checkbox" <?php if ( get_site_option('advertising_location_after_page_content') == '1' ) { echo 'checked="checked"'; } ?> >
            <?php _e('After Page Content'); ?></label>
            </td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Ads per page') ?></th>
            <td>
            <?php
            $advertising_ads_per_page = get_site_option('advertising_ads_per_page', 3);
			?>
            <select name="advertising_ads_per_page" id="advertising_ads_per_page" >
                <option value="1" <?php if ( $advertising_ads_per_page == '1' ) { echo 'selected="selected"'; } ?> ><?php _e('1'); ?></option>
                <option value="2" <?php if ( $advertising_ads_per_page == '2' ) { echo 'selected="selected"'; } ?> ><?php _e('2'); ?></option>
                <option value="3" <?php if ( $advertising_ads_per_page == '3' ) { echo 'selected="selected"'; } ?> ><?php _e('3'); ?></option>
                <option value="4" <?php if ( $advertising_ads_per_page == '4' ) { echo 'selected="selected"'; } ?> ><?php _e('4'); ?></option>
                <option value="5" <?php if ( $advertising_ads_per_page == '5' ) { echo 'selected="selected"'; } ?> ><?php _e('5'); ?></option>
                <option value="6" <?php if ( $advertising_ads_per_page == '6' ) { echo 'selected="selected"'; } ?> ><?php _e('6'); ?></option>
                <option value="7" <?php if ( $advertising_ads_per_page == '7' ) { echo 'selected="selected"'; } ?> ><?php _e('7'); ?></option>
                <option value="8" <?php if ( $advertising_ads_per_page == '8' ) { echo 'selected="selected"'; } ?> ><?php _e('8'); ?></option>
                <option value="9" <?php if ( $advertising_ads_per_page == '9' ) { echo 'selected="selected"'; } ?> ><?php _e('9'); ?></option>
                <option value="10" <?php if ( $advertising_ads_per_page == '10' ) { echo 'selected="selected"'; } ?> ><?php _e('10'); ?></option>
            </select>
            <br /><?php _e('Maximum number of ads to be shown on a single page. For Google Adsense set this to "3".') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('"Before" Ad Code') ?></th>
            <td>
            <textarea name="advertising_before_code" type="text" rows="5" wrap="soft" id="advertising_before_code" style="width: 95%"/><?php echo $advertising_before_code; ?></textarea>
            <br /><?php _e('Used before post and page content.') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('"After" Ad Code') ?></th>
            <td>
            <textarea name="advertising_after_code" type="text" rows="5" wrap="soft" id="advertising_after_code" style="width: 95%"/><?php echo $advertising_after_code; ?></textarea>
            <br /><?php _e('Used after post and page content.') ?></td>
            </tr>
            <tr valign="top">
            <th scope="row"><?php _e('Main Blog') ?></th>
            <td>
            <?php
            $advertising_main_blog = get_site_option('advertising_main_blog', 'hide');
			?>
            <select name="advertising_main_blog" id="advertising_main_blog" >
                <option value="hide" <?php if ( $advertising_main_blog == 'hide' ) { echo 'selected="selected"'; } ?> ><?php _e('Hide Ads'); ?></option>
                <option value="show" <?php if ( $advertising_main_blog == 'show' ) { echo 'selected="selected"'; } ?> ><?php _e('Show Ads'); ?></option>
            </select>
            <br /><?php //_e('') ?></td>
            </tr>
            </table>
            
            <p class="submit">
            <input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			<input type="submit" name="Reset" value="<?php _e('Reset') ?>" />
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
				window.location='ms-admin.php?page=advertising&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
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
				window.location='ms-admin.php?page=advertising&updated=true&updatedmsg=" . urlencode(__('Changes saved.')) . "';
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

?>