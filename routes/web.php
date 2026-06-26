<?php

use App\Http\Controllers\Admin\ApplicationController as AdminApplicationController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\FlowManagementController as AdminFlowManagementController;
use App\Http\Controllers\Admin\SettlementManagementController as AdminSettlementManagementController;
use App\Http\Controllers\ApplicationFormController;
use App\Http\Controllers\Master\MasterDataController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/applications/create');

Route::get('/applications/create', [ApplicationFormController::class, 'create'])->name('applications.create');
Route::get('/applications/management-company-suggestions', [ApplicationFormController::class, 'managementCompanySuggestions'])->name('applications.management-company-suggestions');
Route::post('/applications', [ApplicationFormController::class, 'store'])->name('applications.store');
Route::get('/applications/complete/{application}', [ApplicationFormController::class, 'complete'])->name('applications.complete');

Route::redirect('/customers/create', '/applications/create');
Route::redirect('/customers/{customer}/applications/create', '/applications/create');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/applications', [AdminApplicationController::class, 'index'])->name('applications.index');
    Route::patch('/applications/{application}/flags', [AdminApplicationController::class, 'updateFlags'])->name('applications.update-flags');
    Route::patch('/applications/{application}/fields', [AdminApplicationController::class, 'updateField'])->name('applications.update-field');
    Route::get('/applications/{application}/customer', [AdminCustomerController::class, 'show'])->name('applications.customer.show');
    Route::patch('/applications/{application}/customer', [AdminCustomerController::class, 'update'])->name('applications.customer.update');

    Route::redirect('/screening-completions', '/admin/applications');

    Route::get('/flow-managements', [MasterDataController::class, 'index'])
        ->defaults('table', 'flow_managements')
        ->name('flow-managements.index');
    Route::get('/customers', [MasterDataController::class, 'index'])
        ->defaults('table', 'customers')
        ->name('customers.index');

    Route::patch('/flow-managements/{flowManagement}/fields', [AdminFlowManagementController::class, 'updateField'])->name('flow-managements.update-field');

    Route::get('/settlement-managements', [AdminSettlementManagementController::class, 'index'])->name('settlement-managements.index');
    Route::patch('/settlement-managements/{settlementManagement}/fields', [AdminSettlementManagementController::class, 'updateField'])->name('settlement-managements.update-field');
});

Route::prefix('master')->name('master.')->group(function () {
    Route::redirect('/', '/master/data');
    Route::get('/data', [MasterDataController::class, 'index'])->name('data.index');
    Route::patch('/data/{table}/{record}/fields', [MasterDataController::class, 'updateField'])->name('data.update-field');
});
