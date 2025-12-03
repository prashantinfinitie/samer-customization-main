<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_location_based_seller_discovery extends CI_Migration
{

    public function up()
    {
        // Add latitude field to users table (for seller location)
        if (!$this->db->field_exists('latitude', 'users')) {
            $fields = array(
                'latitude' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '50',
                    'null' => TRUE,
                    'default' => NULL,
                    'comment' => 'User/Seller latitude coordinate',
                    'after' => 'address'
                )
            );
            $this->dbforge->add_column('users', $fields);
        }

        // Add longitude field to users table (for seller location)
        if (!$this->db->field_exists('longitude', 'users')) {
            $fields = array(
                'longitude' => array(
                    'type' => 'VARCHAR',
                    'constraint' => '50',
                    'null' => TRUE,
                    'default' => NULL,
                    'comment' => 'User/Seller longitude coordinate',
                    'after' => 'latitude'
                )
            );
            $this->dbforge->add_column('users', $fields);
        }

        // Add service_radius field to seller_data table (in kilometers)
        if (!$this->db->field_exists('service_radius', 'seller_data')) {
            $fields = array(
                'service_radius' => array(
                    'type' => 'DECIMAL',
                    'constraint' => '10,2',
                    'null' => TRUE,
                    'default' => NULL,
                    'comment' => 'Service radius in kilometers. NULL means no limit.'
                )
            );
            $this->dbforge->add_column('seller_data', $fields);
        }

        // Add is_location_based column to seller_data for enabling/disabling location-based filtering
        if (!$this->db->field_exists('is_location_based', 'seller_data')) {
            $fields = array(
                'is_location_based' => array(
                    'type' => 'TINYINT',
                    'constraint' => '1',
                    'null' => FALSE,
                    'default' => '0',
                    'comment' => '0 = service everywhere, 1 = location-based service'
                )
            );
            $this->dbforge->add_column('seller_data', $fields);
        }

        // Add index for faster location queries (only if columns exist and index doesn't exist)
        if ($this->db->field_exists('latitude', 'users') && $this->db->field_exists('longitude', 'users')) {
            // Check if index already exists
            $index_exists = false;
            $query = $this->db->query("SHOW INDEXES FROM users WHERE Key_name = 'idx_users_location'");
            if ($query && $query->num_rows() > 0) {
                $index_exists = true;
            }

            // Create index only if it doesn't exist
            if (!$index_exists) {
                $this->db->query("CREATE INDEX idx_users_location ON users(latitude, longitude)");
            }
        }
    }

    public function down()
    {
        if ($this->db->field_exists('latitude', 'users')) {
            $this->dbforge->drop_column('users', 'latitude');
        }

        if ($this->db->field_exists('longitude', 'users')) {
            $this->dbforge->drop_column('users', 'longitude');
        }

        if ($this->db->field_exists('service_radius', 'seller_data')) {
            $this->dbforge->drop_column('seller_data', 'service_radius');
        }

        if ($this->db->field_exists('is_location_based', 'seller_data')) {
            $this->dbforge->drop_column('seller_data', 'is_location_based');
        }

        // Drop index
        try {
            $this->db->query("DROP INDEX idx_users_location ON users");
        } catch (Exception $e) {
            // Index might not exist
        }
    }
}

