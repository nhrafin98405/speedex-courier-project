# SpeedEx Courier Service — Realtime Edition

Production-ready PHP 8 + MySQL courier platform with realtime AJAX dashboard,
live parcel tracking, branded email notification system (PHPMailer + SMTP),
glassmorphism UI, dark/light theme, collapsible sidebar and full admin/hub
manager modules.

## Quick Start
1. Import `database/speedex.sql` into MySQL (creates `speedex_courier`).
2. Edit `config/database.php` (DB creds) and `config/mail.php` (SMTP creds).
3. (Optional) `composer require phpmailer/phpmailer` for SMTP. Without it the
   system falls back to PHP `mail()` and still logs every send.
4. Place the folder under your web root (e.g. `htdocs/speedex-courier`) and
   open `http://localhost/speedex-courier/`.
5. Login: `admin@speedex.com` / `password`

## What's New (Realtime Edition)
- Email notification system replacing SMS (registration, booked, in-transit,
  out-for-delivery, delivered) with branded HTML templates in `/emails`.
- Realtime dashboard auto-refresh every 5s via `/api/*` JSON endpoints.
- Live parcel tracking (`tracking.php`) with auto-refreshing timeline.
- Multi-section Send Parcel page with auto-generated tracking ID + cost calc.
- Add Hub admin page and Hub option in registration.
- Collapsible animated sidebar (state saved in localStorage).
- Notifications dropdown with unread badge.
- Email Logs admin page, Activity Logs, Reports w/ Chart.js.
- App loader, toasts, skeleton loaders, floating gradient orbs, neon glow.
- CSRF tokens, PDO prepared statements, password hashing.

## API Endpoints (JSON)
- `/api/dashboard-stats.php`
- `/api/recent-orders.php`
- `/api/chart-data.php`
- `/api/notifications.php`
- `/api/track.php?id=SPX...`
- `/api/live-activities.php`
- `/api/hub-stats.php`
