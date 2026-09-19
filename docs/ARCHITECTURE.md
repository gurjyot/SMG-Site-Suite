# SMG Site Suite Architecture

SMG Site Suite is a modular WordPress/WooCommerce utility suite built on the bundled SMG WP Foundation runtime.

## Rules

- Customers install only SMG Site Suite; Foundation is bundled.
- Disabled modules are not instantiated and register no module hooks.
- Module metadata is explicit in `RegistryFactory`; no runtime directory scanning.
- WooCommerce modules declare WooCommerce as a dependency and remain unavailable without it.
- Only modules that need configuration implement the settings contract.
- High-risk features require recovery design before implementation.
- Large application-sized products remain separate SMG plugins.

## Current categories

Admin, Content, Media, Performance, Security, Utilities, WooCommerce.

## Current module count

15 working modules in the first development catalog.

## Larger modules kept out of the first batch

Wishlist, FOMO Sales Notifications, rule-based Discounts, Change/Hide Login URL, and Code Snippets need more state, recovery, security, or frontend work than the first batch. They should be built as proper module systems rather than squeezed into small utility files.
