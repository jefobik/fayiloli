@extends('layouts.central')

@section('title', 'New Tenant')

@section('content')
<div class="container py-4" style="max-width:720px">

    {{-- ── Breadcrumb ─────────────────────────────────────────────────────── --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="{{ route('tenants.index') }}">Tenants</a>
            </li>
            <li class="breadcrumb-item active" aria-current="page">New Tenant</li>
        </ol>
    </nav>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fa-solid fa-building-user me-2 text-primary" aria-hidden="true"></i>
                Provision New Tenant
            </h5>
            <p class="text-muted small mb-0 mt-1">
                A dedicated PostgreSQL database is created and migrated automatically.
                The tenant starts as <strong>Pending</strong> then transitions to <strong>Active</strong>
                once provisioning completes.
            </p>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('tenants.store') }}" novalidate
                  id="provisionForm">
                @csrf

                {{-- Idempotency key — generated per page-load by TenantController::create().
                     Validated and consumed on first successful POST to prevent double-provisioning. --}}
                <input type="hidden" name="_provision_key" value="{{ $provisionKey }}">

                {{-- Surface a key mismatch as a top-of-form banner (back-button replay, expired session). --}}
                @error('_provision_key')
                <div class="alert alert-warning d-flex align-items-center gap-2 py-2 mb-3" role="alert">
                    <i class="fa-solid fa-triangle-exclamation flex-shrink-0" aria-hidden="true"></i>
                    <span class="small">{{ $message }}</span>
                </div>
                @enderror

                {{-- Organisation Name --}}
                <div class="mb-3">
                    <label for="organization_name" class="form-label fw-semibold">
                        Organisation Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="organization_name" name="organization_name"
                           class="form-control @error('organization_name') is-invalid @enderror"
                           value="{{ old('organization_name') }}"
                           placeholder="NectarMetrics Solutions" required>
                    @error('organization_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Short Name (Subdomain Seed) --}}
                {{--
                    The admin supplies a compact, memorable abbreviation (e.g. 'fmof').
                    SubdomainGenerator::generate(short_name) turns this into the FQDN.
                    Rules: 2–30 chars, lowercase alphanumeric + internal hyphens,
                    must start and end with a letter or digit, globally unique.
                    The JS below auto-suggests an acronym when the org name is typed
                    and enforces slug-safe characters as the admin types.
                --}}
                <div class="mb-3">
                    <label for="short_name" class="form-label fw-semibold">
                        Short Name <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="short_name" name="short_name"
                           class="form-control font-monospace @error('short_name') is-invalid @enderror"
                           value="{{ old('short_name') }}"
                           placeholder="fmof"
                           maxlength="30"
                           autocomplete="off"
                           spellcheck="false"
                           required>
                    @error('short_name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="form-text">
                        2–30 chars — lowercase letters, numbers and hyphens only, no leading/trailing hyphens.
                        Auto-suggested from the organisation name; override freely.
                        This seeds the auto-generated subdomain.
                    </div>
                </div>

                {{-- Admin Email --}}
                <div class="mb-3">
                    <label for="admin_email" class="form-label fw-semibold">
                        Admin Email <span class="text-danger">*</span>
                    </label>
                    <input type="email" id="admin_email" name="admin_email"
                           class="form-control @error('admin_email') is-invalid @enderror"
                           value="{{ old('admin_email') }}"
                           placeholder="info@nectarmetrics.com.ng" required>
                    @error('admin_email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Tenant Type --}}
                <div class="mb-3">
                    <label for="tenant_type" class="form-label fw-semibold">
                        Tenant Type <span class="text-danger">*</span>
                    </label>
                    <select id="tenant_type" name="tenant_type"
                            class="form-select @error('tenant_type') is-invalid @enderror" required>
                        <option value="">— Select a tenant type —</option>
                        @foreach ($tenantTypes as $type)
                            <option value="{{ $type->value }}"
                                    {{ old('tenant_type') === $type->value ? 'selected' : '' }}>
                                {{ $type->label() }}
                            </option>
                        @endforeach
                    </select>
                    @error('tenant_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Auto-generated Subdomain Preview --}}
                {{--
                    The primary subdomain is generated automatically by SubdomainGenerator
                    using the organisation name.  Admins never type a domain — the service
                    normalises the name, checks the reserved-word blacklist, detects
                    collisions and appends a numeric suffix when needed.

                    This widget gives a real-time preview of the FQDN that will be assigned.
                    The actual unique check runs server-side in store(); the preview slug
                    may already be taken and could receive a numeric suffix (e.g. -2).
                --}}
                <div class="mb-3">
                    <label class="form-label fw-semibold">
                        <i class="fa-solid fa-globe me-1 text-muted" aria-hidden="true"></i>
                        Auto-generated Subdomain
                    </label>
                    <div class="border rounded px-3 py-2 bg-light d-flex align-items-center gap-2"
                         aria-live="polite" aria-atomic="true">
                        <i class="fa-solid fa-wand-magic-sparkles text-primary flex-shrink-0" aria-hidden="true"></i>
                        <span class="font-monospace small" id="subdomainPreview">
                            @if (old('short_name'))
                                {{-- Restore preview when returning after a validation error --}}
                                <strong>{{ Str::slug(old('short_name'), '-') }}</strong><span class="text-muted">{{ $domainSuffix }}</span>
                            @else
                                <span class="text-muted fst-italic">enter a short name above…</span>
                            @endif
                        </span>
                    </div>
                    <div class="form-text">
                        Generated from the organisation name.  If the slug is already taken,
                        a numeric suffix (e.g. <code>-2</code>) is appended automatically.
                        Additional domains can be attached after provisioning.
                    </div>
                </div>

                {{-- Notes --}}
                <div class="mb-4">
                    <label for="notes" class="form-label fw-semibold">Notes</label>
                    <textarea id="notes" name="notes" rows="2"
                              class="form-control @error('notes') is-invalid @enderror"
                              placeholder="Optional internal notes…">{{ old('notes') }}</textarea>
                    @error('notes')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- ── Module Access ────────────────────────────────────────── --}}
                <div class="mb-4">
                    <hr class="mb-3">
                    <p class="fw-semibold mb-1">
                        <i class="fa-solid fa-grid me-1 text-muted" aria-hidden="true"></i>
                        Module Access
                    </p>
                    <p class="text-muted small mb-3">
                        Choose which EDMS features are available to this tenant's users.
                        Pre-checked items are the recommended defaults.
                    </p>
                    @error('modules')
                        <div class="alert alert-danger py-1 small mb-2">{{ $message }}</div>
                    @enderror
                    @error('modules.*')
                        <div class="alert alert-danger py-1 small mb-2">{{ $message }}</div>
                    @enderror
                    <div class="row g-2">
                        @foreach ($tenantModules as $module)
                            @php
                                $oldModules = old('modules');
                                $checked = $oldModules !== null
                                    ? in_array($module->value, $oldModules, true)
                                    : in_array($module->value, $defaultModules, true);
                            @endphp
                            <div class="col-sm-6">
                                <div class="form-check border rounded px-3 py-2 h-100 {{ $checked ? 'border-primary bg-primary bg-opacity-10' : '' }}">
                                    <input class="form-check-input" type="checkbox"
                                           name="modules[]"
                                           id="module_{{ $module->value }}"
                                           value="{{ $module->value }}"
                                           {{ $checked ? 'checked' : '' }}>
                                    <label class="form-check-label w-100" for="module_{{ $module->value }}">
                                        <span class="fw-semibold small">
                                            <i class="fa-solid fa-{{ $module->icon() }} me-1 text-muted" aria-hidden="true"></i>
                                            {{ $module->label() }}
                                        </span>
                                        <span class="d-block text-muted" style="font-size:.75rem; line-height:1.3">
                                            {{ $module->description() }}
                                        </span>
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" id="provisionBtn" class="btn btn-primary px-4">
                        <span id="provisionIdle">
                            <i class="fa-solid fa-rocket me-1" aria-hidden="true"></i> Provision Tenant
                        </span>
                        <span id="provisionWorking" class="d-none">
                            <span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>
                            Provisioning&hellip;
                        </span>
                    </button>
                    <a href="{{ route('tenants.index') }}" class="btn btn-outline-secondary" id="cancelBtn">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    'use strict';

    var domainSuffix   = @json($domainSuffix);
    var orgInput       = document.getElementById('organization_name');
    var shortNameInput = document.getElementById('short_name');
    var preview        = document.getElementById('subdomainPreview');

    // Stop-words excluded when building the acronym auto-suggest.
    var SKIP = {
        'of':1,'the':1,'and':1,'for':1,'a':1,'an':1,
        'in':1,'on':1,'at':1,'to':1,'by':1,'or':1
    };

    // ── Slug helper — mirrors SubdomainGenerator::toSlug() ─────────────────
    function toSlug(name) {
        var slug = name
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        if (slug.length > 50) {
            slug = slug.substring(0, 50).replace(/-+$/, '');
        }
        return slug;
    }

    // ── Acronym auto-suggest ────────────────────────────────────────────────
    // Takes the first letter of each significant word in the org name.
    // Falls back to the slug of the first significant word when there are
    // fewer than 2 significant words (single-word agencies, etc.).
    //
    // Examples:
    //   "NectarMetrics Solutions"           → "fmof"
    //   "National Human Resources Agency"        → "nhra"
    //   "Bureau of Public Procurement"           → "bpp"
    function suggestShortName(orgName) {
        var clean = orgName.toLowerCase().replace(/[^a-z0-9 ]/g, ' ');
        var words = clean.split(/\s+/).filter(Boolean);
        var sig   = words.filter(function (w) { return !SKIP[w]; });

        if (sig.length >= 2) {
            return sig.map(function (w) { return w[0]; }).join('').substring(0, 20);
        }
        // Single significant word — use its slug (max 20 chars).
        return toSlug(orgName).substring(0, 20);
    }

    // ── Real-time slug enforcement on short_name input ──────────────────────
    // Normalises to lowercase alphanumeric + hyphen as the admin types,
    // mirroring the server-side regex rule so errors are caught immediately.
    function enforceSlug(value) {
        return value
            .toLowerCase()
            .replace(/[^a-z0-9-]/g, '-')  // non-slug chars → hyphen
            .replace(/-{2,}/g, '-');       // collapse consecutive hyphens
    }

    // ── Live subdomain preview — reads shortNameInput ───────────────────────
    function updatePreview() {
        if (!shortNameInput || !preview) return;

        var slug = toSlug(shortNameInput.value.trim());

        if (!slug) {
            preview.innerHTML = '<span class="text-muted fst-italic">enter a short name above\u2026</span>';
            return;
        }

        var esc = slug.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var sfx = domainSuffix.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        preview.innerHTML =
            '<strong>' + esc + '</strong>' +
            '<span class="text-muted">' + sfx + '</span>';
    }

    // ── Wire events ─────────────────────────────────────────────────────────
    // shortNameDirty: true once the admin manually edits the short_name field.
    // While false, org name changes auto-populate the suggestion.
    var shortNameDirty = !!(shortNameInput && shortNameInput.value.trim());

    if (shortNameInput) {
        shortNameInput.addEventListener('input', function () {
            var pos = shortNameInput.selectionStart;
            var enforced = enforceSlug(shortNameInput.value);
            if (shortNameInput.value !== enforced) {
                shortNameInput.value = enforced;
                shortNameInput.setSelectionRange(pos, pos);
            }
            shortNameDirty = true;
            updatePreview();
        });
    }

    if (orgInput) {
        orgInput.addEventListener('input', function () {
            // Only auto-suggest when the admin has not manually customised short_name.
            if (!shortNameDirty && shortNameInput) {
                var suggested = suggestShortName(orgInput.value.trim());
                shortNameInput.value = suggested;
                updatePreview();
            }
        });
    }

    // Initial render — covers browser autofill / validation-error restore.
    updatePreview();

    // ── Provision-form submit guard ────────────────────────────────────────
    // On first submit: swaps button to a spinner and locks the form.
    // On validation failure the server returns with the @@error banner;
    // the button stays locked until the page reloads with a fresh provision key.

    var form    = document.getElementById('provisionForm');
    var btn     = document.getElementById('provisionBtn');
    var idle    = document.getElementById('provisionIdle');
    var working = document.getElementById('provisionWorking');
    var cancel  = document.getElementById('cancelBtn');

    if (form && btn) {
        form.addEventListener('submit', function (e) {
            if (btn.disabled) {
                e.preventDefault();
                return;
            }

            btn.disabled = true;
            idle.classList.add('d-none');
            working.classList.remove('d-none');

            if (cancel) {
                cancel.classList.add('disabled');
                cancel.setAttribute('aria-disabled', 'true');
                cancel.setAttribute('tabindex', '-1');
            }
        });
    }
})();
</script>
@endpush
