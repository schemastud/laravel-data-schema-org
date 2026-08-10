> You are in **schemastud/laravel-data-schema-org** — a boundary projector marrying spatie/laravel-data DTOs to spatie/schema-org.

A typed registry plus attribute-driven primitives that project a Data object into a Schema.org
node (JSON-LD) at the edge. Internal identity (`@id`) is reused from `laravel-json-reference`; the
leaf stays domain-agnostic and never names concrete schema.org types.

## Vendored family-package conventions

Any repo that vendors another family repo's code (composer `vendor/<vendor>/<pkg>/`, npm
`node_modules/<vendor>/<pkg>/`) checks that vendored repo's own `AGENTS.md` for conventions it
ships with itself before editing through into it.
