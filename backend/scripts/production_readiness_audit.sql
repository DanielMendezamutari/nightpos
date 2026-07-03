-- NightPOS Production Readiness Audit SQL (read-only)
SET SESSION group_concat_max_len = 100000;

SELECT '=== COUNTS ===' AS section;
SELECT 'tenants' t, COUNT(*) c FROM tenants
UNION SELECT 'branches', COUNT(*) FROM branches
UNION SELECT 'users', COUNT(*) FROM users
UNION SELECT 'official_shifts', COUNT(*) FROM official_shifts
UNION SELECT 'cash_sessions', COUNT(*) FROM cash_sessions
UNION SELECT 'cash_movements', COUNT(*) FROM cash_movements
UNION SELECT 'orders', COUNT(*) FROM orders
UNION SELECT 'order_items', COUNT(*) FROM order_items
UNION SELECT 'sales', COUNT(*) FROM sales
UNION SELECT 'sale_items', COUNT(*) FROM sale_items
UNION SELECT 'sale_payments', COUNT(*) FROM sale_payments
UNION SELECT 'staff_settlements', COUNT(*) FROM staff_settlements
UNION SELECT 'staff_fines', COUNT(*) FROM staff_fines
UNION SELECT 'room_services', COUNT(*) FROM room_services
UNION SELECT 'rooms', COUNT(*) FROM rooms
UNION SELECT 'print_jobs', COUNT(*) FROM print_jobs
UNION SELECT 'print_devices', COUNT(*) FROM print_devices
UNION SELECT 'document_sequences', COUNT(*) FROM document_sequences;

SELECT '=== TENANTS ===' AS section;
SELECT id, name, slug, status, plan_name, subscription_starts_at, subscription_ends_at FROM tenants;

SELECT '=== AUTH: users active without branch (non superadmin) ===' AS section;
SELECT u.id, u.username, r.slug role FROM users u
JOIN roles r ON r.id=u.role_id
LEFT JOIN user_branch_access uba ON uba.user_id=u.id
WHERE u.status='active' AND r.slug!='super_admin' AND uba.id IS NULL;

SELECT '=== AUTH: users with branch_id outside tenant ===' AS section;
SELECT u.id, u.username, u.tenant_id, u.branch_id, b.tenant_id b_tenant
FROM users u JOIN branches b ON b.id=u.branch_id
WHERE u.branch_id IS NOT NULL AND u.tenant_id != b.tenant_id;

SELECT '=== CASH: open sessions ===' AS section;
SELECT cs.id, cs.tenant_id, cs.branch_id, cs.status, cs.opened_at, cs.opening_amount,
       TIMESTAMPDIFF(HOUR, cs.opened_at, NOW()) hours_open
FROM cash_sessions cs WHERE cs.status='OPEN';

SELECT '=== CASH: open sessions >14h ===' AS section;
SELECT * FROM cash_sessions WHERE status='OPEN' AND opened_at < NOW() - INTERVAL 14 HOUR;

SELECT '=== CASH: movements without session ===' AS section;
SELECT cm.id, cm.cash_session_id FROM cash_movements cm
LEFT JOIN cash_sessions cs ON cs.id=cm.cash_session_id WHERE cs.id IS NULL LIMIT 20;

SELECT '=== CASH: closed session with null closing ===' AS section;
SELECT id, tenant_id, branch_id, status, opened_at, closed_at, closing_amount, expected_cash
FROM cash_sessions WHERE status='CLOSED' AND (closed_at IS NULL OR closing_amount IS NULL) LIMIT 20;

SELECT '=== SHIFTS: open shifts ===' AS section;
SELECT os.id, os.tenant_id, os.branch_id, os.name, os.status, os.business_date, os.opened_at,
       TIMESTAMPDIFF(HOUR, os.opened_at, NOW()) hours_open
FROM official_shifts os WHERE os.status='OPEN';

SELECT '=== SHIFTS: multiple OPEN per branch ===' AS section;
SELECT tenant_id, branch_id, COUNT(*) c FROM official_shifts WHERE status='OPEN'
GROUP BY tenant_id, branch_id HAVING c > 1;

SELECT '=== SHIFTS: open >14h ===' AS section;
SELECT * FROM official_shifts WHERE status='OPEN' AND opened_at < NOW() - INTERVAL 14 HOUR;

SELECT '=== ORDERS: by status tenant ===' AS section;
SELECT tenant_id, branch_id, status, COUNT(*) c FROM orders GROUP BY tenant_id, branch_id, status;

SELECT '=== ORDERS: OPEN/SENT old ===' AS section;
SELECT id, tenant_id, branch_id, status, table_label, official_shift_id, created_at
FROM orders WHERE status IN ('OPEN','SENT_TO_BAR') AND created_at < NOW() - INTERVAL 14 HOUR;

SELECT '=== ORDERS: BILLED without sale ===' AS section;
SELECT o.id, o.tenant_id, o.branch_id, o.status, o.sale_id FROM orders o
WHERE o.status='BILLED' AND (o.sale_id IS NULL OR o.sale_id=0) LIMIT 20;

SELECT '=== ORDERS: shift CLOSED but order OPEN/SENT/BILLED ===' AS section;
SELECT o.id, o.status, o.official_shift_id, os.status shift_st, os.business_date
FROM orders o JOIN official_shifts os ON os.id=o.official_shift_id
WHERE os.status='CLOSED' AND o.status IN ('OPEN','SENT_TO_BAR','BILLED') LIMIT 30;

SELECT '=== SALES: without items ===' AS section;
SELECT s.id, s.tenant_id, s.total_amount FROM sales s
LEFT JOIN sale_items si ON si.sale_id=s.id WHERE si.id IS NULL LIMIT 20;

