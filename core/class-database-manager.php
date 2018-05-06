<?php

/**
 *
 * Author: Ben Greene
 * Version: 1.0
 * Description:
 *
 * Class that creates and maintains a custom WP table.
 *
 * There are no get/insert functions, those are left to the individual
 * implementations that are built on top of this.
 *
 * Make sure you pass in a valid `.ini` file that can be used
 * to build/update the DB
 */

if( ! class_exists( 'Database_Table_Manager' ) ) {
    class Database_Table_Manager {

        protected $version;
        protected $table_name;
        protected $columns;

        public function init_db( $path_to_ini ) {
            $setup = $this->setup_info( $path_to_ini );

            if( false !== $setup ) {
                if( ! $this->table_exists() ) {
                    $this->create_table();
                }

                if( is_admin() &&
                    ! $this->table_up_to_date() &&
                    isset( $_POST['should_update_table_' . $this->table_name ] ) &&
                    $_POST['should_update_table_' . $this->table_name ] ) {
                    $this->update_table();
                }

                add_action( 'admin_notices', array( $this, 'display_update_notice' ) );
            }
            else {
                error_log( 'Error setting up a table from the file: ' . $path_to_ini . '. Please check your settings.' );
            }
        }

        // Display to the admin that they should update the table.
        public function display_update_notice() {
            if( ! is_admin() ||
                $this->table_up_to_date() ) {
                return;
            } ?>
            <div class="notice notice-success">
                <p>You need to update the database for <?php echo $this->table_name ?> (make sure you back it up first). <form action="" method="POST"><input type="hidden" name="should_update_table_<?php echo $this->table_name; ?>" value="true"/><?php submit_button('Update Now'); ?></form></p>
            </div>
        <?php }

        protected function setup_info( $path_to_ini ) {
            $settings = parse_ini_file( $path_to_ini, true );
            if( false === $settings ) {
                return false;
            }

            // Check if general info is present, if not return false.
            if( isset( $settings['general_info'] ) &&
                isset( $settings['general_info']['table_name'] ) &&
                isset( $settings['general_info']['table_version'] ) ) {
                $this->table_name = $settings['general_info']['table_name'];
                $this->version = $settings['general_info']['table_version'];
            } else {
                return false;
            }

            // Check if there are columns in the ini file, if not return false.
            if( isset( $settings['columns'] ) ) {
                $this->columns = $settings['columns'];
            } else {
                return false;
            }

            return true;
        }

        /**
         * Loop through the table columns and build the new table.
         */
        protected function create_table() {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            global $wpdb;
            $full_table_name = $wpdb->prefix . $this->table_name;
            $charset = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $full_table_name ( ID mediumint NOT NULL AUTO_INCREMENT, ";
            // Build each column individually.
            foreach( $this->columns as $col_name => $col_settings ) {
                // If there isn't a default passed, default is 'null'
                $default = isset( $col_settings['default'] ) ? 'DEFAULT \'' . $col_settings['default'] . '\'' : null;
                // Check if the column should allow NULL values
                $not_null = isset( $col_settings['allow_null'] ) ? '' : 'NOT NULL';
                // Put together the query string pieces
                $sql .= sprintf( '%s %s %s %s, ', $col_name, $col_settings['type'], $default, $not_null);
            }
            $sql .= "PRIMARY KEY (ID) ) $charset;";
            dbDelta( $sql );
            update_option( $this->table_name . '_table_version', $this->version );
        }

        protected function update_table() {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            global $wpdb;

            $full_table_name = $wpdb->prefix . $this->table_name;
            $sql = "";
            $line_prefix = "ALTER TABLE $full_table_name \n";

            foreach ( $this->columns as $name => $col_settings ) {
                // check column exists to determine the command to use
                $result = $wpdb->get_var( "SHOW COLUMNS FROM `$full_table_name` LIKE '$name'" );
                $cmd = !empty( $result ) ? 'MODIFY COLUMN' : 'ADD';
                // If there isn't a default passed, default is 'null'
                $default = '';
                if( empty( $result ) ) {
                    $default = isset( $col_settings['default'] ) ? ' DEFAULT \'' . $col_settings['default'] . '\'' : null;
                }
                // Check if the column should allow NULL values
                $not_null = isset( $col_settings['allow_null'] ) ? '' : 'NOT NULL';
                // Put together the query string pieces
                $sql = sprintf( '%s %s %s %s %s %s;', $line_prefix, $cmd, $name, $col_settings['type'], $not_null, $default);
                $results = $wpdb->query( $sql );

                // Check if there is a value to autopopulate the new columns with AND we're adding the column
                if( isset( $col_settings['autopopulate'] ) &&
                    empty( $result ) ) {
                    $autopopulate = $col_settings['autopopulate'];
                    $update_sql   = "UPDATE $full_table_name SET $name = '$autopopulate';";
                    $wpdb->query( $update_sql );
                }
            }

            update_option( $this->table_name . '_table_version', $this->version );
        }

        protected function str_lreplace( $search, $replace, $subject ) {
            $pos = strrpos($subject, $search);

            if($pos !== false)
            {
                $subject = substr_replace($subject, $replace, $pos, strlen($search));
            }

            return $subject;
        }

        /**
         * ------------------------------------------------------------------
         * Helpers for checking if the table is correctly setup
         * ------------------------------------------------------------------
         */

        protected function table_exists() {
            global $wpdb;
            $full_table_name = $wpdb->prefix . $this->table_name;

            if( $full_table_name == $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") ) {
                return true;
            } else {
                return false;
            }
        }

        protected function table_up_to_date() {
            $table_version = get_option( $this->table_name . '_table_version' );
            if( $table_version == $this->version ) {
                return true;
            }
            return false;
        }
    }
}