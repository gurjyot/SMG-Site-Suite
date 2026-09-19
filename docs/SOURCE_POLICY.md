# Source and reference policy

SMG Site Suite is maintained as its own codebase.

Reference plugins may be reviewed to understand a feature, workflow, or admin experience. Their source code is not a starting point for implementation.

When adding or changing a module:

- implement it against WordPress, WooCommerce, and other public APIs;
- follow Site Suite and SMG WP Foundation architecture;
- do not copy source, then rename variables or rearrange methods;
- do not carry over comments, strings, CSS selectors, class names, or internal data structures from a reference plugin unless they are part of a public API;
- keep user-facing copy short and specific to what the feature does;
- prefer a small amount of useful code over generic abstractions or explanatory comments.

Common WordPress hook names and API calls will naturally appear in many plugins. The surrounding implementation should still be our own.
Before a release candidate is tagged, compare the packaged plugin against any reference plugins used during development. Investigate meaningful overlaps; shared WordPress/WooCommerce API names and signatures are expected, copied implementation is not.
