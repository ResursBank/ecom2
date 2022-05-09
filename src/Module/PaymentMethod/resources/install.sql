CREATE TABLE IF NOT EXISTS `PREFIX_resursbank_payment_method` (
    `method_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Prim Key',
    `identifier` varchar(255) NOT NULL COMMENT 'Raw Method ID',
    `code` varchar(255) NOT NULL COMMENT 'Method Code',
    `active` tinyint(1) NOT NULL COMMENT 'Active',
    `title` varchar(255) NOT NULL COMMENT 'Method Title',
    `sort_order` smallint(5) unsigned NOT NULL DEFAULT 0 COMMENT 'Method Sort Order',
    `min_order_total` decimal(15,5) unsigned NOT NULL DEFAULT 0.00000 COMMENT 'Minimum Order Total',
    `max_order_total` decimal(15,5) unsigned NOT NULL DEFAULT 0.00000 COMMENT 'Maximum Order Total',
    `order_status` varchar(255) NOT NULL DEFAULT 'pending_payment' COMMENT 'Order Status',
    `raw` text DEFAULT NULL COMMENT 'Raw API Data',
    `created_at` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'Created At',
    `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Updated At',
    `specificcountry` varchar(2) NOT NULL COMMENT 'Which country the method can be used with',
    PRIMARY KEY (`method_id`),
    UNIQUE KEY `RESURSBANK_CHECKOUT_ACCOUNT_METHOD_CODE` (`code`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COMMENT='Resurs Bank Payment Method Table';

CREATE TABLE IF NOT EXISTS `PREFIX_resursbank_order` (
    `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Prim Key',
    `order_id` int(10) unsigned NOT NULL COMMENT 'Order reference.',
    `test` tinyint(1) unsigned NOT NULL COMMENT 'Whether the purchase was conducted in test.',
    PRIMARY KEY (`id`),
    CONSTRAINT `FK_RB_ORDER_ORDER` FOREIGN KEY (`order_id`) REFERENCES `ps_orders` (`id_order`) ON DELETE CASCADE,
    UNIQUE (`order_id`)
) ENGINE=ENGINE_TYPE DEFAULT CHARSET=utf8 COMMENT='Resurs Bank Order Data Table';
