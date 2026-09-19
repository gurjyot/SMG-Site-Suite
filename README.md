# SMG Site Suite

A modular WordPress and WooCommerce enhancement suite by Singh Media Group.

**Development status:** v0.1.0-dev

Site Suite bundles SMG WP Foundation internally. There is no separate Foundation plugin dependency.

## Core behavior

- Searchable, category-based module browser inspired by the usability principles of WP Switchboard but independently implemented.
- Disabled modules do not initialize.
- Explicit module registry; no source/docblock scanning.
- Dependency-aware WooCommerce modules.
- Per-module settings only where needed.
- Shared Foundation lifecycle, security and performance contracts.

## Current modules

### Admin
- Login Branding
- Admin Footer
- Dashboard Widgets

### Content
- Disable Comments
- Duplicate Content
- Revision Control

### Media
- Safe SVG Upload

### Performance
- Heartbeat Control

### WooCommerce
- Disable Product Reviews
- Payment Method Column (legacy + HPOS)
- Minimum / Maximum Order Amount
- Free Shipping Method Control
- Buy Now Button
- COD Amount Rules
- Free Shipping Progress Bar

## Next module systems

Wishlist, FOMO Sales Notifications, rule-based Discounts, Change/Hide Login URL, Code Snippets, plus further WooCommerce utilities.

See [Architecture](docs/ARCHITECTURE.md).
