# Egypt Event Discovery Platform — PROJECT_MAP

## [TECH_STACK]

| Layer | Technology | Version | Status |
|-------|-----------|---------|--------|
| **Backend** | Laravel | ^13.9.0 | ✅ Active |
| **Language** | PHP | ^8.3 | ✅ Active |
| **Database** | MySQL 8.x / MariaDB 11.x | — | ✅ Active |
| **Mobile** | Flutter | ^3.29 | ✅ Active |
| **Web Client** | Next.js 15 + Tailwind CSS 4 | ^15.2 | ✅ Active |
| **State** | Riverpod (Mobile) / React Context (Web) | — | ✅ Active |
| **HTTP Client** | Dio (Mobile) / fetch (Web) | — | ✅ Active |
| **Payments** | Paymob API + Fawry API | — | ✅ Integrated |
| **QR** | mobile_scanner + qr_flutter | — | ✅ Active |
| **Notifications** | Laravel Notifications + FCM | — | ✅ Active |
| **Queue** | Laravel Queue (database driver) | — | ✅ Active |
| **Cache** | Laravel Cache (database driver) | — | ✅ Active |

**Excluded (Phase 2):** reserved seating, dynamic pricing, AI recommendations, native iOS/Android (using Flutter), blockchain/NFT, POS.

---

## [SYSTEM_FLOW]

### User Journeys (Attendee)

```
[Launch] → Onboarding → Home Feed
   ├── Browse Categories → Filter → Event Detail
   │     └── Select Ticket → Promo Code → Checkout → Paymob/Fawry → QR Ticket
   ├── Search → Results → Event Detail → Booking
   ├── My Bookings → Ticket List → QR Code → Check-in
   └── Profile → Edit → Settings → Logout
```

### User Journeys (Organizer)

```
[Login] → Dashboard
   ├── Create Event → Add Details → Set Ticket Types → Submit for Approval
   │     └── (auto_approve = on) → Published immediately
   │     └── (auto_approve = off) → Pending Approval → Admin Approves → Published
   ├── My Events → Sales Dashboard → Attendee List (CSV)
   ├── Check-in Scanner → Scan QR → Mark Used
   ├── Promo Codes → Create → Track Usage
   └── Payouts → History → Request Payout
```

### User Journeys (Admin)

```
[Login] → Admin Dashboard
   ├── Pending Events → Review → Approve / Reject
   ├── System Settings → Site Name, Date Format, Auto-approve Toggle
   └── Manage Users (future)
```

### User Journeys (Venue Owner)

```
[Login] → Dashboard
   ├── Venue Profile → Edit Gallery → Amenities → Capacity
   ├── Calendar → Availability
   └── Inquiries → Respond
```

### API Data Flow

```
Flutter App ──HTTPS──► Laravel API ──Eloquent──► MySQL
                            │
                      [Queue: Jobs]
                            │
                     [Email/SMS] ──► SendGrid/FCM
                            │
                     [Paymob/Fawry] ──► Payment Gateway
```

---

### Domain Breakdown (Web — Next.js 15 App Router)

```
web/
├── app/
│   ├── layout.tsx              — RootLayout + AuthProvider + Header
│   ├── page.tsx                — Home (hero, categories, event grid)
│   ├── login/page.tsx          — Login form
│   ├── register/page.tsx       — Register form with role selector
│   ├── search/page.tsx         — Search with text + category filter
│   ├── events/[id]/page.tsx    — Event detail + ticket types + buy CTA
│   ├── checkout/[id]/page.tsx  — Quantity selector + customer form + submit
│   ├── profile/
│   │   ├── page.tsx            — Profile overview + links
│   │   ├── tickets/page.tsx    — My tickets list
│   │   └── settings/page.tsx   — App settings
│   ├── organizer/
│   │   ├── dashboard/page.tsx     — Stats cards + event table with actions
│   │   ├── events/new/page.tsx    — Create event form (venue, is_free, cover)
│   │   └── events/[id]/edit/page.tsx — Edit event form
│   ├── admin/
│   │   ├── layout.tsx          — Admin sidebar navigation
│   │   ├── page.tsx            — Stats cards (events, users, organizers, revenue)
│   │   ├── events/page.tsx     — All events table + approve/reject/delete actions
│   │   ├── users/page.tsx      — User list + toggle active/suspended
│   │   ├── organizers/page.tsx — Organizer list + verify/unverify
│   │   └── settings/page.tsx   — System settings editor
│   └── not-found.tsx
├── components/
│   ├── layout/
│   │   ├── auth-provider.tsx   — React Context auth state
│   │   └── header.tsx          — Nav bar with auth-aware links
│   ├── events/
│   │   ├── event-card.tsx      — Card with image, title, price, city
│   │   ├── event-grid.tsx      — Fetch + render grid of EventCards
│   │   └── category-bar.tsx    — Horizontal category icons
│   └── ui/                    — Placeholder for shared UI primitives
├── lib/
│   ├── types.ts               — TypeScript interfaces (User, Event, Ticket, etc.)
│   └── api.ts                 — fetch wrapper with auth token injection
└── public/images/
```

