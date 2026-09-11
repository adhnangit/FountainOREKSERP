-- ============================================================
-- Inquiries & Leads module — Live Database Migration Script
-- Generated from Laravel migrations, run in this exact order.
--
-- Creates the new Inquiries & Leads tables: inquiry_subjects,
-- inquiry_statuses, inquiries, inquiry_followups.
--
-- Safe to run on a live database — this only adds new tables,
-- nothing existing is altered or dropped.
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------------------------------------------
-- Migration: 2026_09_11_120000_create_inquiry_subjects_table.php
-- Table(s): inquiry_subjects
-- ----------------------------------------------------------------
create table `inquiry_subjects` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ----------------------------------------------------------------
-- Migration: 2026_09_11_120001_create_inquiry_statuses_table.php
-- Table(s): inquiry_statuses
-- ----------------------------------------------------------------
create table `inquiry_statuses` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `color` varchar(7) not null default '#4f46e5', `order_by` int not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';

-- ----------------------------------------------------------------
-- Migration: 2026_09_11_120002_create_inquiries_table.php
-- Table(s): inquiries
-- ----------------------------------------------------------------
create table `inquiries` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `email` varchar(191) null, `phone` varchar(191) null, `subject` varchar(191) not null, `source` varchar(191) null, `message` text null, `internal_notes` text null, `status` varchar(191) not null default 'New', `potential_value` decimal(15, 2) not null default '0', `assigned_to` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `inquiries` add constraint `inquiries_assigned_to_foreign` foreign key (`assigned_to`) references `users` (`id`) on delete set null;

-- ----------------------------------------------------------------
-- Migration: 2026_09_11_120003_create_inquiry_followups_table.php
-- Table(s): inquiry_followups
-- ----------------------------------------------------------------
create table `inquiry_followups` (`id` bigint unsigned not null auto_increment primary key, `inquiry_id` bigint unsigned not null, `followup_date` datetime not null, `notes` text not null, `user_id` bigint unsigned not null, `outcome` varchar(191) null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `inquiry_followups` add constraint `inquiry_followups_inquiry_id_foreign` foreign key (`inquiry_id`) references `inquiries` (`id`) on delete cascade;
alter table `inquiry_followups` add constraint `inquiry_followups_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------------------------------------------
-- Register these as already-run in Laravel's own migrations table.
-- Without this, the next `php artisan migrate` on live will try to
-- run all 4 of these again and fail with "table already exists".
-- Batch number is computed automatically from whatever is already there.
-- ----------------------------------------------------------------
SET @next_batch = (SELECT COALESCE(MAX(batch),0)+1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_09_11_120000_create_inquiry_subjects_table', @next_batch),
('2026_09_11_120001_create_inquiry_statuses_table', @next_batch),
('2026_09_11_120002_create_inquiries_table', @next_batch),
('2026_09_11_120003_create_inquiry_followups_table', @next_batch);
