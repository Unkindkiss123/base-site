# Changelog — Round 3 (WYSIWYG, Uploads, Remember-me, Email verification, 2FA)

All five features shipped and end-to-end smoke-tested against a fresh MariaDB + `php -S` setup.

## WYSIWYG editor
- `includes/admin_layout.php`:
    - `admin_layout_end($extra_scripts = '')` — now accepts page-specific scripts that render after Bootstrap.
    - `admin_wysiwyg_scripts($selector = 'textarea[name="content"]')` — **new** helper that emits the TinyMCE 6 (Community / GPL) bootstrap from `cdn.tiny.cloud`. Toolbar: blocks · bold/italic/underline · lists · link · image · table · raw HTML code view.
- `admin/pages.php`, `admin/blog.php` — Edit & New forms now `admin_layout_end(admin_wysiwyg_scripts())`. The `content` textarea is automatically replaced with the rich editor.
- `includes/Helper.php` — new `safe_html($html)` sanitizer for *display* (the editor produces HTML; we strip `<script>`, `<iframe>`, `<style>`, `on*` attributes, and `javascript:` / `vbscript:` / non-image `data:` URIs, then whitelist a sensible tag list).
- `pages/about.php`, `pages/blog-post.php` — switched from `nl2br(e($content))` to `safe_html($content)` so the rich content renders.
- `includes/Security.php` — CSP whitelisted `cdn.tiny.cloud` for script/style/font/connect-src and added `blob:` to `img-src` (TinyMCE pasted images).

## File upload manager
- `admin/uploads.php` — **new**: browse + upload + delete. Per-upload protections:
    - File-extension whitelist (`jpg, jpeg, png, gif, webp, svg, pdf`).
    - Server-side **MIME verification** via `finfo` (the famous `evil.png` with PHP content is rejected: *"File content does not match its extension"*).
    - 5 MB size cap.
    - Filenames sanitized to `[a-zA-Z0-9_-]` and given a random `bin2hex(random_bytes(4))` suffix so two uploads of "logo.png" don't collide.
    - Deletion is `realpath()`-fenced inside the uploads directory.
- Auto-creates `/uploads/.htaccess` on first visit that denies execution of `.php|.phtml|.phar|.cgi|.pl|.py|.sh` and disables directory indexing — so even if a malicious file somehow made it in, Apache won't run it.
- One-click copyable URL field per file (clicking selects + copies to clipboard).

## Real remember-me (re-introduced safely)
- `database/schema.sql` — new `remember_tokens` table: `(id, user_id, selector, validator_hash, expires_at, created_at)` with CASCADE on user delete.
- `includes/Auth.php`:
    - `login()` now accepts a `$rememberMe` flag again. On success it calls `issueRememberToken()` which stores a *hashed* validator server-side (selector in cleartext for lookup) and writes a `selector:validator` cookie with `HttpOnly`, `SameSite=Lax`, secure-when-HTTPS.
    - `tryRememberLogin()` — re-hydrates a session from the cookie. Constant-time compare with `hash_equals`. Mismatched validator = stolen-cookie heuristic, so the matching DB row is **purged immediately**.
    - `logout()` calls `clearRememberToken()` which deletes the DB row and expires the cookie.
- `index.php` (front controller) — runs `tryRememberLogin()` **once per request**, only when the cookie is present and there's no live session.
- `admin/login.php` — re-added "Remember me for 30 days" checkbox.
- **Smoke-tested**: logged in → got `remember_token` cookie → DROPPED the session cookie → hit `/admin` with only the remember cookie → **HTTP 200** (auto-login worked).

## Email verification
- `database/schema.sql` — new `email_verifications` table with `token_hash` (we never store the cleartext token), `expires_at` (24h), `used_at` (single-use).
- `includes/Auth.php`:
    - `sendEmailVerification($userId)` — creates a token, persists the hash, emails the user a `/verify-email/{token}` link via `Mailer`. Old unused tokens for the same user are wiped to prevent buildup.
    - `verifyEmail($token)` — looks up by hash, enforces expiry + single-use, sets `users.email_verified_at = NOW()`.
- `pages/verify-email.php` — **new**: lands at `/verify-email/{token}` (router preset), shows success or "invalid/expired" with a one-click "Go to login" CTA.
- `admin/users.php` — "New user" form has a (checked by default) "Send email-verification link to this user" checkbox; on submit, the new user receives the email automatically.
- `admin/profile.php` — shows verified state with a date; if not verified, exposes a "Send verification email" button.

## 2FA (TOTP)
- `database/schema.sql` — `users` gained `mfa_secret VARCHAR(64) NULL` and `mfa_enabled TINYINT(1) DEFAULT 0`.
- `includes/Totp.php` — **new**: zero-dependency RFC 6238 implementation. Base32 encode/decode, HMAC-SHA1, ±1-step clock-skew tolerance. Verified against a self-generated code in tests; constant-time compare via `hash_equals`.
- `includes/Auth.php`:
    - `login()` returns `['requires_mfa' => true]` if the account has `mfa_enabled = 1` (after password verifies). Pending user id is stored in `$_SESSION['mfa_pending_user']`.
    - `verifyMfa($code)` consumes the pending state and only then calls `establishSession()`. Without solving 2FA, `/admin` correctly bounces back to `/login`.
- `pages/two-factor.php` — **new** route at `/2fa` (router preset). Rate-limited 8/15min, CSRF-protected. Uses `autocomplete="one-time-code"` and `inputmode="numeric"` for OS-level autofill from iOS/Android.
- `admin/profile.php` — **new** "Enable 2FA" flow:
    1. Generate a 32-char Base32 secret server-side.
    2. Render a QR (via `api.qrserver.com` so we don't have to ship a generator) and the manual key.
    3. User scans with Google Authenticator / Authy / 1Password, types the code, we confirm with `Totp::verify`, then write `mfa_secret` and flip `mfa_enabled = 1`.
    - "Disable 2FA" wipes both columns and is confirm-prompted.
- **Smoke-tested**: enable → log out → log in (password only goes to /2fa) → /admin bounces pre-2FA → submit current TOTP → /admin opens → wrong code shows "Invalid 2FA code".

## Misc polish
- Admin sidebar gained **Uploads** and **My Profile** entries (and a `manage_settings` permission is no longer required for personal-profile features).
- All new icon-only buttons have `aria-label`s; all icons `aria-hidden="true"`; status/MFA badges include `visually-hidden` screen-reader text.

## Verified end-to-end
| Scenario | Result |
| --- | --- |
| Login with `remember_me=1` | 302 → /admin, DB row in `remember_tokens` |
| /admin with only remember cookie (no session) | 200 (auto-login) |
| Enable 2FA via profile | DB `mfa_enabled` flipped to 1 |
| Subsequent login (password only) | 302 → /2fa |
| /admin without solving 2FA | 302 → /login |
| Correct TOTP → /2fa | 302 → /admin (200) |
| Wrong TOTP | 200 + "Invalid 2FA code" |
| Send email verification | row in `email_verifications`, 24h expiry |
| Upload real PNG | stored as `tiny-aca2fcfa.png` |
| Upload PHP-as-PNG | rejected with "File content does not match" |
| `php -l` on every file | clean |
| `safe_html()` against XSS payloads | all 4 attack vectors neutralized |
