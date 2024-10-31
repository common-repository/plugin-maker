<?php
/*
Plugin Name: Plugin Maker
Plugin URI: http://wpprogrammer.com/plugin-maker/
Description: Lets you to create your own plugins (blank, ofcourse).
Version: 1.0
Author: Utkarsh Kukreti
Author URI: http://utkar.sh
*/

if(is_admin())
	PluginMaker::init();

abstract class PluginMaker
{
	static function init()
	{
		add_action('admin_menu', array('PluginMaker', 'admin_menu'));
	}
	
	static function admin_menu()
	{
		add_submenu_page('plugins.php', 'New Plugin', 'New Plugin', 10, __FILE__, array('PluginMaker', 'render'));
	}
	
	static function render()
	{
		echo '<div class="wrap">';
		echo '<h2>Create New Plugin</h2>';
		if(current_user_can('edit_plugins'))
		{
			global $display_name, $user_url;
			get_currentuserinfo();
			$defaults = array(
				'plugin_name' => 'New Plugin',
				'plugin_uri' => get_bloginfo('url'),
				'description' => 'A Brand New Plugin',
				'version' => '1.0',
				'author' => '' != $display_name ? $display_name : get_bloginfo('name'),
				'author_uri' => '' != $user_url ? $user_url : get_bloginfo('url'),
				'' => '');
			$r = wp_parse_args($_POST, $defaults);
			$r['plugin_name'] = strtolower( str_replace(' ', '-', $r['plugin_name']) );
			
			if(isset($_POST['plugin_name']))
			{
				if(!wp_verify_nonce($_POST['_wpnonce'], 'plugin-maker'))
					wp_die(__('Error'));
				echo 'Creating Plugin';
				
				// create dir
				extract($r, EXTR_SKIP);
				
				$file_name = WP_PLUGIN_DIR . '/' . $plugin_name . '/' . $plugin_name . '.php';
				$dir_name = WP_PLUGIN_DIR . '/' . $plugin_name;
				
				if(file_exists( $file_name))
					wp_die("File already exists");

				if(!is_dir($dir_name) && !mkdir($dir_name))
					wp_die("Cannot create directory");
				
				$handle = fopen($file_name, 'w') or wp_die('Cannot open file for editing');
				
				$file_contents = <<<OUT
<?php
/*
Plugin Name: $plugin_name
Plugin URI: $plugin_uri
Description: $description
Version: $version
Author: $author
Author URI: $author_uri
*/


?>
OUT;
				fwrite($handle, $file_contents);
				fclose($handle);
				echo '<p> Plugin successfully created. Start editing the plugin at <a href="' . admin_url('plugin-editor.php?file=' . $plugin_name . '/' . $plugin_name . '.php') . '">here</a>';
			}
			else
			{
				echo '<div class="form-wrap">';
				echo '<form action="" method="post">';
				wp_nonce_field('plugin-maker');
				echo '<table class="form-table">';

				echo '<tr valign="top"><th scope="row">';
				echo '<label for="plugin_name">' . __('Plugin Name') . '</label></th><td>';
				echo '<input type="text"  class="regular-text" id="plugin_name" name="plugin_name" value="' . $r['plugin_name'] . '"/>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><th scope="row">';
				echo '<label for="plugin_uri">' . __('Plugin URI') . '</label></th><td>';
				echo '<input type="text"  class="regular-text" id="plugin_uri" name="plugin_uri" value="' . $r['plugin_uri'] . '"/>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><th scope="row">';			
				echo '<label for="description">' . __('Description') . '</label></th><td>';
				echo '<textarea id="description" name="description" class="regular-text" cols="60" rows="5">' . $r['description'] . '</textarea>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><th scope="row">';
				echo '<label for="version">' . __('Version') . '</label></th><td>';
				echo '<input type="text"  class="regular-text" id="version" name="version" value="' . $r['version'] . '"/>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><th scope="row">';
				echo '<label for="author">' . __('Author') . '</label></th><td>';
				echo '<input type="text"  class="regular-text" id="author" name="author" value="' . $r['author'] . '"/>';
				echo '</td></tr>';
				
				echo '<tr valign="top"><th scope="row">';
				echo '<label for="author_uri">' . __('Author URI') . '</label></th><td>';
				echo '<input type="text"  class="regular-text" id="author_uri" name="author_uri" value="' . $r['author_uri'] . '"/>';
				echo '</td></tr>';			
				
				echo '<tr><td colspan=2><input type="submit" value="Create Plugin" name="submit" class="button"/></td></tr>';
				
				echo '</table>';
				echo '</form>';
				echo '</div>';
			}
		}
		else wp_die(__('Sorry, you are not allowed to create new plugins'));
		echo '</div>';
	}
	
	 
}
