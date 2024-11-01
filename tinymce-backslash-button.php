<?php
/*
Plugin Name: TinyMCE Backslash Button
Plugin URI: http://www.near-mint.com/blog/software/tinymce-backslash-button
Description: This plugin provides a TinyMCE button and Quicktag to enter backslash. Even when using Japanese or Korean font, backslash doesn't appear as Yen or Won mark.
Version: 0.2.6
Author: redcocker
Author URI: http://www.near-mint.com/blog/
Text Domain: tinymce_bks_lang
Domain Path: /languages
*/
/*
Last modified: 2012/2/2
License: GPL v2
*/

class TinyMCE_Backslash_Button {

	var $tinymce_backslash_plugin_url;
	var $tinymce_backslash_db_ver = "0.2";
	var $tinymce_backslash_setting_opt;

	function __construct() {
		load_plugin_textdomain('tinymce_bks_lang', false, dirname(plugin_basename(__FILE__)).'/languages');
		$this->tinymce_backslash_plugin_url = plugin_dir_url(__FILE__);

		$this->tinymce_backslash_setting_opt = get_option('tinymce_backslash_setting_opt');
		add_action('plugins_loaded', array(&$this, 'tinymce_backslash_check_db_ver'));
		add_action('admin_menu', array(&$this, 'tinymce_backslash_register_menu_item'));
		add_filter( 'plugin_action_links', array(&$this, 'tinymce_backslash_setting_link'), 10, 2);
		if ($this->tinymce_backslash_setting_opt['tinymce_button'] == 1 && version_compare(get_bloginfo('version'), "3.2", ">=")) {
			add_action('admin_print_styles-post.php', array(&$this, 'tinymce_backslash_editor_css'));
			add_action('admin_print_styles-post-new.php', array(&$this, 'tinymce_backslash_editor_css'));
			add_action('admin_print_styles-page.php', array(&$this, 'tinymce_backslash_editor_css'));
			add_action('admin_print_styles-page-new.php', array(&$this, 'tinymce_backslash_editor_css'));
		}
		if ($this->tinymce_backslash_setting_opt['tinymce_button'] == 1) {
			add_filter('tiny_mce_version', array(&$this, 'tinymce_backslash_change_tinymce_version'));
			add_action('init', array(&$this, 'tinymce_backslash_addbuttons'));
		}
		if ($this->tinymce_backslash_setting_opt['quicktag'] == 1) {
			if (version_compare(get_bloginfo('version'), "3.2.1", "<=")) {
				add_action('admin_print_styles-post.php', array(&$this, 'tinymce_backslash_load_jquery'));
				add_action('admin_print_styles-post-new.php', array(&$this, 'tinymce_backslash_load_jquery'));
				add_action('admin_print_styles-page.php', array(&$this, 'tinymce_backslash_load_jquery'));
				add_action('admin_print_styles-page-new.php', array(&$this, 'tinymce_backslash_load_jquery'));
			}
			if (strpos($_SERVER['REQUEST_URI'], 'post.php') ||
			strpos($_SERVER['REQUEST_URI'], 'post-new.php') ||
			strpos($_SERVER['REQUEST_URI'], 'page.php') ||
			strpos($_SERVER['REQUEST_URI'], 'page-new.php') ||
			strpos($_SERVER['REQUEST_URI'], 'comment.php')) {
				add_action('admin_print_footer_scripts', array(&$this, 'tinymce_backslash_editor_script'));
			}
		}
	}

	// Create settings array
	function tinymce_backslash_setting_array() {
		$this->tinymce_backslash_setting_opt = array(
			"tinymce_button" => 1,
			"button_row" => "1",
			"quicktag" => 1,
			);
		// Store in DB
		add_option('tinymce_backslash_setting_opt', $this->tinymce_backslash_setting_opt);
		add_option('tinymce_backslash_updated', 'false');
	}

