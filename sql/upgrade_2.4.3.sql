-- v2.4.3 — BR-007 event-close expense attachment flow.
-- Adds the (event_id, event_attendee_email) guard columns to 0_travel_requests
-- (NEW header idempotency) and 0_travel_expenses (EXISTING line idempotency)
-- per FR-EXPENSE-007-001. Existing installs are upgraded idempotently; fresh
-- installs get the full DDL (incl. the UNIQUE uk_event_attendee) from
-- install.sql.

ALTER TABLE `0_travel_requests`
    ADD COLUMN IF NOT EXISTS `event_id` VARCHAR(20) DEFAULT NULL AFTER `created_at`,
    ADD COLUMN IF NOT EXISTS `event_attendee_email` VARCHAR(255) DEFAULT NULL AFTER `event_id`;

ALTER TABLE `0_travel_expenses`
    ADD COLUMN IF NOT EXISTS `event_id` VARCHAR(20) DEFAULT NULL AFTER `status`,
    ADD COLUMN IF NOT EXISTS `event_attendee_email` VARCHAR(255) DEFAULT NULL AFTER `event_id`;