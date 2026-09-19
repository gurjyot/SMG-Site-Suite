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
- Built-in Configurations page with presets and active-module import/export.

## Built-in presets

- Starter Pack
- Clean WordPress
- Security Basics
- Performance Basics
- Admin Productivity
- Woo Store Basics

Presets are additive: they enable useful modules without wiping unrelated active modules or their settings.

## Current catalog

**35 modules** across Admin, Content, Media, Performance, Security, Utilities, Users, and WooCommerce.

Highlights include:

- Disable Comments
- Duplicate Content
- Login Branding
- Admin Footer
- Dashboard Widgets
- Show IDs
- Active Plugins First
- Featured Image Column
- Disable Frontend Admin Bar
- Hide Admin Notices
- Footer Time & Timezone
- Revision Control
- Heartbeat Control
- Disable Emoji Assets
- Disable WordPress Embeds
- Disable Dashicons for Guests
- Clean WordPress Head
- Safe SVG Upload
- Image Size Control
- Hide WordPress Version
- Disable XML-RPC
- Disable Application Passwords
- Disable Theme / Plugin File Editors
- Search Visibility Warning
- Last Login Column
- Registration Date Column
- Disable Product Reviews
- Payment Method Column
- Order Phone Column
- Minimum / Maximum Order Amount
- Free Shipping Method Control
- Buy Now Button
- COD Amount Rules
- Free Shipping Progress Bar
- FOMO Sales Notifications

## FOMO performance model

FOMO does not query orders during visitor requests. It snapshots up to 20 recent paid orders once daily via WP-Cron, stores the compact data in a non-autoloaded option, and randomizes display order in the browser.

## Next larger module systems

Wishlist, rule-based Discounts, Change/Hide Login URL, Code Snippets, plus further WooCommerce utilities.

See [Architecture](docs/ARCHITECTURE.md).
