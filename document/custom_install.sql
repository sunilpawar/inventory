ALTER TABLE `civicrm_membership_type` ADD `shippable_to` VARCHAR(255) NOT NULL AFTER `is_active`;
ALTER TABLE `civicrm_membership_type` ADD `signup_fee` decimal(18,9) DEFAULT 0 AFTER `shippable_to`;
ALTER TABLE `civicrm_membership_type` ADD `renewal_fee` decimal(18,9) DEFAULT 0 AFTER `signup_fee`;

ALTER TABLE `civicrm_line_item` ADD `sale_id` int unsigned DEFAULT NULL COMMENT 'sale id from inventory sale';
ALTER TABLE `civicrm_line_item` ADD `product_variant_id` int unsigned DEFAULT NULL COMMENT 'product variant id from inventory product variant table';
