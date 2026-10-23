-- ============================================================
-- Purchase Orders / Supplier Invoices — Service line items
-- Live Database Migration Script
--
-- Lets a purchase order / supplier invoice line be a Service
-- instead of a Product (mirrors how Sales Invoices already
-- support both). Makes product_id nullable and adds service_id +
-- item_type to purchase_order_items. Safe to run on a live
-- database — existing rows are untouched (all backfilled as
-- item_type='product' since they all have a product_id already).
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------------------------------------------
-- Migration: 2026_10_24_120000_add_service_id_to_purchase_order_items_table.php
-- Table(s): purchase_order_items (product_id now nullable, adds service_id, item_type)
-- ----------------------------------------------------------------
ALTER TABLE purchase_order_items MODIFY product_id BIGINT UNSIGNED NULL;
alter table `purchase_order_items` add `service_id` bigint unsigned null after `product_id`;
alter table `purchase_order_items` add constraint `purchase_order_items_service_id_foreign` foreign key (`service_id`) references `services` (`id`) on delete set null;
alter table `purchase_order_items` add `item_type` enum('product', 'service') not null default 'product' after `service_id`;
UPDATE purchase_order_items SET item_type = 'product' WHERE product_id IS NOT NULL;

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------------------------------------------
-- Register this as already-run in Laravel's own migrations table.
-- Without this, the next `php artisan migrate` on live will try to
-- run it again and fail with "column already exists".
-- Batch number is computed automatically from whatever is already there.
-- ----------------------------------------------------------------
SET @next_batch = (SELECT COALESCE(MAX(batch),0)+1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_10_24_120000_add_service_id_to_purchase_order_items_table', @next_batch);
