# Compatibility Matrix

This file records the compatibility we have tested and the compatibility we are not claiming yet.

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

Site Suite still has modules that depend on classic cart and checkout hooks. Blocks compatibility stays off until those modules have Store API or block implementations and matching tests.

Known classic-first modules are labeled in the module browser with a "Classic Checkout" compatibility badge. When one of those modules is active and the Cart or Checkout page uses WooCommerce Blocks, Site Suite shows an admin warning.

Some server-side WooCommerce filters continue to work with Blocks, but that alone is not sufficient for a compatibility declaration.

## Module compatibility classifications

### HPOS-aware order admin modules

- Payment Method Column
- Order Phone Column
- Woo Order Notes Column
- Order Item Summary Column

These register both legacy order-list hooks and HPOS `wc-orders` hooks.

### Cart, checkout, and product-editor-sensitive modules

| Module | Classification | Current evidence |
|---|---|---|
| Minimum / Maximum Order Amount | Store API server contract covered; Blocks UI unclaimed | Automated Store API smoke covers server-side validation. |
| Auto Apply Coupon | Store API server contract covered; Blocks UI unclaimed | Automated Store API smoke covers coupon application in Store API context. |
| Buy Now Button | Classic | Classic checkout badge and Blocks warning. |
| Direct Checkout | Classic | Classic checkout badge and Blocks warning. |
| Direct Checkout Links | Classic | Classic checkout badge and Blocks warning. |
| Checkout Field Controls | Classic | Classic checkout badge and Blocks warning. |
| Checkout Text Customizer | Classic | Classic checkout badge and Blocks warning. |
| Empty Cart Button | Classic | Classic cart badge and Blocks warning. |
| URL Coupons | Classic | Classic checkout badge and Blocks warning. |
| COD Amount Rules | Untested for Blocks/Store API | Uses classic gateway filtering; no Blocks claim. |
| Free Shipping Method Control | Untested for Blocks/Store API | Uses WooCommerce package-rate filtering; no Blocks claim. |
| Rename Payment Methods | Untested for Blocks/Store API | Uses classic gateway-title filtering; no Blocks claim. |
| Rename Shipping Methods | Untested for Blocks/Store API | Uses package-rate label filtering; no Blocks claim. |
| Quantity Rules | Untested for Blocks/Store API | Uses product quantity and add-to-cart validation hooks; no Blocks claim. |
| Wishlist | Untested for Blocks/Store API | Frontend shortcode/buttons covered by manual smoke checklist; cart actions still need manual and Blocks-specific coverage. |
| Direct Checkout Links | Product Editor unclaimed | Adds a classic product metabox; no block Product Editor alternative yet. |
| Product Price History | Product Editor unclaimed | Adds a classic product metabox; no block Product Editor alternative yet. |
| Product Thumbnail Column, Product SKU Column, Product Badge Manager, Product Tabs Control, Estimated Delivery Message, Custom Stock Messages, Catalog Mode, WhatsApp Product Enquiry | N/A for cart/checkout Blocks | Product-list or product-page behavior only; no cart/checkout declaration. |

### Product Editor

No global compatibility claim is currently made for WooCommerce's block-based Product Editor. Direct Checkout Links and Product Price History add classic product metaboxes and should be migrated or given an alternative Product Editor UI before declaring Product Editor compatibility.

## Release rule

A compatibility declaration may be changed from unsupported/unknown to supported only when:

1. the affected modules have an implementation for the target feature;
2. automated coverage exists for the behavior being claimed;
3. a real WordPress/WooCommerce integration run passes;
4. any remaining manual-only behavior is recorded in the beta checklist.
