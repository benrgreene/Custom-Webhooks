<?php

class BRG_Webhook_Controller {

    protected static $instance;

    public static function get_instance() {
        if( null === self::$instance ) {
            self::$instance = new Webhook_Controller();
        }
        return self::$instance;
    }

    // Use the DB manager to create/update the database
    public function __construct() {
        $table_manager = BRG_Webhook_Table_Manager::get_instance();
        $webhooks = $table_manager->get_all_webhooks();
        if( empty( $webhooks ) ) {
            return;
        }

        foreach( $webhooks as $webhook ) {
            $action   = $webhook['action'];
            $endpoint = $webhook['endpoint'];

            add_action( $action, function( $default_data=false ) use ( $endpoint, $action ) {
                $data = $this->get_data( $action, $default_data );
                $this->make_curl( $endpoint, $data );
            }, 10, 1 );
        }
    }

    /**
     * Get the default data that should be sent with a webhook
     */
    public function get_data( $action, $default_data ) {
        $data = null;

        // New post published (arbitrary type)
        if( 0 === strpos( $action, 'publish_' ) ) {
            $post_type = str_replace( 'publish_', '', $action );
            $data = wp_get_recent_posts( array(
                'numberposts' => 1,
                'post_type'   => $post_type,
            ) );
        }

        // New category
        if( 0 === strpos( $action, 'create_' ) ) {
            $tax_type = str_replace( 'create_', '', $action );
            $data = get_term_by( 'id', $default_data, $tax_type );
        }

        // New user
        if( 'user_register' == $action ) {
            $data = get_user_by( 'id', $default_data );
            if( false !== $data ) {
                // This should NOT be passed
                unset( $data->data->user_pass );
            }
        }

        // Want to allow developers the ability to modify the data.
        $data = apply_filters( 'brg/webhook/data/' . $action, $data, $raw_data );
        return $data;
    }

    public function make_curl( $endpoint, $data ) {
        $ch = curl_init();

        curl_setopt( $ch, CURLOPT_URL, $endpoint );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_ENCODING, '' );
        curl_setopt( $ch, CURLOPT_MAXREDIRS, 10 );
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'accept: text/json',
        ) );
        if( ! empty( $data ) ) {
            curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, 'POST' );
            curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $data ) );
        }

        $raw_data = curl_exec( $ch );
        curl_close($ch);
    }
}
