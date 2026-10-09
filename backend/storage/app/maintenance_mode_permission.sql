-- ═══════════════════════════════════════════════════════════════════
-- OREKS ERP (FountainOREKS) — Maintenance Mode feature: permission seed
-- No schema/table changes needed — maintenance mode reuses the existing
-- system_settings table. This is the only new MySQL "feed": one new
-- permission, granted to super_admin only (deliberately not given to
-- other roles — it can lock out the whole system).
--
-- Run this AFTER deploying the updated application code.
-- INSERT IGNORE throughout — safe to run more than once, and the
-- permission/role ids are looked up by name via subquery rather than
-- hardcoded, since they'll differ from this dev database's ids.
-- ═══════════════════════════════════════════════════════════════════

INSERT IGNORE INTO `permissions` (`name`, `guard_name`, `created_at`, `updated_at`)
VALUES ('system.maintenance.manage', 'web', NOW(), NOW());

INSERT IGNORE INTO `role_has_permissions` (`permission_id`, `role_id`)
  SELECT p.id, r.id FROM `permissions` p, `roles` r
  WHERE p.name = 'system.maintenance.manage' AND p.guard_name = 'web' AND r.name = 'super_admin';
