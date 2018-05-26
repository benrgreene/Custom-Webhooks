<?php

class BRG_Webhook_Admin_Interface_Controller {

    const SETTINGS_PAGE_SLUG  = 'brg_webhooks_settings_page';
    const SETTINGS_GROUP      = 'brg_webhooks_group';
    const SETTINGS_NONCE_NAME = 'brg_webhooks_nonce_name';

    private $plugin_settings = array(
        'brg-webhook-auth',
    );

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu') );
        add_action( 'admin_init', array( $this, 'register_plugin_settings') );
    }

    public function add_admin_menu() {
        $min_level = apply_filters( 'brg/webhooks/minimum_user_level', 'edit_posts' );
        add_menu_page( 'Webhooks', 'Webhooks', $min_level, self::SETTINGS_PAGE_SLUG, '', 'dashicons-analytics' );

        // Register submenu for plugin settings - default page for the plugin
        add_submenu_page( self::SETTINGS_PAGE_SLUG, 'Webhooks Settings', 'Settings', $min_level, self::SETTINGS_PAGE_SLUG, array( $this, 'display_settings_page' ) );


        // Register submenu for README page
        add_submenu_page( self::SETTINGS_PAGE_SLUG, 'README', 'Help', $min_level, self::SETTINGS_PAGE_SLUG . '-readme', array( $this, 'display_readme' ) );
    }

    public function register_plugin_settings() {
        foreach ( $this->plugin_settings as $setting ) {
            register_setting( self::SETTINGS_GROUP, $setting );
        }
    }

    public function display_settings_page() {
        include plugin_dir_path( __DIR__ ) . 'templates/settings.php';
    }

    public function display_readme() {
        include plugin_dir_path( __DIR__ ) . 'templates/readme.php';
    }
}
