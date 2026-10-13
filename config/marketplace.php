<?php

return [

    'platform_commission_rate' => (float) env(
        'PLATFORM_COMMISSION_RATE',
        0.10
    ),

    'pending_hold_minutes' => (int) env('PENDING_HOLD_MINUTES', 60),

];
