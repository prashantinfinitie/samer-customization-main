<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_multi_store_feature extends CI_Migration
{
    public function up()
    {
        // Create stores table
        $this->dbforge->add_field(array(
            'id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'auto_increment' => TRUE
            ),
            'vendor_id' => array(
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => TRUE,
                'comment' => 'References users.id (vendor account)'
            ),
            'store_name' => array(
                'type' => 'VARCHAR',
                'constraint' => '256',
                'null' => TRUE
            ),
            'slug' => array(
                'type' => 'VARCHAR',
                'constraint' => '512',
                'null' => TRUE
            ),
            'store_description' => array(
                'type' => 'VARCHAR',
                'constraint' => '512',
                'null' => TRUE
            ),
            'logo' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'store_url' => array(
                'type' => 'VARCHAR',
                'constraint' => '512',
                'null' => TRUE
            ),
            'category_ids' => array(
                'type' => 'VARCHAR',
                'constraint' => '256',
                'null' => TRUE,
                'comment' => 'Comma-separated category IDs'
            ),
            'deliverable_zipcode_type' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'deliverable_city_type' => array(
                'type' => 'INT',
                'constraint' => 11,
                'null' => TRUE
            ),
            'serviceable_zipcodes' => array(
                'type' => 'VARCHAR',
                'constraint' => '256',
                'null' => TRUE
            ),
            'serviceable_cities' => array(
                'type' => 'VARCHAR',
                'constraint' => '256',
                'null' => TRUE
            ),
            'rating' => array(
                'type' => 'DOUBLE',
                'constraint' => '8,2',
                'default' => 0.00
            ),
            'no_of_ratings' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'commission' => array(
                'type' => 'DOUBLE',
                'constraint' => '10,2',
                'default' => 0.00
            ),
            'low_stock_limit' => array(
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0
            ),
            'status' => array(
                'type' => 'TINYINT',
                'constraint' => 2,
                'default' => 2,
                'comment' => 'approved: 1 | not-approved: 2 | deactive:0 | removed :7'
            ),
            'seo_page_title' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'seo_meta_keywords' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'seo_meta_description' => array(
                'type' => 'VARCHAR',
                'constraint' => '1024',
                'null' => TRUE
            ),
            'seo_og_image' => array(
                'type' => 'TEXT',
                'null' => TRUE
            ),
            'is_default' => array(
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'comment' => '1 = default store for vendor'
            ),
            'date_added' => array(
                'type' => 'TIMESTAMP',
                'null' => FALSE
            )
        ));
        $this->dbforge->add_key('id', TRUE);
        $this->dbforge->add_key('vendor_id');
        $this->dbforge->add_key('slug');
        $this->dbforge->create_table('stores');

        // Set default value for date_added using raw SQL
        $this->db->query("ALTER TABLE `stores` MODIFY `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");

        // Add store_id column to products table
        if (!$this->db->field_exists('store_id', 'products')) {
            $fields = array(
                'store_id' => array(
                    'type' => 'INT',
                    'constraint' => 11,
                    'unsigned' => TRUE,
                    'null' => TRUE,
                    'comment' => 'References stores.id',
                    'after' => 'seller_id'
                )
            );
            $this->dbforge->add_column('products', $fields);
        }

        // Migrate existing seller_data to stores (one store per vendor)
        $this->db->query("
            INSERT INTO stores (
                vendor_id, store_name, slug, store_description, logo, store_url,
                category_ids, deliverable_zipcode_type, deliverable_city_type,
                serviceable_zipcodes, serviceable_cities, rating, no_of_ratings,
                commission, low_stock_limit, status, seo_page_title, seo_meta_keywords,
                seo_meta_description, seo_og_image, is_default, date_added
            )
            SELECT
                user_id as vendor_id,
                store_name,
                slug,
                store_description,
                logo,
                store_url,
                category_ids,
                deliverable_zipcode_type,
                deliverable_city_type,
                serviceable_zipcodes,
                serviceable_cities,
                rating,
                no_of_ratings,
                commission,
                low_stock_limit,
                status,
                seo_page_title,
                seo_meta_keywords,
                seo_meta_description,
                seo_og_image,
                1 as is_default,
                date_added
            FROM seller_data
            WHERE user_id IN (SELECT user_id FROM users_groups WHERE group_id = 4)
        ");

        // Update products table to link to stores
        $this->db->query("
            UPDATE products p
            INNER JOIN stores s ON s.vendor_id = p.seller_id AND s.is_default = 1
            SET p.store_id = s.id
            WHERE p.store_id IS NULL
        ");
    }

    public function down()
    {
        // Remove store_id from products
        if ($this->db->field_exists('store_id', 'products')) {
            $this->dbforge->drop_column('products', 'store_id');
        }

        // Drop stores table
        $this->dbforge->drop_table('stores', TRUE);
    }
}

