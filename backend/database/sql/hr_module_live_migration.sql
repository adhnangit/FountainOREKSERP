-- ============================================================
-- MEDRI HR Module — Live Database Migration Script
-- Generated from Laravel migrations, run in this exact order.
-- Safe to run once on a database that does NOT yet have these tables.
-- ============================================================

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_134454_create_departments_table.php
-- Table(s): departments
-- ----------------------------------------------------------------
create table `departments` (`id` bigint unsigned not null auto_increment primary key, `branch_id` bigint unsigned null, `parent_id` bigint unsigned null, `name` varchar(191) not null, `code` varchar(191) not null, `description` text null, `is_active` tinyint(1) not null default '1', `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `departments` add constraint `departments_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `departments` add constraint `departments_parent_id_foreign` foreign key (`parent_id`) references `departments` (`id`) on delete set null;
alter table `departments` add index `departments_branch_id_index`(`branch_id`);
alter table `departments` add index `departments_parent_id_index`(`parent_id`);
alter table `departments` add unique `departments_code_unique`(`code`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_134455_create_designations_table.php
-- Table(s): designations
-- ----------------------------------------------------------------
create table `designations` (`id` bigint unsigned not null auto_increment primary key, `department_id` bigint unsigned null, `name` varchar(191) not null, `code` varchar(191) not null, `description` text null, `is_active` tinyint(1) not null default '1', `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `designations` add constraint `designations_department_id_foreign` foreign key (`department_id`) references `departments` (`id`) on delete set null;
alter table `designations` add index `designations_department_id_index`(`department_id`);
alter table `designations` add unique `designations_code_unique`(`code`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_134456_create_employees_table.php
-- Table(s): employees
-- ----------------------------------------------------------------
create table `employees` (`id` bigint unsigned not null auto_increment primary key, `user_id` bigint unsigned null, `employee_code` varchar(191) not null, `branch_id` bigint unsigned null, `department_id` bigint unsigned null, `designation_id` bigint unsigned null, `reporting_manager_id` bigint unsigned null, `first_name` varchar(191) not null, `last_name` varchar(191) null, `date_of_birth` date null, `gender` enum('male', 'female', 'other') null, `marital_status` enum('single', 'married', 'divorced', 'widowed') null, `nic_passport` varchar(191) null, `nationality` varchar(191) null, `photo_path` varchar(191) null, `personal_email` varchar(191) null, `phone` varchar(191) null, `phone2` varchar(191) null, `address` text null, `city` varchar(191) null, `district` varchar(191) null, `emergency_contact_name` varchar(191) null, `emergency_contact_relationship` varchar(191) null, `emergency_contact_phone` varchar(191) null, `bank_name` varchar(191) null, `bank_branch` varchar(191) null, `bank_account_name` varchar(191) null, `bank_account_number` varchar(191) null, `employment_type` enum('full_time', 'part_time', 'contract', 'intern') not null default 'full_time', `join_date` date not null, `probation_period_months` smallint unsigned null, `confirmation_date` date null, `employment_status` enum('active', 'on_leave', 'suspended', 'terminated') not null default 'active', `exit_date` date null, `exit_reason` text null, `is_active` tinyint(1) not null default '1', `created_by` bigint unsigned null, `notes` text null, `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `employees` add constraint `employees_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete set null;
alter table `employees` add constraint `employees_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `employees` add constraint `employees_department_id_foreign` foreign key (`department_id`) references `departments` (`id`) on delete set null;
alter table `employees` add constraint `employees_designation_id_foreign` foreign key (`designation_id`) references `designations` (`id`) on delete set null;
alter table `employees` add constraint `employees_reporting_manager_id_foreign` foreign key (`reporting_manager_id`) references `employees` (`id`) on delete set null;
alter table `employees` add constraint `employees_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `employees` add index `employees_branch_id_index`(`branch_id`);
alter table `employees` add index `employees_department_id_index`(`department_id`);
alter table `employees` add index `employees_designation_id_index`(`designation_id`);
alter table `employees` add index `employees_reporting_manager_id_index`(`reporting_manager_id`);
alter table `employees` add index `employees_employment_status_index`(`employment_status`);
alter table `employees` add index `employees_join_date_index`(`join_date`);
alter table `employees` add unique `employees_user_id_unique`(`user_id`);
alter table `employees` add unique `employees_employee_code_unique`(`employee_code`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_134457_create_employee_documents_table.php
-- Table(s): employee_documents
-- ----------------------------------------------------------------
create table `employee_documents` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `document_type` varchar(191) not null, `title` varchar(191) not null, `file_path` varchar(191) not null, `expiry_date` date null, `uploaded_by` bigint unsigned null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `employee_documents` add constraint `employee_documents_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `employee_documents` add constraint `employee_documents_uploaded_by_foreign` foreign key (`uploaded_by`) references `users` (`id`) on delete set null;
alter table `employee_documents` add index `employee_documents_employee_id_index`(`employee_id`);
alter table `employee_documents` add index `employee_documents_expiry_date_index`(`expiry_date`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_134458_create_employee_history_table.php
-- Table(s): employee_history
-- ----------------------------------------------------------------
create table `employee_history` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `field_changed` varchar(191) not null, `old_value` varchar(191) null, `new_value` varchar(191) null, `effective_date` date null, `changed_by` bigint unsigned null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `employee_history` add constraint `employee_history_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `employee_history` add constraint `employee_history_changed_by_foreign` foreign key (`changed_by`) references `users` (`id`) on delete set null;
alter table `employee_history` add index `employee_history_employee_id_index`(`employee_id`);
alter table `employee_history` add index `employee_history_field_changed_index`(`field_changed`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_150000_create_holidays_table.php
-- Table(s): holidays
-- ----------------------------------------------------------------
create table `holidays` (`id` bigint unsigned not null auto_increment primary key, `branch_id` bigint unsigned null, `date` date not null, `name` varchar(191) not null, `is_recurring_yearly` tinyint(1) not null default '0', `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `holidays` add constraint `holidays_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `holidays` add index `holidays_branch_id_index`(`branch_id`);
alter table `holidays` add index `holidays_date_index`(`date`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_150001_create_attendances_table.php
-- Table(s): attendances
-- ----------------------------------------------------------------
create table `attendances` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `branch_id` bigint unsigned null, `date` date not null, `status` enum('present', 'absent', 'half_day', 'late', 'on_leave', 'holiday', 'weekend') not null default 'present', `time_in` time null, `time_out` time null, `work_hours` decimal(5, 2) null, `late_minutes` smallint unsigned null, `notes` text null, `marked_by` bigint unsigned null, `source` enum('manual', 'bulk', 'import') not null default 'manual', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `attendances` add constraint `attendances_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `attendances` add constraint `attendances_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `attendances` add constraint `attendances_marked_by_foreign` foreign key (`marked_by`) references `users` (`id`) on delete set null;
alter table `attendances` add unique `attendances_employee_id_date_unique`(`employee_id`, `date`);
alter table `attendances` add index `attendances_branch_id_index`(`branch_id`);
alter table `attendances` add index `attendances_date_index`(`date`);
alter table `attendances` add index `attendances_status_index`(`status`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_161000_create_leave_types_table.php
-- Table(s): leave_types
-- ----------------------------------------------------------------
create table `leave_types` (`id` bigint unsigned not null auto_increment primary key, `branch_id` bigint unsigned null, `name` varchar(191) not null, `code` varchar(191) not null, `max_days_per_year` decimal(5, 1) null, `is_paid` tinyint(1) not null default '1', `requires_approval` tinyint(1) not null default '1', `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `leave_types` add constraint `leave_types_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `leave_types` add index `leave_types_branch_id_index`(`branch_id`);
alter table `leave_types` add unique `leave_types_code_unique`(`code`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_161001_create_leave_balances_table.php
-- Table(s): leave_balances
-- ----------------------------------------------------------------
create table `leave_balances` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `leave_type_id` bigint unsigned not null, `year` smallint unsigned not null, `allocated_days` decimal(5, 1) not null default '0', `used_days` decimal(5, 1) not null default '0', `carried_forward` decimal(5, 1) not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `leave_balances` add constraint `leave_balances_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `leave_balances` add constraint `leave_balances_leave_type_id_foreign` foreign key (`leave_type_id`) references `leave_types` (`id`) on delete cascade;
alter table `leave_balances` add unique `leave_balances_employee_id_leave_type_id_year_unique`(`employee_id`, `leave_type_id`, `year`);
alter table `leave_balances` add index `leave_balances_year_index`(`year`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_161002_create_leave_requests_table.php
-- Table(s): leave_requests
-- ----------------------------------------------------------------
create table `leave_requests` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `leave_type_id` bigint unsigned not null, `start_date` date not null, `end_date` date not null, `is_half_day` tinyint(1) not null default '0', `half_day_period` enum('first_half', 'second_half') null, `total_days` decimal(5, 1) not null, `reason` text null, `status` enum('pending', 'approved', 'rejected', 'cancelled') not null default 'pending', `approved_by` bigint unsigned null, `approved_at` timestamp null, `decision_notes` text null, `applied_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `leave_requests` add constraint `leave_requests_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `leave_requests` add constraint `leave_requests_leave_type_id_foreign` foreign key (`leave_type_id`) references `leave_types` (`id`) on delete restrict;
alter table `leave_requests` add constraint `leave_requests_approved_by_foreign` foreign key (`approved_by`) references `users` (`id`) on delete set null;
alter table `leave_requests` add constraint `leave_requests_applied_by_foreign` foreign key (`applied_by`) references `users` (`id`) on delete set null;
alter table `leave_requests` add index `leave_requests_employee_id_index`(`employee_id`);
alter table `leave_requests` add index `leave_requests_status_index`(`status`);
alter table `leave_requests` add index `leave_requests_start_date_index`(`start_date`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_161003_add_leave_request_id_to_attendances_table.php
-- Table(s): attendances (add leave_request_id)
-- ----------------------------------------------------------------
alter table `attendances` add `leave_request_id` bigint unsigned null after `employee_id`;
alter table `attendances` add constraint `attendances_leave_request_id_foreign` foreign key (`leave_request_id`) references `leave_requests` (`id`) on delete set null;

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_170000_add_salary_fields_to_employees_table.php
-- Table(s): employees (add salary fields)
-- ----------------------------------------------------------------
alter table `employees` add `basic_salary` decimal(12, 2) null after `bank_account_number`;
alter table `employees` add `epf_etf_applicable` tinyint(1) not null default '1' after `basic_salary`;

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_170001_create_salary_components_table.php
-- Table(s): salary_components
-- ----------------------------------------------------------------
create table `salary_components` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `name` varchar(191) not null, `type` enum('allowance', 'deduction') not null, `amount` decimal(10, 2) not null, `is_active` tinyint(1) not null default '1', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `salary_components` add constraint `salary_components_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `salary_components` add index `salary_components_employee_id_index`(`employee_id`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_170002_create_payroll_runs_table.php
-- Table(s): payroll_runs
-- ----------------------------------------------------------------
create table `payroll_runs` (`id` bigint unsigned not null auto_increment primary key, `branch_id` bigint unsigned not null, `month` tinyint unsigned not null, `year` smallint unsigned not null, `status` enum('draft', 'paid') not null default 'draft', `created_by` bigint unsigned null, `paid_at` timestamp null, `paid_by` bigint unsigned null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `payroll_runs` add constraint `payroll_runs_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete restrict;
alter table `payroll_runs` add constraint `payroll_runs_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `payroll_runs` add constraint `payroll_runs_paid_by_foreign` foreign key (`paid_by`) references `users` (`id`) on delete set null;
alter table `payroll_runs` add unique `payroll_runs_branch_id_month_year_unique`(`branch_id`, `month`, `year`);
alter table `payroll_runs` add index `payroll_runs_status_index`(`status`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_19_170003_create_payslips_table.php
-- Table(s): payslips
-- ----------------------------------------------------------------
create table `payslips` (`id` bigint unsigned not null auto_increment primary key, `payroll_run_id` bigint unsigned not null, `employee_id` bigint unsigned not null, `basic_salary` decimal(12, 2) not null default '0', `total_allowances` decimal(12, 2) not null default '0', `total_deductions` decimal(12, 2) not null default '0', `unpaid_leave_days` decimal(5, 1) not null default '0', `unpaid_leave_deduction` decimal(12, 2) not null default '0', `gross_pay` decimal(12, 2) not null default '0', `epf_employee` decimal(12, 2) not null default '0', `epf_employer` decimal(12, 2) not null default '0', `etf_employer` decimal(12, 2) not null default '0', `net_pay` decimal(12, 2) not null default '0', `components` json null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `payslips` add constraint `payslips_payroll_run_id_foreign` foreign key (`payroll_run_id`) references `payroll_runs` (`id`) on delete cascade;
alter table `payslips` add constraint `payslips_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete restrict;
alter table `payslips` add unique `payslips_payroll_run_id_employee_id_unique`(`payroll_run_id`, `employee_id`);
alter table `payslips` add index `payslips_employee_id_index`(`employee_id`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_100000_create_job_openings_table.php
-- Table(s): job_openings
-- ----------------------------------------------------------------
create table `job_openings` (`id` bigint unsigned not null auto_increment primary key, `branch_id` bigint unsigned null, `department_id` bigint unsigned null, `designation_id` bigint unsigned null, `title` varchar(191) not null, `description` text null, `requirements` text null, `employment_type` enum('full_time', 'part_time', 'contract', 'intern') not null default 'full_time', `openings_count` smallint unsigned not null default '1', `status` enum('open', 'on_hold', 'closed', 'filled') not null default 'open', `posted_date` date null, `closing_date` date null, `created_by` bigint unsigned null, `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `job_openings` add constraint `job_openings_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `job_openings` add constraint `job_openings_department_id_foreign` foreign key (`department_id`) references `departments` (`id`) on delete set null;
alter table `job_openings` add constraint `job_openings_designation_id_foreign` foreign key (`designation_id`) references `designations` (`id`) on delete set null;
alter table `job_openings` add constraint `job_openings_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `job_openings` add index `job_openings_branch_id_index`(`branch_id`);
alter table `job_openings` add index `job_openings_status_index`(`status`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_100001_create_candidates_table.php
-- Table(s): candidates
-- ----------------------------------------------------------------
create table `candidates` (`id` bigint unsigned not null auto_increment primary key, `job_opening_id` bigint unsigned null, `first_name` varchar(191) not null, `last_name` varchar(191) null, `email` varchar(191) null, `phone` varchar(191) null, `resume_path` varchar(191) null, `cover_letter` text null, `source` varchar(191) null, `status` enum('applied', 'screening', 'interview', 'offer', 'hired', 'rejected', 'withdrawn') not null default 'applied', `rating` tinyint unsigned null, `notes` text null, `offered_salary` decimal(12, 2) null, `offer_date` date null, `employee_id` bigint unsigned null, `created_by` bigint unsigned null, `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `candidates` add constraint `candidates_job_opening_id_foreign` foreign key (`job_opening_id`) references `job_openings` (`id`) on delete set null;
alter table `candidates` add constraint `candidates_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete set null;
alter table `candidates` add constraint `candidates_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `candidates` add index `candidates_job_opening_id_index`(`job_opening_id`);
alter table `candidates` add index `candidates_status_index`(`status`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_100002_create_candidate_interviews_table.php
-- Table(s): candidate_interviews
-- ----------------------------------------------------------------
create table `candidate_interviews` (`id` bigint unsigned not null auto_increment primary key, `candidate_id` bigint unsigned not null, `scheduled_at` datetime not null, `mode` enum('in_person', 'phone', 'video') not null default 'in_person', `interviewer_id` bigint unsigned null, `location_or_link` varchar(191) null, `status` enum('scheduled', 'completed', 'cancelled', 'no_show') not null default 'scheduled', `feedback` text null, `rating` tinyint unsigned null, `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `candidate_interviews` add constraint `candidate_interviews_candidate_id_foreign` foreign key (`candidate_id`) references `candidates` (`id`) on delete cascade;
alter table `candidate_interviews` add constraint `candidate_interviews_interviewer_id_foreign` foreign key (`interviewer_id`) references `users` (`id`) on delete set null;
alter table `candidate_interviews` add constraint `candidate_interviews_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `candidate_interviews` add index `candidate_interviews_candidate_id_index`(`candidate_id`);
alter table `candidate_interviews` add index `candidate_interviews_scheduled_at_index`(`scheduled_at`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_100003_create_candidate_status_history_table.php
-- Table(s): candidate_status_history
-- ----------------------------------------------------------------
create table `candidate_status_history` (`id` bigint unsigned not null auto_increment primary key, `candidate_id` bigint unsigned not null, `old_status` varchar(191) null, `new_status` varchar(191) not null, `changed_by` bigint unsigned null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `candidate_status_history` add constraint `candidate_status_history_candidate_id_foreign` foreign key (`candidate_id`) references `candidates` (`id`) on delete cascade;
alter table `candidate_status_history` add constraint `candidate_status_history_changed_by_foreign` foreign key (`changed_by`) references `users` (`id`) on delete set null;
alter table `candidate_status_history` add index `candidate_status_history_candidate_id_index`(`candidate_id`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_110000_create_performance_cycles_table.php
-- Table(s): performance_cycles
-- ----------------------------------------------------------------
create table `performance_cycles` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `start_date` date not null, `end_date` date not null, `status` enum('draft', 'active', 'closed') not null default 'draft', `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `performance_cycles` add constraint `performance_cycles_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_110001_create_performance_reviews_table.php
-- Table(s): performance_reviews
-- ----------------------------------------------------------------
create table `performance_reviews` (`id` bigint unsigned not null auto_increment primary key, `cycle_id` bigint unsigned not null, `employee_id` bigint unsigned not null, `reviewer_id` bigint unsigned null, `status` enum('pending', 'in_progress', 'completed') not null default 'pending', `overall_rating` tinyint unsigned null, `employee_comments` text null, `reviewer_comments` text null, `completed_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `performance_reviews` add constraint `performance_reviews_cycle_id_foreign` foreign key (`cycle_id`) references `performance_cycles` (`id`) on delete cascade;
alter table `performance_reviews` add constraint `performance_reviews_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `performance_reviews` add constraint `performance_reviews_reviewer_id_foreign` foreign key (`reviewer_id`) references `users` (`id`) on delete set null;
alter table `performance_reviews` add unique `performance_reviews_cycle_id_employee_id_unique`(`cycle_id`, `employee_id`);
alter table `performance_reviews` add index `performance_reviews_employee_id_index`(`employee_id`);
alter table `performance_reviews` add index `performance_reviews_status_index`(`status`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_110002_create_performance_goals_table.php
-- Table(s): performance_goals
-- ----------------------------------------------------------------
create table `performance_goals` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `review_id` bigint unsigned null, `title` varchar(191) not null, `description` text null, `target_date` date null, `status` enum('not_started', 'in_progress', 'completed', 'cancelled') not null default 'not_started', `progress_percent` tinyint unsigned not null default '0', `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `performance_goals` add constraint `performance_goals_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `performance_goals` add constraint `performance_goals_review_id_foreign` foreign key (`review_id`) references `performance_reviews` (`id`) on delete set null;
alter table `performance_goals` add constraint `performance_goals_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `performance_goals` add index `performance_goals_employee_id_index`(`employee_id`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_120000_create_checklist_templates_table.php
-- Table(s): checklist_templates + checklist_template_items
-- ----------------------------------------------------------------
create table `checklist_templates` (`id` bigint unsigned not null auto_increment primary key, `name` varchar(191) not null, `type` enum('onboarding', 'offboarding') not null, `employment_type` enum('full_time', 'part_time', 'contract', 'intern') null, `is_active` tinyint(1) not null default '1', `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `checklist_templates` add constraint `checklist_templates_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `checklist_templates` add index `checklist_templates_type_index`(`type`);
create table `checklist_template_items` (`id` bigint unsigned not null auto_increment primary key, `template_id` bigint unsigned not null, `title` varchar(191) not null, `description` text null, `due_days_offset` int not null default '0', `sort_order` smallint unsigned not null default '0', `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `checklist_template_items` add constraint `checklist_template_items_template_id_foreign` foreign key (`template_id`) references `checklist_templates` (`id`) on delete cascade;
alter table `checklist_template_items` add index `checklist_template_items_template_id_index`(`template_id`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_120001_create_employee_checklist_tasks_table.php
-- Table(s): employee_checklist_tasks
-- ----------------------------------------------------------------
create table `employee_checklist_tasks` (`id` bigint unsigned not null auto_increment primary key, `employee_id` bigint unsigned not null, `type` enum('onboarding', 'offboarding') not null, `title` varchar(191) not null, `description` text null, `due_date` date null, `status` enum('pending', 'completed') not null default 'pending', `completed_by` bigint unsigned null, `completed_at` timestamp null, `sort_order` smallint unsigned not null default '0', `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `employee_checklist_tasks` add constraint `employee_checklist_tasks_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `employee_checklist_tasks` add constraint `employee_checklist_tasks_completed_by_foreign` foreign key (`completed_by`) references `users` (`id`) on delete set null;
alter table `employee_checklist_tasks` add constraint `employee_checklist_tasks_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `employee_checklist_tasks` add index `employee_checklist_tasks_employee_id_index`(`employee_id`);
alter table `employee_checklist_tasks` add index `employee_checklist_tasks_type_index`(`type`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_130000_create_assets_table.php
-- Table(s): assets + asset_assignments
-- ----------------------------------------------------------------
create table `assets` (`id` bigint unsigned not null auto_increment primary key, `asset_code` varchar(191) not null, `name` varchar(191) not null, `category` varchar(191) null, `branch_id` bigint unsigned null, `purchase_date` date null, `purchase_cost` decimal(12, 2) null, `serial_number` varchar(191) null, `status` enum('available', 'assigned', 'under_repair', 'retired') not null default 'available', `notes` text null, `deleted_at` timestamp null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `assets` add constraint `assets_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `assets` add index `assets_branch_id_index`(`branch_id`);
alter table `assets` add index `assets_status_index`(`status`);
alter table `assets` add unique `assets_asset_code_unique`(`asset_code`);
create table `asset_assignments` (`id` bigint unsigned not null auto_increment primary key, `asset_id` bigint unsigned not null, `employee_id` bigint unsigned not null, `assigned_date` date not null, `returned_date` date null, `assigned_by` bigint unsigned null, `condition_on_assign` varchar(191) null, `condition_on_return` varchar(191) null, `notes` text null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `asset_assignments` add constraint `asset_assignments_asset_id_foreign` foreign key (`asset_id`) references `assets` (`id`) on delete cascade;
alter table `asset_assignments` add constraint `asset_assignments_employee_id_foreign` foreign key (`employee_id`) references `employees` (`id`) on delete cascade;
alter table `asset_assignments` add constraint `asset_assignments_assigned_by_foreign` foreign key (`assigned_by`) references `users` (`id`) on delete set null;
alter table `asset_assignments` add index `asset_assignments_asset_id_index`(`asset_id`);
alter table `asset_assignments` add index `asset_assignments_employee_id_index`(`employee_id`);

-- ----------------------------------------------------------------
-- Migration: 2026_08_20_140000_create_announcements_table.php
-- Table(s): announcements + announcement_reads
-- ----------------------------------------------------------------
create table `announcements` (`id` bigint unsigned not null auto_increment primary key, `branch_id` bigint unsigned null, `title` varchar(191) not null, `body` text not null, `is_pinned` tinyint(1) not null default '0', `published_at` timestamp null, `expires_at` date null, `created_by` bigint unsigned null, `created_at` timestamp null, `updated_at` timestamp null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `announcements` add constraint `announcements_branch_id_foreign` foreign key (`branch_id`) references `branches` (`id`) on delete set null;
alter table `announcements` add constraint `announcements_created_by_foreign` foreign key (`created_by`) references `users` (`id`) on delete set null;
alter table `announcements` add index `announcements_branch_id_index`(`branch_id`);
alter table `announcements` add index `announcements_published_at_index`(`published_at`);
create table `announcement_reads` (`id` bigint unsigned not null auto_increment primary key, `announcement_id` bigint unsigned not null, `user_id` bigint unsigned not null, `read_at` timestamp not null) default character set utf8mb4 collate 'utf8mb4_unicode_ci';
alter table `announcement_reads` add constraint `announcement_reads_announcement_id_foreign` foreign key (`announcement_id`) references `announcements` (`id`) on delete cascade;
alter table `announcement_reads` add constraint `announcement_reads_user_id_foreign` foreign key (`user_id`) references `users` (`id`) on delete cascade;
alter table `announcement_reads` add unique `announcement_reads_announcement_id_user_id_unique`(`announcement_id`, `user_id`);

SET FOREIGN_KEY_CHECKS=1;

-- ----------------------------------------------------------------
-- Register these as already-run in Laravel's own migrations table.
-- Without this, the next `php artisan migrate` on live will try to
-- run all 26 of these again and fail with "table already exists".
-- Batch number is computed automatically from whatever is already there.
-- ----------------------------------------------------------------
SET @next_batch = (SELECT COALESCE(MAX(batch),0)+1 FROM `migrations`);

INSERT INTO `migrations` (`migration`, `batch`) VALUES
('2026_08_19_134454_create_departments_table', @next_batch),
('2026_08_19_134455_create_designations_table', @next_batch),
('2026_08_19_134456_create_employees_table', @next_batch),
('2026_08_19_134457_create_employee_documents_table', @next_batch),
('2026_08_19_134458_create_employee_history_table', @next_batch),
('2026_08_19_150000_create_holidays_table', @next_batch),
('2026_08_19_150001_create_attendances_table', @next_batch),
('2026_08_19_161000_create_leave_types_table', @next_batch),
('2026_08_19_161001_create_leave_balances_table', @next_batch),
('2026_08_19_161002_create_leave_requests_table', @next_batch),
('2026_08_19_161003_add_leave_request_id_to_attendances_table', @next_batch),
('2026_08_19_170000_add_salary_fields_to_employees_table', @next_batch),
('2026_08_19_170001_create_salary_components_table', @next_batch),
('2026_08_19_170002_create_payroll_runs_table', @next_batch),
('2026_08_19_170003_create_payslips_table', @next_batch),
('2026_08_20_100000_create_job_openings_table', @next_batch),
('2026_08_20_100001_create_candidates_table', @next_batch),
('2026_08_20_100002_create_candidate_interviews_table', @next_batch),
('2026_08_20_100003_create_candidate_status_history_table', @next_batch),
('2026_08_20_110000_create_performance_cycles_table', @next_batch),
('2026_08_20_110001_create_performance_reviews_table', @next_batch),
('2026_08_20_110002_create_performance_goals_table', @next_batch),
('2026_08_20_120000_create_checklist_templates_table', @next_batch),
('2026_08_20_120001_create_employee_checklist_tasks_table', @next_batch),
('2026_08_20_130000_create_assets_table', @next_batch),
('2026_08_20_140000_create_announcements_table', @next_batch);
