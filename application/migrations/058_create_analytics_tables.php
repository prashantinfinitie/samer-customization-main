<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_create_analytics_tables extends CI_Migration
{
    public function up()
    {
        // Create analytics_daily_snapshots table only if it doesn't exist
        if (!$this->db->table_exists('analytics_daily_snapshots')) {
            $this->dbforge->add_field(array(
                'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'date' => array(
                'type' => 'DATE',
                'null' => FALSE
            ),
            'seller_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL
            ),
            'gross_revenue' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'net_revenue' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'total_orders' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_units' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_profit' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'cart_additions' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'conversion_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00
            ),
            'new_customers' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returning_customers' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returns_count' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returns_amount' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            )
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('date');
            $this->dbforge->add_key('seller_id');
            $this->dbforge->create_table('analytics_daily_snapshots');

            // Set timestamp defaults (CI dbforge lacks default CURRENT_TIMESTAMP support)
            $this->db->query("
                ALTER TABLE `analytics_daily_snapshots`
                MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ");
        }

        // Create analytics_weekly_snapshots table only if it doesn't exist
        if (!$this->db->table_exists('analytics_weekly_snapshots')) {
            $this->dbforge->add_field(array(
                'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'year' => array(
                'type' => 'INT',
                'constraint' => 4,
                'null' => FALSE
            ),
            'week' => array(
                'type' => 'INT',
                'constraint' => 2,
                'null' => FALSE
            ),
            'week_start_date' => array(
                'type' => 'DATE',
                'null' => FALSE
            ),
            'week_end_date' => array(
                'type' => 'DATE',
                'null' => FALSE
            ),
            'seller_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL
            ),
            'gross_revenue' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'net_revenue' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'total_orders' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_units' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_profit' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'conversion_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00
            ),
            'new_customers' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returning_customers' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returns_count' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returns_amount' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            )
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('year');
            $this->dbforge->add_key('week');
            $this->dbforge->add_key('seller_id');
            $this->dbforge->create_table('analytics_weekly_snapshots');

            // Set timestamp defaults (CI dbforge lacks default CURRENT_TIMESTAMP support)
            $this->db->query("
                ALTER TABLE `analytics_weekly_snapshots`
                MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ");
        }

        // Create analytics_monthly_snapshots table only if it doesn't exist
        if (!$this->db->table_exists('analytics_monthly_snapshots')) {
            $this->dbforge->add_field(array(
                'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'year' => array(
                'type' => 'INT',
                'constraint' => 4,
                'null' => FALSE
            ),
            'month' => array(
                'type' => 'INT',
                'constraint' => 2,
                'null' => FALSE
            ),
            'seller_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE,
                'default' => NULL
            ),
            'gross_revenue' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'net_revenue' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'total_orders' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_units' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_profit' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'conversion_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00
            ),
            'new_customers' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returning_customers' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returns_count' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'returns_amount' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            )
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('year');
            $this->dbforge->add_key('month');
            $this->dbforge->add_key('seller_id');
            $this->dbforge->create_table('analytics_monthly_snapshots');

            // Set timestamp defaults (CI dbforge lacks default CURRENT_TIMESTAMP support)
            $this->db->query("
                ALTER TABLE `analytics_monthly_snapshots`
                MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ");
        }

        // Create customer_analytics table only if it doesn't exist
        if (!$this->db->table_exists('customer_analytics')) {
            $this->dbforge->add_field(array(
                'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'user_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'first_order_date' => array(
                'type' => 'DATE',
                'null' => TRUE
            ),
            'last_order_date' => array(
                'type' => 'DATE',
                'null' => TRUE
            ),
            'total_orders' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_spent' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'average_order_value' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'reorder_interval_days' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'is_returning' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            )
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('user_id');
            $this->dbforge->create_table('customer_analytics');

            // Set timestamp defaults (CI dbforge lacks default CURRENT_TIMESTAMP support)
            $this->db->query("
                ALTER TABLE `customer_analytics`
                MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ");
        }

        // Create product_analytics table only if it doesn't exist
        if (!$this->db->table_exists('product_analytics')) {
            $this->dbforge->add_field(array(
                'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'product_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => FALSE
            ),
            'product_variant_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'seller_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'total_sold' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'total_revenue' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'total_profit' => array(
                'type' => 'DECIMAL',
                'constraint' => '15,2',
                'default' => 0.00
            ),
            'average_weekly_sales' => array(
                'type' => 'DECIMAL',
                'constraint' => '10,2',
                'default' => 0.00
            ),
            'returns_count' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'return_rate' => array(
                'type' => 'DECIMAL',
                'constraint' => '5,2',
                'default' => 0.00
            ),
            'last_sale_date' => array(
                'type' => 'DATE',
                'null' => TRUE
            ),
            'created_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            ),
            'updated_at' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            )
            ));
            $this->dbforge->add_key('id', TRUE);
            $this->dbforge->add_key('product_id');
            $this->dbforge->add_key('product_variant_id');
            $this->dbforge->add_key('seller_id');
            $this->dbforge->create_table('product_analytics');

            // Set timestamp defaults (CI dbforge lacks default CURRENT_TIMESTAMP support)
            $this->db->query("
                ALTER TABLE `product_analytics`
                MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
            ");
        }
    }

    public function down()
    {
        $this->dbforge->drop_table('product_analytics');
        $this->dbforge->drop_table('customer_analytics');
        $this->dbforge->drop_table('analytics_monthly_snapshots');
        $this->dbforge->drop_table('analytics_weekly_snapshots');
        $this->dbforge->drop_table('analytics_daily_snapshots');
    }
}

