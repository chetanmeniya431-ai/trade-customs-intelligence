<?php

use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\ReportPdfController;
use App\Http\Controllers\ShipmentDocumentDownloadController;
use App\Http\Controllers\TariffDocumentDownloadController;
use App\Livewire\Auth\ForgotPassword;
use App\Livewire\Auth\Login;
use App\Livewire\Auth\ResetPassword;
use App\Livewire\Clients\Index as ClientsIndex;
use App\Livewire\SuperAdmin\ContactRequests as SuperAdminContacts;
use App\Livewire\Dashboard;
use App\Livewire\HsCode\Finder as HsCodeFinder;
use App\Livewire\Insights\Index as InsightsIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Settings\Users as SettingsUsers;
use App\Livewire\Shipments\Create as ShipmentsCreate;
use App\Livewire\Shipments\Import as ShipmentsImportPage;
use App\Livewire\Shipments\Index as ShipmentsIndex;
use App\Livewire\Shipments\Show as ShipmentsShow;
use App\Livewire\Signals\Index as SignalsIndex;
use App\Livewire\TariffDocuments\Index as TariffDocumentsIndex;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('dashboard'));

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
    Route::get('/forgot-password', ForgotPassword::class)->name('password.request');
    Route::get('/reset-password/{token}', ResetPassword::class)->name('password.reset');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', LogoutController::class)->name('logout');

    Route::get('/dashboard', Dashboard::class)->name('dashboard');

    Route::get('/shipments', ShipmentsIndex::class)->name('shipments.index');
    Route::get('/shipments/import', ShipmentsImportPage::class)
        ->name('shipments.import')
        ->middleware('role:Customs Broker,Import/Export Coordinator');
    Route::get('/shipments/create', ShipmentsCreate::class)
        ->name('shipments.create')
        ->middleware('role:Customs Broker,Import/Export Coordinator');
    Route::get('/shipments/{shipment}', ShipmentsShow::class)->name('shipments.show');
    Route::get('/shipment-documents/{document}/download', ShipmentDocumentDownloadController::class)
        ->name('shipments.documents.download');

    Route::get('/hs-code-finder', HsCodeFinder::class)->name('hs-code-finder');

    Route::get('/tariff-documents', TariffDocumentsIndex::class)
        ->name('tariff-documents.index')
        ->middleware('role:Customs Broker,Import/Export Coordinator');
    Route::get('/tariff-documents/{tariffDocument}/download', TariffDocumentDownloadController::class)
        ->name('tariff-documents.download');

    Route::get('/signals', SignalsIndex::class)->name('signals.index');
    Route::get('/insights', InsightsIndex::class)->name('insights.index');
    Route::get('/reports', ReportsIndex::class)->name('reports.index');
    Route::get('/reports/pdf', ReportPdfController::class)->name('reports.pdf');

    Route::get('/clients', ClientsIndex::class)
        ->name('clients.index')
        ->middleware('role:Customs Broker,Import/Export Coordinator');

    Route::get('/settings/users', SettingsUsers::class)
        ->name('settings.users')
        ->middleware('role:Customs Broker');

    Route::get('/super-admin/contacts', SuperAdminContacts::class)
        ->name('super-admin.contacts')
        ->middleware('role:Super Admin');
});