	// Check DB table version and create table
	function tinymce_backslash_check_db_ver() {
		$current_checkver_stamp = get_option('tinymce_backslash_checkver_stamp');
		if (!$current_checkver_stamp || version_compare($current_checkver_stamp, $this->tinymce_backslash_db_ver, "!=")) {
			$updated_count = 0;
			// For new installation, update from ver. 0.6 or older
			if (!$current_checkver_stamp) {
				// Register array
				$this->tinymce_backslash_setting_array();
				$updated_count = $updated_count + 1;
			}
			// For update from older version.
			if ($current_checkver_stamp && version_compare($current_checkver_stamp, $this->tinymce_backslash_db_ver, "!=")) {
				// Delete old setting array
				if (is_array($this->tinymce_backslash_setting_opt)){
					include_once('uninstall.php');
				}
				// Register array
				$this->tinymce_backslash_setting_array();
				$updated_count = $updated_count + 1;
			}
			update_option('tinymce_backslash_checkver_stamp', $this->tinymce_backslash_db_ver);
			// Stamp for showing messages
			if ($updated_count != 0) {
				update_option('tinymce_backslash_updated', 'true');
			}
		}
	}

	// Register the setting panel and hooks
	function tinymce_backslash_register_menu_item() {
		$tinymce_backslash_page_hook = add_options_page('TinyMCE Backslash Button Options', 'TinyMCE Backslash', 'manage_options', 'tinymce-backslash-options', array(&$this, 'tinymce_backslash_options_panel'));
		if ($tinymce_backslash_page_hook != null) {
			$tinymce_backslash_page_hook = '-'.$tinymce_backslash_page_hook;
		}
		add_action('admin_print_scripts'.$tinymce_backslash_page_hook, array(&$this, 'tinymce_backslash_load_jscript_for_admin'));
		if (get_option('tinymce_backslash_updated') == "true" && !(isset($_POST['Tinymce_Backslash_Setting_Submit']) && $_POST['tinymce_backslash_hidden_value'] == "true") && !(isset($_POST['Tinymce_Backslash_Reset']) && $_POST['tinymce_backslash_reset'] == "true")) {
			add_action('admin_notices', array(&$this, 'tinymce_backslash_admin_updated_notice'));
		}
	}

	// Message for admin when DB table updated
	function tinymce_backslash_admin_updated_notice(){
		echo '<div id="message" class="updated"><p>'.__("TinyMCE Backslash Button has successfully created new DB table.<br />If you upgraded to this version, some setting options may be added or reset to the default values.<br />Go to the <a href=\"options-general.php?page=tinymce-backslash-options\">setting panel</a> and configure TinyMCE Backslash Button now. Once you save your settings, this message will be cleared.", "tinymce_bks_lang").'</p></div>';
	}

	// Show plugin info in the footer
	function tinymce_backslash_add_admin_footer() {
		$tinymce_backslash_plugin_data = get_plugin_data(__FILE__);
		printf('%1$s by %2$s<br />', $tinymce_backslash_plugin_data['Title'].' '.$tinymce_backslash_plugin_data['Version'], $tinymce_backslash_plugin_data['Author']);
	}

	// Register the setting panel
	function tinymce_backslash_setting_link($links, $file) {
		static $this_plugin;
		if (! $this_plugin) $this_plugin = plugin_basename(__FILE__);
		if ($file == $this_plugin){
			$settings_link = '<a href="options-general.php?page=tinymce-backslash-options">'.__("Settings", "tinymce_bks_lang").'</a>';
			array_unshift($links, $settings_link);
		}  
		return $links;
	}

	// Load stylesheet for fullscreen mode
	function tinymce_backslash_editor_css() {
		wp_enqueue_style('tinymce-backslash-editor', $this->tinymce_backslash_plugin_url.'tinymce_backslash_fullscreen.css', false, '1.0');
	}

	// Load script in setting panel
	function tinymce_backslash_load_jscript_for_admin() {
		wp_enqueue_script('rc_admin_js', $this->tinymce_backslash_plugin_url.'rc-admin-js.js', false, '1.1');
	}

