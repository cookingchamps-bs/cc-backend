# Cooking Champs backend security remediation

Tracking against commit `174e2030` review (re-verified 18 Nov 2026). **Code** = implemented in `cookingchamps-backend` in this pass. **Ops** = requires live server / product / legal work.

## A. Do today

| Item | Status | Notes |
|------|--------|--------|
| Default admin seeder password | **Code** | `AdminSeeder` uses `ADMIN_SEED_EMAIL` / `ADMIN_SEED_PASSWORD` (random if unset). **Ops:** rotate live admin + 2FA; confirm `admin@gmail.com` / `123456` dead on prod. |
| Docroot / sensitive files | **Partial** | Root `.htaccess` denies `app/`, `storage/logs`, `.env*`, `composer.lock`, etc. **Ops:** point vhost docroot at `public/`; delete server `error_log`, `auth.php.zip`; curl-verify 403/404. |
| Hosting / Stripe / OpenAI rotation | **Ops** | Not verifiable from repo. |
| Access log review | **Ops** | Check prod logs for abused endpoints. |

## B. Critical (this week)

| Item | Status | Notes |
|------|--------|--------|
| Reset password OTP | **Code** | `reset-password` requires `email` + 6-digit `otp`; 15m expiry; 5-failure lockout; tokens revoked after reset. |
| Social login | **Code** | Requires `id_token`; Google/Apple verified server-side; random password (migrates legacy `123456`). **App:** Flutter must send `id_token` (not email alone). Configure `GOOGLE_CLIENT_ID`, `APPLE_CLIENT_ID`. |
| update-profile kid ownership | **Code** | Parent/kid ownership enforced. |
| friend-request `type=all` | **Code** | Returns 410; use pending/accepted. |
| Friend/follow spoofing | **Code** | Sender from auth; accept only receiver/parent-of-kid-receiver; share own stories only. Parent invite codes / block-report: **not done**. |
| register-kid | **Code** | `auth:sanctum` + `parent.user`; `parent_id` from auth. |

## C. High

| Item | Status | Notes |
|------|--------|--------|
| story-detail ACL | **Code** | `ContentAccess::canViewStory`. |
| order-details ACL | **Code** | Scoped to `user_id`. |
| Kid tokens on parent routes | **Code** | `EnsureParentUser` on cart/address/orders/checkout/AI/register-kid. |
| Legacy `/payment/success` | **Code** | Stripe `payment_status === paid` + idempotent by session id. Webhook as source of truth: **partial** (API checkout already verifies session). |
| Account deletion | **Partial** | In-app still soft-delete. Real deletion flow: **not done**. |
| composer audit / updates | **Ops** | Run `composer update` + `composer audit` on server/CI. |
| Stripe/OpenAI in DB | **Partial** | Encrypted casts on `Admin`; env preferred in `Helper`. **Ops:** move keys to `.env`, rotate, clear DB columns. |

## D. Legal / D–E

Privacy, consent tables, data export, retention jobs, Play/Apple compliance: **not implemented** in this pass (product/legal scope).

| Item | Status | Notes |
|------|--------|--------|
| Sanctum token expiry | **Code** | Default 30 days via `SANCTUM_TOKEN_EXPIRATION_MINUTES`. |
| Revoke tokens on password change/reset | **Code** | |
| delete-notification owner | **Code** | |
| Public home featured stories | **Code** | Hides `user_id`, `kid_id`, `role` on featured list. |
| recipe-detail ACL | **Code** | Owner or `recipe_of_the_week`. |
| purchase-plan / checkout user_id | **Code** | Auth user; kid ownership checked. |
| Strong passwords | **Code** | Parent 8+ uncompromised; kid 8+. Email re-verify on change: **not done**. |
| Generic API errors (selected) | **Partial** | Auth/social/password paths. Widespread catch blocks: **remaining**. |
| Rate limiting | **Partial** | Existing `throttle:5,1` on auth; dedicated OTP/social limits: **tune in prod**. |
| ModSecurity / upload limits | **Partial** | Root `.user.ini` still cPanel-managed; ModSecurity still off in root `.htaccess`. |
| Housekeeping | **Code** | Removed `error_log`, `config/auth_old.php`, `Middleware_old/`. `HomeControllerV1` kept (still routed). |

## Verify locally

```bash
cd cookingchamps-backend
php artisan test
```

Manual checks: reset without OTP → 401; `friend-request?type=all` → 410; kid token on `cart-list` → 403; social login without `id_token` → validation error.