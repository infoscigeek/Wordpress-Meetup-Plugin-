<?php
/*
Plugin Name: WP Meetup
Plugin URI: http://nuancedmedia.com/
Description: Pulls events from Meetup.com onto your blog
Version: 1.0
Author: Nuanced Media
Author URI: http://nuancedmedia.com/
*/
?>

<?php
$meetup = new WP_Meetup();

class WP_Meetup {
    
    private $dir;
    private $options;
    private $admin_page_url;
    private $mu_api;
    
    function WP_Meetup() {
        
        if (!empty($_POST)) $this->handle_post_data();
        
        $this->dir = WP_PLUGIN_DIR . "/wp-meetup/";
        $this->options = array();
        $this->options['api_key'] = get_option('wp_meetup_api_key') ? get_option('wp_meetup_api_key') : FALSE;
        $this->options['group_url_name'] = get_option('wp_meetup_group_url_name') ? get_option('wp_meetup_group_url_name') : FALSE;
        $this->admin_page_url = admin_url("options-general.php?page=wp_meetup");
        
        //var_dump($this->options);
        
        include("meetup_api/MeetupAPIBase.php");
        $this->mu_api = new MeetupAPIBase($this->options['api_key'], 'groups');
        
        
        add_action('admin_menu', array($this, 'admin_menu'));
        
    }
    
    function admin_menu() {
        add_options_page('WP Meetup Options', 'WP Meetup', 'manage_options', 'wp_meetup', array($this, 'admin_options'));
    }
    
    function handle_post_data() {
        if (array_key_exists('api_key', $_POST)) {
            update_option('wp_meetup_api_key', $_POST['api_key']);
            $this->options['api_key'] = $_POST['api_key'];
        }
        
        
        if (array_key_exists('group_url', $_POST)) {
            $parsed_name = str_replace("http://www.meetup.com/", "", $_POST['group_url']);
            $parsed_name = substr($parsed_name, 0, strpos($parsed_name, "/"));
            update_option('wp_meetup_group_url_name', $parsed_name);
            $this->options['group_url_name'] = $parsed_name;
        }
    }
    
    function admin_options() {
        if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
        
        $data = array();
        $data['has_api_key'] = !empty($this->options['api_key']);
        $data['group_url'] = !empty($this->options['group_url_name']) ? "http://www.meetup.com/" . $this->options['group_url_name'] : FALSE;
        
        echo $this->get_include_contents($this->dir . "options-page.php", $data);
        
    }
    
    function get_include_contents($filename, $vars = array()) {
        if (is_file($filename)) {
            ob_start();
            foreach ($vars as $name => $value) {
                $$name = $value;
            }
            include $filename;
            return ob_get_clean();
        }
        return false;
    }
    
}

?>