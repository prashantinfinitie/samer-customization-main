-- =====================================================
-- Multi-Store Feature Migration SQL
-- Migration: 057_add_multi_store_feature
-- Description: Adds multi-store support for vendors
-- =====================================================

-- Step 1: Create stores table
CREATE TABLE IF NOT EXISTS `stores` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vendor_id` int(11) UNSIGNED NOT NULL COMMENT 'References users.id (vendor account)',
  `store_name` varchar(256) DEFAULT NULL,
  `slug` varchar(512) DEFAULT NULL,
  `store_description` varchar(512) DEFAULT NULL,
  `logo` text DEFAULT NULL,
  `store_url` varchar(512) DEFAULT NULL,
  `category_ids` varchar(256) DEFAULT NULL COMMENT 'Comma-separated category IDs',
  `deliverable_zipcode_type` int(11) DEFAULT NULL,
  `deliverable_city_type` int(11) DEFAULT NULL,
  `serviceable_zipcodes` varchar(256) DEFAULT NULL,
  `serviceable_cities` varchar(256) DEFAULT NULL,
  `rating` double(8,2) NOT NULL DEFAULT 0.00,
  `no_of_ratings` int(11) NOT NULL DEFAULT 0,
  `commission` double(10,2) NOT NULL DEFAULT 0.00,
  `low_stock_limit` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(2) NOT NULL DEFAULT 2 COMMENT 'approved: 1 | not-approved: 2 | deactive:0 | removed :7',
  `seo_page_title` text DEFAULT NULL,
  `seo_meta_keywords` text DEFAULT NULL,
  `seo_meta_description` varchar(1024) DEFAULT NULL,
  `seo_og_image` text DEFAULT NULL,
  `is_default` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1 = default store for vendor',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `vendor_id` (`vendor_id`),
  KEY `slug` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Step 2: Add store_id column to products table
ALTER TABLE `products` 
ADD COLUMN `store_id` int(11) UNSIGNED DEFAULT NULL COMMENT 'References stores.id' AFTER `seller_id`;

-- Step 3: Migrate existing seller_data to stores (one store per vendor)
-- This creates a default store for each existing vendor
INSERT INTO `stores` (
    `vendor_id`, 
    `store_name`, 
    `slug`, 
    `store_description`, 
    `logo`, 
    `store_url`,
    `category_ids`, 
    `deliverable_zipcode_type`, 
    `deliverable_city_type`,
    `serviceable_zipcodes`, 
    `serviceable_cities`, 
    `rating`, 
    `no_of_ratings`,
    `commission`, 
    `low_stock_limit`, 
    `status`, 
    `seo_page_title`, 
    `seo_meta_keywords`,
    `seo_meta_description`, 
    `seo_og_image`, 
    `is_default`, 
    `date_added`
)
SELECT 
    `user_id` as `vendor_id`,
    `store_name`,
    `slug`,
    `store_description`,
    `logo`,
    `store_url`,
    `category_ids`,
    `deliverable_zipcode_type`,
    `deliverable_city_type`,
    `serviceable_zipcodes`,
    `serviceable_cities`,
    `rating`,
    `no_of_ratings`,
    `commission`,
    `low_stock_limit`,
    `status`,
    `seo_page_title`,
    `seo_meta_keywords`,
    `seo_meta_description`,
    `seo_og_image`,
    1 as `is_default`,
    `date_added`
FROM `seller_data`
WHERE `user_id` IN (SELECT `user_id` FROM `users_groups` WHERE `group_id` = 4);

-- Step 4: Update products table to link existing products to their default stores
UPDATE `products` p
INNER JOIN `stores` s ON s.`vendor_id` = p.`seller_id` AND s.`is_default` = 1
SET p.`store_id` = s.`id`
WHERE p.`store_id` IS NULL;

-- =====================================================
-- Rollback SQL (if needed to reverse the migration)
-- =====================================================
-- 
-- ALTER TABLE `products` DROP COLUMN `store_id`;
-- DROP TABLE IF EXISTS `stores`;
-- 
-- =====================================================

