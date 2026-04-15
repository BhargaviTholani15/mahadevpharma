-- =============================================================================
-- PharmaFlow — Consolidated MySQL Schema
-- Generated from Laravel migrations in database/migrations
-- Engine: InnoDB / Charset: utf8mb4
-- =============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------------------------------
-- 1. Roles & Permissions (RBAC)
-- -----------------------------------------------------------------------------
CREATE TABLE `roles` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(50)  NOT NULL,
    `slug`        VARCHAR(50)  NOT NULL,
    `description` VARCHAR(255) NULL,
    `is_system`   TINYINT(1)   NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `permissions` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(100) NOT NULL,
    `module`      VARCHAR(50)  NOT NULL,
    `description` VARCHAR(255) NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permissions_slug_unique` (`slug`),
    KEY `permissions_module_index` (`module`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `role_permissions` (
    `role_id`       BIGINT UNSIGNED NOT NULL,
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `created_at`    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`role_id`, `permission_id`),
    CONSTRAINT `fk_rp_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rp_perm` FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 2. Users & Laravel System Tables
-- -----------------------------------------------------------------------------
CREATE TABLE `users` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `role_id`        BIGINT UNSIGNED NOT NULL,
    `full_name`      VARCHAR(150) NOT NULL,
    `email`          VARCHAR(255) NULL,
    `phone`          VARCHAR(15)  NOT NULL,
    `password`       VARCHAR(255) NOT NULL,
    `otp_code`       VARCHAR(6)   NULL,
    `otp_expires_at` TIMESTAMP NULL,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `last_login_at`  TIMESTAMP NULL,
    `deleted_at`     TIMESTAMP NULL,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `users_email_unique` (`email`),
    UNIQUE KEY `users_phone_unique` (`phone`),
    KEY `users_is_active_index` (`is_active`),
    KEY `users_active_deleted_index` (`is_active`, `deleted_at`),
    CONSTRAINT `fk_users_role` FOREIGN KEY (`role_id`) REFERENCES `roles`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `password_reset_tokens` (
    `email`      VARCHAR(255) NOT NULL,
    `token`      VARCHAR(255) NOT NULL,
    `created_at` TIMESTAMP NULL,
    PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `sessions` (
    `id`            VARCHAR(255) NOT NULL,
    `user_id`       BIGINT UNSIGNED NULL,
    `ip_address`    VARCHAR(45) NULL,
    `user_agent`    TEXT NULL,
    `payload`       LONGTEXT NOT NULL,
    `last_activity` INT NOT NULL,
    PRIMARY KEY (`id`),
    KEY `sessions_user_id_index` (`user_id`),
    KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cache` (
    `key`        VARCHAR(255) NOT NULL,
    `value`      MEDIUMTEXT NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `cache_locks` (
    `key`        VARCHAR(255) NOT NULL,
    `owner`      VARCHAR(255) NOT NULL,
    `expiration` INT NOT NULL,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `jobs` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue`        VARCHAR(255) NOT NULL,
    `payload`      LONGTEXT NOT NULL,
    `attempts`     TINYINT UNSIGNED NOT NULL,
    `reserved_at`  INT UNSIGNED NULL,
    `available_at` INT UNSIGNED NOT NULL,
    `created_at`   INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `job_batches` (
    `id`             VARCHAR(255) NOT NULL,
    `name`           VARCHAR(255) NOT NULL,
    `total_jobs`     INT NOT NULL,
    `pending_jobs`   INT NOT NULL,
    `failed_jobs`    INT NOT NULL,
    `failed_job_ids` LONGTEXT NOT NULL,
    `options`        MEDIUMTEXT NULL,
    `cancelled_at`   INT NULL,
    `created_at`     INT NOT NULL,
    `finished_at`    INT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `failed_jobs` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `uuid`       VARCHAR(255) NOT NULL,
    `connection` TEXT NOT NULL,
    `queue`      TEXT NOT NULL,
    `payload`    LONGTEXT NOT NULL,
    `exception`  LONGTEXT NOT NULL,
    `failed_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 3. Clients (pharmacies/retailers)
-- -----------------------------------------------------------------------------
CREATE TABLE `clients` (
    `id`                      BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`                 BIGINT UNSIGNED NOT NULL,
    `business_name`           VARCHAR(255) NOT NULL,
    `proprietor_name`         VARCHAR(150) NULL,
    `business_type`           VARCHAR(50)  NULL,
    `drug_license_no`         VARCHAR(50)  NOT NULL,
    `dl_expiry_date`          VARCHAR(10)  NULL,
    `gst_number`              VARCHAR(15)  NULL,
    `pan_number`              VARCHAR(10)  NULL,
    `fssai_number`            VARCHAR(20)  NULL,
    `state_code`              CHAR(2) NOT NULL,
    `address_line1`           VARCHAR(255) NOT NULL,
    `address_line2`           VARCHAR(255) NULL,
    `city`                    VARCHAR(100) NOT NULL,
    `district`                VARCHAR(100) NULL,
    `state`                   VARCHAR(100) NOT NULL,
    `pincode`                 CHAR(6) NOT NULL,
    `alt_phone`               VARCHAR(15)  NULL,
    `contact_person`          VARCHAR(150) NULL,
    `contact_designation`     VARCHAR(100) NULL,
    `delivery_address`        VARCHAR(500) NULL,
    `delivery_city`           VARCHAR(100) NULL,
    `delivery_pincode`        VARCHAR(6)   NULL,
    `delivery_instructions`   VARCHAR(500) NULL,
    `preferred_delivery_time` VARCHAR(50)  NULL,
    `bank_name`               VARCHAR(100) NULL,
    `bank_account_no`         VARCHAR(30)  NULL,
    `bank_ifsc`               VARCHAR(11)  NULL,
    `bank_branch`             VARCHAR(100) NULL,
    `notes`                   TEXT NULL,
    `credit_limit`            DECIMAL(12,2) NOT NULL DEFAULT 0,
    `current_outstanding`     DECIMAL(12,2) NOT NULL DEFAULT 0,
    `credit_period_days`      SMALLINT UNSIGNED NOT NULL DEFAULT 30,
    `kyc_verified`            TINYINT(1) NOT NULL DEFAULT 0,
    `kyc_verified_at`         TIMESTAMP NULL,
    `kyc_verified_by`         BIGINT UNSIGNED NULL,
    `is_active`               TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at`              TIMESTAMP NULL,
    `created_at`              TIMESTAMP NULL,
    `updated_at`              TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `clients_user_id_unique` (`user_id`),
    UNIQUE KEY `clients_drug_license_no_unique` (`drug_license_no`),
    KEY `clients_gst_number_index` (`gst_number`),
    KEY `clients_state_code_index` (`state_code`),
    KEY `clients_active_deleted_index` (`is_active`, `deleted_at`),
    KEY `clients_current_outstanding_index` (`current_outstanding`),
    CONSTRAINT `fk_clients_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_clients_kyc_by` FOREIGN KEY (`kyc_verified_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 4. Catalog (categories, brands, HSN, products)
-- -----------------------------------------------------------------------------
CREATE TABLE `categories` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(100) NOT NULL,
    `parent_id`  BIGINT UNSIGNED NULL,
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    `is_active`  TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `categories_slug_unique` (`slug`),
    CONSTRAINT `fk_categories_parent` FOREIGN KEY (`parent_id`) REFERENCES `categories`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `brands` (
    `id`           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`         VARCHAR(150) NOT NULL,
    `slug`         VARCHAR(150) NOT NULL,
    `manufacturer` VARCHAR(255) NULL,
    `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`   TIMESTAMP NULL,
    `updated_at`   TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `brands_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `hsn_codes` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `code`           VARCHAR(10) NOT NULL,
    `description`    VARCHAR(255) NOT NULL,
    `cgst_rate`      DECIMAL(5,2) NOT NULL,
    `sgst_rate`      DECIMAL(5,2) NOT NULL,
    `igst_rate`      DECIMAL(5,2) NOT NULL,
    `effective_from` DATE NOT NULL,
    `effective_to`   DATE NULL,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `hsn_codes_code_from_unique` (`code`, `effective_from`),
    KEY `hsn_codes_effective_to_index` (`effective_to`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `products` (
    `id`                        BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`                      VARCHAR(255) NOT NULL,
    `generic_name`              VARCHAR(255) NULL,
    `composition`               TEXT NULL,
    `brand_id`                  BIGINT UNSIGNED NULL,
    `category_id`               BIGINT UNSIGNED NULL,
    `hsn_code_id`               BIGINT UNSIGNED NOT NULL,
    `dosage_form`               VARCHAR(50) NULL,
    `route_of_administration`   VARCHAR(50) NULL,
    `strength`                  VARCHAR(50) NULL,
    `pack_size`                 VARCHAR(50) NULL,
    `sku`                       VARCHAR(50) NOT NULL,
    `is_prescription_only`      TINYINT(1) NOT NULL DEFAULT 0,
    `schedule_type`             VARCHAR(20) NULL,
    `storage_conditions`        VARCHAR(100) NULL,
    `shelf_life_months`         INT NULL,
    `is_controlled`             TINYINT(1) NOT NULL DEFAULT 0,
    `is_returnable`             TINYINT(1) NOT NULL DEFAULT 1,
    `manufacturer_name`         VARCHAR(255) NULL,
    `manufacturer_address`      TEXT NULL,
    `country_of_origin`         VARCHAR(100) NOT NULL DEFAULT 'India',
    `marketing_authorization`   VARCHAR(100) NULL,
    `mrp`                       DECIMAL(12,2) NULL,
    `purchase_price`            DECIMAL(12,2) NULL,
    `selling_price`             DECIMAL(12,2) NULL,
    `ptr`                       DECIMAL(12,2) NULL,
    `pts`                       DECIMAL(12,2) NULL,
    `margin_pct`                DECIMAL(5,2) NULL,
    `min_stock_level`           INT NOT NULL DEFAULT 0,
    `reorder_level`             INT NOT NULL DEFAULT 0,
    `reorder_quantity`          INT NOT NULL DEFAULT 0,
    `lead_time_days`            INT NULL,
    `rack_location`             VARCHAR(50) NULL,
    `description`               TEXT NULL,
    `usage_instructions`        TEXT NULL,
    `side_effects`              TEXT NULL,
    `image_url`                 VARCHAR(500) NULL,
    `barcode`                   VARCHAR(50) NULL,
    `tags`                      JSON NULL,
    `batch_prefix`              VARCHAR(20) NULL,
    `near_expiry_alert_days`    INT NOT NULL DEFAULT 90,
    `is_active`                 TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at`                TIMESTAMP NULL,
    `created_at`                TIMESTAMP NULL,
    `updated_at`                TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `products_sku_unique` (`sku`),
    UNIQUE KEY `products_barcode_unique` (`barcode`),
    KEY `products_name_index` (`name`),
    KEY `products_generic_name_index` (`generic_name`),
    KEY `products_active_deleted_index` (`is_active`, `deleted_at`),
    CONSTRAINT `fk_products_brand` FOREIGN KEY (`brand_id`) REFERENCES `brands`(`id`),
    CONSTRAINT `fk_products_category` FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`),
    CONSTRAINT `fk_products_hsn` FOREIGN KEY (`hsn_code_id`) REFERENCES `hsn_codes`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 5. Inventory (warehouses, batches, stock, movements)
-- -----------------------------------------------------------------------------
CREATE TABLE `warehouses` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`          VARCHAR(150) NOT NULL,
    `code`          VARCHAR(20) NOT NULL,
    `state_code`    CHAR(2) NOT NULL,
    `address_line1` VARCHAR(255) NOT NULL,
    `city`          VARCHAR(100) NOT NULL,
    `state`         VARCHAR(100) NOT NULL,
    `pincode`       CHAR(6) NOT NULL,
    `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `warehouses_code_unique` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `batches` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id`     BIGINT UNSIGNED NOT NULL,
    `batch_number`   VARCHAR(50) NOT NULL,
    `mfg_date`       DATE NULL,
    `expiry_date`    DATE NOT NULL,
    `mrp`            DECIMAL(10,2) NOT NULL,
    `purchase_price` DECIMAL(10,2) NOT NULL,
    `selling_price`  DECIMAL(10,2) NOT NULL,
    `is_active`      TINYINT(1) NOT NULL DEFAULT 1,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `batches_product_batch_unique` (`product_id`, `batch_number`),
    KEY `batches_expiry_date_index` (`expiry_date`),
    KEY `batches_is_active_index` (`is_active`),
    CONSTRAINT `fk_batches_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `warehouse_stocks` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `warehouse_id`  BIGINT UNSIGNED NOT NULL,
    `batch_id`      BIGINT UNSIGNED NOT NULL,
    `quantity`      INT NOT NULL DEFAULT 0,
    `reserved_qty`  INT NOT NULL DEFAULT 0,
    `reorder_level` INT NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `warehouse_stocks_wh_batch_unique` (`warehouse_id`, `batch_id`),
    KEY `warehouse_stocks_quantity_index` (`quantity`),
    CONSTRAINT `fk_ws_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    CONSTRAINT `fk_ws_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stock_movements` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `warehouse_id`    BIGINT UNSIGNED NOT NULL,
    `batch_id`        BIGINT UNSIGNED NOT NULL,
    `movement_type`   ENUM('PURCHASE_IN','SALE_OUT','RETURN_IN','RETURN_OUT',
                          'TRANSFER_IN','TRANSFER_OUT','ADJUSTMENT','DAMAGED','EXPIRED') NOT NULL,
    `quantity_change` INT NOT NULL,
    `quantity_before` INT NOT NULL,
    `quantity_after`  INT NOT NULL,
    `reference_type`  VARCHAR(50) NULL,
    `reference_id`    BIGINT UNSIGNED NULL,
    `reason`          VARCHAR(255) NULL,
    `performed_by`    BIGINT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `sm_movement_type_index` (`movement_type`),
    KEY `sm_wh_batch_index` (`warehouse_id`, `batch_id`),
    KEY `sm_reference_index` (`reference_type`, `reference_id`),
    KEY `sm_created_at_index` (`created_at`),
    CONSTRAINT `fk_sm_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    CONSTRAINT `fk_sm_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`),
    CONSTRAINT `fk_sm_user` FOREIGN KEY (`performed_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 6. Orders
-- -----------------------------------------------------------------------------
CREATE TABLE `orders` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_number`        VARCHAR(30) NOT NULL,
    `client_id`           BIGINT UNSIGNED NOT NULL,
    `warehouse_id`        BIGINT UNSIGNED NOT NULL,
    `status`              ENUM('DRAFT','PENDING','APPROVED','PACKED',
                               'OUT_FOR_DELIVERY','DELIVERED','CANCELLED','RETURNED')
                          NOT NULL DEFAULT 'PENDING',
    `subtotal`            DECIMAL(12,2) NOT NULL DEFAULT 0,
    `discount_amount`     DECIMAL(12,2) NOT NULL DEFAULT 0,
    `taxable_amount`      DECIMAL(12,2) NOT NULL DEFAULT 0,
    `cgst_total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `sgst_total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `igst_total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total_amount`        DECIMAL(12,2) NOT NULL DEFAULT 0,
    `is_credit_order`     TINYINT(1) NOT NULL DEFAULT 1,
    `notes`               TEXT NULL,
    `approved_by`         BIGINT UNSIGNED NULL,
    `approved_at`         TIMESTAMP NULL,
    `packed_by`           BIGINT UNSIGNED NULL,
    `packed_at`           TIMESTAMP NULL,
    `cancelled_by`        BIGINT UNSIGNED NULL,
    `cancelled_at`        TIMESTAMP NULL,
    `cancellation_reason` VARCHAR(255) NULL,
    `created_by`          BIGINT UNSIGNED NOT NULL,
    `deleted_at`          TIMESTAMP NULL,
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `orders_order_number_unique` (`order_number`),
    KEY `orders_status_index` (`status`),
    KEY `orders_client_status_index` (`client_id`, `status`),
    KEY `orders_client_created_index` (`client_id`, `created_at`),
    CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    CONSTRAINT `fk_orders_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    CONSTRAINT `fk_orders_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_orders_packed_by` FOREIGN KEY (`packed_by`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_orders_cancelled_by` FOREIGN KEY (`cancelled_by`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_orders_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `order_items` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`        BIGINT UNSIGNED NOT NULL,
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `batch_id`        BIGINT UNSIGNED NOT NULL,
    `quantity`        INT NOT NULL,
    `unit_price`      DECIMAL(10,2) NOT NULL,
    `mrp`             DECIMAL(10,2) NOT NULL,
    `discount_pct`    DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `taxable_amount`  DECIMAL(12,2) NOT NULL,
    `cgst_rate`       DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `cgst_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
    `sgst_rate`       DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `sgst_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
    `igst_rate`       DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `igst_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
    `line_total`      DECIMAL(12,2) NOT NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `order_items_order_id_index` (`order_id`),
    KEY `order_items_product_id_index` (`product_id`),
    CONSTRAINT `fk_oi_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_oi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    CONSTRAINT `fk_oi_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 7. Invoices (GST)
-- -----------------------------------------------------------------------------
CREATE TABLE `invoices` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_number`      VARCHAR(30) NOT NULL,
    `order_id`            BIGINT UNSIGNED NOT NULL,
    `client_id`           BIGINT UNSIGNED NOT NULL,
    `warehouse_id`        BIGINT UNSIGNED NOT NULL,
    `invoice_date`        DATE NOT NULL,
    `due_date`            DATE NOT NULL,
    `subtotal`            DECIMAL(12,2) NOT NULL,
    `discount_total`      DECIMAL(12,2) NOT NULL DEFAULT 0,
    `taxable_total`       DECIMAL(12,2) NOT NULL,
    `cgst_total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `sgst_total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `igst_total`          DECIMAL(12,2) NOT NULL DEFAULT 0,
    `round_off`           DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `grand_total`         DECIMAL(12,2) NOT NULL,
    `amount_paid`         DECIMAL(12,2) NOT NULL DEFAULT 0,
    `balance_due`         DECIMAL(12,2) NOT NULL,
    `status`              ENUM('DRAFT','ISSUED','PARTIALLY_PAID','PAID','CANCELLED','CREDIT_NOTE')
                          NOT NULL DEFAULT 'ISSUED',
    `is_inter_state`      TINYINT(1) NOT NULL,
    `supply_state_code`   CHAR(2) NOT NULL,
    `billing_state_code`  CHAR(2) NOT NULL,
    `cancelled_at`        TIMESTAMP NULL,
    `cancellation_reason` VARCHAR(255) NULL,
    `created_at`          TIMESTAMP NULL,
    `updated_at`          TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `invoices_invoice_number_unique` (`invoice_number`),
    KEY `invoices_due_date_index` (`due_date`),
    KEY `invoices_status_index` (`status`),
    KEY `invoices_client_due_index` (`client_id`, `due_date`),
    KEY `invoices_invoice_date_index` (`invoice_date`),
    CONSTRAINT `fk_inv_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
    CONSTRAINT `fk_inv_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    CONSTRAINT `fk_inv_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `invoice_items` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `invoice_id`      BIGINT UNSIGNED NOT NULL,
    `order_item_id`   BIGINT UNSIGNED NULL,
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `batch_id`        BIGINT UNSIGNED NOT NULL,
    `hsn_code`        VARCHAR(10) NOT NULL,
    `quantity`        INT NOT NULL,
    `unit_price`      DECIMAL(10,2) NOT NULL,
    `mrp`             DECIMAL(10,2) NOT NULL,
    `discount_pct`    DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0,
    `taxable_amount`  DECIMAL(12,2) NOT NULL,
    `cgst_rate`       DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `cgst_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
    `sgst_rate`       DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `sgst_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
    `igst_rate`       DECIMAL(5,2)  NOT NULL DEFAULT 0,
    `igst_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0,
    `line_total`      DECIMAL(12,2) NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `ii_hsn_code_index` (`hsn_code`),
    KEY `ii_invoice_id_index` (`invoice_id`),
    KEY `ii_product_id_index` (`product_id`),
    CONSTRAINT `fk_ii_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_ii_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`),
    CONSTRAINT `fk_ii_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    CONSTRAINT `fk_ii_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 8. Payments
-- -----------------------------------------------------------------------------
CREATE TABLE `payments` (
    `id`               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_number`   VARCHAR(30) NOT NULL,
    `client_id`        BIGINT UNSIGNED NOT NULL,
    `amount`           DECIMAL(12,2) NOT NULL,
    `payment_method`   ENUM('CASH','UPI','BANK_TRANSFER','CHEQUE','PAYMENT_GATEWAY','CREDIT_NOTE') NOT NULL,
    `payment_date`     DATE NOT NULL,
    `reference_number` VARCHAR(100) NULL,
    `gateway_txn_id`   VARCHAR(255) NULL,
    `status`           ENUM('PENDING','CONFIRMED','BOUNCED','REVERSED','CANCELLED')
                       NOT NULL DEFAULT 'CONFIRMED',
    `notes`            TEXT NULL,
    `received_by`      BIGINT UNSIGNED NULL,
    `confirmed_by`     BIGINT UNSIGNED NULL,
    `confirmed_at`     TIMESTAMP NULL,
    `created_at`       TIMESTAMP NULL,
    `updated_at`       TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `payments_payment_number_unique` (`payment_number`),
    KEY `payments_status_index` (`status`),
    KEY `payments_client_date_index` (`client_id`, `payment_date`),
    KEY `payments_method_index` (`payment_method`),
    CONSTRAINT `fk_pay_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    CONSTRAINT `fk_pay_received_by` FOREIGN KEY (`received_by`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_pay_confirmed_by` FOREIGN KEY (`confirmed_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `payment_allocations` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `payment_id` BIGINT UNSIGNED NOT NULL,
    `invoice_id` BIGINT UNSIGNED NOT NULL,
    `amount`     DECIMAL(12,2) NOT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `pa_payment_id_index` (`payment_id`),
    KEY `pa_invoice_id_index` (`invoice_id`),
    CONSTRAINT `fk_pa_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_pa_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 9. Ledger
-- -----------------------------------------------------------------------------
CREATE TABLE `ledger_entries` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `client_id`       BIGINT UNSIGNED NOT NULL,
    `entry_date`      DATE NOT NULL,
    `entry_type`      ENUM('INVOICE','PAYMENT','CREDIT_NOTE','DEBIT_NOTE','OPENING_BALANCE','ADJUSTMENT') NOT NULL,
    `debit_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0,
    `credit_amount`   DECIMAL(12,2) NOT NULL DEFAULT 0,
    `running_balance` DECIMAL(12,2) NOT NULL,
    `reference_type`  VARCHAR(50) NOT NULL,
    `reference_id`    BIGINT UNSIGNED NOT NULL,
    `narration`       VARCHAR(500) NULL,
    `financial_year`  CHAR(7) NOT NULL,
    `created_by`      BIGINT UNSIGNED NOT NULL,
    `created_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `le_entry_type_index` (`entry_type`),
    KEY `le_client_date_index` (`client_id`, `entry_date`),
    KEY `le_reference_index` (`reference_type`, `reference_id`),
    KEY `le_client_fy_index` (`client_id`, `financial_year`),
    CONSTRAINT `fk_le_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    CONSTRAINT `fk_le_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 10. Delivery Agents & Assignments
-- -----------------------------------------------------------------------------
CREATE TABLE `delivery_agents` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`              VARCHAR(150) NOT NULL,
    `phone`             VARCHAR(15) NOT NULL,
    `alt_phone`         VARCHAR(15) NULL,
    `email`             VARCHAR(255) NULL,
    `vehicle_type`      VARCHAR(50) NULL,
    `vehicle_number`    VARCHAR(20) NULL,
    `license_number`    VARCHAR(30) NULL,
    `license_expiry`    VARCHAR(10) NULL,
    `zone`              VARCHAR(100) NULL,
    `address`           VARCHAR(500) NULL,
    `emergency_contact` VARCHAR(15) NULL,
    `id_proof_type`     VARCHAR(50) NULL,
    `id_proof_number`   VARCHAR(50) NULL,
    `joining_date`      DATE NULL,
    `is_available`      TINYINT(1) NOT NULL DEFAULT 1,
    `is_active`         TINYINT(1) NOT NULL DEFAULT 1,
    `notes`             TEXT NULL,
    `deleted_at`        TIMESTAMP NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `delivery_agents_phone_unique` (`phone`),
    KEY `da_active_available_index` (`is_active`, `is_available`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `delivery_assignments` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `order_id`          BIGINT UNSIGNED NOT NULL,
    `assigned_to`       BIGINT UNSIGNED NOT NULL,
    `delivery_agent_id` BIGINT UNSIGNED NULL,
    `assigned_by`       BIGINT UNSIGNED NOT NULL,
    `status`            ENUM('ASSIGNED','PICKED_UP','IN_TRANSIT','DELIVERED',
                             'FAILED','RETURNED','REASSIGNED') NOT NULL DEFAULT 'ASSIGNED',
    `delivery_otp`      VARCHAR(6) NULL,
    `otp_expires_at`    TIMESTAMP NULL,
    `otp_verified_at`   TIMESTAMP NULL,
    `scheduled_date`    DATE NULL,
    `delivered_at`      TIMESTAMP NULL,
    `delivery_lat`      DECIMAL(10,7) NULL,
    `delivery_lng`      DECIMAL(10,7) NULL,
    `failure_reason`    VARCHAR(255) NULL,
    `notes`             TEXT NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `da_status_index` (`status`),
    KEY `da_scheduled_date_index` (`scheduled_date`),
    KEY `da_assigned_to_status_index` (`assigned_to`, `status`),
    CONSTRAINT `fk_dasg_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
    CONSTRAINT `fk_dasg_assigned_to` FOREIGN KEY (`assigned_to`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_dasg_agent` FOREIGN KEY (`delivery_agent_id`) REFERENCES `delivery_agents`(`id`),
    CONSTRAINT `fk_dasg_assigned_by` FOREIGN KEY (`assigned_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 11. Notifications, Activity Logs, Company Settings
-- -----------------------------------------------------------------------------
CREATE TABLE `notifications` (
    `id`             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`        BIGINT UNSIGNED NOT NULL,
    `channel`        ENUM('SMS','EMAIL','PUSH','IN_APP','WHATSAPP') NOT NULL,
    `event_type`     VARCHAR(50) NOT NULL,
    `title`          VARCHAR(255) NOT NULL,
    `body`           TEXT NOT NULL,
    `reference_type` VARCHAR(50) NULL,
    `reference_id`   BIGINT UNSIGNED NULL,
    `status`         ENUM('QUEUED','SENT','DELIVERED','FAILED','READ') NOT NULL DEFAULT 'QUEUED',
    `sent_at`        TIMESTAMP NULL,
    `read_at`        TIMESTAMP NULL,
    `failure_reason` VARCHAR(255) NULL,
    `retry_count`    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `created_at`     TIMESTAMP NULL,
    `updated_at`     TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `notif_event_type_index` (`event_type`),
    KEY `notif_status_index` (`status`),
    KEY `notif_user_status_index` (`user_id`, `status`),
    CONSTRAINT `fk_notif_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `activity_logs` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id`     BIGINT UNSIGNED NULL,
    `action`      VARCHAR(100) NOT NULL,
    `entity_type` VARCHAR(50) NOT NULL,
    `entity_id`   BIGINT UNSIGNED NOT NULL,
    `old_values`  JSON NULL,
    `new_values`  JSON NULL,
    `ip_address`  VARCHAR(45) NULL,
    `user_agent`  VARCHAR(500) NULL,
    `created_at`  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `al_action_index` (`action`),
    KEY `al_created_at_index` (`created_at`),
    KEY `al_entity_index` (`entity_type`, `entity_id`),
    CONSTRAINT `fk_al_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `company_settings` (
    `id`                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `company_name`        VARCHAR(255) NOT NULL,
    `gst_number`          VARCHAR(15) NOT NULL,
    `drug_license_no`     VARCHAR(50) NOT NULL,
    `state_code`          CHAR(2) NOT NULL,
    `address_line1`       VARCHAR(255) NOT NULL,
    `city`                VARCHAR(100) NOT NULL,
    `state`               VARCHAR(100) NOT NULL,
    `pincode`             CHAR(6) NOT NULL,
    `phone`               VARCHAR(15) NOT NULL,
    `email`               VARCHAR(255) NULL,
    `logo_path`           VARCHAR(255) NULL,
    `invoice_prefix`      VARCHAR(10) NOT NULL DEFAULT 'INV',
    `current_invoice_seq` BIGINT UNSIGNED NOT NULL DEFAULT 0,
    `financial_year`      CHAR(7) NOT NULL,
    `updated_at`          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 12. Suppliers & Purchase Orders
-- -----------------------------------------------------------------------------
CREATE TABLE `suppliers` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`               VARCHAR(255) NOT NULL,
    `contact_person`     VARCHAR(150) NULL,
    `email`              VARCHAR(255) NULL,
    `phone`              VARCHAR(15) NOT NULL,
    `gst_number`         VARCHAR(15) NULL,
    `drug_license_no`    VARCHAR(50) NULL,
    `address_line1`      VARCHAR(255) NOT NULL,
    `city`               VARCHAR(100) NOT NULL,
    `state`              VARCHAR(100) NOT NULL,
    `state_code`         VARCHAR(2) NOT NULL,
    `pincode`            VARCHAR(6) NOT NULL,
    `payment_terms_days` INT NOT NULL DEFAULT 30,
    `is_active`          TINYINT(1) NOT NULL DEFAULT 1,
    `deleted_at`         TIMESTAMP NULL,
    `created_at`         TIMESTAMP NULL,
    `updated_at`         TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `suppliers_is_active_index` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `purchase_orders` (
    `id`                     BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `po_number`              VARCHAR(50) NOT NULL,
    `supplier_id`            BIGINT UNSIGNED NOT NULL,
    `warehouse_id`           BIGINT UNSIGNED NOT NULL,
    `status`                 ENUM('DRAFT','SENT','PARTIALLY_RECEIVED','RECEIVED','CANCELLED')
                             NOT NULL DEFAULT 'DRAFT',
    `subtotal`               DECIMAL(14,2) NOT NULL DEFAULT 0,
    `tax_amount`             DECIMAL(12,2) NOT NULL DEFAULT 0,
    `total_amount`           DECIMAL(14,2) NOT NULL DEFAULT 0,
    `expected_delivery_date` DATE NULL,
    `notes`                  TEXT NULL,
    `created_by`             BIGINT UNSIGNED NOT NULL,
    `approved_by`            BIGINT UNSIGNED NULL,
    `approved_at`            TIMESTAMP NULL,
    `deleted_at`             TIMESTAMP NULL,
    `created_at`             TIMESTAMP NULL,
    `updated_at`             TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `po_po_number_unique` (`po_number`),
    KEY `po_status_index` (`status`),
    KEY `po_supplier_status_index` (`supplier_id`, `status`),
    CONSTRAINT `fk_po_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers`(`id`),
    CONSTRAINT `fk_po_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    CONSTRAINT `fk_po_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_po_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `purchase_order_items` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `purchase_order_id`  BIGINT UNSIGNED NOT NULL,
    `product_id`         BIGINT UNSIGNED NOT NULL,
    `batch_number`       VARCHAR(50) NULL,
    `quantity_ordered`   INT NOT NULL,
    `quantity_received`  INT NOT NULL DEFAULT 0,
    `unit_price`         DECIMAL(12,2) NOT NULL,
    `tax_amount`         DECIMAL(10,2) NOT NULL DEFAULT 0,
    `line_total`         DECIMAL(12,2) NOT NULL,
    `mfg_date`           DATE NULL,
    `expiry_date`        DATE NULL,
    `mrp`                DECIMAL(10,2) NULL,
    `created_at`         TIMESTAMP NULL,
    `updated_at`         TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `poi_po_id_index` (`purchase_order_id`),
    KEY `poi_product_id_index` (`product_id`),
    CONSTRAINT `fk_poi_po` FOREIGN KEY (`purchase_order_id`) REFERENCES `purchase_orders`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_poi_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 13. Sales Returns
-- -----------------------------------------------------------------------------
CREATE TABLE `sales_returns` (
    `id`            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `return_number` VARCHAR(50) NOT NULL,
    `order_id`      BIGINT UNSIGNED NOT NULL,
    `invoice_id`    BIGINT UNSIGNED NULL,
    `client_id`     BIGINT UNSIGNED NOT NULL,
    `warehouse_id`  BIGINT UNSIGNED NOT NULL,
    `status`        ENUM('REQUESTED','APPROVED','RECEIVED','REJECTED','CREDIT_ISSUED')
                    NOT NULL DEFAULT 'REQUESTED',
    `reason`        ENUM('DAMAGED','EXPIRED','WRONG_PRODUCT','QUALITY_ISSUE','EXCESS_QUANTITY','OTHER') NOT NULL,
    `total_amount`  DECIMAL(14,2) NOT NULL DEFAULT 0,
    `notes`         TEXT NULL,
    `approved_by`   BIGINT UNSIGNED NULL,
    `approved_at`   TIMESTAMP NULL,
    `received_at`   TIMESTAMP NULL,
    `created_by`    BIGINT UNSIGNED NOT NULL,
    `deleted_at`    TIMESTAMP NULL,
    `created_at`    TIMESTAMP NULL,
    `updated_at`    TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `sr_return_number_unique` (`return_number`),
    KEY `sr_status_index` (`status`),
    KEY `sr_client_status_index` (`client_id`, `status`),
    CONSTRAINT `fk_sr_order` FOREIGN KEY (`order_id`) REFERENCES `orders`(`id`),
    CONSTRAINT `fk_sr_invoice` FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`),
    CONSTRAINT `fk_sr_client` FOREIGN KEY (`client_id`) REFERENCES `clients`(`id`),
    CONSTRAINT `fk_sr_warehouse` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses`(`id`),
    CONSTRAINT `fk_sr_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_sr_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `sales_return_items` (
    `id`              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `sales_return_id` BIGINT UNSIGNED NOT NULL,
    `order_item_id`   BIGINT UNSIGNED NULL,
    `product_id`      BIGINT UNSIGNED NOT NULL,
    `batch_id`        BIGINT UNSIGNED NOT NULL,
    `quantity`        INT NOT NULL,
    `unit_price`      DECIMAL(12,2) NOT NULL,
    `line_total`      DECIMAL(12,2) NOT NULL,
    `condition`       ENUM('GOOD','DAMAGED','EXPIRED') NOT NULL,
    `created_at`      TIMESTAMP NULL,
    `updated_at`      TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `sri_sr_id_index` (`sales_return_id`),
    KEY `sri_product_id_index` (`product_id`),
    CONSTRAINT `fk_sri_sr` FOREIGN KEY (`sales_return_id`) REFERENCES `sales_returns`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sri_order_item` FOREIGN KEY (`order_item_id`) REFERENCES `order_items`(`id`),
    CONSTRAINT `fk_sri_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    CONSTRAINT `fk_sri_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 14. Stock Transfers
-- -----------------------------------------------------------------------------
CREATE TABLE `stock_transfers` (
    `id`                 BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `transfer_number`    VARCHAR(50) NOT NULL,
    `from_warehouse_id`  BIGINT UNSIGNED NOT NULL,
    `to_warehouse_id`    BIGINT UNSIGNED NOT NULL,
    `status`             ENUM('DRAFT','APPROVED','IN_TRANSIT','RECEIVED','CANCELLED')
                         NOT NULL DEFAULT 'DRAFT',
    `notes`              TEXT NULL,
    `created_by`         BIGINT UNSIGNED NOT NULL,
    `approved_by`        BIGINT UNSIGNED NULL,
    `approved_at`        TIMESTAMP NULL,
    `shipped_at`         TIMESTAMP NULL,
    `received_at`        TIMESTAMP NULL,
    `deleted_at`         TIMESTAMP NULL,
    `created_at`         TIMESTAMP NULL,
    `updated_at`         TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `st_transfer_number_unique` (`transfer_number`),
    KEY `st_status_index` (`status`),
    CONSTRAINT `fk_st_from_wh` FOREIGN KEY (`from_warehouse_id`) REFERENCES `warehouses`(`id`),
    CONSTRAINT `fk_st_to_wh` FOREIGN KEY (`to_warehouse_id`) REFERENCES `warehouses`(`id`),
    CONSTRAINT `fk_st_created_by` FOREIGN KEY (`created_by`) REFERENCES `users`(`id`),
    CONSTRAINT `fk_st_approved_by` FOREIGN KEY (`approved_by`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `stock_transfer_items` (
    `id`                BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `stock_transfer_id` BIGINT UNSIGNED NOT NULL,
    `product_id`        BIGINT UNSIGNED NOT NULL,
    `batch_id`          BIGINT UNSIGNED NOT NULL,
    `quantity`          INT NOT NULL,
    `created_at`        TIMESTAMP NULL,
    `updated_at`        TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    KEY `sti_st_id_index` (`stock_transfer_id`),
    KEY `sti_product_id_index` (`product_id`),
    CONSTRAINT `fk_sti_st` FOREIGN KEY (`stock_transfer_id`) REFERENCES `stock_transfers`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_sti_product` FOREIGN KEY (`product_id`) REFERENCES `products`(`id`),
    CONSTRAINT `fk_sti_batch` FOREIGN KEY (`batch_id`) REFERENCES `batches`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- -----------------------------------------------------------------------------
-- 15. Lookup Tables
-- -----------------------------------------------------------------------------
CREATE TABLE `dosage_forms` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(100) NOT NULL,
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `dosage_forms_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `strengths` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(100) NOT NULL,
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `strengths_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `pack_sizes` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(100) NOT NULL,
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `pack_sizes_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `drug_schedules` (
    `id`          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`        VARCHAR(100) NOT NULL,
    `slug`        VARCHAR(100) NOT NULL,
    `description` VARCHAR(255) NULL,
    `sort_order`  SMALLINT NOT NULL DEFAULT 0,
    `created_at`  TIMESTAMP NULL,
    `updated_at`  TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `drug_schedules_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE `storage_conditions` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(100) NOT NULL,
    `sort_order` SMALLINT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP NULL,
    `updated_at` TIMESTAMP NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `storage_conditions_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- =============================================================================
-- SEED DATA (from RolePermissionSeeder + DemoDataSeeder)
-- =============================================================================

-- ─── Roles ───────────────────────────────────────────────────────────────────
INSERT INTO `roles` (`id`, `name`, `slug`, `description`, `is_system`, `created_at`, `updated_at`) VALUES
(1, 'Admin',  'admin',  'Full system access',  1, NOW(), NOW()),
(2, 'Staff',  'staff',  'Operational staff',   1, NOW(), NOW()),
(3, 'Client', 'client', 'Medical shop client', 1, NOW(), NOW());

-- ─── Permissions ─────────────────────────────────────────────────────────────
INSERT INTO `permissions` (`id`, `name`, `slug`, `module`) VALUES
(1,  'Dashboard View',   'dashboard.view',   'dashboard'),
(2,  'Products View',    'products.view',    'products'),
(3,  'Products Create',  'products.create',  'products'),
(4,  'Products Edit',    'products.edit',    'products'),
(5,  'Products Delete',  'products.delete',  'products'),
(6,  'Products Import',  'products.import',  'products'),
(7,  'Inventory View',   'inventory.view',   'inventory'),
(8,  'Inventory Adjust', 'inventory.adjust', 'inventory'),
(9,  'Inventory Transfer','inventory.transfer','inventory'),
(10, 'Orders View',      'orders.view',      'orders'),
(11, 'Orders Create',    'orders.create',    'orders'),
(12, 'Orders Approve',   'orders.approve',   'orders'),
(13, 'Orders Cancel',    'orders.cancel',    'orders'),
(14, 'Orders Modify',    'orders.modify',    'orders'),
(15, 'Invoices View',    'invoices.view',    'invoices'),
(16, 'Invoices Create',  'invoices.create',  'invoices'),
(17, 'Invoices Cancel',  'invoices.cancel',  'invoices'),
(18, 'Payments View',    'payments.view',    'payments'),
(19, 'Payments Create',  'payments.create',  'payments'),
(20, 'Payments Confirm', 'payments.confirm', 'payments'),
(21, 'Clients View',     'clients.view',     'clients'),
(22, 'Clients Create',   'clients.create',   'clients'),
(23, 'Clients Edit',     'clients.edit',     'clients'),
(24, 'Clients Kyc',      'clients.kyc',      'clients'),
(25, 'Delivery View',    'delivery.view',    'delivery'),
(26, 'Delivery Assign',  'delivery.assign',  'delivery'),
(27, 'Delivery Update',  'delivery.update',  'delivery'),
(28, 'Reports View',     'reports.view',     'reports'),
(29, 'Reports Export',   'reports.export',   'reports'),
(30, 'Settings View',    'settings.view',    'settings'),
(31, 'Settings Edit',    'settings.edit',    'settings');

-- ─── Role → Permission mapping ───────────────────────────────────────────────
-- Admin: all 31 permissions
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(1,1),(1,2),(1,3),(1,4),(1,5),(1,6),(1,7),(1,8),(1,9),(1,10),
(1,11),(1,12),(1,13),(1,14),(1,15),(1,16),(1,17),(1,18),(1,19),(1,20),
(1,21),(1,22),(1,23),(1,24),(1,25),(1,26),(1,27),(1,28),(1,29),(1,30),(1,31);

-- Staff: dashboard, products.view, inventory.view, orders (view/approve/modify),
--        invoices (view/create), payments (view/create), clients.view,
--        delivery (view/assign/update), reports.view
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(2,1),(2,2),(2,7),(2,10),(2,12),(2,14),(2,15),(2,16),(2,18),(2,19),
(2,21),(2,25),(2,26),(2,27),(2,28);

-- Client: dashboard, products.view, orders (view/create), invoices.view, payments.view
INSERT INTO `role_permissions` (`role_id`, `permission_id`) VALUES
(3,1),(3,2),(3,10),(3,11),(3,15),(3,18);

-- ─── Company Settings ───────────────────────────────────────────────────────
INSERT INTO `company_settings`
(`id`, `company_name`, `gst_number`, `drug_license_no`, `state_code`,
 `address_line1`, `city`, `state`, `pincode`, `phone`, `email`,
 `invoice_prefix`, `current_invoice_seq`, `financial_year`)
VALUES
(1, 'Mahadev Pharma', '36ABBPT6277A1ZN', '345/HD/AP/2002, 346/HD/AP/2002', '36',
 '2-3-166 & 17, 1st Floor, Taj Plaza, Nallagopalpet Main Road',
 'Secunderabad', 'Telangana', '500003', '8919383362', 'info@mahadevpharma.in',
 'INV', 0, '2025-26');

-- ─── Users ──────────────────────────────────────────────────────────────────
-- Passwords below are real bcrypt hashes (cost 12):
--   phone 8919383362  →  Admin@123
--   phone 9999999999  →  Vendor@123
-- Login uses PHONE (not email). See App\Http\Controllers\Web\AuthController.
INSERT INTO `users` (`id`, `role_id`, `full_name`, `email`, `phone`, `password`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 1, 'Admin',          'admin@mahadevpharma.in', '8919383362', '$2y$12$Fy7bKoUAPi/7gmAnMOz0rOGZwDNht1EmHXYhtvOABS4uOwEslcEHu', 1, NOW(), NOW()),
(2, 3, 'Demo Pharmacy',  'vendor@demo.com',        '9999999999', '$2y$12$MtFTkKq1aAU5WYGLtig7j.MrrYQt8Xvz10Yg5vvDwrkmjp4nnlFRG', 1, NOW(), NOW());

-- ─── Default Warehouse ──────────────────────────────────────────────────────
INSERT INTO `warehouses` (`id`, `name`, `code`, `state_code`, `address_line1`, `city`, `state`, `pincode`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Main Warehouse', 'WH-SEC-01', '36', '2-3-166 & 17, 1st Floor, Taj Plaza', 'Secunderabad', 'Telangana', '500003', 1, NOW(), NOW());

-- ─── Categories ─────────────────────────────────────────────────────────────
INSERT INTO `categories` (`id`, `name`, `slug`, `sort_order`, `is_active`, `created_at`, `updated_at`) VALUES
(1,  'Tablets',                 'tablets',                 0, 1, NOW(), NOW()),
(2,  'Capsules',                'capsules',                1, 1, NOW(), NOW()),
(3,  'Syrups & Suspensions',    'syrups-suspensions',      2, 1, NOW(), NOW()),
(4,  'Injections & Vials',      'injections-vials',        3, 1, NOW(), NOW()),
(5,  'Ointments & Creams',      'ointments-creams',        4, 1, NOW(), NOW()),
(6,  'Drops & Solutions',       'drops-solutions',         5, 1, NOW(), NOW()),
(7,  'Powders & Sachets',       'powders-sachets',         6, 1, NOW(), NOW()),
(8,  'Inhalers & Nebulizers',   'inhalers-nebulizers',     7, 1, NOW(), NOW()),
(9,  'Surgical & Dressing',     'surgical-dressing',       8, 1, NOW(), NOW()),
(10, 'Vitamins & Supplements',  'vitamins-supplements',    9, 1, NOW(), NOW());

-- ─── Brands ─────────────────────────────────────────────────────────────────
INSERT INTO `brands` (`id`, `name`, `slug`, `manufacturer`, `is_active`, `created_at`, `updated_at`) VALUES
(1,  'Cipla',       'cipla',       'Cipla Ltd.',                             1, NOW(), NOW()),
(2,  'Sun Pharma',  'sun-pharma',  'Sun Pharmaceutical Industries Ltd.',     1, NOW(), NOW()),
(3,  'Dr. Reddy\'s','dr-reddys',   'Dr. Reddy\'s Laboratories Ltd.',         1, NOW(), NOW()),
(4,  'Lupin',       'lupin',       'Lupin Ltd.',                             1, NOW(), NOW()),
(5,  'Mankind',     'mankind',     'Mankind Pharma Ltd.',                    1, NOW(), NOW()),
(6,  'Zydus',       'zydus',       'Zydus Lifesciences Ltd.',                1, NOW(), NOW()),
(7,  'Torrent',     'torrent',     'Torrent Pharmaceuticals Ltd.',           1, NOW(), NOW()),
(8,  'Alkem',       'alkem',       'Alkem Laboratories Ltd.',                1, NOW(), NOW()),
(9,  'Abbott',      'abbott',      'Abbott India Ltd.',                      1, NOW(), NOW()),
(10, 'GSK',         'gsk',         'GlaxoSmithKline Pharmaceuticals Ltd.',   1, NOW(), NOW()),
(11, 'Biocon',      'biocon',      'Biocon Ltd.',                            1, NOW(), NOW()),
(12, 'Glenmark',    'glenmark',    'Glenmark Pharmaceuticals Ltd.',          1, NOW(), NOW());

-- ─── HSN Codes ──────────────────────────────────────────────────────────────
INSERT INTO `hsn_codes` (`id`, `code`, `description`, `cgst_rate`, `sgst_rate`, `igst_rate`, `effective_from`, `created_at`, `updated_at`) VALUES
(1, '3004', 'Medicaments for therapeutic/prophylactic use, in measured doses',     6.00, 6.00, 12.00, '2024-01-01', NOW(), NOW()),
(2, '3003', 'Medicaments not put up in measured doses or packing for retail',      6.00, 6.00, 12.00, '2024-01-01', NOW(), NOW()),
(3, '3005', 'Wadding, gauze, bandages, dressings with pharmaceutical substances',  6.00, 6.00, 12.00, '2024-01-01', NOW(), NOW()),
(4, '3006', 'Pharmaceutical goods (surgical, dental, ophthalmic)',                 6.00, 6.00, 12.00, '2024-01-01', NOW(), NOW()),
(5, '2106', 'Food preparations / Nutritional supplements',                         9.00, 9.00, 18.00, '2024-01-01', NOW(), NOW()),
(6, '9018', 'Medical/surgical instruments and apparatus',                          6.00, 6.00, 12.00, '2024-01-01', NOW(), NOW());

-- ─── Dosage Forms ───────────────────────────────────────────────────────────
INSERT INTO `dosage_forms` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES
(1,  'Tablet',             'tablet',             0,  NOW(), NOW()),
(2,  'Capsule',            'capsule',            1,  NOW(), NOW()),
(3,  'Syrup',              'syrup',              2,  NOW(), NOW()),
(4,  'Suspension',         'suspension',         3,  NOW(), NOW()),
(5,  'Injection',          'injection',          4,  NOW(), NOW()),
(6,  'Cream',              'cream',              5,  NOW(), NOW()),
(7,  'Ointment',           'ointment',           6,  NOW(), NOW()),
(8,  'Gel',                'gel',                7,  NOW(), NOW()),
(9,  'Drops',              'drops',              8,  NOW(), NOW()),
(10, 'Powder',             'powder',             9,  NOW(), NOW()),
(11, 'Inhaler',            'inhaler',            10, NOW(), NOW()),
(12, 'Nebulizer Solution', 'nebulizer-solution', 11, NOW(), NOW()),
(13, 'Suppository',        'suppository',        12, NOW(), NOW()),
(14, 'Patch',              'patch',              13, NOW(), NOW()),
(15, 'Spray',              'spray',              14, NOW(), NOW()),
(16, 'Lotion',             'lotion',             15, NOW(), NOW()),
(17, 'Solution',           'solution',           16, NOW(), NOW()),
(18, 'Sachet',             'sachet',             17, NOW(), NOW());

-- ─── Strengths ──────────────────────────────────────────────────────────────
INSERT INTO `strengths` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES
(1,'5mg','5mg',0,NOW(),NOW()),(2,'10mg','10mg',1,NOW(),NOW()),(3,'20mg','20mg',2,NOW(),NOW()),
(4,'25mg','25mg',3,NOW(),NOW()),(5,'40mg','40mg',4,NOW(),NOW()),(6,'50mg','50mg',5,NOW(),NOW()),
(7,'100mg','100mg',6,NOW(),NOW()),(8,'150mg','150mg',7,NOW(),NOW()),(9,'200mg','200mg',8,NOW(),NOW()),
(10,'250mg','250mg',9,NOW(),NOW()),(11,'300mg','300mg',10,NOW(),NOW()),(12,'400mg','400mg',11,NOW(),NOW()),
(13,'500mg','500mg',12,NOW(),NOW()),(14,'625mg','625mg',13,NOW(),NOW()),(15,'750mg','750mg',14,NOW(),NOW()),
(16,'1000mg','1000mg',15,NOW(),NOW()),(17,'1g','1g',16,NOW(),NOW()),(18,'2g','2g',17,NOW(),NOW()),
(19,'5ml','5ml',18,NOW(),NOW()),(20,'10ml','10ml',19,NOW(),NOW()),(21,'15ml','15ml',20,NOW(),NOW()),
(22,'30ml','30ml',21,NOW(),NOW()),(23,'50ml','50ml',22,NOW(),NOW()),(24,'60ml','60ml',23,NOW(),NOW()),
(25,'100ml','100ml',24,NOW(),NOW()),(26,'150ml','150ml',25,NOW(),NOW()),(27,'200ml','200ml',26,NOW(),NOW()),
(28,'5mg/5ml','5mg-5ml',27,NOW(),NOW()),(29,'10mg/5ml','10mg-5ml',28,NOW(),NOW()),
(30,'125mg/5ml','125mg-5ml',29,NOW(),NOW()),(31,'250mg/5ml','250mg-5ml',30,NOW(),NOW()),
(32,'1%','1',31,NOW(),NOW()),(33,'2%','2',32,NOW(),NOW()),(34,'5%','5',33,NOW(),NOW()),
(35,'0.1%','01',34,NOW(),NOW()),(36,'0.5%','05',35,NOW(),NOW()),(37,'10%','10',36,NOW(),NOW()),
(38,'5mg/ml','5mg-ml',37,NOW(),NOW()),(39,'10mg/ml','10mg-ml',38,NOW(),NOW()),
(40,'40mg/ml','40mg-ml',39,NOW(),NOW());

-- ─── Pack Sizes ─────────────────────────────────────────────────────────────
INSERT INTO `pack_sizes` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES
(1,'1 unit','1-unit',0,NOW(),NOW()),(2,'3 units','3-units',1,NOW(),NOW()),
(3,'5 units','5-units',2,NOW(),NOW()),(4,'10 units','10-units',3,NOW(),NOW()),
(5,'5 tablets/strip','5-tabletsstrip',4,NOW(),NOW()),(6,'10 tablets/strip','10-tabletsstrip',5,NOW(),NOW()),
(7,'15 tablets/strip','15-tabletsstrip',6,NOW(),NOW()),(8,'20 tablets/strip','20-tabletsstrip',7,NOW(),NOW()),
(9,'30 tablets/strip','30-tabletsstrip',8,NOW(),NOW()),
(10,'10 capsules/strip','10-capsulesstrip',9,NOW(),NOW()),(11,'15 capsules/strip','15-capsulesstrip',10,NOW(),NOW()),
(12,'30ml bottle','30ml-bottle',11,NOW(),NOW()),(13,'60ml bottle','60ml-bottle',12,NOW(),NOW()),
(14,'100ml bottle','100ml-bottle',13,NOW(),NOW()),(15,'150ml bottle','150ml-bottle',14,NOW(),NOW()),
(16,'200ml bottle','200ml-bottle',15,NOW(),NOW()),
(17,'1 vial','1-vial',16,NOW(),NOW()),(18,'5 vials','5-vials',17,NOW(),NOW()),(19,'10 vials','10-vials',18,NOW(),NOW()),
(20,'1 ampoule','1-ampoule',19,NOW(),NOW()),(21,'5 ampoules','5-ampoules',20,NOW(),NOW()),
(22,'10 ampoules','10-ampoules',21,NOW(),NOW()),
(23,'5g tube','5g-tube',22,NOW(),NOW()),(24,'10g tube','10g-tube',23,NOW(),NOW()),
(25,'15g tube','15g-tube',24,NOW(),NOW()),(26,'20g tube','20g-tube',25,NOW(),NOW()),
(27,'30g tube','30g-tube',26,NOW(),NOW()),(28,'50g tube','50g-tube',27,NOW(),NOW()),
(29,'1 inhaler','1-inhaler',28,NOW(),NOW()),(30,'1 sachet','1-sachet',29,NOW(),NOW()),
(31,'10 sachets','10-sachets',30,NOW(),NOW()),(32,'30 sachets','30-sachets',31,NOW(),NOW());

-- ─── Drug Schedules ─────────────────────────────────────────────────────────
INSERT INTO `drug_schedules` (`id`, `name`, `slug`, `description`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'OTC',         'otc',         'Over the counter — no prescription needed',                                    0, NOW(), NOW()),
(2, 'Schedule G',  'schedule-g',  'Can be sold only under supervision of a pharmacist',                            1, NOW(), NOW()),
(3, 'Schedule H',  'schedule-h',  'Can be sold only on prescription of a registered medical practitioner',         2, NOW(), NOW()),
(4, 'Schedule H1', 'schedule-h1', 'Requires prescription; record to be maintained by pharmacist for 3 years',      3, NOW(), NOW()),
(5, 'Schedule X',  'schedule-x',  'Narcotic and psychotropic substances — strict prescription and record-keeping', 4, NOW(), NOW()),
(6, 'Narcotics',   'narcotics',   'Controlled under NDPS Act — special license required',                         5, NOW(), NOW());

-- ─── Storage Conditions ─────────────────────────────────────────────────────
INSERT INTO `storage_conditions` (`id`, `name`, `slug`, `sort_order`, `created_at`, `updated_at`) VALUES
(1, 'Room Temperature',           'room-temperature',            0, NOW(), NOW()),
(2, 'Cool & Dry Place',           'cool-dry-place',              1, NOW(), NOW()),
(3, 'Refrigerated (2-8°C)',       'refrigerated-2-8c',           2, NOW(), NOW()),
(4, 'Frozen (-20°C)',             'frozen-20c',                  3, NOW(), NOW()),
(5, 'Controlled Room Temperature','controlled-room-temperature', 4, NOW(), NOW()),
(6, 'Protect from Light',         'protect-from-light',          5, NOW(), NOW()),
(7, 'Protect from Moisture',      'protect-from-moisture',       6, NOW(), NOW()),
(8, 'Do Not Freeze',              'do-not-freeze',               7, NOW(), NOW());

SET FOREIGN_KEY_CHECKS = 1;
