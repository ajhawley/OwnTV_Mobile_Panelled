#!/usr/bin/env bash
set -euo pipefail

if [[ "${EUID}" -ne 0 ]]; then
  echo "Run this script as root (for example: sudo bash ubuntu-24.04-setup.sh)." >&2
  exit 1
fi

apt-get update
apt-get install -y \
  nginx sqlite3 composer git unzip \
  php8.3-cli php8.3-fpm php8.3-sqlite3 php8.3-mbstring php8.3-xml \
  php8.3-curl php8.3-zip php8.3-bcmath php8.3-intl php8.3-common \
  certbot python3-certbot-nginx

systemctl enable --now nginx php8.3-fpm

echo "PHP, Composer, SQLite and Nginx installed. Deploy the project, configure .env, then run Composer and Artisan as described in server/README.md."
