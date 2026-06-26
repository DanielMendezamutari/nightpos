<?php

return [
    'room_service' => [
        'default_girl_percent' => (float) env('NIGHTPOS_DEFAULT_ROOM_GIRL_PERCENT', 60),
    ],
    'cleaning' => [
        'default_base_amount' => (float) env('NIGHTPOS_DEFAULT_CLEANING_BASE', 30),
        'default_room_amount' => (float) env('NIGHTPOS_DEFAULT_CLEANING_ROOM', 10),
    ],
    'girl_unique_cleaning' => [
        'threshold' => (float) env('NIGHTPOS_GIRL_UNIQUE_CLEANING_THRESHOLD', 100),
        'amount' => (float) env('NIGHTPOS_GIRL_UNIQUE_CLEANING_AMOUNT', 10),
    ],
    'notifications' => [
        'whatsapp_enabled' => (bool) env('NIGHTPOS_WHATSAPP_ENABLED', false),
        'whatsapp_provider' => env('NIGHTPOS_WHATSAPP_PROVIDER', null),
        'whatsapp_phone_cleaning' => env('NIGHTPOS_WHATSAPP_PHONE_CLEANING', null),
        'whatsapp_template_room_due' => env('NIGHTPOS_WHATSAPP_TEMPLATE_ROOM_DUE', 'room_due'),
    ],
    'printing' => [
        'ticket_footer' => env('NIGHTPOS_PRINT_TICKET_FOOTER', 'Powered by Ribersoft · WhatsApp 67369293'),
        'agent_online_seconds' => (int) env('NIGHTPOS_AGENT_ONLINE_SECONDS', 120),
    ],
    'platform_operations' => [
        'backend_version' => env('NIGHTPOS_BACKEND_VERSION', '1.0.0'),
        'cache_seconds' => (int) env('NIGHTPOS_OPS_CACHE_SECONDS', 60),
        'metrics_lookback_days' => (int) env('NIGHTPOS_OPS_METRICS_LOOKBACK_DAYS', 90),
        'activity_online_minutes' => (int) env('NIGHTPOS_OPS_ACTIVITY_ONLINE_MINUTES', 15),
        'activity_warning_hours' => (int) env('NIGHTPOS_OPS_ACTIVITY_WARNING_HOURS', 2),
        'activity_offline_hours' => (int) env('NIGHTPOS_OPS_ACTIVITY_OFFLINE_HOURS', 24),
        'cash_session_warning_hours' => (int) env('NIGHTPOS_OPS_CASH_WARNING_HOURS', 14),
        'shift_warning_hours' => (int) env('NIGHTPOS_OPS_SHIFT_WARNING_HOURS', 14),
        'no_sales_warning_days' => (int) env('NIGHTPOS_OPS_NO_SALES_WARNING_DAYS', 2),
        'print_failures_warning_count' => (int) env('NIGHTPOS_OPS_PRINT_FAIL_WARNING', 3),
    ],
];