## [ARCHITECTURE]

### Domain Breakdown (Backend — Laravel)

```
app/
├── Models/
│   ├── User.php              — morphable roles, preferences JSON
│   ├── Organizer.php         — bilingual fields, social_media JSON
│   ├── Venue.php             — bilingual, gallery JSON, amenities JSON
│   ├── Event.php             — bilingual, gallery JSON, tags JSON
│   ├── TicketType.php        — price, currency EGP, quantity
│   ├── Order.php             — order_number UUID, payment_details JSON
│   ├── Ticket.php            — ticket_code UUID, qr_code_url
│   ├── PromoCode.php         — discount_type, usage tracking
│   ├── Comment.php           — threaded (parent_id), approval
│   ├── Follow.php            — user <-> organizer
│   ├── UserEventAction.php   — attending/interested/saved
│   ├── Payout.php            — status workflow
│   └── SystemSetting.php     — key/value config store
│
├── Http/Controllers/Api/
│   ├── AuthController.php
│   ├── ProfileController.php
│   ├── EventController.php   — search, filter, CRUD, publish with auto-approve
│   ├── TicketController.php  — purchase, my tickets, QR
│   ├── CheckInController.php — scan + verify
│   ├── OrganizerController.php
│   ├── VenueController.php
│   ├── PromoCodeController.php
│   ├── CommentController.php
│   ├── FollowController.php
│   ├── PayoutController.php
│   ├── OrderController.php
│   └── AdminController.php   — dashboard stats, events CRUD, users, organizers, venues, settings
│
├── Services/
│   ├── PaymentService.php    — Paymob + Fawry abstraction
│   ├── TicketService.php     — purchase logic, code gen, QR
│   ├── CheckInService.php    — validation + idempotency
│   └── SystemConfig.php      — cached key/value config reader
│
├── Events/
│   ├── TicketPurchased.php
│   └── EventPublished.php
│
└── Notifications/
    ├── TicketConfirmation.php
    └── CheckInSuccess.php
```

### Domain Breakdown (Mobile — Flutter)

```
lib/
├── core/
│   ├── config/
│   │   ├── app_config.dart         — base URL, timeouts
│   │   └── theme.dart              — colors, typography, RTL
│   ├── network/
│   │   ├── api_client.dart         — Dio wrapper
│   │   ├── api_exceptions.dart     — error handling
│   │   └── interceptors/
│   │       └── auth_interceptor.dart
│   └── storage/
│       └── local_storage.dart      — shared preferences
│
├── features/
│   ├── auth/                       — login, register, onboarding
│   ├── events/                     — browse, detail, search
│   ├── booking/                    — ticket select, checkout, confirmation
│   ├── profile/                    — settings, my tickets, history
│   ├── admin/                      — admin dashboard, events, settings
│   ├── organizer/                  — dashboard, create event, analytics
│   └── checkin/                    — QR scanner for organizers
│
├── shared/
│   ├── models/
│   │   ├── user.dart
│   │   ├── event.dart
│   │   ├── ticket.dart
│   │   ├── organizer.dart
│   │   └── venue.dart
│   ├── services/
│   │   └── notification_service.dart
│   └── widgets/
│       ├── event_card.dart
│       ├── loading.dart
│       ├── error_widget.dart
│       └── empty_state.dart
```

---

## Database Schema (MySQL)

Tables implemented: `users`, `password_reset_tokens`, `sessions`, `personal_access_tokens`, `user_roles`, `organizers`, `venues`, `events`, `ticket_types`, `orders`, `tickets`, `promo_codes`, `comments`, `follows`, `user_event_actions`, `payouts`, `system_settings`, `jobs`, `job_batches`, `failed_jobs`, `cache`, `cache_locks`.

Full schema in `docs/event_platform_schema.sql`.

---

## [ORPHANS & PENDING]

