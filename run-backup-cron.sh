#!/usr/bin/env sh
set -eu

if [ -z "${APP_URL:-}" ]; then
    echo "APP_URL is required for automatic backups." >&2
    exit 1
fi

if [ -z "${AUTO_BACKUP_TOKEN:-}" ]; then
    echo "AUTO_BACKUP_TOKEN is required for automatic backups." >&2
    exit 1
fi

curl -fsS \
    -X POST \
    -H "X-Backup-Token: ${AUTO_BACKUP_TOKEN}" \
    "${APP_URL%/}/internal/backups/run"
