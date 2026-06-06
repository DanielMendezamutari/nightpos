<?php

return [
    'room_service' => [
        'default_girl_percent' => (float) env('NIGHTPOS_DEFAULT_ROOM_GIRL_PERCENT', 50),
    ],
    'cleaning' => [
        'default_base_amount' => (float) env('NIGHTPOS_DEFAULT_CLEANING_BASE', 30),
        'default_room_amount' => (float) env('NIGHTPOS_DEFAULT_CLEANING_ROOM', 10),
    ],
    'notifications' => [
        'whatsapp_enabled' => (bool) env('NIGHTPOS_WHATSAPP_ENABLED', false),
        'whatsapp_provider' => env('NIGHTPOS_WHATSAPP_PROVIDER', null),
        'whatsapp_phone_cleaning' => env('NIGHTPOS_WHATSAPP_PHONE_CLEANING', null),
        'whatsapp_template_room_due' => env('NIGHTPOS_WHATSAPP_TEMPLATE_ROOM_DUE', 'room_due'),
    ],
];
