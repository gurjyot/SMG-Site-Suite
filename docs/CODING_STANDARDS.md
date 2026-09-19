# Coding standards

SMG WordPress plugins are written against the public WordPress and WooCommerce APIs and follow WordPress coding and security practices.

## Required rules

- PHP must remain compatible with the plugin's declared minimum PHP version.
- Sanitize and validate request data before use.
- Unslash WordPress request data before sanitizing it.
- Check capabilities before privileged actions.
- Use nonces for state-changing admin, AJAX, and form actions.
- Escape output as late as possible for the output context.
- Use prepared SQL for values in database queries.
- Prefer WordPress and WooCommerce APIs over direct database access.
- WooCommerce order data must use WooCommerce CRUD so HPOS remains supported.
- Do not use classes or APIs under `Automattic\WooCommerce\Internal` or anything marked `@internal`.
- User-facing strings must be translatable and use the plugin text domain.
- Global functions, constants, options, hooks, handles, and identifiers owned by the plugin must use the SMG prefix/namespace.
- Do not use development-only helpers such as `var_dump()`, `print_r()`, or debug output in shipping code.
- New code must be readable PHP, not compressed into one-line methods or generated-looking blocks.
- Comments should explain why a decision exists, not narrate obvious code.
- Reference plugins may be used to understand features and workflows, never as source material.

## Automated enforcement

CI blocks merges when the configured WordPressCS security/interoperability rules fail. Plugin Check, syntax checks, integration tests, exact ZIP install tests, and WooCommerce compatibility tests remain separate required gates.

The source is also reviewed for normal WordPress formatting and readability. Large mechanical formatting changes should be isolated from behavioral changes so regressions are easy to spot.
