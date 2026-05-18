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

# ── 1. Check prerequisites ────────────────────────────────────────────────────
info "Checking prerequisites..."

command -v docker >/dev/null 2>&1 || { err "Docker is not installed. Install it first."; exit 1; }
command -v docker-compose >/dev/null 2>&1 || docker compose version >/dev/null 2>&1 || { err "Docker Compose is not installed."; exit 1; }

DOCKER_COMPOSE="docker-compose"
if docker compose version >/dev/null 2>&1; then
  DOCKER_COMPOSE="docker compose"
fi

ok "Docker & Docker Compose are available"

# ── 2. Setup production environment ────────────────────────────────────────────
if [ ! -f backend/.env ]; then
  info "Creating backend/.env from template..."
  cp backend/.env.example backend/.env
  APP_KEY=$(php -r "echo 'base64:' . base64_encode(random_bytes(32));")
  if [[ "$OSTYPE" == "darwin"* ]]; then
    sed -i '' "s/APP_KEY=/APP_KEY=$APP_KEY/" backend/.env
  else
    sed -i "s/APP_KEY=/APP_KEY=$APP_KEY/" backend/.env
  fi
  ok "Generated APP_KEY and created .env"
fi

# Prompt for domain
read -rp "Enter your domain (e.g., eventhub.example.com): " APP_DOMAIN
if [ -z "$APP_DOMAIN" ]; then
  warn "No domain set. Will use localhost. Configure APP_DOMAIN later."
  APP_DOMAIN="localhost"
fi

# Prompt for DB password
read -rsp "Enter MySQL password for 'eventhub' user: " DB_PASSWORD
echo ""
if [ -z "$DB_PASSWORD" ]; then
  DB_PASSWORD="changeme"
  warn "Using default DB password! Change it immediately."
fi

read -rsp "Enter MySQL root password: " DB_ROOT_PASSWORD
echo ""
if [ -z "$DB_ROOT_PASSWORD" ]; then
  DB_ROOT_PASSWORD="rootpass"
  warn "Using default root password! Change it immediately."
fi

# Update .env with production values
if [[ "$OSTYPE" == "darwin"* ]]; then
  sed -i '' "s/APP_URL=http:\/\/localhost:8000/APP_URL=https:\/\/$APP_DOMAIN/" backend/.env
  sed -i '' "s/DB_HOST=.*/DB_HOST=mysql/" backend/.env
  sed -i '' "s/DB_USERNAME=.*/DB_USERNAME=eventhub/" backend/.env
  sed -i '' "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" backend/.env
  sed -i '' "s/APP_ENV=.*/APP_ENV=production/" backend/.env
  sed -i '' "s/APP_DEBUG=true/APP_DEBUG=false/" backend/.env
  sed -i '' "s/SANCTUM_STATEFUL_DOMAINS=.*/SANCTUM_STATEFUL_DOMAINS=$APP_DOMAIN/" backend/.env
else
  sed -i "s/APP_URL=http:\/\/localhost:8000/APP_URL=https:\/\/$APP_DOMAIN/" backend/.env
  sed -i "s/DB_HOST=.*/DB_HOST=mysql/" backend/.env
  sed -i "s/DB_USERNAME=.*/DB_USERNAME=eventhub/" backend/.env
  sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=$DB_PASSWORD/" backend/.env
  sed -i "s/APP_ENV=.*/APP_ENV=production/" backend/.env
  sed -i "s/APP_DEBUG=true/APP_DEBUG=false/" backend/.env
  sed -i "s/SANCTUM_STATEFUL_DOMAINS=.*/SANCTUM_STATEFUL_DOMAINS=$APP_DOMAIN/" backend/.env
fi

export APP_DOMAIN DB_PASSWORD DB_ROOT_PASSWORD

# ── 3. Pull images & build ────────────────────────────────────────────────────
info "Building and starting containers..."
$DOCKER_COMPOSE up --build -d

ok "Containers are running"

# ── 4. Run migrations & seeders ────────────────────────────────────────────────
info "Waiting for MySQL to be ready..."
sleep 10

info "Running database migrations..."
$DOCKER_COMPOSE exec -T backend php artisan migrate --seed --no-interaction --force

ok "Migrations complete"

# ── 5. Create storage symlink ──────────────────────────────────────────────────
info "Creating storage symlink..."
$DOCKER_COMPOSE exec -T backend php artisan storage:link --force

ok "Storage linked"

# ── 6. Create admin user (optional) ────────────────────────────────────────────
info "Creating admin user..."
$DOCKER_COMPOSE exec -T backend php artisan db:seed --class=AdminUserSeeder --force 2>/dev/null || true

ok "Admin seeded (if seeder exists)"

# ── 7. Summary ─────────────────────────────────────────────────────────────────
echo ""
echo -e "${GREEN}══════════════════════════════════════════════${NC}"
echo -e "${GREEN}  Deployment complete!${NC}"
echo -e "${GREEN}══════════════════════════════════════════════${NC}"
echo ""
echo "  Frontend : http://$APP_DOMAIN"
echo "  API      : http://$APP_DOMAIN/api"
echo ""
echo "  Useful commands:"
echo "    View logs : $DOCKER_COMPOSE logs -f"
echo "    Restart   : $DOCKER_COMPOSE restart"
echo "    Stop      : $DOCKER_COMPOSE down"
echo "    Update    : git pull && $DOCKER_COMPOSE up --build -d"
echo ""
echo -e "${YELLOW}  Next steps:${NC}"
echo "  1. Set up DNS A record pointing to your Droplet IP"
echo "  2. Configure SSL with:"
echo "     $DOCKER_COMPOSE exec nginx apk add certbot certbot-nginx"
echo "     $DOCKER_COMPOSE exec nginx certbot --nginx -d $APP_DOMAIN"
echo "  3. Update PAYMOB/FAWRY/FCM keys in backend/.env and restart:"
echo "     $DOCKER_COMPOSE restart backend"
echo ""
