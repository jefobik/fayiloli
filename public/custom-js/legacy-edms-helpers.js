/**
 * ─────────────────────────────────────────────────────────────────────────────
 * Legacy EDMS Helper Functions
 * ─────────────────────────────────────────────────────────────────────────────
 * This file contains the restored legacy JavaScript functions used across the 
 * Fayiloli application. These are maintained for backward compatibility 
 * during the migration to Livewire/Alpine.js.
 * ─────────────────────────────────────────────────────────────────────────────
 */

// ─── Modal Helpers (Bootstrap 5 Bridge) ──────────────────────────────────────
function openModal(id) {
    var $m = jQuery('#' + id);
    if ($m.length === 0) return;
    $m.find('.error-message').html('');
    $m.find('.invalid-feedback').remove();
    $m.find('.is-invalid').removeClass('is-invalid');
    var form = $m.find('#FolderCreateForm')[0];
    if (form) form.reset();
    $m.find('#renderFolderCategoryHtml').empty();
    
    if (typeof bootstrap !== 'undefined') {
        var modal = bootstrap.Modal.getOrCreateInstance($m[0]);
        modal.show();
    }
}

function closeModal(id) {
    var $m = jQuery('#' + id);
    if ($m.length === 0) return;
    if (typeof bootstrap !== 'undefined') {
        var modal = bootstrap.Modal.getInstance($m[0]);
        if (modal) modal.hide();
    }
    $m.find('.error-message').html('');
}

// ─── Document Management Helpers ─────────────────────────────────────────────
function generateRandomToken() {
    var ts = new Date().getTime().toString(16);
    return ts + '_' + Math.random().toString(36).substring(2, 10);
}

function copyUrl() {
    var el = document.getElementById('sharedUrlId');
    if (!el) return;
    el.select(); el.setSelectionRange(0, 99999);
    document.execCommand('copy');
    el.blur();
    if (typeof edmsToast === 'function') {
        edmsToast('URL copied to clipboard!', 'success');
    }
}

function sendEmail(formId) {
    var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var formData = new FormData(document.getElementById(formId));
    formData.append('_token', csrfToken);
    formData.append('title', document.getElementById('documentTitle')?.value);
    formData.append('folder_id', localStorage.getItem('selectedFolderId'));
    formData.append('document_id', localStorage.getItem('selectedDocumentId'));

    fetch('/send-email', {
        method: 'POST',
        body: formData,
        headers: { 'X-CSRF-TOKEN': csrfToken }
    })
    .then(res => res.json())
    .then(r => {
        document.getElementById('renderDocumentCommentHtml').innerHTML = r.html;
        if (typeof edmsToast === 'function') edmsToast('Email sent successfully!', 'success');
    })
    .catch(() => {
        if (typeof edmsToast === 'function') edmsToast('Failed to send email', 'error');
    });
}

function uploadFiles() {
    const input = document.createElement('input');
    input.type = 'file'; input.multiple = true; input.style.display = 'none';
    input.onchange = () => input.files.length && uploadToServer(input.files, 'files');
    document.body.appendChild(input); input.click();
}

function uploadFolder() {
    const input = document.createElement('input');
    input.type = 'file'; input.multiple = true; input.webkitdirectory = true;
    input.style.display = 'none';
    input.onchange = () => input.files.length && uploadToServer(input.files, 'folder');
    document.body.appendChild(input); input.click();
}

function uploadToServer(files, type) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const formData = new FormData();
    formData.append('_token', csrfToken);
    formData.append('folder_id', localStorage.getItem('selectedFolderId'));
    formData.append('document_id', localStorage.getItem('selectedDocumentId'));
    formData.append('type', type);
    for (let i = 0; i < files.length; i++) formData.append('files[]', files[i]);

    if (typeof edmsToast === 'function') edmsToast('Uploading…', 'info', 0);
    fetch('/upload', { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken }})
    .then(res => res.json())
    .then(r => {
        document.querySelectorAll('.toast-notif').forEach(t => t.remove());
        if (typeof edmsToast === 'function') edmsToast('Upload complete!', 'success');
        if (typeof fetchFiles === 'function') fetchFiles(r.url, 'folder');
    })
    .catch(() => {
        document.querySelectorAll('.toast-notif').forEach(t => t.remove());
        if (typeof edmsToast === 'function') edmsToast('Upload failed', 'error');
    });
}

function toggleSubfolders(button) {
    var sf = button.nextElementSibling;
    if (!sf) return;
    sf.style.display = sf.style.display === 'none' ? 'block' : 'none';
    var folderId = button.parentNode.dataset.folderId;
    var sfOpen = JSON.parse(localStorage.getItem('subfoldersOpen')) || {};
    sfOpen[folderId] = sf.style.display === 'block';
    localStorage.setItem('subfoldersOpen', JSON.stringify(sfOpen));
}

function selectSubfolder(id) { localStorage.setItem('selectedSubfolder', id); }

function previewDocumentImageFile(el) {
    var url = el.dataset.preview;
    var Ext = jQuery('.previewFileExtension').val();
    if (typeof previewCourseFile === 'function') previewCourseFile(Ext, url);
}

function showFilters() { jQuery('.custom-dropdown').css('display', 'flex'); }

// ─── Legacy Validation & Form Helpers ────────────────────────────────────────
function validation(xhr, $form) {
    if (xhr.status === 422) {
        var errors = xhr.responseJSON.errors;
        $form.find('.invalid-feedback').remove();
        $form.find('.is-invalid').removeClass('is-invalid');
        for (var key in errors) {
            var $input = $form.find('[name="' + key + '"]');
            $input.addClass('is-invalid');
            $form.find('.error-message').html('<div class="p-2 bg-danger text-white rounded">' + errors[key][0] + '</div>');
        }
    } else {
        $form.find('.error-message').html('<div class="p-2 bg-danger text-white rounded">' + (xhr.responseJSON?.message || 'Error occurred') + '</div>');
    }
}

function saveForm(route, formId, cb) {
    var form = document.getElementById(formId);
    var $form = jQuery('#' + formId);
    var data = new FormData(form);
    jQuery.ajax({
        url: route, type: 'POST', data: data, processData: false, contentType: false,
        success: function (r) { if (typeof cb === 'function') cb(r); },
        error: function (xhr) { validation(xhr, $form); }
    });
}
