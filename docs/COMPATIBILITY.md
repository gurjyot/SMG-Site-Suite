# Compatibility Matrix

This document tracks compatibility claims that have actually been tested or intentionally withheld.

## Current test targets

| Layer | Current target | Compatibility-back target |
|---|---:|---:|
| WordPress | 7.1.1 | 7.0.5 |
| WooCommerce | 11.1.1 | 10.9.4 |
| PHP | 8.3 | 8.3 |
| MySQL | 8.0 | 8.0 |

## WooCommerce feature declarations

### High-Performance Order Storage (HPOS)

**Declared compatible.**

Order-related Site Suite modules use WooCommerce CRUD and register both legacy and HPOS list-table hooks where they modify order-list columns.

The integration workflow creates and reloads real WooCommerce orders, then repeats the runtime smoke after HPOS is enabled.

### Cart and Checkout Blocks

**Declared incompatible for now.**

This is deliberate. Site Suite contains several optional modules that currently rely on classic cart/checkout PHP hooks. The plugin must not claim block compatibility until those modules have block/Store API implementations and tests.

Known classic-first modules include:

- Checkout Field Controls
- Checkout Text Customizer
- Empty Cart Button
- Direct Checkout UI behavior
- parts of Auto Apply Coupon behavior
- cart-page action/button customizations

Some server-side WooCommerce filters continue to work with Blocks, but that alone is not sufficient for a compatibility declaration.

## Module compatibility classifications

### HPOS-aware order admin modules

- Payment Method Column
- Order Phone Column
- Woo Order Notes Column
- Order Item Summary Column

These register both legacy order-list hooks and HPOS `wc-orders` hooks.

### Store API / Blocks-adjacent modules

These should receive dedicated block tests before Site Suite changes its global Blocks declaration:

- Order Amount Rules
- Auto Apply Coupon
- COD Rules
- Shipping rules
- Checkout Field Controls
- Checkout Text Customizer
- Empty Cart Button
- Direct Checkout
- Wishlist cart actions

### Product Editor

No global compatibility claim is currently made for WooCommerce's block-based Product Editor. Modules that add classic product metaboxes should be migrated or given an alternative Product Editor UI before declaring compatibility.

## Release rule

A compatibility declaration may be changed from unsupported/unknown to supported only when:

1. the affected modules have an implementation for the target feature;
2. automated coverage exists where practical;
3. a real WordPress/WooCommerce integration run passes;
4. any remaining manual-only behavior is recorded in the beta checklist.
