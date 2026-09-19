# SMG Site Suite

A modular WordPress and WooCommerce enhancement suite by Singh Media Group.

## Architecture principles

- Disabled modules load no module PHP and register no module hooks.
- Modules are explicitly registered; the plugin does not scan arbitrary PHP files at runtime.
- Module assets load only where needed.
- WooCommerce modules are isolated and dependency-aware.
- Small enhancements live here; application-sized SMG products remain standalone plugins.
- WordPress capabilities, nonces, sanitization, escaping, and recovery paths are required from the start.

## Initial categories

Admin, Content, Media, Performance, Security, Utilities, and WooCommerce.

## First module batch

Disable Comments, Duplicate Content, Safe SVG Upload, Login Branding, Admin Footer, Dashboard Widgets, Revision Control, Heartbeat Control, Disable WooCommerce Reviews, Payment Method Column, Min/Max Order Amount, and Free Shipping Method Control.

Subsequent modules include COD Rules, Buy Now, Shipping Progress Bar, Login URL, Wishlist, FOMO Sales Notifications, Discounts, and Code Snippets.
