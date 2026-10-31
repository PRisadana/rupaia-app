<?php

use Illuminate\Support\Facades\Route;

Route::view('/about', 'about');

// Increment 1: Core System
require __DIR__ . '/auth.php';
require __DIR__ . '/modules/account.php';
require __DIR__ . '/modules/content.php';
require __DIR__ . '/modules/folder.php';
require __DIR__ . '/modules/license.php';
require __DIR__ . '/modules/showcase.php';
require __DIR__ . '/modules/catalog.php';
require __DIR__ . '/modules/policy.php';

// Increment 2: Transaction System
require __DIR__ . '/modules/cart.php';
require __DIR__ . '/modules/checkout.php';
require __DIR__ . '/modules/payment.php';
require __DIR__ . '/modules/kyc.php';
require __DIR__ . '/modules/bank-account.php';
require __DIR__ . '/modules/payout.php';
require __DIR__ . '/modules/distribution.php';
require __DIR__ . '/modules/report.php';
require __DIR__ . '/modules/admin.php';

// Increment 3: Advanced Features
require __DIR__ . '/modules/editing.php';
require __DIR__ . '/modules/analytics.php';
require __DIR__ . '/modules/rating.php';
