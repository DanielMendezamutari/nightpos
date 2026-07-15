SELECT cs.id,
       cs.status,
       u.name AS opened_by_name,
       cs.official_shift_id,
       cs.opened_at,
       cs.closed_at,
       cs.opening_amount,
       COALESCE(s.total_sales_amount,0) AS sales_total,
       COALESCE(s.sales_count,0) AS sales_count,
       COALESCE(s.cash_total,0) AS cash_total,
       COALESCE(s.qr_total,0) AS qr_total,
       COALESCE(s.card_total,0) AS card_total,
       COALESCE(s.mixed_total,0) AS mixed_total,
       COALESCE(rs.room_services_total,0) AS room_services_total,
       COALESCE(rb.bracelets_total,0) AS bracelets_total,
       COALESCE(sh.shows_total,0) AS shows_total
FROM cash_sessions cs
LEFT JOIN users u ON u.id = cs.opened_by_user_id
LEFT JOIN (
    SELECT cash_session_id,
           SUM(total) AS total_sales_amount,
           COUNT(*) AS sales_count,
           SUM(CASE WHEN payment_mode='CASH' THEN total ELSE 0 END) AS cash_total,
           SUM(CASE WHEN payment_mode='QR' THEN total ELSE 0 END) AS qr_total,
           SUM(CASE WHEN payment_mode='CARD' THEN total ELSE 0 END) AS card_total,
           SUM(CASE WHEN payment_mode='MIXED' THEN total ELSE 0 END) AS mixed_total
    FROM sales
    WHERE status='PAID'
    GROUP BY cash_session_id
) s ON s.cash_session_id = cs.id
LEFT JOIN (
    SELECT cash_session_id, SUM(total_amount) AS room_services_total
    FROM room_services
    WHERE status IN ('FINISHED','PAID')
    GROUP BY cash_session_id
) rs ON rs.cash_session_id = cs.id
LEFT JOIN (
    SELECT cash_session_id, SUM(total_amount) AS bracelets_total
    FROM bracelets
    GROUP BY cash_session_id
) rb ON rb.cash_session_id = cs.id
LEFT JOIN (
    SELECT cash_session_id, SUM(total_amount) AS shows_total
    FROM shows
    GROUP BY cash_session_id
) sh ON sh.cash_session_id = cs.id
WHERE cs.id IN (36,37)
ORDER BY cs.id;
