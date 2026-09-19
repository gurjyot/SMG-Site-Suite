# Beta Readiness Checklist

This checklist is a release gate. Do not call a build beta-ready unless every required automated gate is green and every required manual item is explicitly checked.

## Automated gates

- [x] PHP syntax matrix on PHP 8.0, 8.1, 8.2, and 8.3.
- [x] Registry smoke: unique slugs, registered classes, WooCommerce dependency declarations.
- [x] JavaScript syntax checks.
- [x] Minimum runtime: WordPress 6.5.11 + PHP 8.0 activates and passes core lifecycle smoke.
- [x] Official WordPress Plugin Check workflow.
- [x] Real WordPress/WooCommerce integration matrix:
  - WordPress 7.1.1 + WooCommerce 11.1.1 + PHP 8.3.
  - WordPress 7.0.5 + WooCommerce 10.9.4 + PHP 8.3.
  - inactive-module isolation.
  - WooCommerce CRUD order creation/reload.
  - HPOS migration and CRUD rerun.
  - Store API server-contract smoke for Order Amount Rules and Auto Apply Coupon.
  - Checkout Blocks compatibility warning contract.
- [x] Exact packaged ZIP installs and activates successfully on clean WordPress.
- [x] Packaged ZIP contains only intended shipping files.

## Compatibility policy

- [x] HPOS compatibility declaration exists.
- [x] Cart/Checkout Blocks are explicitly declared incompatible until migrated/tested.
- [x] Compatibility ledger exists in `docs/COMPATIBILITY.md`.
- [ ] Classic-only cart/checkout modules have user-facing compatibility indicators.
- [ ] Store API / Checkout Blocks alternatives are implemented for modules selected for Blocks support.
- [ ] Product Editor compatibility is audited before any compatibility claim.

## High-risk manual smoke tests

Run these on a staging site with a backup and a second administrator account.

### Protected Owner
- [ ] Enable as owner A.
- [ ] Verify admin B cannot edit, demote, remove, or delete owner A.
- [ ] Verify admin B cannot deactivate Site Suite through normal plugin UI.
- [ ] Verify owner A can still access Site Suite and recover normal administration.
- [ ] Verify disabling Protected Owner is possible only for a protected owner.

### Login as User
- [ ] Owner can impersonate a normal user.
- [ ] Protected owners cannot be impersonated.
- [ ] Return-to-owner control works.
- [ ] Return token expires and cannot be reused.

### Temporary Login
- [ ] Create one-use temporary login.
- [ ] Link cannot be reused.
- [ ] Revocation works.
- [ ] Created temporary account is removed at expiry.
- [ ] Existing-account temporary access does not delete the real account.

### Admin Menu Organizer
- [ ] Detected menu/submenu tree matches installed plugins.
- [ ] Hidden items are hidden for client admin.
- [ ] Protected owner sees full menu.
- [ ] Dashboard cannot be accidentally removed.

### Custom Dashboard Page
- [ ] Gutenberg page embeds correctly.
- [ ] Bricks page embeds correctly.
- [ ] Builder page has no duplicate site header/footer when a blank template is used.
- [ ] Dashboard remains usable on tablet/mobile wp-admin.

### Replace Media
- [ ] Replace JPEG with JPEG and regenerate sizes.
- [ ] Replace PNG/WebP image.
- [ ] Attachment ID, alt text, caption, description, and post references remain intact.
- [ ] Old generated sizes are removed.
- [ ] Permission checks reject unauthorized replacement.

### SMTP / Mail Log
- [ ] TLS SMTP test.
- [ ] SSL SMTP test if supported by target mail server.
- [ ] Failed-mail entry is recorded.
- [ ] Mail log filtering/export/clear work.
- [ ] Stored password is preserved when password field is left blank.

### Redirects / 404
- [ ] 301/302/307/308 behavior.
- [ ] Query strings do not create redirect loops.
- [ ] 404-to-redirect workflow.
- [ ] Hit counter and last-used timestamp.
- [ ] No redirect applies in wp-admin/AJAX/cron.

## WooCommerce manual smoke tests

### Order administration
- [ ] Legacy order table columns.
- [ ] HPOS order table columns.
- [ ] Payment Method, Phone, Latest Note, and Item Summary values.
- [ ] Order CRUD with HPOS enabled.

### Classic cart/checkout
- [ ] Order amount rules.
- [ ] COD rules.
- [ ] Checkout Field Controls.
- [ ] Checkout Text Customizer.
- [ ] Empty Cart Button.
- [ ] Direct Checkout.
- [ ] Auto Apply Coupon.
- [ ] URL Coupons.
- [ ] Payment/Shipping renaming.

### Wishlist
- [ ] Guest add/remove.
- [ ] Guest-to-user merge.
- [ ] Logged-in persistence.
- [ ] Share URL.
- [ ] Stock state.
- [ ] Move eligible simple products to cart.
- [ ] Variable products remain safe/not silently corrupted.
- [ ] Purchased products auto-remove when configured.

### FOMO
- [ ] Daily cron schedules correctly.
- [ ] Maximum 20 paid orders fetched.
- [ ] No `wc_get_orders()` occurs on visitor page loads.
- [ ] Random client-side ordering.
- [ ] Customer privacy formatting.
- [ ] Empty snapshot produces no frontend assets.

## Performance checks

- [x] Inactive module implementation files are required to remain unloaded in integration CI.
- [x] Baseline request query count recorded with Site Suite active and zero modules.
- [x] Baseline included-file count recorded.
- [x] Compare zero-module Site Suite vs Site Suite disabled.
- [ ] Representative 10-module profile recorded.
- [ ] Woo storefront profile recorded with representative Woo modules.
- [ ] No module performs an unbounded query during ordinary frontend requests.

## Release package

- [x] Deterministic ZIP packager exists.
- [x] Exact ZIP install smoke green.
- [ ] Version changed from `0.1.0-dev` to beta version only after gates pass.
- [ ] Changelog updated with beta scope.
- [x] No development/test/workflow files inside ZIP.
- [ ] ZIP inspected for secrets, credentials, local paths, and private reference-plugin code.

## Beta decision

A staging beta may be produced once all automated gates are green and the critical manual smoke tests have been completed for:
Protected Owner, Login as User, Temporary Login, Admin Menu Organizer, Custom Dashboard, Replace Media, SMTP, redirects, HPOS order admin, Wishlist, and FOMO.
