# Admin sidebar navigation (restructure)

## Rationale (by group)

| Group | Rationale |
|-------|-----------|
| **Dashboard** | Entry point and daily overview; stays first. |
| **Orders** | High-frequency operational work; moved directly under Dashboard. |
| **Users** | All people/org accounts (parents, kids, vendors, schools) in one place. |
| **Products** | Merchandise catalog: categories and product CRUD together. |
| **Stories** | Story moderation and featured/BFTC lists only. |
| **Recipes** | Recipe CRUD and popularity / month highlights. |
| **Library** | Shared taxonomy: tools, recipe categories, age groups. |
| **Promotions** | Time-bound campaigns (growing activity, monthly challenge). |
| **Pages** | Static About Us copy blocks. |
| **Subscriptions** | Billing plans; separated from one-off store fees. |
| **Store fees** | Checkout-related amounts (tax, platform, shipping) grouped as financial config. |
| **Settings** | Admin profile, security, and legal/help pages. |

## Old menu → new group → position → icon

| Old menu (label / location) | New group | Position (top→bottom) | Parent icon |
|-----------------------------|-----------|-------------------------|-------------|
| Dashboard | Dashboard | 1 | `fas fa-tachometer-alt` |
| Orders | Orders | 2 | `fas fa-shopping-cart` |
| Users → Parent | Users | 3 | `fas fa-users` |
| Users → Kid | Users | 3 | (same parent) |
| Merchandise → Vendor | Users | 3 | (same parent) |
| School | Users | 3 | (same parent; label **Schools**) |
| Merchandise → Product categories | Products | 4 | `fas fa-box` |
| Merchandise → Add Products | Products | 4 | (sub-item **Products**) |
| Stories (all / featured / BFTC) | Stories | 5 | `fas fa-newspaper` |
| Recipes (all / popular / ROTM) | Recipes | 6 | `fas fa-utensils` |
| Recipe Tools | Library | 7 | `fas fa-layer-group` |
| Modules → Categories | Library | 7 | (sub-item **Recipe categories**) |
| Modules → Age Groups | Library | 7 | (same parent) |
| Growing Activity | Promotions | 8 | `fas fa-bullhorn` |
| Monthly Challenge | Promotions | 8 | (same parent) |
| About Us (4 pages) | Pages | 9 | `fas fa-file-lines` |
| Banners (was commented in legacy sidebar) | — | — | Not linked (controller missing; see sidebar comment) |
| Subscription | Subscriptions | 10 | `fas fa-credit-card` |
| Tax Value | Store fees | 11 | `fas fa-money-bill-wave` |
| Platform fee | Store fees | 11 | (same parent) |
| Shipping Charge | Store fees | 11 | (sub-item **Shipping**) |
| Settings (profile, password, help, privacy, terms) | Settings | 12 | `fas fa-cog` |
| Logout | (unchanged) | last | `fas fa-right-from-bracket` |

## Technical notes

- **Routes / permissions:** No route names, middleware, or permission keys were changed. The school guard block is unchanged.
- **Active state:** Each of Stories / Recipes / Library / Promotions / Pages has its own `$is…` flag and `menu-open` when any child matches. Recipe “all recipes” uses `routeIs('recipe.*')` so `admin/recipe-tool` does not highlight as a recipe. Privacy/terms use `Request::is` on their real paths (fixes legacy incorrect `help-&-support` active match).
- **Icons:** Font Awesome classes already loaded in `admin/layouts/master.blade.php` (CDN). Only `fas` / `far` glyphs are used.

## Verification checklist

- [ ] Click every sidebar link (admin guard): each loads the same screen as before.
- [ ] Open **Orders** and a **view order** URL: **Orders** stays active.
- [ ] Open **Parents**, **Kids**, **Vendors**, **Schools** (and add/edit school if used): **Users** expands and the correct child is active.
- [ ] Open **Product categories** and **Products**: **Products** group expands; correct active child.
- [ ] Open story list, featured, BFTC: only **Stories** expands; correct child active.
- [ ] Open recipe list, popular, ROTM: only **Recipes** expands; correct child active.
- [ ] Open recipe tools, categories, age groups: only **Library** expands; correct child active.
- [ ] Open growing activity, monthly challenge: only **Promotions** expands; correct child active.
- [ ] Open each About page: only **Pages** expands; correct child active.
- [ ] Open **Subscriptions**: single item highlights.
- [ ] Open tax, platform fee, shipping: **Store fees** expands; correct child active.
- [ ] Open profile, change password, help, privacy, terms: **Settings** expands; privacy/terms no longer both track “help” URL.
- [ ] **Logout** still shows confirm dialog.
- [ ] Resize to mobile width: sidebar toggles as before (AdminLTE behavior).
- [ ] School login sidebar: only Dashboard + Logout; unchanged.
