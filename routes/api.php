<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\UsageController;
use Illuminate\Support\Facades\Route;

Route::post('/usage', [UsageController::class, 'store'])->middleware('throttle:usage');

Route::post('/subscriptions', [SubscriptionController::class, 'store']);
Route::post('/subscriptions/{subscription}/change-plan', [SubscriptionController::class, 'changePlan']);

Route::get('/merchants/{merchant}/dashboard', [DashboardController::class, 'show']);
