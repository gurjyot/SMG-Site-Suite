# Agentic Readiness

SMG Site Suite exposes its management surface through the WordPress Abilities API on WordPress 6.9 and newer.

The abilities layer is an adapter over the existing Site Suite registry, module manager, dependency checks, lifecycle hooks, and settings contracts. It does not maintain a second source of truth.

## Compatibility

- WordPress 6.9+: Site Suite abilities are registered.
- WordPress 6.5–6.8: the plugin continues to work, but the Abilities API is unavailable and the agentic layer remains dormant.
- REST: Site Suite abilities are exposed through the core `/wp-abilities/v1` API.
- MCP: abilities are marked public for the official WordPress MCP Adapter. The MCP Adapter is a separate WordPress component/plugin and is not bundled with Site Suite.

## Registered abilities

| Ability | Purpose | Read-only | Destructive |
| --- | --- | --- | --- |
| `smg-site-suite/list-modules` | Discover modules and status | Yes | No |
| `smg-site-suite/get-module` | Inspect one module | Yes | No |
| `smg-site-suite/activate-module` | Activate a module through ModuleManager | No | No |
| `smg-site-suite/deactivate-module` | Deactivate a module through ModuleManager | No | Yes |
| `smg-site-suite/get-module-settings` | Read a module's settings schema and values | Yes | No |
| `smg-site-suite/update-module-settings` | Save through the module's existing sanitizer | No | Yes |
| `smg-site-suite/get-system-summary` | Read structured environment diagnostics | Yes | No |
| `smg-site-suite/list-cron-events` | Inspect scheduled WordPress cron events | Yes | No |
| `smg-site-suite/list-404s` | Inspect tracked 404 requests | Yes | No |
| `smg-site-suite/list-redirects` | Read redirect rules and usage stats | Yes | No |
| `smg-site-suite/upsert-redirect` | Create or replace a local redirect | No | Yes |
| `smg-site-suite/delete-redirect` | Delete a local redirect | No | Yes |
| `smg-site-suite/get-site-inventory` | Read runtime/theme/plugin/Site Suite inventory | Yes | No |
| `smg-site-suite/get-database-table-sizes` | Read bounded database table size data | Yes | No |
| `smg-site-suite/list-rewrite-rules` | Read bounded WordPress rewrite rules | Yes | No |

All abilities declare typed input/output schemas and idempotency annotations.

## Permissions

Every Site Suite ability requires `manage_options`.

When Protected Owner is active, the current user must also be a protected owner. This keeps REST, PHP, and MCP execution aligned with Site Suite's wp-admin ownership rules and prevents another administrator from bypassing the Protected Owner boundary through an agent.

The WordPress Abilities API performs schema validation and invokes the permission callback before the execute callback.

## Design rules

1. Abilities call existing application services; they do not write Site Suite options directly when a manager/settings contract exists.
2. Module activation and deactivation always use `ModuleManager`.
3. Settings writes always use the target module's `saveSettings()` sanitizer.
4. Dependency failures are returned as structured WordPress errors.
5. High-risk functionality should not become an ability merely because it exists in wp-admin.
6. New mutating abilities must declare accurate `readonly`, `destructive`, and `idempotent` annotations.
7. Agent access must never weaken Protected Owner or WordPress capability checks.
8. Module-specific operational abilities require that module to be active; agent access does not silently bypass disabled modules.
9. Redirect abilities accept local paths only, reject direct loops, and preserve the existing Redirect Manager as the source of truth.
10. Diagnostic abilities return bounded structured data and do not expose plugin settings, passwords, email logs, user records, or WooCommerce customer/order data.

## MCP

Site Suite does not implement its own MCP server.

The intended path is:

`AI agent -> WordPress MCP Adapter -> WordPress Ability -> Site Suite application service`

This keeps Site Suite aligned with WordPress rather than creating a proprietary agent protocol.

## Testing

Integration CI verifies:

- all Site Suite abilities are registered on supported WordPress versions;
- abilities are REST-visible and marked for MCP discovery;
- module discovery works;
- activation/deactivation persists through ModuleManager;
- settings updates pass through the module sanitizer;
- operational abilities refuse to run while their module is inactive;
- system summary, cron, and 404 data are returned as structured bounded results;
- redirect create/list/delete round-trips use local-only validation and idempotent deletion;
- site inventory returns settings-free runtime/theme/plugin/module data;
- database size and rewrite-rule abilities enforce bounded result limits;
- Protected Owner blocks another administrator from ability execution;
- WordPress 6.5 minimum-runtime activation remains unaffected.
