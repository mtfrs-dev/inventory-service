<?php

// use App\Http\Controllers\Api\AuthWebhookController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ItemAttachmentController;
use App\Http\Controllers\Api\ItemController;
use App\Http\Controllers\Api\ProjectWebhookController;
use App\Http\Controllers\Api\SubcategoryController;
// use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StatusController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix'  => 'v1'], function(){
    Route::apiResource('categories',    CategoryController::class);
    Route::apiResource('subcategories', SubcategoryController::class);
    Route::apiResource('statuses',      StatusController::class);
    
    // ROUTES FOR ITEM METHODS
    Route::controller(ItemController::class)->group(function () {
        // READ ALL
        Route::get('/items',        'index');
        // READ BY ID
        Route::get('/items/{item}', 'show');
        // CREATE ONE
        Route::post('/items',       'store');
        // GENERATE
        Route::post('/items/generate', 'generate');

        // BULK UPDATE STATUS
        Route::patch('/items/status', 'bulkUpdateStatus');
        // BULK UPDATE SERIAL NUMBERS
        Route::patch('/items', 'bulkUpdateSerialNumber');
        // UPDATE
        Route::patch('/items/{item}', 'update');
        // DELETE
        Route::delete('/items/{item}', 'destroy');
    });

    Route::post('/items/{item}/attachments',                        [ItemAttachmentController::class, 'store']);
    Route::patch('/items/{item}/attachments/{attachment}',          [ItemAttachmentController::class, 'update']);
    Route::delete('/items/{item}/attachments/{attachment}',         [ItemAttachmentController::class, 'destroy']);
});

Route::middleware(['throttle:service-webhooks', 'service.trust'])
    ->prefix('webhooks/projects')
    ->group(function () {
        Route::post('/upserted', [ProjectWebhookController::class, 'upserted']);
        Route::post('/deleted',  [ProjectWebhookController::class, 'deleted']);
    });

// Route::middleware(['throttle:service-webhooks', 'service.trust'])
//     ->prefix('webhooks/auth')
//     ->group(function () {
//         Route::post('/token-revoked', [AuthWebhookController::class, 'tokenRevoked']);
//         Route::post('/user-deactivated', [AuthWebhookController::class, 'userDeactivated']);
//     });

// Route::middleware('jwt.auth')->prefix('v1')->group(function () {
//     // ROUTES FOR ITEM CRUD METHODS
//     Route::controller(ItemController::class)->group(function () {
//         // READ ALL / ONE
//         Route::middleware(['scope:inventory:read', 'permission:item.view'])->group(function () {
//             Route::get('/items',        'index');
//             Route::get('/items/{item}', 'show');
//         });
//         // CREATE & GENERATE
//         Route::middleware(['scope:inventory:write', 'permission:item.create'])->group(function () {
//             Route::post('/items',                   'store');
//             Route::post('/items/jobs/generate',     'generate'); // USING JOB
//         });
//         Route::middleware(['scope:inventory:write', 'permission:item.create'])->post('/items', 'store');
//         // UPDATE
//         Route::middleware(['scope:inventory:write', 'permission:item.update'])->patch('/items/{item}', 'update');
//         // DELETE
//         Route::middleware(['scope:inventory:write', 'permission:item.delete'])->delete('/items/{item}', 'destroy');
//     });

//     // QR CODE
//     Route::middleware(['scope:inventory:read', 'permission:item.view'])
//         ->get('/items/{item}/qr-code', [ItemController::class, 'qrCode']);

//     Route::middleware(['scope:inventory:read', 'permission:status.view'])
//         ->get('/items/{item}/status-logs', [StatusController::class, 'index']);

//     Route::middleware(['scope:inventory:write', 'permission:status.update'])
//         ->post('/items/{item}/status', [StatusController::class, 'update']);

//     Route::middleware(['throttle:reports', 'scope:inventory:reports', 'permission:report.generate'])
//         ->post('/reports', [ReportController::class, 'generate']);

//     Route::middleware(['scope:inventory:read', 'permission:report.view'])
//         ->get('/reports', [ReportController::class, 'index']);

//     Route::middleware(['role:super_admin,inventory_manager', 'permission:audit.view'])
//         ->get('/audit-logs', [AuditController::class, 'index']);
// });