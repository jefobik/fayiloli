<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\TenantController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileRequestController;
use App\Http\Controllers\ShareDocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserManagementController;

// ═══════════════════════════════════════════════════════════════════════════
//  CENTRAL-DOMAIN ROUTES  (localhost / 127.0.0.1)
//  All routes here run against the central PostgreSQL database.
// ═══════════════════════════════════════════════════════════════════════════

// ─── Authentication ────────────────────────────────────────────────────────
Auth::routes();

// ─── Tenant Management (Super-Admin) ──────────────────────────────────────
//  Full CRUD for tenants; database provisioning fires automatically via
//  TenancyServiceProvider event listeners.
Route::middleware('auth')->prefix('admin/tenants')->name('tenants.')->group(function () {
    Route::get('/',                             [TenantController::class, 'index'])->name('index');
    Route::get('/create',                       [TenantController::class, 'create'])->name('create');
    Route::post('/',                            [TenantController::class, 'store'])->name('store');
    Route::get('/{tenant}',                     [TenantController::class, 'show'])->name('show');
    Route::get('/{tenant}/edit',                [TenantController::class, 'edit'])->name('edit');
    Route::put('/{tenant}',                     [TenantController::class, 'update'])->name('update');
    Route::delete('/{tenant}',                  [TenantController::class, 'destroy'])->name('destroy');
    Route::post('/{tenant}/domains',            [TenantController::class, 'addDomain'])->name('domains.add');
    Route::delete('/{tenant}/domains',          [TenantController::class, 'removeDomain'])->name('domains.remove');
    Route::patch('/{tenant}/toggle-active',     [TenantController::class, 'toggleActive'])->name('toggle_active');
});

// ─── User Management (tenant-scoped) ──────────────────────────────────────
//  The 'tenant.initialized' guard aborts 403 if tenancy is not active, so
//  this group is only useful when accessed from a tenant domain.
Route::middleware(['auth', 'tenant.initialized'])->group(function () {
    Route::resource('users', UserManagementController::class);
});

// ─── EDMS Application Routes ───────────────────────────────────────────────
Route::middleware('auth')->group(function () {

    // ─── Documents ────────────────────────────────────────────────────────
    Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
    Route::post('/update-visibility', [DocumentController::class, 'updateVisibility'])->name('update.visibility');
    Route::get('/getFiles/{folder}', [DocumentController::class, 'getFiles'])->name('getFiles');
    Route::post('/send-email-document', [DocumentController::class, 'sendDocumentEmail'])->name('send.email');
    Route::get('/getDocumentComments', [DocumentController::class, 'getDocumentComments'])->name('getDocumentComments');
    Route::post('/upload', [DocumentController::class, 'uploadDocumentFiles'])->name('upload');
    Route::post('/change-document', [DocumentController::class, 'changeFile'])->name('changeFile');
    Route::get('/filter-documents-by-tags', [DocumentController::class, 'filterDocumentByTag'])->name('filterDocumentByTag');
    Route::post('/update-document-order', [DocumentController::class, 'updateDocumentOrder'])->name('update.document.order');

    // ─── Users ────────────────────────────────────────────────────────────
    Route::get('/api/users', [UserController::class, 'search'])->name('users.search');

    // ─── File Requests ────────────────────────────────────────────────────
    Route::post('/request-document', [FileRequestController::class, 'store'])->name('fileRequest.store');

    // ─── Folders ─────────────────────────────────────────────────────────
    Route::resource('folders', FolderController::class);
    Route::post('/update-folder-positions', [FolderController::class, 'updateFolderPositions'])->name('folders.updatePositions');
    Route::post('/update-folder-child-positions', [FolderController::class, 'updateFolderChildPositions'])->name('folders.updateChildPositions');
    Route::post('/folders/details', [FolderController::class, 'fetchDetails'])->name('folders.fetchDetails');
    Route::post('/folders/download-zip', [FolderController::class, 'downloadZip'])->name('folders.downloadZip');
    Route::post('/folders/delete', [FolderController::class, 'deleteSelecetdFolder'])->name('folders.deleteSelecetdFolder');

    // ─── Tags ─────────────────────────────────────────────────────────────
    Route::resource('tags', TagController::class);
    Route::get('/search-tags', [TagController::class, 'searchTags'])->name('searchTags');
    Route::get('/add-tag', [TagController::class, 'addTag'])->name('addTag');

    // ─── Workspaces (alias for folders) ──────────────────────────────────
    Route::resource('workspaces', FolderController::class);

    // ─── Notifications ────────────────────────────────────────────────────
    Route::get('/notifications/fetch', [NotificationController::class, 'fetchNotifications'])->name('notifications.fetch');
    Route::post('/notifications/{notification}/dismiss', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');
    Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
    Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');

    // ─── Home / Modules ───────────────────────────────────────────────────
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
    Route::get('/projects', fn() => view('projects.index'))->name('projects.index');
    Route::get('/contacts', fn() => view('contacts.index'))->name('contacts.index');
});

// ─── Share Documents (public — no auth required) ──────────────────────────
Route::get('/{slug?}/share/{id?}/{token?}', [ShareDocumentController::class, 'getSharedDocuments'])->name('getSharedDocuments');
Route::post('/share-document', [ShareDocumentController::class, 'sharedDocuments'])->name('sharedDocuments');
