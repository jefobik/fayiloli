{{--
    Reusable permission checkbox grid.
    Expects: $permissions (grouped array), $rolePermissions (array of granted perm names)
--}}
<div class="permission-grid" x-data="permissionGrid()">
    @foreach($permissions as $group => $groupPerms)
        @php
            $groupId = 'grp_' . preg_replace('/\W/', '_', $group);
            $permNames = collect($groupPerms)->pluck('name')->toArray();
        @endphp

        <div class="card border-0 bg-light mb-3">
            <div class="card-header bg-transparent border-0 py-2 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox"
                           class="form-check-input group-toggle mt-0"
                           id="{{ $groupId }}"
                           data-group="{{ $group }}"
                           aria-label="Select all {{ $group }} permissions"
                           @change="toggleGroup('{{ $group }}', $event.target.checked)">
                    <label for="{{ $groupId }}"
                           class="fw-semibold text-uppercase text-muted mb-0 cursor-pointer"
                           style="font-size:0.7rem;letter-spacing:0.08em">
                        {{ $group }}
                    </label>
                </div>
                <span class="badge bg-secondary bg-opacity-10 text-secondary" style="font-size:0.68rem"
                      x-text="groupCount('{{ $group }}') + ' / {{ count($groupPerms) }}'"></span>
            </div>
            <div class="card-body pt-0 pb-2">
                <div class="row g-2">
                    @foreach($groupPerms as $perm)
                        <div class="col-sm-6 col-md-4">
                            <div class="form-check mb-0">
                                <input class="form-check-input perm-check"
                                       type="checkbox"
                                       name="permissions[]"
                                       value="{{ $perm->name }}"
                                       id="perm_{{ $perm->id }}"
                                       data-group="{{ $group }}"
                                       {{ in_array($perm->name, $rolePermissions ?? [], true) ? 'checked' : '' }}
                                       @change="syncGroupToggle('{{ $group }}')">
                                <label class="form-check-label small" for="perm_{{ $perm->id }}">
                                    {{ $perm->name }}
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

<script>
function permissionGrid() {
    return {
        init() {
            // Sync initial group toggle states
            document.querySelectorAll('.group-toggle').forEach(toggle => {
                this.syncGroupToggle(toggle.dataset.group);
            });
        },
        toggleGroup(group, checked) {
            document.querySelectorAll(`.perm-check[data-group="${group}"]`).forEach(cb => {
                cb.checked = checked;
            });
        },
        syncGroupToggle(group) {
            const checks = document.querySelectorAll(`.perm-check[data-group="${group}"]`);
            const toggle = document.querySelector(`.group-toggle[data-group="${group}"]`);
            if (!toggle) return;
            const all  = checks.length;
            const done = [...checks].filter(c => c.checked).length;
            toggle.checked       = done === all;
            toggle.indeterminate = done > 0 && done < all;
        },
        groupCount(group) {
            return [...document.querySelectorAll(`.perm-check[data-group="${group}"]`)].filter(c => c.checked).length;
        }
    }
}
</script>
