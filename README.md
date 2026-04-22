# FocusMatrix

> **Decide more. Do less. Lead better.**
> **Entscheiden statt abarbeiten.**

FocusMatrix is a bilingual (🇬🇧 English / 🇩🇪 German) SaaS for managers that turns the **Only‑You‑Principle** into a daily operating system. Every task gets forced through one question:

> **"Can only I really do this?"**

…and is routed into **Keep**, **Delegate**, or **Drop** — with structured guardrails for each path.

Built with **Laravel 11 · Inertia.js · Vue 3 · Tailwind CSS · Jetstream (teams + 2FA)**.

---

## ✦ Modules

| # | Module | Purpose |
|---|---|---|
| 1 | **Principle Dashboard** | Focus score, weekly stats, self‑check streak, guiding principle |
| 2 | **Triage Inbox** | Quick capture + the "Can only I do this?" wizard |
| 3 | **Decision Matrix (Keep)** | Auto‑categorises into the four Only‑You categories |
| 4 | **Delegation Cockpit** | Goal, frame, deadline, decision scope, resources, anti‑micromanagement |
| 5 | **Drop / Omit Lever** | Boldly kill tasks, meetings, reports with structured checklist |
| 6 | **Weekly Self‑Check** | Friday ritual with 4 reflection questions + focus score |
| 7 | **Organisation Check** | Team clarity checks (who decides what, by when) |
| 8 | **Guiding Principle widget** | Always‑visible sidebar reminder |
| — | **AI Co‑Pilot hook** | Heuristic suggestion today; OpenAI drop‑in ready |

---

## ✦ Tech Stack

- **Backend:** Laravel 11 (PHP 8.3)
- **Frontend:** Inertia.js + Vue 3 + Tailwind v3 + Headless UI + Heroicons
- **Auth / Teams:** Laravel Jetstream (Inertia stack, teams enabled)
- **i18n:** Laravel localization + `vue-i18n` — EN + DE
- **DB:** SQLite (dev) — swap to MySQL/Postgres in prod
- **Build:** Vite 5

---

## ✦ Local Setup

```bash
# 1. Install PHP deps
composer install

# 2. Install JS deps
npm install

# 3. Env + key
cp .env.example .env
php artisan key:generate

# 4. Database (SQLite default)
touch database/database.sqlite
php artisan migrate --seed

# 5. Build assets
npm run build

# 6. Serve
php artisan serve
# then open http://127.0.0.1:8000
```

For development with hot reload, run `npm run dev` alongside `php artisan serve`.

---

## ✦ Demo Accounts (from seeder)

| Email | Password | Role |
|---|---|---|
| `demo@focusmatrix.app` | `password` | Manager (EN) |
| `maria@focusmatrix.app` | `password` | Team member (DE) |

Log in with either, or register a new account at `/register`.

---

## ✦ Bilingual (EN / DE)

- Language switcher in both the marketing header and the authenticated app header.
- URL query `?lang=en` or `?lang=de` forces the locale and persists it (session + user profile).
- Browser `Accept‑Language` is used as the initial default.
- Frontend strings live in `resources/js/i18n/locales/{en,de}.json`.
- Server‑side strings use Laravel's `__()` helper.

---

## ✦ Design System

**Corporate navy + graphite** with an accent blue:

| Token | Hex | Role |
|---|---|---|
| `navy-900` | `#0d1c33` | Primary dark |
| `navy-800` | `#172a48` | Headers / sidebar |
| `accent` | `#2f6bff` | CTAs, highlights |
| `graphite-50` | `#f7f8fa` | App background |

Utility classes are exposed as `fm-card`, `fm-btn-primary`, `fm-badge-keep`, etc. in `resources/css/app.css`.

---

## ✦ Roadmap (post‑MVP)

- OpenAI / Anthropic Co‑Pilot (triage suggestions, delegation draft messages, weekly insights)
- Calendar integration + auto‑blocked focus time
- Slack / Teams / Outlook / Gmail integrations
- Voice capture (Whisper transcription)
- Stripe Cashier billing (Free / Pro €12 / Team €29 / Enterprise)
- Analytics dashboards + delegation health tracker
- SSO / SCIM (enterprise)
- DSGVO / GDPR: Impressum, data export, audit log

---

## ✦ License

Proprietary. © FocusMatrix.
