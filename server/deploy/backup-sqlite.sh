#!/usr/bin/env bash
set -euo pipefail

APP_DIR="/var/www/owntv-custom/server"
DB_FILE="$APP_DIR/database/database.sqlite"
BACKUP_DIR="/var/backups/owntv-panel"
STAMP="$(date -u +%Y%m%dT%H%M%SZ)"

install -d -m 0700 -o www-data -g www-data "$BACKUP_DIR"
if [[ ! -f "$DB_FILE" ]]; then
  echo "SQLite database not found: $DB_FILE" >&2
  exit 1
fi

sqlite3 "$DB_FILE" ".backup '$BACKUP_DIR/panel-$STAMP.sqlite'"
chown www-data:www-data "$BACKUP_DIR/panel-$STAMP.sqlite"
chmod 0600 "$BACKUP_DIR/panel-$STAMP.sqlite"
find "$BACKUP_DIR" -type f -name 'panel-*.sqlite' -printf '%T@ %p\n' | sort -nr | awk 'NR>7 {print $2}' | while read -r old; do rm -f -- "$old"; done
