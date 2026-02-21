<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Str;
use Stancl\Tenancy\Database\Models\Domain;

/**
 * SubdomainGenerator — Enterprise-grade, collision-proof subdomain provisioner.
 *
 * Generates environment-aware, slug-based FQDNs for tenant workspaces:
 *   Local:      {slug}.localhost
 *   Staging:    {slug}.staging.{TENANT_BASE_DOMAIN}
 *   Production: {slug}.{TENANT_BASE_DOMAIN}
 *
 * Collision prevention strategy (in order):
 *   1. Reserved-name blacklist (www, api, admin, mail, infrastructure names, etc.)
 *   2. Reserved words are prefixed with 'org-' to remain usable but safe
 *   3. Database existence check against the domains table
 *   4. Numeric suffix increment: finance → finance-2 → finance-3 … finance-999
 *   5. Random 8-char lowercase suffix beyond 999 (cryptographically seeded)
 *
 * Safe against:
 *   - DNS collision (checks domains table before returning)
 *   - Reserved name takeover (centralised blacklist)
 *   - Homoglyph / lookalike attacks (Str::slug normalises unicode)
 *   - Path traversal (output is alphanumeric + hyphen only, regex: /^[a-z0-9][a-z0-9\-]*[a-z0-9]$/)
 *   - DNS label overflow (label hard-capped at 50 chars, well below RFC 1035's 63-char limit)
 *   - Domain takeover (atomic unique DB constraint is the final guard; this check is an optimisation)
 */
class SubdomainGenerator
{
    /**
     * Subdomains that must never be allocated to tenants.
     *
     * Covers DNS/service names, infrastructure endpoints, platform internals,
     * and names that could mislead users or enable phishing against the central
     * admin panel. Checked case-insensitively.
     *
     * @var list<string>
     */
    private const RESERVED = [
        // ── DNS / Network infrastructure ─────────────────────────────────────
        'www', 'wwww',
        'ns', 'ns0', 'ns1', 'ns2', 'ns3', 'ns4', 'ns5',
        'mx', 'mx1', 'mx2', 'mx3',
        'cdn', 'edge', 'static', 'assets', 'media', 'images', 'img',
        'files', 'storage', 'uploads', 'download', 'downloads',
        'ftp', 'sftp', 'ssh', 'vpn', 'proxy', 'gateway', 'relay',
        'firewall', 'lb', 'balancer',

        // ── Mail infrastructure ───────────────────────────────────────────────
        'mail', 'mail1', 'mail2', 'smtp', 'smtps',
        'pop', 'pop3', 'imap', 'imaps', 'webmail', 'email',
        'mta', 'mda', 'mua', 'bounce', 'postmaster',

        // ── Control panels ────────────────────────────────────────────────────
        'cpanel', 'whm', 'plesk', 'directadmin', 'kloxo', 'hestia',
        'webadmin', 'panel', 'hosting',

        // ── Platform internals ────────────────────────────────────────────────
        'admin', 'superadmin', 'super', 'root', 'master', 'central',
        'system', 'sys', 'internal', 'intranet', 'extranet',
        'manage', 'management', 'console', 'control', 'hub',

        // ── Auth / Identity ───────────────────────────────────────────────────
        'auth', 'oauth', 'oauth2', 'sso', 'saml', 'oidc', 'idp',
        'login', 'logout', 'register', 'signup', 'signin', 'signout',
        'account', 'accounts', 'identity', 'password', 'reset', 'forgot',
        'verify', 'verification', 'confirm', 'activate', 'activate2',

        // ── API endpoints ─────────────────────────────────────────────────────
        'api', 'api-v1', 'api-v2', 'api-v3', 'graphql', 'grpc',
        'ws', 'wss', 'socket', 'io', 'realtime', 'stream',
        'webhook', 'webhooks', 'callback', 'callbacks',
        'rpc', 'rest',

        // ── CI/CD / DevOps environments ───────────────────────────────────────
        'dev', 'develop', 'development',
        'test', 'testing', 'qa', 'qat', 'qe', 'uat', 'sit', 'fat',
        'staging', 'stage', 'stg',
        'prod', 'production',
        'preview', 'demo', 'sandbox',
        'canary', 'beta', 'alpha', 'rc', 'release', 'nightly',
        'hotfix', 'feature', 'local',

        // ── Documentation / Support ───────────────────────────────────────────
        'help', 'support', 'docs', 'doc', 'documentation',
        'kb', 'knowledge', 'wiki', 'faq',
        'status', 'uptime', 'health', 'healthz', 'ping', 'monitor',
        'blog', 'news', 'press', 'updates',
        'about', 'contact',

        // ── Analytics / Observability ─────────────────────────────────────────
        'metrics', 'analytics', 'telemetry', 'logs', 'logging',
        'grafana', 'kibana', 'sentry', 'datadog', 'newrelic',
        'prometheus', 'elastic', 'splunk', 'loggly',
        'track', 'tracking',

        // ── Platform brand names ──────────────────────────────────────────────
        'fayiloli', 'edms', 'nectarmetrics',

        // ── Generic catch-all ─────────────────────────────────────────────────
        'app', 'web', 'portal', 'dashboard', 'home',
        'server', 'host', 'localhost', 'localdomain',
        'secure', 'ssl', 'tls',
        'cloud', 'cluster', 'node', 'worker',
        'default', 'null', 'undefined',
    ];

    // ── Public API ────────────────────────────────────────────────────────────

