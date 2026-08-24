-- ============================================================
-- Task Manager module — Live Database Migration Script
-- Generated from Laravel migrations, run in this exact order.
--
-- Creates the new Task Manager tables (work_task_categories,
-- work_tasks, work_task_followups), then drops the old plain
-- "Tasks" feature's tables (task_comments, tasks) which this
-- module replaces.
--
-- IMPORTANT: back up the live database before running this —
-- the DROP TABLE statements at the end are irreversible and will
-- delete any existing rows in `tasks` / `task_comments`.
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------------------------------------------
-- Migration: 2026_08_24_120000_create_work_task_categories_table.php
-- Table(s): work_task_categories
-- ----------------------------------------------------------------
create table `work_task_categories` (`id` bigint unsigned not null auto_increment primary key, `parent_id` bigint unsigned null, `name` varchar(191) not null, `color` varchar(7) not null default '#2563EB', `status` varchar(191) not null default 'Active', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `work_task_categories` add constraint `work_task_categories_parent_id_foreign` foreign key (`parent_id`) references `work_task_categories` (`id`) on delete set null;

-- ----------------------------------------------------------------
-- Migration: 2026_08_24_120001_create_work_tasks_table.php
-- Table(s): work_tasks
-- ----------------------------------------------------------------
create table `work_tasks` (`id` bigint unsigned not null auto_increment primary key, `branch_id` bigint unsigned null, `category_id` bigint unsigned null, `title` varchar(191) not null, `description` text null, `assigned_to` bigint unsigned null, `created_by` bigint unsigned null, `priority` enum('Low', 'Medium', 'High') not null default 'Medium', `status` enum('Pending', 'In Progress', 'Completed', 'Cancelled') not null default 'Pending', `due_date` date null, `completed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `work_tasks` add constraint `work_tasks_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `work_tasks` add constraint `work_tasks_category_id_foreign` foreign key (`category_id`) references `work_task_categories` (`id`) on delete set null;
alter table `work_tasks` add constraint `work_tasks_assigned_to_foreign` foreign key (`assigned_to`) references `users` (`id`) on delete set null;
alter table `work_tasks` add constraint `work_tasks_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;

-- ----------------------------------------------------------------
-- Migration: 2026_08_24_120002_create_work_task_followups_table.php
-- Table(s): work_task_followups
-- ----------------------------------------------------------------
create table `work_task_followups` (`id` bigint unsigned not null auto_increment primary key, `task_id` bigint unsigned not null, `user_id` bigint unsigned null, `note` text not null, `status_snapshot` varchar(191) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `work_task_followups` add constraint `work_task_followups_task_id_foreign` foreign key (`task_id`) references `work_tasks` (`id`) on delete cascade;
alter table `work_task_followups` add constraint `work_task_followups_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;

-- ----------------------------------------------------------------
-- Migration: 2026_08_24_130000_drop_tasks_and_task_comments_tables.php
-- Table(s): task_comments, tasks (removed — replaced by the Task Manager module above)
-- ----------------------------------------------------------------
drop table if exists `task_comments`;
drop table if exists `tasks`;

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------------------------------------------
-- Register these as already-run in Laravel's own migrations table.
-- Without this, the next `php artisan migrate` on live will try to
-- run all 4 of these again and fail with "table already exists".
-- Batch number is computed automatically from whatever is already there.
-- ----------------------------------------------------------------
SET @next_batch = (SELECT COALESCE(MAX(batch),0)+1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_08_24_120000_create_work_task_categories_table', @next_batch),
('2026_08_24_120001_create_work_tasks_table', @next_batch),
('2026_08_24_120002_create_work_task_followups_table', @next_batch),
('2026_08_24_130000_drop_tasks_and_task_comments_tables', @next_batch);
