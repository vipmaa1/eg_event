# EventHub — Egypt Event Discovery Platform

Multi-platform event discovery and ticketing platform serving the Egyptian market with bilingual (AR/EN) support.

## Tech Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 13 / PHP 8.3 / MySQL 8.0 |
| **Web** | Next.js 15 (App Router) / React 19 / TypeScript / Tailwind CSS 4 |
| **Mobile** | Flutter 3.29 / Dart 3.7 / Riverpod |
| **Auth** | Laravel Sanctum (token-based) |
| **Payments** | Paymob + Fawry (Egyptian gateways) |
| **Notifications** | FCM (push) / SendGrid (email) |
| **Infra** | Docker Compose (nginx, PHP-FPM, Next.js, MySQL) |

## Project Structure

```
events/
├── backend/          Laravel API
│   ├── app/
│   │   ├── Http/Controllers/Api/   14 API controllers
│   │   ├── Models/                 14 Eloquent models
│   │   └── Services/               4 business services
│   ├── config/                     5 config files
│   ├── database/
│   │   ├── migrations/             9 migration files
│   │   └── seeders/                4 seeders
│   ├── routes/api.php              ~50 API endpoints
│   └── tests/                      Feature + Unit tests
├── web/              Next.js client
│   ├── app/                        App Router pages (18 routes)
│   ├── components/                 Reusable React components
│   └── lib/                        Types + API client
├── mobile/           Flutter app
│   ├── lib/
│   │   ├── core/                   Config, network, theme
│   │   ├── features/               6 feature modules
│   │   └── shared/                 Models, widgets
│   └── assets/                     Images, fonts
├── deploy/          Production deployment
│   ├── deploy.sh                   DigitalOcean deployment script
│   ├── backend.Dockerfile          Multi-stage PHP-FPM image
│   ├── web.Dockerfile              Multi-stage Next.js image
│   └── nginx.conf                  Reverse proxy config
├── docs/             Business plan, UI/UX spec, SQL schema
└── docker-compose.yml              Production services
```

## User Roles

- **Attendee** — browse events, purchase tickets, comment, follow organizers
- **Organizer** — create and manage events, track sales, check-in attendees
- **Venue Owner** — manage venue profiles and availability
- **Admin** — approve events, manage users/organizers/venues, system settings

## Quick Start

### Backend

```bash
cd backend
cp .env.example .env
composer install
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

### Web

```bash
cd web
npm install
npm run dev
```

### Mobile

```bash
cd mobile
flutter pub get
flutter run
```

### Docker (Production)

```bash
docker compose up -d
```

## API Endpoints

**Public:** `POST /api/register`, `POST /api/login`, `GET /api/events`, `GET /api/events/{id}`, `GET /api/organizers`, `GET /api/venues`

**Authenticated:** Profile, event CRUD, ticket purchase, check-in, promo codes, comments, follows, event actions, payouts

**Admin:** Dashboard stats, event approval, user/organizer/venue management, system settings

## Key Features

- Bilingual (Arabic/English) across all interfaces
- QR code ticketing with scanner check-in
- Promo code discounts
- Multi-gateway payments (Paymob, Fawry)
- Organizer payout system
- Real-time event search and filtering
- Role-based access control
- Soft-deletes and status workflows
- Mobile-first responsive design
