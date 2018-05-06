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
}