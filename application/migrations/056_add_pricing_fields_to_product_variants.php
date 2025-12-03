<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Migration_add_pricing_fields_to_product_variants extends CI_Migration
{
    public function up()
    {
        // Add cost_price, vendor_price, and seller_price columns to product_variants table
        // Check and add each column individually to avoid duplicate column errors

        // Add cost_price column after price
        if (!$this->db->field_exists('cost_price', 'product_variants')) {
            $fields = array(
                'cost_price' => array(
                    'type' => 'DOUBLE',
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'price'
                )
            );
            $this->dbforge->add_column('product_variants', $fields);
        }

        // Add vendor_price column after cost_price (or after price if cost_price doesn't exist)
        if (!$this->db->field_exists('vendor_price', 'product_variants')) {
            $after_field = $this->db->field_exists('cost_price', 'product_variants') ? 'cost_price' : 'price';
            $fields = array(
                'vendor_price' => array(
                    'type' => 'DOUBLE',
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => $after_field
                )
            );
            $this->dbforge->add_column('product_variants', $fields);
        }

        // Add seller_price column after vendor_price (or after cost_price/price if vendor_price doesn't exist)
        if (!$this->db->field_exists('seller_price', 'product_variants')) {
            $after_field = 'price'; // Default to after price
            if ($this->db->field_exists('vendor_price', 'product_variants')) {
                $after_field = 'vendor_price';
            } elseif ($this->db->field_exists('cost_price', 'product_variants')) {
                $after_field = 'cost_price';
            }

            $fields = array(
                'seller_price' => array(
                    'type' => 'DOUBLE',
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => $after_field
                )
            );
            $this->dbforge->add_column('product_variants', $fields);
        }
    }

    public function down()
    {
        // Remove the columns if migration is rolled back (check if they exist first)
        if ($this->db->field_exists('seller_price', 'product_variants')) {
            $this->dbforge->drop_column('product_variants', 'seller_price');
        }
        if ($this->db->field_exists('vendor_price', 'product_variants')) {
            $this->dbforge->drop_column('product_variants', 'vendor_price');
        }
        if ($this->db->field_exists('cost_price', 'product_variants')) {
            $this->dbforge->drop_column('product_variants', 'cost_price');
        }
    }
}

