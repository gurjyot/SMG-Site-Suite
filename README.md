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
- Agency Client Handoff

The Agency Client Handoff preset enables the protected-owner, admin-menu, custom-dashboard, branding, dashboard-cleanup, environment, and activity-log tools used for managed client sites.

Presets are additive: they enable useful modules without wiping unrelated active modules or their settings.

## Current catalog

**135 modules** across Admin, Content, Email, Media, Performance, Security, Utilities, Users, and WooCommerce.

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
- Protected Owner
- Visual Admin Menu Organizer
- Custom Dashboard Page
- Temporary Login management
- Multiple User Roles
- Disable User Account
- Environment Indicator
- Cron Viewer
- Database Table Sizes
- Replace Media
- Default Featured Image
- SMTP Test Email
- Redirect-from-404 workflow
- Duplicate Navigation Menu
- Recover Missed Scheduled Posts
- Disable Big Image Scaling
- Disable Self Pingbacks
- Remove Comment Website Field
- Email Sender Identity
- System Summary
- Maintenance Mode
- Head / Body / Footer Code
- Generic Login Errors
- Search Posts Only
- Disable Texturize
- Remove Recent Comments CSS
- Login / Logout Redirects
- Disable Product Reviews
- Payment Method Column
- Order Phone Column
- Minimum / Maximum Order Amount
- Free Shipping Method Control
- Buy Now Button
- COD Amount Rules
- Free Shipping Progress Bar
- FOMO Sales Notifications
- WooCommerce Wishlist

## Agentic interface

On WordPress 6.9 and newer, Site Suite registers WordPress Abilities for module discovery, activation/deactivation, settings management, system diagnostics, cron inspection, 404 inspection, and validated local redirect management. The abilities reuse existing Site Suite services, are available through the core Abilities REST API, and are marked for discovery by the official WordPress MCP Adapter.

Protected Owner also applies to the agentic interface: when it is active, another administrator cannot operate Site Suite through an Ability.

WordPress 6.5–6.8 remain supported; the agentic layer simply stays dormant because those versions do not include the Abilities API.

See [Agentic Readiness](docs/AGENTIC_READINESS.md).

## FOMO performance model

FOMO does not query orders during visitor requests. It snapshots up to 20 recent paid orders once daily via WP-Cron, stores the compact data in a non-autoloaded option, and randomizes display order in the browser.

## Next larger module systems

Rule-based Discounts, Change/Hide Login URL, Code Snippets, advanced wishlist features (sharing, multiple lists, notes), plus further WooCommerce utilities.

See [Architecture](docs/ARCHITECTURE.md).
