# Fayiloli EDMS — DNS Configuration Reference

## Overview

Fayiloli uses wildcard DNS to route all tenant subdomains to a single server IP.
A single `A` record `*.fayiloli.ng` handles every tenant workspace automatically —
no DNS change is needed when a new tenant is provisioned.

---

## Staging Environment

**Nameserver / DNS provider:** Cloudflare (recommended) or equivalent

| Type | Name                        | Value               | Proxy | TTL  | Purpose                              |
|------|-----------------------------|---------------------|-------|------|--------------------------------------|
| A    | `staging.fayiloli.ng`       | `<STAGING_IP>`      | DNS   | Auto | Central admin panel                  |
| A    | `admin.staging.fayiloli.ng` | `<STAGING_IP>`      | DNS   | Auto | Central admin panel (explicit alias) |
| A    | `*.staging.fayiloli.ng`     | `<STAGING_IP>`      | DNS   | Auto | All tenant workspaces                |

> **Cloudflare note:** Set the wildcard record to **DNS only** (grey cloud), NOT proxied.
> Cloudflare does not proxy wildcard records on free plans. On paid plans, ensure
> your SSL mode is set to **Full (strict)** if proxied.

### Staging `.env` snippet

```env
DEPLOYMENT_ENV=staging
TENANT_BASE_DOMAIN=fayiloli.ng
CENTRAL_DOMAIN=admin.staging.fayiloli.ng
APP_URL=https://admin.staging.fayiloli.ng
```

---

## Production Environment

| Type | Name               | Value          | Proxy | TTL  | Purpose                          |
|------|--------------------|----------------|-------|------|----------------------------------|
| A    | `fayiloli.ng`      | `<PROD_IP>`    | DNS   | Auto | Apex (redirects → admin panel)   |
| A    | `admin.fayiloli.ng`| `<PROD_IP>`    | DNS   | Auto | Central super-admin panel        |
| A    | `*.fayiloli.ng`    | `<PROD_IP>`    | DNS   | Auto | All tenant workspaces            |
| MX   | `fayiloli.ng`      | your mail host  | —    | Auto | Transactional email              |
| TXT  | `fayiloli.ng`      | SPF record      | —    | Auto | Email anti-spoofing              |

> **IMPORTANT:** The wildcard `*.fayiloli.ng` must point to the **same IP** as the
> Nginx server running the Laravel app. No per-tenant DNS records are needed.

### Production `.env` snippet

```env
DEPLOYMENT_ENV=production
TENANT_BASE_DOMAIN=fayiloli.ng
CENTRAL_DOMAIN=admin.fayiloli.ng
APP_URL=https://admin.fayiloli.ng
APP_ENV=production
APP_DEBUG=false
```

---

## Local Development

No DNS server required. Add entries to `/etc/hosts` manually for each tenant
and the central domain.

```
# /etc/hosts — Fayiloli EDMS local development
127.0.0.1  localhost                    # central admin panel
127.0.0.1  finance.localhost            # NectarMetrics Solutions (sample)
127.0.0.1  hra.localhost                # Human Resources Agency (sample)
# Add one line per new tenant provisioned locally:
# 127.0.0.1  {slug}.localhost
```

**macOS:** `sudo nano /etc/hosts` then flush: `sudo dscacheutil -flushcache && sudo killall -HUP mDNSResponder`
**Linux:** `sudo nano /etc/hosts` (takes effect immediately)
**Windows:** `C:\Windows\System32\drivers\etc\hosts` (run Notepad as Administrator)

---

## How Wildcard DNS Works with Tenancy

```
User request:  https://finance.fayiloli.ng/
       │
       ▼
DNS:   *.fayiloli.ng  →  A  →  <SERVER_IP>
       │
       ▼
Nginx: *.fayiloli.ng server block → PHP-FPM (passes Host: finance.fayiloli.ng)
       │
       ▼
Laravel: InitializeTenancyByDomain reads Host header
         → queries domains table WHERE domain = 'finance.fayiloli.ng'
         → finds tenant, switches DB connection to tenant{uuid}
         → request serves Finance Ministry workspace
```

Unknown subdomains (not registered in the domains table) → `InitializeTenancyByDomain::$onFail` → `abort(404)`.
This prevents domain squatting — an attacker cannot claim a subdomain by pointing DNS at your server.

---

## SSL Certificate Scope

A single wildcard certificate covers:

| Certificate | Covers |
|-------------|--------|
| `*.fayiloli.ng` | All `{tenant}.fayiloli.ng` workspaces |
| `fayiloli.ng` (SAN) | Apex domain redirect |

For staging, a separate wildcard certificate covers `*.staging.fayiloli.ng`.

See [infrastructure/ssl/obtain-wildcard-ssl.sh](../ssl/obtain-wildcard-ssl.sh) for the provisioning script.

---

## Cloudflare Zone Setup Checklist

- [ ] Add domain `fayiloli.ng` to Cloudflare
- [ ] Set nameservers at your registrar to Cloudflare's NS records
- [ ] Add `A` record: `fayiloli.ng` → `<PROD_IP>` (DNS only, TTL Auto)
- [ ] Add `A` record: `admin.fayiloli.ng` → `<PROD_IP>` (DNS only, TTL Auto)
- [ ] Add `A` record: `*.fayiloli.ng` → `<PROD_IP>` (DNS only, TTL Auto)
- [ ] Create API Token: **Zone → DNS → Edit** (scoped to `fayiloli.ng`)
- [ ] Store token in `/etc/letsencrypt/cloudflare.ini` on the VPS
- [ ] Run `infrastructure/ssl/obtain-wildcard-ssl.sh` to provision wildcard cert
- [ ] Enable **DNSSEC** in Cloudflare for the zone
- [ ] Set SSL/TLS mode to **Full (strict)** in Cloudflare (if proxied)

---

## Security Notes

| Threat | Mitigation |
|--------|-----------|
| Subdomain takeover | `InitializeTenancyByDomain::$onFail` returns 404 for unregistered domains |
| Reserved-name squatting | `SubdomainGenerator::RESERVED` blacklist prevents allocation of `admin`, `api`, `www`, etc. |
| Collision | Numeric suffix (-2, -3 …) + DB unique constraint prevent duplicate domain registration |
| SSL downgrade | HSTS with `includeSubDomains` + Nginx HTTP→HTTPS redirect |
| Cross-tenant data leak | Separate PostgreSQL DB per tenant; session cookies scoped to subdomain |
| DNS hijacking | DNSSEC enabled on Cloudflare zone |