SELECT '=== SALES: without payments ===' AS section;
SELECT s.id, s.tenant_id, s.total_amount, s.status FROM sales s
LEFT JOIN sale_payments sp ON sp.sale_id=s.id WHERE sp.id IS NULL LIMIT 20;

SELECT '=== SALES: payment sum mismatch ===' AS section;
SELECT s.id, s.tenant_id, s.total_amount,
       COALESCE(SUM(sp.amount),0) paid_sum,
       s.total_amount - COALESCE(SUM(sp.amount),0) diff
FROM sales s LEFT JOIN sale_payments sp ON sp.sale_id=s.id
GROUP BY s.id, s.tenant_id, s.total_amount
HAVING ABS(diff) > 0.01 LIMIT 20;

SELECT '=== SETTLEMENTS: PENDING on closed shift ===' AS section;
SELECT ss.id, ss.tenant_id, ss.branch_id, ss.settlement_type, ss.net_amount, ss.official_shift_id, os.status
FROM staff_settlements ss JOIN official_shifts os ON os.id=ss.official_shift_id
WHERE ss.status='PENDING' AND os.status!='OPEN';

SELECT '=== SETTLEMENTS: PAID no cash_movement ===' AS section;
SELECT id, tenant_id, branch_id, ticket_number FROM staff_settlements
WHERE status='PAID' AND cash_movement_id IS NULL;

SELECT '=== SETTLEMENTS: duplicate tickets ===' AS section;
SELECT tenant_id, branch_id, ticket_number, COUNT(*) c FROM staff_settlements
WHERE ticket_number IS NOT NULL GROUP BY tenant_id, branch_id, ticket_number HAVING c>1;

SELECT '=== SETTLEMENTS: doc sequence lag ===' AS section;
SELECT ds.tenant_id, ds.branch_id, ds.period_key, ds.last_value,
  (SELECT MAX(CAST(SUBSTRING_INDEX(ss.ticket_number,'-',-1) AS UNSIGNED))
   FROM staff_settlements ss
   WHERE ss.tenant_id=ds.tenant_id AND ss.branch_id=ds.branch_id
     AND ss.ticket_number LIKE CONCAT('%-',ds.period_key,'-%')) max_ticket
FROM document_sequences ds WHERE ds.document_type='SETTLEMENT_PAYMENT';

SELECT '=== FINES: pending old ===' AS section;
SELECT id, tenant_id, status, amount, created_at FROM staff_fines WHERE status='PENDING';

SELECT '=== FINES: applied no settlement ===' AS section;
SELECT id, tenant_id, status, applied_settlement_id FROM staff_fines
WHERE status='APPLIED' AND applied_settlement_id IS NULL;

SELECT '=== ROOMS: status summary ===' AS section;
SELECT tenant_id, branch_id, status, COUNT(*) c FROM rooms GROUP BY tenant_id, branch_id, status;

SELECT '=== ROOMS: OCCUPIED ===' AS section;
SELECT * FROM rooms WHERE status='OCCUPIED';

SELECT '=== ROOM SERVICES: active ===' AS section;
SELECT rs.id, rs.tenant_id, rs.room_id, rs.status, rs.started_at, rs.expected_ends_at, r.status room_st
FROM room_services rs LEFT JOIN rooms r ON r.id=rs.room_id
WHERE rs.status IN ('ACTIVE','RUNNING','PENDING') OR (rs.status='ACTIVE' AND rs.ended_at IS NOT NULL);

SELECT '=== ROOM SERVICES: finished but room not available ===' AS section;
SELECT rs.id, rs.room_id, rs.status rs_st, r.status room_st
FROM room_services rs JOIN rooms r ON r.id=rs.room_id
WHERE rs.status='FINISHED' AND r.status NOT IN ('AVAILABLE','CLEANING') LIMIT 20;

SELECT '=== PRINT JOBS summary ===' AS section;
SELECT status, COUNT(*) c, MIN(created_at) oldest, MAX(created_at) newest FROM print_jobs GROUP BY status;

SELECT '=== PRINT JOBS pending/failed ===' AS section;
SELECT id, tenant_id, branch_id, type, status, source_type, source_id, attempts, created_at, LEFT(last_error,80) err
FROM print_jobs WHERE status IN ('PENDING','FAILED') ORDER BY id;

SELECT '=== PRINT DEVICES ===' AS section;
SELECT id, tenant_id, branch_id, name, status, enabled, last_seen_at, last_error FROM print_devices;

SELECT '=== ORPHAN: order_items no order ===' AS section;
SELECT oi.id FROM order_items oi LEFT JOIN orders o ON o.id=oi.order_id WHERE o.id IS NULL LIMIT 10;

SELECT '=== ORPHAN: settlements bad shift ===' AS section;
SELECT ss.id FROM staff_settlements ss LEFT JOIN official_shifts os ON os.id=ss.official_shift_id WHERE os.id IS NULL;

SELECT '=== SaaS: branches per tenant ===' AS section;
SELECT t.slug, b.id, b.code, b.name, b.status FROM tenants t JOIN branches b ON b.tenant_id=t.id ORDER BY t.id, b.id;

SELECT '=== SaaS: duplicate branch codes per tenant ===' AS section;
SELECT tenant_id, code, COUNT(*) c FROM branches GROUP BY tenant_id, code HAVING c>1;

SELECT '=== INDEX: staff_settlements ticket ===' AS section;
SHOW INDEX FROM staff_settlements WHERE Key_name LIKE '%ticket%';

SELECT '=== INDEX: document_sequences ===' AS section;
SHOW INDEX FROM document_sequences;