| ID | Item | Status | Target Milestone |
|----|------|--------|-----------------|
| M-01 | Laravel project scaffolding + composer.json | ✅ Done | M1 |
| M-02 | All MySQL migrations (20 tables) | ✅ Done | M1 |
| M-03 | Seeder: admin user + demo data | ✅ Done | M1 |
| M-04 | Auth Controller (register, login, logout, me) | ✅ Done | M2 |
| M-05 | User role assignment + middleware | ✅ Done | M2 |
| M-06 | Organizer CRUD + verification | ✅ Done | M3 |
| M-07 | Venue CRUD | ✅ Done | M3 |
| M-08 | Event CRUD + search/filter + status workflow | ✅ Done | M3 |
| M-09 | TicketType CRUD (no reserved seating) | ✅ Done | M4 |
| M-10 | Order/Ticket purchase flow + PaymentService | ✅ Done | M4 |
| M-11 | QR code generation + ticket PDF | ✅ Done | M4 |
| M-12 | Promo Code engine + validation | ✅ Done | M4 |
| M-13 | Check-in system (scan + verify) | ✅ Done | M4 |
| M-14 | Comments (threaded) + moderation | ✅ Done | M5 |
| M-15 | Follows + UserEventActions (save/attend) | ✅ Done | M5 |
| M-16 | Payouts for organizers | ✅ Done | M5 |
| M-17 | Dashboard analytics endpoints | ✅ Done | M5 |
| M-18 | Flutter app scaffold + Dio client + theme | ✅ Done | M6 |
| M-19 | Flutter auth screens (login, register, onboarding) | ✅ Done | M6 |
| M-20 | Flutter event discovery (home, search, detail) | ✅ Done | M7 |
| M-21 | Flutter booking flow + payment | ✅ Done | M7 |
| M-22 | Flutter profile + my tickets | ✅ Done | M7 |
| M-23 | Flutter organizer dashboard + event mgmt | ✅ Done | M8 |
| M-24 | Flutter check-in scanner | ✅ Done | M8 |
| M-25 | Backend tests (Feature + Unit) | ✅ Done | M8 |
| M-26 | Flutter widget tests | ⏳ Pending | M8 |
| M-27 | Web client scaffold + auth pages | ✅ Done | M9 |
| M-28 | Web event discovery (home, search, detail) | ✅ Done | M9 |
| M-29 | Web booking/checkout flow | ✅ Done | M9 |
| M-30 | Web profile + tickets | ✅ Done | M9 |
| M-31 | Web organizer dashboard + event creation | ✅ Done | M9 |
| M-32 | Admin moderation workflow + pending_approval status | ✅ Done | M10 |
| M-33 | System settings (site name, date format, auto-approve toggle) | ✅ Done | M10 |
| M-34 | Admin approve/reject API + event status badge UI | ✅ Done | M10 |
| M-35 | Event status `pending_approval` in Flutter + Web dashboards | ✅ Done | M10 |
| M-36 | Admin dashboard API (stats, events, users, organizers, venues) | ✅ Done | M10 |
| M-37 | Admin web pages (layout, dashboard, events, users, organizers, settings) | ✅ Done | M10 |
| M-38 | Admin Flutter screens (dashboard, events, settings) | ✅ Done | M10 |
| M-39 | AdminTest for all admin API endpoints | ✅ Done | M10 |
| M-40 | Admin nav link in header when user has admin role | ✅ Done | M10 |
| M-41 | Organizer dashboard: per-event stats, publish/cancel actions, event table | ✅ Done | M10 |
| M-42 | Organizer create event: venue dropdown, is_free, cover_image fields | ✅ Done | M10 |
| M-43 | Organizer edit event page (web) | ✅ Done | M10 |
| M-44 | Organizer dashboard mobile: event list with publish/cancel actions | ✅ Done | M10 |
| M-45 | OrganizerTest for dashboard stats, update, cancel | ✅ Done | M10 |

---

## Milestones (Verifiable Goals)

| Milestone | Deliverable | Verification |
|-----------|------------|--------------|
| **M1** | Backend scaffold + migrations + seeders | `php artisan migrate:fresh --seed` succeeds |
| **M2** | Auth + Roles + Profile APIs | POST /api/register returns 201 with token |
| **M3** | Organizer + Venue + Event CRUD | POST /api/events returns 201; search returns results |
| **M4** | Ticketing + Payments + Promo + Check-in | Purchase flow: POST /api/orders → QR code → scanner validates |
| **M5** | Social + Payouts + Dashboard | Attendee follows organizer; payout status workflow |
| **M6** | Flutter scaffold + Auth | User registers and logs in via mobile |
| **M7** | Flutter Event Discovery + Booking | User discovers event → buys ticket → sees QR |
| **M8** | Flutter Organizer/Check-in + Tests | Organizer scans ticket → status changes to 'used' |
| **M9** | Web Client (Next.js) auth + browse + booking + organizer | User registers, browses events, purchases ticket on web |
| **M10** | Admin dashboard (API + Web + Mobile) + event images | Admin approves events, manages users/organizers/venues/settings from web and mobile |
