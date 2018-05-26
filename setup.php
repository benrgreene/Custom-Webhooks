<?php

/**
    Plugin Name: Custom Webhooks for WordPress
    Plugin URI:
    Description: Add custom defined webhooks to your WordPress site
    Author: Ben Greene
    Version: 1
    License: MIT
 */

require( 'core/admin-controller.php' );
require( 'core/webhook-controller.php' );
require( 'core/class-webhook-table-manager.php' );

class BRG_Webhooks {

    protected static $instance;

    public static function get_instance() {
        if( null === self::$instance ) {
            self::$instance = new BRG_Webhooks();
        }
        return self::$instance;
    }

    public function __construct() {
        $this->admin_controller = new BRG_Webhook_Admin_Interface_Controller();
        $this->webhook_controller = new BRG_Webhook_Controller();
    }

    public function save_webhooks() {
        add_action( 'init', array( $this, 'proccess_saved_webhooks' ) );
    }

    public function proccess_saved_webhooks() {
        // Only admins can update webhook info
        if( ! current_user_can( 'update_core' ) ) {
            return;
        }

        $table_manager = BRG_Webhook_Table_Manager::get_instance();
        $user_id = $table_manager->get_user_id();
        
        if( ! empty( $_POST['brg-webhook-auth'] ) ) {
            update_option( 'brg-webhook-auth', $_POST['brg-webhook-auth'] );
        }
        if( ! empty( $_POST['brg-webhooks'] ) ) {
            $webhooks = $_POST['brg-webhooks'];
            $webhooks = str_replace( '\"', '"', $webhooks );
            $table_manager->register_user_webhooks( $webhooks );
        }
    }
}

$webhook_handler = BRG_Webhooks::get_instance();
$webhook_handler->save_webhooks();