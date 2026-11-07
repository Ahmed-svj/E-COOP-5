#!/usr/bin/env bash
# Codespaces / Dev Container setup: database, app user, .env and demo data.
set -euo pipefail
sudo apt-get update -qq && sudo apt-get install -y -qq default-mysql-client > /dev/null
sudo docker-php-ext-install pdo_mysql > /dev/null 2>&1 || true
printf '[client]\nuser=root\npassword=root\nhost=db\n' > ~/.my.cnf
until mysqladmin ping --silent; do echo "waiting for MySQL..."; sleep 2; done
mysql < database/schema.sql
mysql -e "CREATE USER IF NOT EXISTS 'coop_app'@'%' IDENTIFIED BY 'ChangeThisStrongPassword!'; GRANT SELECT, INSERT, UPDATE, DELETE ON coop_db.* TO 'coop_app'@'%';"
cp -n .env.example .env
sed -i 's/^DB_HOST=.*/DB_HOST=db/' .env
php database/seed.php
echo "Ready. Open the forwarded port 8080. Password for every demo account: Coop2026!"