    /**
     * Generate a unique, environment-aware FQDN for a new tenant.
     *
     * This is the primary method called from TenantController::store().
     * Returns a fully-qualified domain name ready for:
     *   $tenant->domains()->create(['domain' => $fqdn])
     *
     * @param  string $organizationName  Human-readable org name (e.g. "NectarMetrics Solutions").
     * @return string                    Collision-free FQDN  (e.g. "fayiloli.ng").
     */
    public function generate(string $organizationName): string
    {
        $slug       = $this->toSlug($organizationName);
        $uniqueSlug = $this->resolveUniqueSlug($slug);

        return $this->buildFqdn($uniqueSlug);
    }

    /**
     * Return a non-unique preview FQDN for a given org name.
     *
     * Used by TenantController::create() to pass the domain suffix pattern
     * to the view for the live JS preview.  Does NOT check the domains table.
     *
     * @param  string $organizationName  Raw org name from the form field.
     * @return string                    Preview FQDN (may already be taken).
     */
    public function preview(string $organizationName): string
    {
        $slug = $this->toSlug($organizationName);

        if ($this->isReserved($slug)) {
            $slug = 'org-' . $slug;
        }

        return $this->buildFqdn($slug);
    }

    /**
     * Convert an organisation name to a DNS-safe slug.
     *
     * Rules applied (in order):
     *   1. Str::slug() — unicode transliteration, lowercase, non-alphanum → '-'
     *   2. Collapse consecutive hyphens (e.g. "A & B" → "a--b" → "a-b")
     *   3. Strip leading/trailing hyphens
     *   4. Hard cap at 50 chars to leave room for numeric suffixes within
     *      the RFC 1035 63-char DNS label limit
     *   5. Fallback to 'tenant' if normalisation produces an empty string
     *
     * @param  string $name  Raw organisation name.
     * @return string        DNS-safe slug, max 50 chars, guaranteed non-empty.
     */
    public function toSlug(string $name): string
    {
        $slug = Str::slug($name, '-');

        // Collapse multiple consecutive hyphens.
        $slug = (string) preg_replace('/-{2,}/', '-', $slug);
        $slug = trim($slug, '-');

        // Hard cap — leaves room for '-999' (4 chars) within the 63-char label limit.
        if (strlen($slug) > 50) {
            $slug = rtrim(substr($slug, 0, 50), '-');
        }

        return $slug ?: 'tenant';
    }

    /**
     * Check whether a slug is in the reserved list.
     *
     * @param  string $slug  Candidate slug (already normalised to lowercase).
     * @return bool
     */
    public function isReserved(string $slug): bool
    {
        return in_array(strtolower($slug), self::RESERVED, true);
    }

    /**
     * Build the environment-specific FQDN from a validated slug.
     *
     * ENV pattern (driven by tenancy.deployment_environment):
     *   local      → {slug}.localhost
     *   staging    → {slug}.staging.{tenant_base_domain}
     *   production → {slug}.{tenant_base_domain}
     *
     * @param  string $slug  Validated, unique slug portion.
     * @return string        Fully-qualified domain name.
     */
    public function buildFqdn(string $slug): string
    {
        $env        = config('tenancy.deployment_environment', 'local');
        $baseDomain = config('tenancy.tenant_base_domain', 'localhost');

        return match ($env) {
            'local'   => "{$slug}.localhost",
            'staging' => "{$slug}.staging.{$baseDomain}",
            default   => "{$slug}.{$baseDomain}",  // production + any unknown env
        };
    }

    /**
     * Return the domain suffix for the current environment (the part after {slug}).
     *
     * E.g.:
     *   local      → '.localhost'
     *   staging    → '.staging.fayiloli.ng'
     *   production → '.fayiloli.ng'
     *
     * Used by TenantController::create() to pass to the Blade live-preview widget.
     *
     * @return string
     */
    public function domainSuffix(): string
    {
        $env        = config('tenancy.deployment_environment', 'local');
        $baseDomain = config('tenancy.tenant_base_domain', 'localhost');

        return match ($env) {
            'local'   => '.localhost',
            'staging' => ".staging.{$baseDomain}",
            default   => ".{$baseDomain}",
        };
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    /**
     * Resolve a unique slug that is both non-reserved and not already registered
     * in the domains table.
     *
     * Strategy:
     *   Step 1 — Reserved guard: prepend 'org-' to blocked words.
     *   Step 2 — Quick-path: no collision → return immediately.
     *   Step 3 — Numeric suffix loop: base-2 … base-999.
     *   Step 4 — Random fallback: base-{8 random lowercase chars}.
     *             Statistically collision-proof (26^8 ≈ 200 billion combinations).
     *
     * @param  string $slug  Base slug from toSlug().
     * @return string        Unique slug (without base domain).
     */
    private function resolveUniqueSlug(string $slug): string
    {
        // Step 1 — reserved word guard.
        if ($this->isReserved($slug)) {
            $slug = 'org-' . $slug;
        }

        // Step 2 — optimistic quick path.
        if (! $this->fqdnExists($this->buildFqdn($slug))) {
            return $slug;
        }

        // Step 3 — numeric suffix: finance-2, finance-3 … finance-999.
        for ($i = 2; $i <= 999; $i++) {
            $candidate = "{$slug}-{$i}";
            if (! $this->fqdnExists($this->buildFqdn($candidate))) {
                return $candidate;
            }
        }

        // Step 4 — random fallback (extremely unlikely to be needed).
        return $slug . '-' . Str::lower(Str::random(8));
    }

    /**
     * Check the central domains table for an existing record.
     *
     * NOTE: The unique constraint on domains.domain is the atomic final guard.
     *       This check is a performance optimisation to avoid a constraint
     *       violation exception from bubbling up to the user.
     *
     * @param  string $fqdn  Fully-qualified domain name to check.
     * @return bool
     */
    private function fqdnExists(string $fqdn): bool
    {
        return Domain::where('domain', $fqdn)->exists();
    }
}
