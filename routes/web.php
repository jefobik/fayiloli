<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TagController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FileRequestController;
use App\Http\Controllers\ShareDocumentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\UserManagementController;

// ─── User Management (tenant-scoped) ─────────────────────────────────────────
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::resource('users', UserManagementController::class);
});

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

Auth::routes();

// ─── Share Documents (public) ─────────────────────────────────────────────
Route::get('/{slug?}/share/{id?}/{token?}', [ShareDocumentController::class, 'getSharedDocuments'])->name('getSharedDocuments');
Route::post('/share-document', [ShareDocumentController::class, 'sharedDocuments'])->name('sharedDocuments');
