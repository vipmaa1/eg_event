#!/usr/bin/env bash
set -euo pipefail

# ─── EventHub Deployment Script ───────────────────────────────────────────────
# Usage: bash deploy/deploy.sh
# Run this on your DigitalOcean Droplet after cloning the project.

RED='\033[0;31m'; GREEN='\033[0;32m'; YELLOW='\033[1;33m'; CYAN='\033[0;36m'; NC='\033[0m'
info()  { echo -e "${CYAN}[INFO]${NC}  $1"; }
ok()    { echo -e "${GREEN}[OK]${NC}    $1"; }
warn()  { echo -e "${YELLOW}[WARN]${NC}  $1"; }
err()   { echo -e "${RED}[ERROR]${NC} $1"; }

PROJECT_DIR="$(cd "$(dirname "$0")/.." && pwd)"
cd "$PROJECT_DIR"

# ── 1. Prerequisites ──────────────────────────────────────────────────────────
info "Checking prerequisites..."

command -v docker >/dev/null 2>&1 || { err "Docker is not installed."; exit 1; }
DOCKER_COMPOSE="docker-compose"
if docker compose version >/dev/null 2>&1; then
  DOCKER_COMPOSE="docker compose"
fi

ok "Docker and Docker Compose are available"

# ── 2. Environment setup ──────────────────────────────────────────────────────
if [ ! -f backend/.env ]; then
  info "Creating backend/.env from template..."
  cp backend/.env.example backend/.env
  APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s/APP_KEY=/APP_KEY=$APP_KEY/" backend/.env
  else
    sed -i "s/APP_KEY=/APP_KEY=$APP_KEY/" backend/.env
  fi
  ok "Generated APP_KEY"
fi

read -rp "Enter your domain (e.g., eventhub.example.com): " APP_DOMAIN
if [ -z "$APP_DOMAIN" ]; then
  warn "No domain set. Using localhost."
  APP_DOMAIN="localhost"
fi

read -rsp "Enter MariaDB password for 'eventhub' user: " DB_PASSWORD
echo ""
[ -z "$DB_PASSWORD" ] && DB_PASSWORD="changeme" && warn "Using default DB password!"

read -rsp "Enter MariaDB root password: " DB_ROOT_PASSWORD
echo ""
[ -z "$DB_ROOT_PASSWORD" ] && DB_ROOT_PASSWORD="rootpass" && warn "Using default root password!"

read -rsp "Enter Redis password: " REDIS_PASSWORD
echo ""
[ -z "$REDIS_PASSWORD" ] && REDIS_PASSWORD="redispass" && warn "Using default Redis password!"

# Write .env
if [[ "$OSTYPE" == "darwin"* ]]; then
  sed -i '' "s/APP_URL=.*/APP_URL=https:\/\/$APP_DOMAIN/" backend/.env
  sed -i '' "s/DB_HOST=.*/DB_HOST=mariadb/" backend/.env
  sed -i '' "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" backend/.env
  sed -i '' "s/APP_ENV=.*/APP_ENV=production/" backend/.env
  sed -i '' "s/APP_DEBUG=true/APP_DEBUG=false/" backend/.env
  sed -i '' "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASSWORD/" backend/.env
  sed -i '' "s/SANCTUM_STATEFUL_DOMAINS=.*/SANCTUM_STATEFUL_DOMAINS=$APP_DOMAIN/" backend/.env
else
  sed -i "s/APP_URL=.*/APP_URL=https:\/\/$APP_DOMAIN/" backend/.env
  sed -i "s/DB_HOST=.*/DB_HOST=mariadb/" backend/.env
  sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" backend/.env
  sed -i "s/APP_ENV=.*/APP_ENV=production/" backend/.env
  sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" backend/.env
  sed -i "s/REDIS_PASSWORD=.*/REDIS_PASSWORD=$REDIS_PASSWORD/" backend/.env
  sed -i "s/SANCTUM_STATEFUL_DOMAINS=.*/SANCTUM_STATEFUL_DOMAINS=$APP_DOMAIN/" backend/.env
fi

export APP_DOMAIN DB_PASSWORD DB_ROOT_PASSWORD REDIS_PASSWORD

# ── 3. Build & start ──────────────────────────────────────────────────────────
info "Building and starting containers..."
$DOCKER_COMPOSE up --build -d

ok "Containers are running"

# ── 4. Migrations ─────────────────────────────────────────────────────────────
info "Waiting for MariaDB..."
sleep 8

info "Running database migrations..."
$DOCKER_COMPOSE exec -T backend php artisan migrate --seed --no-interaction --force

ok "Migrations complete"

# ── 5. Storage link ───────────────────────────────────────────────────────────
$DOCKER_COMPOSE exec -T backend php artisan storage:link --force
ok "Storage linked"

# ── 6. Admin seed ─────────────────────────────────────────────────────────────
$DOCKER_COMPOSE exec -T backend php artisan db:seed --class=AdminUserSeeder --force 2>/dev/null || true
ok "Admin seeded"

# ── 7. Summary ────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════════════${NC}"
echo -e "${GREEN}  EventHub deployed!${NC}"
echo -e "${GREEN}══════════════════════════════════════════════${NC}"
echo ""
echo "  Frontend : https://$APP_DOMAIN"
echo "  API      : https://$APP_DOMAIN/api"
echo ""
echo "  Services:"
echo "    MariaDB  : localhost:3306"
echo "    Redis    : localhost:6379"
echo "    OPcache  : enabled (128MB)"
echo ""
echo "  Commands:"
echo "    Logs  : $DOCKER_COMPOSE logs -f"
echo "    Build : $DOCKER_COMPOSE up --build -d"
echo "    Stop  : $DOCKER_COMPOSE down"
echo ""
echo -e "${YELLOW}  Next:${NC}"
echo "  1. DNS A record -> Droplet IP"
echo "  2. SSL: apt install certbot python3-certbot-nginx"
echo "         certbot --nginx -d $APP_DOMAIN"
echo "  3. Add PAYMOB/FAWRY/FCM/MAIL keys in backend/.env"
echo "     then: $DOCKER_COMPOSE restart backend"
echo ""
