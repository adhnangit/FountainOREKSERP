-- ============================================================
-- Task Categories — Soft Delete feature
-- Live Database Migration Script
--
-- Adds a nullable deleted_at column to work_task_categories so
-- deleting a category no longer removes the row — it's hidden
-- from normal queries but recoverable. Safe to run on a live
-- database — this only adds a column, nothing existing is
-- altered or dropped.
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------------------------------------------
-- Migration: 2026_10_09_130000_add_soft_deletes_to_work_task_categories_table.php
-- Table(s): work_task_categories (adds deleted_at column)
-- ----------------------------------------------------------------
alter table `work_task_categories` add `deleted_at` timestamp null;

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------------------------------------------
-- Register this as already-run in Laravel's own migrations table.
-- Without this, the next `php artisan migrate` on live will try to
-- run it again and fail with "column already exists".
-- Batch number is computed automatically from whatever is already there.
-- ----------------------------------------------------------------
SET @next_batch = (SELECT COALESCE(MAX(batch),0)+1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_10_09_130000_add_soft_deletes_to_work_task_categories_table', @next_batch);
