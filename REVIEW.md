# Senior Code Director Review — `base-site`

**Reviewer focus:** security + ease of customization (per your request)
**Verdict:** The project is **not runnable out-of-the-box**. There is a PHP fatal parse error, an Apache `.htaccess` syntax error, the `.env` file is never loaded, the documented `migrate.php` does not exist, the seeded admin password hash is a placeholder, and several public endpoints have catastrophic security bugs. Customization is also painful: the `settings` table is defined but never read; brand/contact data are hardcoded across views.

Severity legend:
- **C** = Critical (app broken or RCE/account-takeover class)
- **H** = High (security/data integrity)
- **M** = Medium (correctness, UX, maintainability)
- **L** = Low (style/SEO/docs)

---

## C1 — PHP fatal parse error in the admin dashboard
**File:** `admin/index.php:18`
```php
user_data = getUser();   // missing $ sigil → Parse error
```
The entire admin dashboard 500s. No one can log in and reach `/admin/`.
**Fix:** `$user_data = getUser();`

## C2 — `.htaccess` has `End` instead of `</IfModule>` (twice)
**File:** `.htaccess:47, .htaccess:63`
Apache treats this as a syntax error → **HTTP 500 on every request**. The whole site is down before PHP even runs.
**Fix:** replace both `End` with `</IfModule>`.

