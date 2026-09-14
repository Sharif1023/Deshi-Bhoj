<?php
use App\Core\DB;
// Idempotent, additive upgrade. No orders, uploads or existing content are deleted.
DB::run("UPDATE settings SET value='0' WHERE setting_key='ordering.minimum'");
DB::run("INSERT INTO order_events (order_id,status,created_at) SELECT o.id,o.status,CASE WHEN o.status='Pending' THEN o.created_at ELSE ? END FROM orders o WHERE NOT EXISTS (SELECT 1 FROM order_events ev WHERE ev.order_id=o.id)", [date('Y-m-d H:i:s')]);
