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

## Heavy modules deliberately deferred

Wishlist, FOMO Sales Notifications, rule-based Discounts, Change/Hide Login URL, and Code Snippets require more state, recovery, security, or frontend architecture than the first batch and will be built as dedicated module systems rather than rushed utility files.