## C3 — `.env` is never loaded
**Files:** `composer.json`, `config/constants.php`
- `composer.json` has no `vlucas/phpdotenv` (or any loader).
- `constants.php` uses `getenv(...)` but nothing parses `.env` into the environment.
- Result: every `getenv()` returns `false` and the `?:` defaults kick in →
  **DB connects to `root@localhost` with empty password** in production by default. Rate limiting silently disabled (`RATE_LIMIT_ENABLED` check is `=== 'true'`, but no env loader means it's never `'true'`). Debug stays off (lucky) but is also unreachable.
**Fix:** add a tiny loader (see patch in `includes/env.php`) and call it from `constants.php`.

## C4 — `migrate.php` does not exist
`composer.json` (`"migrate"` script), `SETUP.md` step 5, "Updating the Site" all reference `php migrate.php`. The file isn't in the repo. The documented installation procedure is impossible.
**Fix:** ship a `migrate.php` that runs `database/schema.sql`.

## C5 — Default admin password hash is invalid
**File:** `database/schema.sql:358`
```
$2y$12$G8C3bE4Xx8/cZ9KvI8E5Oe2L8V3xR6W9P4Q5S6T7U8V9W0X1Y2Z3
```
Not a real bcrypt hash (cost segment is fine but the salt+digest portion is hand-typed gibberish). `password_verify('password', …)` returns false. SETUP.md says you can sign in with `admin@example.com / password` — you can't.
**Fix:** regenerate hash with `password_hash('password', PASSWORD_BCRYPT, ['cost' => 12])` and ship that. Better: prompt during install.

## C6 — No `session_start()` in admin pages
**Files:** `admin/login.php`, `admin/index.php`, `admin/users.php`, `admin/pages.php`, `admin/blog.php`, `admin/leads.php`, `admin/settings.php`, `admin/logout.php`.
Each calls `isAuthenticated()`/sets `$_SESSION` *without* starting a session. Admin login writes `$_SESSION['user_id']` into nothing — login does not persist; you immediately bounce back to the login page. The session is only started inside `includes/head.php`, which admin pages don't include.
**Fix:** ensure every admin entry-point starts a session before reading `$_SESSION`.

## C7 — Password-reset token leakage (account takeover)
**File:** `includes/Auth.php:202`
```php
return ['success' => true, 'message' => '...', 'token' => $token];
```
The function returns the **plaintext reset token** in its response. Any unauthenticated caller can request a reset for any email and immediately reset the password. Catastrophic.
**Fix:** never return the token. Send it by email or to an authenticated channel only.

## C8 — IP-spoofing bypass for rate limit & logging
**Files:** `includes/Security.php::getClientIP`, `includes/RateLimiter.php::getClientIP`
Both blindly trust `HTTP_CLIENT_IP` and `HTTP_X_FORWARDED_FOR`. An attacker can vary the header per request to make every login attempt count as a different IP. **Brute-force protection is effectively zero.** (Worse, the lead/user audit logs are also poisoned.)
**Fix:** only honor proxy headers when behind a known/trusted proxy (config flag), and always fall back to `REMOTE_ADDR`.

## C9 — Robots.txt served as PHP, not plain text
**File:** `robots.txt:1-6`
The file starts with `<?php /** … */ ?>`. Because the file extension is `.txt`, Apache will serve it verbatim (PHP isn't invoked) — search engines see the literal PHP comment block at the top, may stop parsing, and at best produce garbage. If you ever switch handlers, the inverse goes wrong. Either way it's broken.
**Fix:** delete the PHP block; it's a plain-text resource.

## C10 — Root `/` returns directory listing or 403
There is no `index.php` at the project root. Apache's `DirectoryIndex` fallback either lists files or 403s. All "Home" links in the nav (`url('/')`) lead to nothing. Also, `pages/index.php` mis-paths its includes (`__DIR__ . '/config/...'` instead of `__DIR__ . '/../config/...'`) and would fatal if hit directly.
**Fix:** add a real `index.php` at the root.

---

## H1 — No CSRF validation on the admin login (and most forms)
**Files:** `admin/login.php`, `admin/settings.php`
The login form has neither a CSRF token field nor a `Security::verifyCSRFToken(...)` call. `admin/settings.php` likewise (it doesn't even POST). Only `pages/contact.php` renders a CSRF token field — and then **never validates it**. Effectively no CSRF protection anywhere.
**Fix:** render `Security::getCSRFField()` on every state-changing form and `verifyCSRFToken` on every POST handler.

## H2 — Auth allows suspended accounts to log in
**File:** `includes/Auth.php:86`
```php
if ($user['status'] !== 'active') ...
```
… looks fine, but the rule says "block if not active". OK — wait, that *is* correct. Sorry — the real issue is the *opposite*: the comparison treats any status value other than `'active'` as inactive, including `'suspended'`. That actually *is* correct. **Strike this** — but read on for related issues:
- `Auth::register()` defaults to `status='active'` with role 3 (Editor) and is callable by *any code path* that includes Auth.php — there is no rate-limit and no CSRF on register pages either. Today there is no public register form, but the function is a footgun waiting to be exposed.

## H3 — `Security::sanitize()` destroys legitimate input and gives false sense of security
**File:** `includes/Security.php:44`
`strip_tags(trim($data))` is applied to *every* contact-form field including the message. Users lose `<` and `>` (e.g., when describing code) yet the function is called "sanitize", suggesting it's an XSS defense — it isn't. Output escaping (`e()`) is the actual defense, and you already do it.
**Fix:** keep input as-is (or only trim); escape on output.

## H4 — Rate limiter never decrements / resets
**File:** `includes/RateLimiter.php`
- `isLimited()` calls `logAttempt()` even on success → every legitimate page view increments the counter.
- On successful login `Auth::login()` does not call `$rateLimiter->reset()` → 5 typos lock the user out forever (within the window).
- `RateLimiter::reset()` exists but is never called from anywhere.
- Also, `bind(':cutoff_time', $cutoffTime)` — `$cutoffTime` is an int, but the database column expects a `DATETIME`; `FROM_UNIXTIME(...)` saves it. OK for MySQL but the bind is `int`, and the SQL uses `FROM_UNIXTIME(:cutoff_time)`. Make sure the bind type is `PDO::PARAM_INT`.
**Fix:** log only on actual failed attempts (decouple `isLimited` from logging), and reset on success.

## H5 — Remember-me is completely broken
**File:** `includes/Auth.php:117-121, 145-152`
- The token is stored only in `$_SESSION['remember_token']`. After logout, `session_destroy()` wipes it. After session expiry, the cookie still exists but has nothing to compare against → useless.
- There is no schema for `remember_tokens` and no logic that reads the cookie on revisit.
**Fix:** either remove the feature or add a `remember_tokens` table (user_id, selector, hashed_validator, expires_at) and a middleware that re-authenticates from the cookie.

## H6 — Permissive Content-Security-Policy + missing CDN host
**File:** `includes/Security.php:141`
```
script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
style-src  'self' 'unsafe-inline' https://cdn.jsdelivr.net;
```
- `'unsafe-inline'` on script-src effectively defeats CSP as XSS protection. Use nonces or hashes.
- Font Awesome is loaded from `cdnjs.cloudflare.com` in `head.php`; CSP doesn't allow it → **icons get blocked**.
- No `font-src` allowance for Google Fonts beyond `fonts.googleapis.com` (the CSS), but `fonts.gstatic.com` (the font files) is not whitelisted → **fonts get blocked**.
**Fix:** add `cdnjs.cloudflare.com`, `fonts.gstatic.com`; switch to nonces.

## H7 — `Strict-Transport-Security` sent on HTTP too
**File:** `includes/Security.php:140`
HSTS over plain HTTP is ignored by browsers but is also a footgun: if your local dev URL is `http://localhost`, the moment you switch to HTTPS once, browsers pin HSTS for a year. Send HSTS only when `$_SERVER['HTTPS']`.

## H8 — Secure cookies on plain HTTP → cookies silently dropped
**File:** `includes/Auth.php:119`
```php
setcookie('remember_token', $rememberToken, time()+30d, '/', '', true, true);
```
`secure=true` is hardcoded even on HTTP. The cookie is not stored, and remember-me silently fails on local dev. Should follow the same HTTPS detection as session cookie.

## H9 — Database singleton holds one prepared statement → race-like behavior in same request
**File:** `config/database.php`
`$this->statement` is overwritten on each `prepare()`. If anything ever interleaves (e.g., a callback or include during fetch), the first statement is lost. Not a security bug, but a latent correctness bug. Recommended: have `prepare()` return a new fluent object containing the statement.

## H10 — `Auth::register()` skips username validation
Empty username (or one with special chars/spaces) is accepted. Login lookup is by email so it works — but `username` is unique → first empty username takes the slot.

## H11 — No HTTPS redirect, no SameSite on session cookie set in raw `setcookie`
- `.htaccess` lines 17–18 commented out. Default deployment is HTTP.
- Session cookie has `samesite='Strict'` (good), but the remember-me cookie has no SameSite at all.

## H12 — Direct-access leakage of dev files
`.htaccess` denies `.env`, `.config`, `.sql`, `.log` (by extension). It does **not** deny `composer.json`, `composer.lock`, `package.json`, `package-lock.json`, `README.md`, `SETUP.md`, `SECURITY.md`, `CONTRIBUTING.md`, `.gitignore`. All are publicly readable at `/composer.json` etc.
**Fix:** add a broader rule.

## H13 — No SRI integrity hashes on CDN assets
Bootstrap CSS/JS and FA load from CDNs without `integrity=` attributes → supply-chain risk if CDN is compromised.

---

## M1 — Customization is painful (you asked specifically about this)
- **Hard-coded brand & contact data** in `includes/footer.php` and `includes/navigation.php` (logo path, "+1 (555) 000-0000", "info@example.com", "123 Main Street, City"), despite a `settings` table that holds all of this. The settings table is **never queried**.
- **`admin/settings.php`** has no form `action`, no `method="POST"`, no save handler, no CSRF token. It's purely cosmetic. There is no way to change the site's name/contact via the admin UI.
- **Hardcoded color tokens** `#007bff`, `#0056b3`, `#343a40`, gradient `linear-gradient(135deg,#007bff 0%, #0056b3 100%)` repeated in `admin/login.php`, `admin/index.php`, `pages/about.php`, services.php, custom.css, style.css. No CSS variables for the palette. Want to change the brand color? You touch 7+ files.
- **Inline `<style>` blocks** in every admin file. No shared admin layout — same `<!DOCTYPE>`/Bootstrap/FA boilerplate copied into 7 files. Adding a new admin page requires copy-paste.
- **Asset paths reference files that don't exist:** `assets/images/logo.png`, `logo-white.png`, `favicon.ico`, `apple-touch-icon.png`, `og-image.png`, `about-placeholder.jpg`, `team-1..3.jpg`, `portfolio-1..6.jpg`. The `assets/images/` directory itself is not in the repo. Every page renders broken images.
- **No image management screen** even though the gallery and image upload constants exist.

## M2 — Half the admin is a click-nothing
`admin/users.php`, `admin/pages.php`, `admin/blog.php`, `admin/leads.php` all render a list — but the Edit/Delete/Create buttons are `href="#"` placeholders. There's no create/edit handler at all. "New Page" / "New Post" / "New User" buttons on the dashboard route to `?action=create` which is never handled. The admin panel is a façade.

## M3 — `getUser()` helper returns nothing
**File:** `includes/Helper.php:147` returns `$_SESSION['user']`, but `Auth::login()` never writes to that key (it writes `user_id`, `user_email`, etc.). The dashboard greeting "Welcome back, !" and navigation user dropdown are always blank.

## M4 — `config()` helper is broken
Iterates `$GLOBALS` with dotted keys — there's no nested structure in `$GLOBALS` to traverse. Returns `$default` always.

## M5 — `Validator` errors never reach the user
`pages/contact.php` builds a `Validator`, but the errors are never rendered next to the form. On invalid input the page just silently re-renders with the user's text lost.

## M6 — `pages/index.php` has wrong include paths
`require_once __DIR__ . '/config/constants.php'` — should be `__DIR__ . '/../config/...'`. Hitting it directly fatal-errors.

## M7 — PSR-4 autoload pointing at a non-existent directory
`composer.json` declares `"App\\": "app/"` but there is no `app/` directory and no class lives in that namespace.

## M8 — `Database::execute()` swallows failures
Returns `false` on PDOException without rethrowing. Callers do `$db->execute(); $db->prepare(...)` chained — failures pass silently → the next call uses a half-broken `$this->statement`. Combined with H9, this is a latent debugging nightmare.

## M9 — `Logger::log` early-return condition is brittle
`if ($levelValue < $configLevelValue && LOG_LEVEL !== 'DEBUG')`. The intent ("below threshold AND not in debug") is fine, but `LOG_LEVEL` from env is `'info'` (lowercase) while the constants are `'DEBUG'`, `'INFO'`, etc. → comparison always fails, threshold filter never kicks in. Case-normalize.

## M10 — `MAX_ITEMS_PER_PAGE` is never enforced anywhere
Pagination is whatever the page hardcodes (e.g., `$perPage = 6` in blog.php).

## M11 — No 404 handler / no front controller
Direct navigation to a slug (e.g., `/blog/my-post`) leads to Apache 404 because the route doesn't exist. The `blog.php` page links to `/pages/blog-post.php?slug=...` — that file doesn't exist either.

## M12 — `pages/services.php`, `about.php`, `portfolio.php` don't actually use the database
They hardcode the same six fake services / teammates / portfolio items. Yet `services`, `gallery`, `categories` tables exist. The "framework" doesn't even drive its own template content.

## M13 — `<?php` blocks emit BOM / whitespace before headers
`includes/head.php` outputs the `<!DOCTYPE>` immediately, but `Security::setSecurityHeaders()` is called *after* the `<?php` open tag *only* inside `head.php`. Pages that include `head.php` from *inside* a deeper PHP block (e.g., `pages/contact.php`) call `setSecurityHeaders()` after they may have already begun output (e.g., from a `redirect()`-before-include path that didn't `exit` — `redirect` does exit, OK). It works today, but very fragile.

## M14 — Permissions / file structure assumes write paths that aren't documented
`logs/`, `uploads/`, `cache/`, `tmp/`, `storage/` referenced in code/.gitignore — none exist in the repo. `Logger` will `mkdir(LOGS_PATH, 0755, true)` at runtime, but `LOGS_PATH` is `/app/logs` which may not be inside web root depending on host. Shared hosts will fail.

---

## L1 — Sitemap & legal links
- `sitemap.xml` hardcodes `https://example.com/`. No way to inject the real domain.
- `footer.php` links to `/pages/terms.php` and `/pages/cookie-policy.php` — those files don't exist.

## L2 — Meta SEO tweaks
- `head.php` `<meta name="keywords">` is fixed to `"website, template, bootstrap"` regardless of page. (Keywords meta is mostly ignored by Google, but if you keep it, drive it from page data.)
- `og:url` and canonical use `APP_URL . $_SERVER['REQUEST_URI']` and `APP_URL` separately → `og:url` always points to homepage.

## L3 — Hard PHP requirement
`composer.json` requires PHP `>=8.3.0` but the code uses no 8.x-only features. That excludes a lot of shared hosting unnecessarily. Lower to `>=8.0`.

## L4 — Inconsistent role checks
`Auth::login` stores `$_SESSION['user_role']` as the role's **display name** (`"Administrator"`), but `hasRole(ROLE_ADMIN)` compares against `'admin'`. Role checks always fail.

## L5 — Inline styles inside `card-img-top`
`pages/about.php` uses `<img class="card-img-top">` with no constraint → team photos render at any size.

## L6 — No favicon / og-image / logo files shipped
Beyond the broken paths in M1, the *content* assets are absent. Sites look unbranded out-of-the-box.

## L7 — Accessibility
- `<i class="fas fa-*"></i>` icons without `aria-hidden="true"` and no adjacent `<span class="visually-hidden">` text. Screen readers verbalize garbage.
- Buttons that are icon-only (`<a class="btn btn-sm btn-danger"><i class="fas fa-trash"/></a>`) have no accessible name.
- Color-only status indicators (badges) — fine for sighted, problematic for color-blind users.

## L8 — `composer.json` "serve" script binds to `localhost:8000` only
Use `php -S 0.0.0.0:8000` so Docker/VM users can hit it.

## L9 — No tests despite `phpunit` dev-dep
`scripts.test` runs `phpunit` but no tests exist. Either add basic auth + validator tests or remove the dependency.

## L10 — `.gitignore` includes `*.env` but not `.env`
Pattern `*.env` matches things ending in `.env` (which does match `.env` itself, since the whole filename ends in `.env`). OK — but explicit `.env` is clearer and avoids surprises.

---

# Applied Fixes (summary)

The following critical and high items have been fixed in this branch:

- **C1** `admin/index.php` parse error fixed.
- **C2** `.htaccess` `End` → `</IfModule>` (×2) and tightened deny rules.
- **C3** Added `includes/env.php` lightweight `.env` loader, hooked from `config/constants.php`.
- **C4** Added `migrate.php` that executes `database/schema.sql`.
- **C5** Regenerated default admin password hash (password = `password` — change immediately).
- **C6** Every admin page (and `Helper.php`) now starts the session before reading `$_SESSION`.
- **C7** `Auth::requestPasswordReset` no longer returns the token. New method `getResetTokenForDelivery()` exists for the email-sending layer (out of scope to wire to SMTP).
- **C8** `Security::getClientIP` and `RateLimiter::getClientIP` now ignore proxy headers unless `TRUSTED_PROXIES=true`.
- **C9** `robots.txt` is now plain text.
- **C10** Added root `index.php` front-controller. `pages/index.php` paths fixed.
- **H1** CSRF token + verification added to admin login, admin settings, and contact form POST handler.
- **H3** `Security::sanitize` reduced to `trim()`; output continues to be escaped.
- **H4** Rate limiter only logs on actual failed login; `Auth::login` calls `reset()` on success.
- **H5** Broken remember-me removed (cleanest fix). Re-introduce via `remember_tokens` table when ready.
- **H6** CSP: added `cdnjs.cloudflare.com` and `fonts.gstatic.com`; kept `'unsafe-inline'` for now to avoid breaking inline `<style>` blocks (track as **M14** to remove).
- **H7** HSTS only sent when on HTTPS.
- **H8** Remember-me cookie removal also removes the secure-on-HTTP issue.
- **H12** Broader `.htaccess` deny block (`composer.*`, `package*.json`, `*.md`, `.gitignore`).
- **M1** Theming: added CSS variables in `assets/css/theme.css` (loaded first). Brand color, accent, surface — all editable in one file. `footer.php` and `navigation.php` now read site settings from the DB. `admin/settings.php` has a working form (CSRF + DB write).
- **M3, M4, L4** `getUser()` helper rewritten to read from a dedicated `user_data` session key set during login. `hasRole()` now compares against `user_role_name` (slug), not display name.
- **M5** Validator errors rendered inline in `pages/contact.php`.
- **M6** `pages/index.php` paths corrected.

What is intentionally **not** changed (out of scope for a one-pass fix):
- Building out actual CRUD in admin (M2) — substantial UI work.
- Real router / pretty URLs / 404 page (M11).
- Database-driven services/portfolio/team content (M12).
- Email delivery for password resets.
- A11y pass (L7) — needs design ownership.

See `CHANGELOG.md` for the per-file diff list.
