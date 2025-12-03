<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Migration: Add Arabic Language Columns
 * 
 * Adds Arabic language support columns for:
 * - Products: name_ar, short_description_ar, description_ar
 * - Categories: name_ar
 * 
 * These columns store Arabic translations which are displayed when
 * the user's language preference is Arabic. If Arabic content is empty,
 * the system falls back to English content.
 */
class Migration_add_arabic_language_columns extends CI_Migration
{

    public function up()
    {
        // =====================================================================
        // PRODUCTS TABLE - Add Arabic columns
        // =====================================================================
        
        // Add name_ar column after name
        if (!$this->db->field_exists('name_ar', 'products')) {
            $this->dbforge->add_column('products', [
                'name_ar' => [
                    'type' => 'VARCHAR',
                    'constraint' => 512,
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'name',
                    'comment' => 'Product name in Arabic'
                ]
            ]);
        }

        // Add short_description_ar column after short_description
        if (!$this->db->field_exists('short_description_ar', 'products')) {
            $this->dbforge->add_column('products', [
                'short_description_ar' => [
                    'type' => 'MEDIUMTEXT',
                    'null' => TRUE,
                    'after' => 'short_description',
                    'comment' => 'Short description in Arabic'
                ]
            ]);
        }

        // Add description_ar column after description
        if (!$this->db->field_exists('description_ar', 'products')) {
            $this->dbforge->add_column('products', [
                'description_ar' => [
                    'type' => 'MEDIUMTEXT',
                    'null' => TRUE,
                    'after' => 'description',
                    'comment' => 'Full description in Arabic'
                ]
            ]);
        }

        // =====================================================================
        // CATEGORIES TABLE - Add Arabic column
        // =====================================================================
        
        // Add name_ar column after name
        if (!$this->db->field_exists('name_ar', 'categories')) {
            $this->dbforge->add_column('categories', [
                'name_ar' => [
                    'type' => 'VARCHAR',
                    'constraint' => 256,
                    'null' => TRUE,
                    'default' => NULL,
                    'after' => 'name',
                    'comment' => 'Category name in Arabic'
                ]
            ]);
        }
    }

    public function down()
    {
        // =====================================================================
        // ROLLBACK - Remove Arabic columns
        // =====================================================================
        
        // Products table
        if ($this->db->field_exists('name_ar', 'products')) {
            $this->dbforge->drop_column('products', 'name_ar');
        }
        if ($this->db->field_exists('short_description_ar', 'products')) {
            $this->dbforge->drop_column('products', 'short_description_ar');
        }
        if ($this->db->field_exists('description_ar', 'products')) {
            $this->dbforge->drop_column('products', 'description_ar');
        }

        // Categories table
        if ($this->db->field_exists('name_ar', 'categories')) {
            $this->dbforge->drop_column('categories', 'name_ar');
        }
    }
}



