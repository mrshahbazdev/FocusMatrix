# FocusMatrix — Local Dev & Testing

## Local Development Setup

### Prerequisites
- PHP 8.3+ (Laravel 13 / Jetstream requirement). On Ubuntu, install via `ppa:ondrej/php`:
  ```
  sudo add-apt-repository ppa:ondrej/php -y
  sudo apt-get install -y php8.3-cli php8.3-mbstring php8.3-xml php8.3-curl php8.3-zip php8.3-sqlite3
  ```
- Node.js (v22+ works), npm
- Composer (use `php8.3 /usr/local/bin/composer` if system PHP is older)

### Setup Steps
1. `cp .env.example .env`
2. `php8.3 /usr/local/bin/composer install --no-interaction`
3. `npm install`
4. `php8.3 artisan key:generate`
5. `touch database/database.sqlite` (SQLite is default DB)
6. `php8.3 artisan migrate --force`
7. Set `APP_URL=http://localhost:8000` in `.env`
8. Set `MAIL_MAILER=log` in `.env` (no SMTP server needed locally; emails go to `storage/logs/laravel.log`)

### Running Servers
- PHP: `php8.3 artisan serve --host=0.0.0.0 --port=8000`
- Vite: `npx vite --host 0.0.0.0` (may auto-pick port 5173 or 5174)
- App at `http://localhost:8000`

### Creating Test Users (via tinker)
```bash
php8.3 artisan tinker --execute="
\$user = \App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@test.com',
    'password' => bcrypt('password123'),
    'email_verified_at' => now(),
]);
\$team = \$user->ownedTeams()->create([
    'name' => 'Test Team',
    'personal_team' => true,
]);
\$user->forceFill(['current_team_id' => \$team->id])->save();
echo 'User created: ' . \$user->id;
"
```

## Architecture Notes

### Default Branch
The default branch might not be `main` — check `git remote show origin | grep 'HEAD branch'`.

### Layout System
- `FocusLayout` — Main app layout with navy sidebar. Used by all authenticated pages.
- `AppLayout` — Old Jetstream layout (no longer used, but still exists in codebase).
- Auth pages use a split-screen layout (navy branding panel + form).

### Design Tokens (Tailwind)
- **Navy**: Primary dark palette (navy-900 = `#0d1c33`)
- **Graphite**: Neutral grays (graphite-500 = `#646d7d`)
- **Accent**: Blue highlight (`#2f6bff`)
- No indigo or default gray colors should appear in the UI.

### Team Invitations
- **Custom accept route**: `GET /accept-invitation/{invitation}` (inside `auth:sanctum + verified` middleware)
- Controller: `AcceptInvitationController` — validates authenticated user's email matches invitation email
- **Auto-accept on registration**: `CreateNewUser::acceptPendingInvitations()` automatically accepts all pending invitations when a new user registers with a matching email
- **Email template**: `resources/views/emails/team-invitation.blade.php` — two buttons: "Create Account" and "Accept Invitation"
- **Mail class**: `App\Mail\TeamInvitationMail` — generates simple route URL (no signed URLs)

## Testing Team Invitations

### Creating Invitations via UI
1. Log in as team owner
2. Navigate to Team Settings (sidebar link)
3. Scroll to "Add Team Member" form
4. Enter email, select role, click "Add"

### Creating Invitations via Tinker
```bash
php8.3 artisan tinker --execute="
\$model = \Laravel\Jetstream\Jetstream::teamInvitationModel();
\$inv = new \$model;
\$inv->team_id = 1;
\$inv->email = 'invited@test.com';
\$inv->role = 'editor';
\$inv->save();
echo 'ID=' . \$inv->id;
"
```

### Testing Accept Flow
1. Create invitation for an email
2. Create/use a user with that email
3. Log in as that user
4. Visit `http://localhost:8000/accept-invitation/{id}`
5. Should redirect to dashboard; verify via DB:
   ```bash
   php8.3 artisan tinker --execute="\$u = \App\Models\User::where('email','invited@test.com')->first(); echo \$u->allTeams()->pluck('name');"
   ```

### Security Tests
- Wrong email: Visit accept URL while logged in as different email → expect 403
- Non-existent invitation: Visit `/accept-invitation/99999` → expect redirect to dashboard

## Key Pages to Verify
- `/dashboard` — Main dashboard with FocusLayout sidebar
- `/teams/{id}` — Team Settings (navy heading, graphite subtext, Add Member form)
- `/user/profile` — Profile Settings (Profile Info, Password, 2FA, Browser Sessions, Delete Account)
- `/user/api-tokens` — API Tokens management
- `/login`, `/register` — Auth pages with split-screen branded layout

## Devin Secrets Needed
No secrets required for local testing. SMTP is set to `log` driver locally.
For production testing, would need `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD` for actual email delivery.
