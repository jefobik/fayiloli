<div>
    @if($currentTenant)
        <x-ts-dropdown position="bottom-end">
            <x-slot:action>
                @php
                    $chipGc = $wsTypeColors[$currentTenant->tenant_type?->value ?? ''] ?? ['#7c3aed', '#6d28d9'];
                    $chipInitials = strtoupper(
                        substr($currentTenant->organization_name, 0, 1) .
                        substr(explode(' ', $currentTenant->organization_name)[1] ?? '', 0, 1)
                    );
                @endphp
                <button type="button" class="flex items-center min-h-[44px] gap-2 pl-1.5 pr-2.5 py-1.5 rounded-lg
                                           bg-[var(--panel-bg)] border border-[var(--panel-border)]
                                           hover:border-[var(--tenant-primary)]
                                           transition-all duration-150
                                           focus:outline-none focus:ring-2 focus:ring-[var(--tenant-primary)]"
                    aria-label="Switch Workspace. Current: {{ $currentTenant->organization_name }}">
                    <div class="w-8 h-8 rounded-md shrink-0 flex items-center justify-center
                                                text-white text-[0.7rem] font-extrabold shadow-sm"
                        style="background: linear-gradient(135deg, {{ $chipGc[0] }}, {{ $chipGc[1] }});" aria-hidden="true">
                        {{ $chipInitials }}</div>
                    <span class="hidden md:block text-xs font-semibold text-[var(--text-main)]
                                                 max-w-25 truncate leading-tight">
                        {{ $currentTenant->organization_name }}
                    </span>
                    <x-ts-icon name="chevron-down" class="h-3 w-3 text-[var(--text-muted)]" />
                </button>
            </x-slot:action>

            <div class="px-4 py-3 border-b flex flex-col items-start border-[var(--panel-border)]">
                <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest leading-none mb-1">
                    Current Workspace</p>
                <p class="text-sm font-bold text-[var(--text-main)] truncate">
                    {{ $currentTenant->organization_name }}
                </p>
            </div>

            <a class="group flex flex-row min-h-[44px] items-center gap-3 px-4 py-3 hover:bg-[var(--slate-100)] dark:hover:bg-white/5 transition-colors no-underline"
                role="menuitem" href="{{ route('portal.dashboard') }}">
                <div class="flex items-center justify-center w-8 h-8 rounded-full bg-[var(--tenant-primary)]/10 text-[var(--tenant-primary)] shrink-0 group-hover:scale-105 group-hover:bg-[var(--tenant-primary)]/20 transition-all text-xs"
                    aria-hidden="true">
                    <x-ts-icon name="arrows-right-left" class="h-4 w-4" />
                </div>
                <div class="flex flex-col items-start justify-center">
                    <div class="text-[0.85rem] font-bold text-[var(--tenant-primary)] leading-none">
                        Central Portal
                    </div>
                    <div class="text-xs font-medium text-[var(--text-muted)] mt-1 leading-none">Management dashboard</div>
                </div>
            </a>

            @if($availableTenants->isNotEmpty())
                <div class="px-4 py-2 border-t border-[var(--panel-border)] bg-[var(--slate-100)] dark:bg-white/5">
                    <p class="text-xs font-bold text-[var(--text-muted)] uppercase tracking-widest mb-0">
                        Switch Workspace</p>
                </div>

                <div class="max-h-64 overflow-y-auto">
                    @foreach($availableTenants as $t)
                        @php
                            $wsWords = array_values(array_filter(explode(' ', $t->organization_name)));
                            $wsInitials = strtoupper(
                                substr($wsWords[0] ?? '', 0, 1) . substr($wsWords[1] ?? '', 0, 1)
                            );
                            $wsColors = $wsTypeColors[$t->tenant_type?->value ?? ''] ?? ['#7c3aed', '#6d28d9'];
                            $switchUrl = route('switch.workspace', $t->id);
                        @endphp
                        <a href="{{ $switchUrl }}"
                            class="group flex flex-row items-center gap-3 px-4 py-2 hover:bg-[var(--slate-100)] dark:hover:bg-white/5 border-b border-[var(--panel-border)] transition-colors no-underline last:border-0"
                            role="menuitem" aria-label="Switch to {{ $t->organization_name }}">
                            <div class="flex items-center justify-center w-7 h-7 rounded shrink-0
                                                                                        text-white text-[0.6rem] font-extrabold shadow-sm
                                                                                        transition-transform group-hover:scale-105"
                                style="background: linear-gradient(135deg, {{ $wsColors[0] }}, {{ $wsColors[1] }});"
                                aria-hidden="true">
                                {{ $wsInitials }}
                            </div>
                            <div class="flex flex-col items-start justify-center overflow-hidden w-full">
                                <div
                                    class="text-[0.8rem] font-semibold text-[var(--text-main)]
                                                                                            group-hover:text-[var(--tenant-primary)]
                                                                                            transition-colors truncate w-full leading-tight">
                                    {{ $t->organization_name }}
                                </div>
                                <div class="text-xs font-medium text-[var(--text-muted)]
                                                                                            truncate w-full mt-0.5 leading-tight">
                                    {{ $t->domains->first()?->domain ?? 'No domain' }}
                                </div>
                            </div>
                            <x-ts-icon name="arrow-right"
                                class="h-3 w-3 text-[var(--text-muted)] group-hover:text-[var(--tenant-primary)] transition-colors shrink-0" />
                        </a>
                    @endforeach
                </div>
            @endif
        </x-ts-dropdown>
    @endif
</div>