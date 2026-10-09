-- ============================================================
-- Task Manager — Document Attachments feature
-- Live Database Migration Script
--
-- Adds nullable attachment_path/attachment_name columns to both
-- work_tasks (attach a document while creating a task) and
-- work_task_followups (attach a document on a follow-up/comment).
-- Safe to run on a live database — this only adds columns,
-- nothing existing is altered or dropped.
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------------------------------------------
-- Migration: 2026_10_10_120000_add_attachment_to_work_tasks_table.php
-- Table(s): work_tasks (adds attachment_path, attachment_name)
-- ----------------------------------------------------------------
alter table `work_tasks` add `attachment_path` varchar(191) null after `due_date`;
alter table `work_tasks` add `attachment_name` varchar(191) null after `attachment_path`;

-- ----------------------------------------------------------------
-- Migration: 2026_10_10_120001_add_attachment_to_work_task_followups_table.php
-- Table(s): work_task_followups (adds attachment_path, attachment_name)
-- ----------------------------------------------------------------
alter table `work_task_followups` add `attachment_path` varchar(191) null after `note`;
alter table `work_task_followups` add `attachment_name` varchar(191) null after `attachment_path`;

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------------------------------------------
-- Register these as already-run in Laravel's own migrations table.
-- Without this, the next `php artisan migrate` on live will try to
-- run them again and fail with "column already exists".
-- Batch number is computed automatically from whatever is already there.
-- ----------------------------------------------------------------
SET @next_batch = (SELECT COALESCE(MAX(batch),0)+1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_10_10_120000_add_attachment_to_work_tasks_table', @next_batch),
('2026_10_10_120001_add_attachment_to_work_task_followups_table', @next_batch);
