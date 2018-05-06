<?php

require( 'class-database-manager.php' );

class BRG_Webhook_Table_Manager extends Database_Table_Manager {

  public static $instance;

  public static function get_instance() {
    if( null == self::$instance) {
      self::$instance = new BRG_Webhook_Table_Manager();
    }
    return self::$instance;
  }

  private function __construct() {
    $db_path = plugin_dir_path( __DIR__ ) . 'webhook-table.ini';
    $this->init_db( $db_path );
  }

  // Add user webhooks to the DB
  public function register_user_webhooks( $webhooks ) {
    $user_id = $this->get_user_id();
    if( false === $user_id ) {
      return;
    }

    $webhooks_exist = count( $this->get_user_webhooks( $user_id ) ) > 0;
    if( $webhooks_exist ) {
      $this->update_user_webhooks( $user_id, $webhooks );
    } else {
      $this->add_user_webhooks( $user_id, $webhooks );
    }
  }

  function add_user_webhooks( $user_id, $webhooks ) {
    global $wpdb;

    $sql = $wpdb->prepare( "INSERT INTO {$wpdb->prefix}{$this->table_name} (user_id, webhook_json ) VALUES (%d, '%s')", array(
      $user_id,
      $webhooks
    ) );

    $wpdb->query( $sql );
  }

  function update_user_webhooks( $user_id, $webhooks ) {
    global $wpdb;
    $sql = $wpdb->prepare( "UPDATE {$wpdb->prefix}{$this->table_name} SET webhook_json=%s WHERE user_id=%d", array(
      $webhooks,
      $user_id,
    ) );
    $wpdb->query( $sql );
  }

  public function get_user_webhooks( $user_id ) {
    global $wpdb;
    $sql = $wpdb->prepare( "SELECT * FROM {$wpdb->prefix}{$this->table_name} WHERE user_id=%d", array(
      $user_id,
    ) );
    $results = $wpdb->get_results( $sql );
    return $results;
  }

  public function get_all_webhooks() {

  }

  // Returns the default user id, or 0 for any site admins
  // false is returned for non logged in users 
  public function get_user_id() {
    $user_id = get_current_user_id();
    $user_id = (0 == $user_id) ? false : 0;
    $user_id = is_admin() ? 0 : $user_id;
    return $user_id;
  }
}