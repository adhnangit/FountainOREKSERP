-- ============================================================
-- Task Manager — "Archive completed task" feature
-- Live Database Migration Script
--
-- Adds a nullable archived_at column to work_tasks. Safe to run
-- on a live database — this only adds a column, nothing existing
-- is altered or dropped, and all existing rows get archived_at = NULL
-- (i.e. "not archived", same as before this column existed).
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------------------------------------------
-- Migration: 2026_10_09_120000_add_archived_at_to_work_tasks_table.php
-- Table(s): work_tasks (adds archived_at column)
-- ----------------------------------------------------------------
alter table `work_tasks` add `archived_at` timestamp null after `completed_at`;

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------------------------------------------
-- Register this as already-run in Laravel's own migrations table.
-- Without this, the next `php artisan migrate` on live will try to
-- run it again and fail with "column already exists".
-- Batch number is computed automatically from whatever is already there.
-- ----------------------------------------------------------------
SET @next_batch = (SELECT COALESCE(MAX(batch),0)+1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_10_09_120000_add_archived_at_to_work_tasks_table', @next_batch);
