<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Penalty Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic penalty calculation for late repayments
    |
    */

    'tolerance_days' => env('PENALTY_TOLERANCE_DAYS', 30),
    'penalty_monthly_rate' => env('PENALTY_MONTHLY_RATE', 10.0), // 1% par mois (12% annuel ÷ 12)
    'penalty_calculation_base' => env('PENALTY_CALCULATION_BASE', 'remaining_amount'), // 'remaining_amount' or 'original_amount'
    'auto_calculate' => env('PENALTY_AUTO_CALCULATE', true),
    'max_penalty_percentage' => env('MAX_PENALTY_PERCENTAGE', 10.0),
];
