-- NightPOS Real DB QA Audit SQL
-- Database: nigtpos (hosting import)

-- 1. Duplicate tickets
SELECT '1_duplicate_tickets' AS audit;
SELECT tenant_id, branch_id, ticket_number, COUNT(*) AS c
FROM staff_settlements
WHERE ticket_number IS NOT NULL
GROUP BY tenant_id, branch_id, ticket_number
HAVING c > 1;

-- 2. document_sequences lag vs max ticket
SELECT '2_document_sequences_lag' AS audit;
SELECT ds.tenant_id, ds.branch_id, ds.period_key, ds.last_value AS seq_last_value,
       COALESCE(t.max_seq, 0) AS max_ticket_seq,
       CASE WHEN ds.last_value < COALESCE(t.max_seq, 0) THEN 'LAGGED' ELSE 'OK' END AS status
FROM document_sequences ds
LEFT JOIN (
    SELECT tenant_id, branch_id,
           SUBSTRING_INDEX(ticket_number, '-', -2) AS period_suffix,
           MAX(CAST(SUBSTRING_INDEX(ticket_number, '-', -1) AS UNSIGNED)) AS max_seq
    FROM staff_settlements
    WHERE ticket_number IS NOT NULL
      AND ticket_number REGEXP '-[0-9]{4}-[0-9]{6}$'
    GROUP BY tenant_id, branch_id, period_suffix
) t ON t.tenant_id = ds.tenant_id AND t.branch_id = ds.branch_id
   AND ds.period_key = SUBSTRING_INDEX(t.period_suffix, '-', 1);

-- Simpler lag check per period_key
SELECT ds.id, ds.tenant_id, ds.branch_id, ds.document_type, ds.period_key, ds.last_value,
       (SELECT MAX(CAST(SUBSTRING_INDEX(ss.ticket_number, '-', -1) AS UNSIGNED))
        FROM staff_settlements ss
        WHERE ss.tenant_id = ds.tenant_id AND ss.branch_id = ds.branch_id
          AND ss.ticket_number IS NOT NULL
          AND ss.ticket_number LIKE CONCAT('%-', ds.period_key, '-%')) AS max_ticket_num
FROM document_sequences ds;

-- 3. Old open cash sessions
SELECT '3_old_open_cash_sessions' AS audit;
SELECT id, tenant_id, branch_id, status, opened_at, opened_by_user_id
FROM cash_sessions
WHERE status = 'OPEN'
  AND opened_at < NOW() - INTERVAL 14 HOUR;

-- 4. Old open shifts
SELECT '4_old_open_shifts' AS audit;
SELECT id, tenant_id, branch_id, name, status, business_date, opened_at
FROM official_shifts
WHERE status = 'OPEN'
  AND opened_at < NOW() - INTERVAL 14 HOUR;

-- 5. Old pending orders
SELECT '5_old_pending_orders' AS audit;
SELECT id, tenant_id, branch_id, status, table_label, created_at
FROM orders
WHERE status IN ('OPEN', 'SENT_TO_BAR')
  AND created_at < NOW() - INTERVAL 14 HOUR;

-- 6. Occupied rooms without active piece (if tables exist)
SELECT '6_rooms_occupied_no_active_piece' AS audit;
SELECT r.id, r.tenant_id, r.branch_id, r.code, r.status
FROM rooms r
WHERE r.status = 'OCCUPIED'
  AND NOT EXISTS (
    SELECT 1 FROM room_pieces rp
    WHERE rp.room_id = r.id AND rp.status IN ('ACTIVE', 'RUNNING')
  );

-- 7. PENDING settlements on closed shifts
SELECT '7_pending_settlements_closed_shift' AS audit;
SELECT ss.id, ss.tenant_id, ss.branch_id, ss.status, ss.settlement_type, os.status AS shift_status, os.business_date
FROM staff_settlements ss
JOIN official_shifts os ON os.id = ss.official_shift_id
WHERE ss.status = 'PENDING'
  AND os.status != 'OPEN';

-- 8. PAID settlements without cash_movement
SELECT '8_paid_no_cash_movement' AS audit;
SELECT id, tenant_id, branch_id, settlement_type, ticket_number, paid_at, cash_movement_id
FROM staff_settlements
WHERE status = 'PAID'
  AND cash_movement_id IS NULL;

-- 9. APPLIED fines without settlement link
SELECT '9_applied_fines_no_settlement' AS audit;
SELECT id, tenant_id, branch_id, staff_user_id, status, settlement_id, amount
FROM staff_fines
WHERE status = 'APPLIED'
  AND (settlement_id IS NULL OR settlement_id = 0);

-- 10. Old print jobs
SELECT '10_old_print_jobs' AS audit;
SELECT status, COUNT(*) AS c, MIN(created_at) AS oldest, MAX(created_at) AS newest
FROM print_jobs
GROUP BY status;
SELECT id, tenant_id, branch_id, status, job_type, created_at, error_message
FROM print_jobs
WHERE status IN ('PENDING', 'FAILED')
  AND created_at < NOW() - INTERVAL 14 HOUR
ORDER BY id DESC
LIMIT 20;

-- 11. Active users without branch
SELECT '11_users_no_branch' AS audit;
SELECT id, username, tenant_id, branch_id, status, role_id
FROM users
WHERE status = 'active'
  AND (branch_id IS NULL OR branch_id = 0);

-- 12. Roles missing basic permissions (sample)
SELECT '12_roles_permission_count' AS audit;
SELECT r.id, r.slug, r.name, COUNT(rp.permission_id) AS permission_count
FROM roles r
LEFT JOIN role_permissions rp ON rp.role_id = r.id
GROUP BY r.id, r.slug, r.name
ORDER BY permission_count ASC;

-- Extra: settlement status breakdown
SELECT 'extra_settlements' AS audit;
SELECT tenant_id, branch_id, status, COUNT(*) c FROM staff_settlements GROUP BY tenant_id, branch_id, status;

-- Extra: orders by status
SELECT 'extra_orders' AS audit;
SELECT tenant_id, branch_id, status, COUNT(*) c FROM orders GROUP BY tenant_id, branch_id, status;

-- Extra: cash sessions
SELECT 'extra_cash_sessions' AS audit;
SELECT id, tenant_id, branch_id, status, opened_at, closed_at FROM cash_sessions ORDER BY id;

-- Extra: tenants/branches
SELECT 'extra_tenants' AS audit;
SELECT t.id, t.name, t.slug, t.status, b.id AS branch_id, b.code, b.name AS branch_name
FROM tenants t
LEFT JOIN branches b ON b.tenant_id = t.id;

-- Extra: users for login QA
SELECT 'extra_users_sample' AS audit;
SELECT u.id, u.username, u.name, u.status, r.slug AS role, b.code AS branch_code, t.slug AS tenant
FROM users u
JOIN roles r ON r.id = u.role_id
LEFT JOIN branches b ON b.id = u.branch_id
LEFT JOIN tenants t ON t.id = u.tenant_id
WHERE u.status = 'active'
ORDER BY t.id, r.slug, u.username;
