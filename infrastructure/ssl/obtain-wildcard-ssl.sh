#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# obtain-wildcard-ssl.sh
# Fayiloli EDMS — Wildcard Let's Encrypt SSL provisioner
#
# Issues a wildcard TLS certificate covering:
#   *.fayiloli.ng        (all tenant subdomains)
#    fayiloli.ng         (apex domain)
#
# Uses the Certbot DNS-01 challenge via the Cloudflare DNS plugin.
# DNS-01 is the ONLY challenge type that supports wildcard certificates.
#
# REQUIREMENTS:
#   - Ubuntu/Debian VPS with root/sudo access
#   - Domain DNS managed by Cloudflare
#   - python3-certbot-dns-cloudflare installed (step 1 below)
#   - A Cloudflare API token with Zone:DNS:Edit permission
#
# USAGE:
#   chmod +x infrastructure/ssl/obtain-wildcard-ssl.sh
#   sudo ./infrastructure/ssl/obtain-wildcard-ssl.sh
#
# For staging environment, set DOMAIN and STAGING_FLAG before running:
#   DOMAIN=staging.fayiloli.ng STAGING_FLAG="--staging" sudo -E ./obtain-wildcard-ssl.sh
# ─────────────────────────────────────────────────────────────────────────────

set -euo pipefail

# ── Configuration ─────────────────────────────────────────────────────────────
DOMAIN="${DOMAIN:-fayiloli.ng}"
EMAIL="${CERTBOT_EMAIL:-devops@nectarmetrics.com.ng}"
CF_CREDENTIALS="/etc/letsencrypt/cloudflare.ini"
STAGING_FLAG="${STAGING_FLAG:-}"   # set to "--staging" to use Let's Encrypt staging CA

# ─────────────────────────────────────────────────────────────────────────────

echo "======================================================================"
echo "  Fayiloli EDMS — Wildcard SSL Provisioner"
echo "  Domain  : *.${DOMAIN} + ${DOMAIN}"
echo "  Email   : ${EMAIL}"
echo "  Staging : ${STAGING_FLAG:-no (production CA)}"
echo "======================================================================"

# ── Step 1: Install Certbot + Cloudflare DNS plugin ──────────────────────────
echo ""
echo "[1/5] Installing Certbot and Cloudflare DNS plugin..."

if ! command -v certbot &>/dev/null; then
    apt-get update -qq
    apt-get install -y -qq certbot python3-certbot-dns-cloudflare
    echo "      Certbot installed."
else
    echo "      Certbot already installed: $(certbot --version 2>&1)"
fi

# ── Step 2: Create Cloudflare API credentials file ───────────────────────────
echo ""
echo "[2/5] Configuring Cloudflare credentials..."

if [[ -f "$CF_CREDENTIALS" ]]; then
    echo "      ${CF_CREDENTIALS} already exists — skipping creation."
    echo "      To update the token, run: sudo nano ${CF_CREDENTIALS}"
else
    if [[ -z "${CLOUDFLARE_API_TOKEN:-}" ]]; then
        echo ""
        echo "  ► Enter your Cloudflare API Token."
        echo "    Required permissions: Zone → DNS → Edit"
        echo "    Create one at: https://dash.cloudflare.com/profile/api-tokens"
        echo ""
        read -rsp "  Cloudflare API Token: " CF_TOKEN
        echo ""
    else
        CF_TOKEN="$CLOUDFLARE_API_TOKEN"
        echo "      Using CLOUDFLARE_API_TOKEN from environment."
    fi

    mkdir -p "$(dirname "$CF_CREDENTIALS")"
    cat > "$CF_CREDENTIALS" <<EOF
# Cloudflare API token for Certbot DNS-01 challenge.
# Generated: $(date -u +"%Y-%m-%dT%H:%M:%SZ")
# Permissions required: Zone → DNS → Edit (scoped to ${DOMAIN})
dns_cloudflare_api_token = ${CF_TOKEN}
EOF
    chmod 600 "$CF_CREDENTIALS"
    echo "      Credentials written to ${CF_CREDENTIALS} (mode 600)."
fi

# ── Step 3: Obtain the wildcard certificate ───────────────────────────────────
echo ""
echo "[3/5] Requesting wildcard certificate from Let's Encrypt..."
echo "      Domains: *.${DOMAIN}  +  ${DOMAIN}"
echo "      This requires a DNS TXT record to be created in Cloudflare."
echo "      The Cloudflare plugin does this automatically — no manual action needed."
echo ""

certbot certonly \
    --dns-cloudflare \
    --dns-cloudflare-credentials "$CF_CREDENTIALS" \
    --dns-cloudflare-propagation-seconds 60 \
    ${STAGING_FLAG} \
    --non-interactive \
    --agree-tos \
    --email "$EMAIL" \
    --expand \
    -d "*.${DOMAIN}" \
    -d "${DOMAIN}"

echo ""
echo "      Certificate obtained successfully."
echo "      Location: /etc/letsencrypt/live/${DOMAIN}/"

# ── Step 4: Verify certificate ───────────────────────────────────────────────
echo ""
echo "[4/5] Verifying certificate..."

CERT_PATH="/etc/letsencrypt/live/${DOMAIN}/fullchain.pem"

if [[ -f "$CERT_PATH" ]]; then
    openssl x509 -in "$CERT_PATH" -noout -subject -issuer -dates
    echo ""
    echo "      SAN (Subject Alternative Names):"
    openssl x509 -in "$CERT_PATH" -noout -text \
        | grep -A1 "Subject Alternative Name" \
        | tail -1 \
        | tr ',' '\n' \
        | sed 's/^\s*/      /'
else
    echo "      WARNING: Certificate file not found at ${CERT_PATH}"
    exit 1
fi

# ── Step 5: Set up auto-renewal ───────────────────────────────────────────────
echo ""
echo "[5/5] Configuring automatic renewal..."

CRON_JOB="0 2 * * * root certbot renew --quiet --post-hook 'systemctl reload nginx'"
CRON_FILE="/etc/cron.d/certbot-fayiloli"

if [[ ! -f "$CRON_FILE" ]]; then
    echo "$CRON_JOB" > "$CRON_FILE"
    chmod 644 "$CRON_FILE"
    echo "      Cron job written to ${CRON_FILE}"
    echo "      Certificates will auto-renew daily at 02:00 UTC."
else
    echo "      Cron job already exists at ${CRON_FILE} — skipping."
fi

# ── Test renewal (dry-run) ────────────────────────────────────────────────────
echo ""
echo "      Running renewal dry-run to verify configuration..."
certbot renew --dry-run --cert-name "${DOMAIN}" 2>&1 | tail -5
echo "      Dry-run passed."

# ── Summary ───────────────────────────────────────────────────────────────────
echo ""
echo "======================================================================"
echo "  SSL provisioning complete."
echo ""
echo "  Certificate files:"
echo "    fullchain : /etc/letsencrypt/live/${DOMAIN}/fullchain.pem"
echo "    privkey   : /etc/letsencrypt/live/${DOMAIN}/privkey.pem"
echo "    chain     : /etc/letsencrypt/live/${DOMAIN}/chain.pem"
echo ""
echo "  Next steps:"
echo "    1. Update Nginx config to reference these cert paths."
echo "    2. Reload Nginx:  sudo systemctl reload nginx"
echo "    3. Test HTTPS:    curl -I https://${DOMAIN}"
echo "    4. Verify wildcard: curl -I https://test.${DOMAIN}"
echo ""
echo "  Auto-renewal: daily at 02:00 UTC via ${CRON_FILE}"
echo "======================================================================"