	// Add TinyMCE pluguin
	function tinymce_backslash_addbuttons() {
		// Don't bother doing this stuff if the current user lacks permissions
		if (!current_user_can('edit_posts') && !current_user_can('edit_pages'))
			return;
		// Add only in Rich Editor mode
		if (get_user_option('rich_editing') == 'true') {
		// add the button for wp25 in a new way
			add_filter("mce_external_plugins", array(&$this, 'tinymce_backslash_add_tinymce_plugin'));
			$button_row = $this->tinymce_backslash_setting_opt['button_row'];
			if ($button_row== "2" || $button_row== "3" || $button_row== "4") {
				add_filter('mce_buttons_'.$button_row, array(&$this, 'tinymce_backslash_register_button'));
			} else {
				add_filter('mce_buttons', array(&$this, 'tinymce_backslash_register_button'));
			}
			if (version_compare(get_bloginfo('version'), "3.2", ">=")) {
				add_filter('wp_fullscreen_buttons', array(&$this, 'tinymce_backslash_fullscreen'));
			}
		}
	}

	// used to insert button in wordpress 2.5x editor
	function tinymce_backslash_register_button($buttons) {
		array_push($buttons, "tinymce_backslash");
		return $buttons;
	}

	// Load the TinyMCE plugin : editor_plugin.js (wp2.5)
	function tinymce_backslash_add_tinymce_plugin($plugin_array) {
		$plugin_array['tinymce_backslash'] = $this->tinymce_backslash_plugin_url.'tinymce/editor_plugin.js';
		return $plugin_array;
	}

	function tinymce_backslash_change_tinymce_version($version) {
		return ++$version;
	}

	// For fullscreen mode
	function tinymce_backslash_fullscreen($buttons) {
		$buttons['tinymce_backslash'] = array(
		'title' => __('TinyMCE Backslash Button', 'tinymce_bks_lang'),
		'onclick' => "tinyMCE.execCommand('tinymce_backslash_cmd');",
		'both' => false);
		return $buttons;
	}

	// Load jQuery on the header
	function tinymce_backslash_load_jquery() {
		wp_enqueue_script('jquery');
	}

