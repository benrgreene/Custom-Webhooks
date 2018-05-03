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
        $webhooks = json_decode( get_option( 'webhooks' ), true );
        if( ! is_array( $webhooks) ) {
            return;
        }

        foreach( $webhooks as $webhook ) {
            $action   = $webhook['action'];
            $endpoint = $webhook['endpoint'];
            $data     = $this->get_data( $action );

            add_action( $action, function() use ( $endpoint, $data ) {
                $this->make_curl( $endpoint, $data );
            } );
        }
    }

    /**
     * Get the default data that should be sent with a webhook
     */
    public function get_data( $action ) {
        $data = null;

        // New post published (arbitrary type)
        if( 0 === strpos( $action, 'publish_' ) ) {
            $post_type = str_replace( 'publish_', '', $action );
            $data = wp_get_recent_posts( array(
                'numberposts' => 1,
                'post_type'   => $post_type,
            ) );
        }

        // Want to allow developers the ability to modify the data.
        $data = apply_filters( 'brg/webhook/data/' . $action, $data );
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
