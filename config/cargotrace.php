<?php

return [

    'delay_threshold_hours' => env('CARGOTRACE_DELAY_THRESHOLD', 12),

    'primary_corridor' => 'Southeast Asia -> Europe',

    'primary_kpi' => 'On-Time Delivery Rate',

    'valid_statuses' => [
        'CREATED',
        'BOOKED',
        'PICKED_UP',
        'IN_TRANSIT',
        'DELIVERED',
    ],

    'exception_types' => [
        'CUSTOMS_HOLD',
        'WEATHER_DELAY',
        'PORT_CONGESTION',
        'DAMAGED',
        'MISROUTED',
        'DOCUMENTATION_ISSUE',
    ],

    'exception_statuses' => [
        'OPEN',
        'INVESTIGATING',
        'RESOLVED',
    ],

];