	// Load scripts for HTML editor
	function tinymce_backslash_editor_script() {
		echo "\n<script type=\"text/javascript\">
function InsertBackslash() {
	edInsertContent(edCanvas, '<span style=\"font-family: \'Courier New\',Courier,monospace;\">\\\\</span>');
}\n\n";

		if (version_compare(get_bloginfo('version'), "3.2.1", "<=")) {
			echo "function InsertBackslashRegisterQtButton() {
	jQuery('#ed_toolbar').each( function() {
		var button = document.createElement('input');
		button.type = 'button';
		button.value = '".__("Backslash", "tinymce_bks_lang")."';
		button.onclick = InsertBackslash;
		button.className = 'ed_button';
		button.title = '".__("Insert Backslash", "tinymce_bks_lang")."';
		button.id = 'ed_backslash';

		jQuery(this).append(button);
	});
}
 
InsertBackslashRegisterQtButton();\n";
		} else {
			echo "QTags.addButton( 'ed_backslash', '".__("Backslash", "tinymce_bks_lang")."', InsertBackslash);\n";
		}
			echo "</script>\n";
	}

	// Setting panel
	function tinymce_backslash_options_panel(){
		if(!function_exists('current_user_can') || !current_user_can('manage_options')){
			die(__('Cheatin&#8217; uh?'));
		} 
		add_action('in_admin_footer', array(&$this, 'tinymce_backslash_add_admin_footer'));

		// Update setting options
		if (isset($_POST['Tinymce_Backslash_Setting_Submit']) && $_POST['tinymce_backslash_hidden_value'] == "true" && check_admin_referer("tinymce_backslash_update_options", "_wpnonce_update_options")) {
			if ($_POST['tinymce_button'] == 1) {
				$this->tinymce_backslash_setting_opt['tinymce_button'] = 1;
			} else {
				$this->tinymce_backslash_setting_opt['tinymce_button'] = 0;
			}
			$this->tinymce_backslash_setting_opt['button_row'] = $_POST['button_row'];
			if ($_POST['quicktag'] == 1) {
				$this->tinymce_backslash_setting_opt['quicktag'] = 1;
			} else {
				$this->tinymce_backslash_setting_opt['quicktag'] = 0;
			}
			// Store in DB
			update_option('tinymce_backslash_setting_opt', $this->tinymce_backslash_setting_opt);
			update_option('tinymce_backslash_updated', 'false');
			// Show message for admin
			echo "<div id='setting-error-settings_updated' class='updated fade'><p><strong>".__("Settings saved.","tinymce_bks_lang")."</strong></p></div>";
		}
		// Reset all settings
		if (isset($_POST['Tinymce_Backslash_Reset']) && $_POST['tinymce_backslash_reset'] == "true" && check_admin_referer("tinymce_backslash_reset_options", "_wpnonce_reset_options")) {
			include_once('uninstall.php');
			$this->tinymce_backslash_setting_array();
			update_option('tinymce_backslash_checkver_stamp', $this->tinymce_backslash_db_ver);
			// Show message for admin
			echo "<div id='setting-error-settings_updated' class='updated fade'><p><strong>".__("All settings were reset. Please <a href=\"options-general.php?page=tinymce-backslash-options\">reload the page</a>.", "tinymce_bks_lang")."</strong></p></div>";
		}

	?> 
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2>TinyMCE Backslash Button</h2>
	<form method="post" action="">
	<?php wp_nonce_field("tinymce_backslash_update_options", "_wpnonce_update_options"); ?>
	<input type="hidden" name="tinymce_backslash_hidden_value" value="true" />
	<h3><?php _e("1. TinyMCE Button Settings", "tinymce_bks_lang") ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Add TinyMCE Button', 'tinymce_bks_lang') ?></th>
				<td>
					<input type="checkbox" name="tinymce_button" value="1" <?php if($this->tinymce_backslash_setting_opt['tinymce_button'] == 1){echo 'checked="checked" ';} ?>/><br />
					<p><small><?php _e("Enable/Disable TinyMCE button. It can help you to type backslash in Visual Editor.", "tinymce_bks_lang") ?></small></p>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Place the buttons in", "tinymce_bks_lang") ?></th> 
				<td>
					<select name="button_row">
						<option value="1" <?php if ($this->tinymce_backslash_setting_opt['button_row'] == "1") {echo 'selected="selected"';} ?>><?php _e("1st row", "tinymce_bks_lang") ?></option>
						<option value="2" <?php if ($this->tinymce_backslash_setting_opt['button_row'] == "2") {echo 'selected="selected"';} ?>><?php _e("2nd row", "tinymce_bks_lang") ?></option>
						<option value="3" <?php if ($this->tinymce_backslash_setting_opt['button_row'] == "3") {echo 'selected="selected"';} ?>><?php _e("3rd row", "tinymce_bks_lang") ?></option>
						<option value="4" <?php if ($this->tinymce_backslash_setting_opt['button_row'] == "4") {echo 'selected="selected"';} ?>><?php _e("4th row", "tinymce_bks_lang") ?></option>
					</select> <?php _e("of TinyMCE toolbar.", "tinymce_bks_lang") ?>
					<p><small><?php _e("Choose TinyMCE toolbar row which buttons will be placed in.", "tinymce_bks_lang") ?></small></p>
				</td>
			</tr>
		</table>
	<h3><?php _e("2. Quicktag Button Settings", 'tinymce_bks_lang') ?></h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e('Add Quicktag Button', 'tinymce_bks_lang') ?></th>
				<td>
					<input type="checkbox" name="quicktag" value="1" <?php if($this->tinymce_backslash_setting_opt['quicktag'] == 1){echo 'checked="checked" ';} ?>/><br />
					<p><small><?php _e("Enable/Disable Quicktag button. It can help you to type backslash in HTML Editor.", "tinymce_bks_lang") ?></small></p>
				</td>
			</tr>
		</table>
		<p class="submit">
		<input type="submit" name="Tinymce_Backslash_Setting_Submit" value="<?php _e("Save Changes", "tinymce_bks_lang") ?>" />
		</p>
	</form>
	<h3><?php _e("3. Restore all settings to default", "tinymce_bks_lang") ?></h3>
	<form method="post" action="" onsubmit="return confirmreset()">
	<?php wp_nonce_field("tinymce_backslash_reset_options", "_wpnonce_reset_options"); ?>
		<p class="submit">
		<input type="hidden" name="tinymce_backslash_reset" value="true" />
		<input type="submit" name="Tinymce_Backslash_Reset" value="<?php _e("Reset All Settings", "tinymce_bks_lang") ?>" />
		</p>
	</form>
	<h3><a href="javascript:showhide('id1');" name="system_info"><?php _e("4. Your System Info", "tinymce_bks_lang") ?></a></h3>
	<div id="id1" style="display:none; margin-left:20px">
	<p>
	<?php _e("Server OS:", "tinymce_bks_lang") ?> <?php echo php_uname('s').' '.php_uname('r'); ?><br />
	<?php _e("PHP version:", "tinymce_bks_lang") ?> <?php echo phpversion(); ?><br />
	<?php _e("MySQL version:", "tinymce_bks_lang") ?> <?php echo mysql_get_server_info(); ?><br />
	<?php _e("WordPress version:", "tinymce_bks_lang") ?> <?php bloginfo("version"); ?><br />
	<?php _e("Site URL:", "tinymce_bks_lang") ?> <?php if(function_exists("home_url")) { echo home_url(); } else { echo get_option('home'); } ?><br />
	<?php _e("WordPress URL:", "tinymce_bks_lang") ?> <?php echo site_url(); ?><br />
	<?php _e("WordPress language:", "tinymce_bks_lang") ?> <?php bloginfo("language"); ?><br />
	<?php _e("WordPress character set:", "tinymce_bks_lang") ?> <?php bloginfo("charset"); ?><br />
	<?php _e("WordPress theme:", "tinymce_bks_lang") ?> <?php $tinymce_backslash_theme = get_theme(get_current_theme()); echo $tinymce_backslash_theme['Name'].' '.$tinymce_backslash_theme['Version']; ?><br />
	<?php _e("TinyMCE Backslash Button version:", "tinymce_bks_lang") ?> <?php $tinymce_backslash_plugin_data = get_plugin_data(__FILE__); echo $tinymce_backslash_plugin_data['Version']; ?><br />
	<?php _e("TinyMCE Backslash Button DB version:", "tinymce_bks_lang") ?> <?php echo get_option('tinymce_backslash_checkver_stamp'); ?><br />
	<?php _e("TinyMCE Backslash Button URL:", "tinymce_bks_lang") ?> <?php echo $this->tinymce_backslash_plugin_url; ?><br />
	<?php _e("Your browser:", "tinymce_bks_lang") ?> <?php echo esc_html($_SERVER['HTTP_USER_AGENT']); ?>
	</p>
	</div>
	<p>
	<?php _e("To report a bug ,submit requests and feedback, ", "tinymce_bks_lang") ?><?php _e("Use <a href=\"http://wordpress.org/tags/tinymce-backslash-button?forum_id=10\">Forum</a> or <a href=\"http://www.near-mint.com/blog/contact\">Mail From</a>", "tinymce_bks_lang") ?>
	</p>
	</div>
	<?php } 
}

// Start this plugin
$TinyMCE_Backslash_Button = new TinyMCE_Backslash_Button();

?>