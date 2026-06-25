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
    ],
];
