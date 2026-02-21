<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileRequestController;
use App\Http\Controllers\ShareDocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserManagementController;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| These routes are served exclusively from tenant domains.
|
| InitializeTenancyByDomain is NOT listed here because it is already
| prepended to the global 'web' middleware group in bootstrap/app.php.
| It self-skips for central domains (127.0.0.1, localhost) via the
| tenancy.central_domains config, so there is zero overhead on the
| super-admin side.  On tenant domains it switches the DB connection
| before any controller or auth guard runs.
|
| Auth::routes() is also NOT repeated here; the single registration in
| routes/web.php covers both central and tenant auth.  On a tenant domain,
| the global InitializeTenancyByDomain switches the DB connection before
| the auth guard runs, so credentials are validated against the correct
| tenant database.
|
| PreventAccessFromCentralDomains is applied only to EDMS routes so that
| the super-admin on 127.0.0.1 cannot accidentally browse tenant data.
|
| EnsureModuleEnabled ('tenant.module:<name>') gates each route group
| by checking the tenant's settings.modules JSONB array. Tenants without
| an explicit module list fall back to TenantModule::defaults().
|
*/

Route::middleware(['web', PreventAccessFromCentralDomains::class])->group(function () {

    // ─── User Management (tenant-admin only) ──────────────────────────────
    Route::middleware(['auth', 'tenant.module:users'])->group(function () {
        Route::resource('users', UserManagementController::class);
    });

    // ─── EDMS Application ──────────────────────────────────────────────────
    Route::middleware('auth')->group(function () {

        // ── Dashboard / Home ───────────────────────────────────────────────
        // The home route acts as the tenant landing page; it is not gated
        // behind a specific module so users always have somewhere to land.
        Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

        // ── Documents ──────────────────────────────────────────────────────
        Route::middleware('tenant.module:documents')->group(function () {
            Route::get('/', [DocumentController::class, 'index'])->name('documents.index');
            Route::post('/update-visibility', [DocumentController::class, 'updateVisibility'])->name('update.visibility');
            Route::get('/getFiles/{folder}', [DocumentController::class, 'getFiles'])->name('getFiles');
            Route::post('/send-email-document', [DocumentController::class, 'sendDocumentEmail'])->name('send.email');
            Route::get('/getDocumentComments', [DocumentController::class, 'getDocumentComments'])->name('getDocumentComments');
            Route::post('/upload', [DocumentController::class, 'uploadDocumentFiles'])->name('upload');
            Route::post('/change-document', [DocumentController::class, 'changeFile'])->name('changeFile');
            Route::get('/filter-documents-by-tags', [DocumentController::class, 'filterDocumentByTag'])->name('filterDocumentByTag');
            Route::post('/update-document-order', [DocumentController::class, 'updateDocumentOrder'])->name('update.document.order');
        });

        // ── Users API (used by document/share pickers — gated with documents) ──
        Route::middleware('tenant.module:documents')->group(function () {
            Route::get('/api/users', [UserController::class, 'search'])->name('users.search');
        });

        // ── Folders ────────────────────────────────────────────────────────
        Route::middleware('tenant.module:folders')->group(function () {
            Route::resource('folders', FolderController::class);
            Route::post('/update-folder-positions', [FolderController::class, 'updateFolderPositions'])->name('folders.updatePositions');
            Route::post('/update-folder-child-positions', [FolderController::class, 'updateFolderChildPositions'])->name('folders.updateChildPositions');
            Route::post('/folders/details', [FolderController::class, 'fetchDetails'])->name('folders.fetchDetails');
            Route::post('/folders/download-zip', [FolderController::class, 'downloadZip'])->name('folders.downloadZip');
            Route::post('/folders/delete', [FolderController::class, 'deleteSelecetdFolder'])->name('folders.deleteSelecetdFolder');
        });

        // ── Tags ───────────────────────────────────────────────────────────
        Route::middleware('tenant.module:tags')->group(function () {
            Route::resource('tags', TagController::class);
            Route::get('/search-tags', [TagController::class, 'searchTags'])->name('searchTags');
            Route::get('/add-tag', [TagController::class, 'addTag'])->name('addTag');
        });

        // ── File Requests ──────────────────────────────────────────────────
        Route::middleware('tenant.module:file_requests')->group(function () {
            Route::post('/request-document', [FileRequestController::class, 'store'])->name('fileRequest.store');
        });

        // ── Notifications ──────────────────────────────────────────────────
        Route::middleware('tenant.module:notifications')->group(function () {
            Route::get('/notifications/fetch', [NotificationController::class, 'fetchNotifications'])->name('notifications.fetch');
            Route::post('/notifications/{notification}/dismiss', [NotificationController::class, 'dismiss'])->name('notifications.dismiss');
            Route::get('/notifications/{notification}', [NotificationController::class, 'show'])->name('notifications.show');
            Route::post('/notifications', [NotificationController::class, 'store'])->name('notifications.store');
        });

        // ── Projects ───────────────────────────────────────────────────────
        Route::middleware('tenant.module:projects')->group(function () {
            Route::get('/projects', fn () => view('projects.index'))->name('projects.index');
        });

        // ── Contacts ───────────────────────────────────────────────────────
        Route::middleware('tenant.module:contacts')->group(function () {
            Route::get('/contacts', fn () => view('contacts.index'))->name('contacts.index');
        });
    });

    // ─── Document Sharing (public — auth not required, shares module gated) ──
    Route::middleware('tenant.module:shares')->group(function () {
        Route::get('/{slug?}/share/{id?}/{token?}', [ShareDocumentController::class, 'getSharedDocuments'])->name('getSharedDocuments');
        Route::post('/share-document', [ShareDocumentController::class, 'sharedDocuments'])->name('sharedDocuments');
    });
});
