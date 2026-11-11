# 0002. External XML mapping for Doctrine

## Status

Accepted — 2026-08-19

## Context

Domain classes carry the rules of the system and are covered by tests that run
without a database. We want to keep `src/Domain` free of any dependency on the
persistence layer, so that swapping or upgrading the ORM is an infrastructure
change rather than a rewrite of the domain (see 0001).

The domain also relies on constructs the ORM has to accommodate rather than
dictate: private constructors, `private(set)` properties, value objects mapped
as embeddables, and identifiers wrapped in custom types.

Doctrine ORM 3 offers attributes, XML and PHP mapping. YAML was removed in
ORM 3 and is not an option.

Attributes are the framework default and the best supported path, but they put
persistence metadata inside the domain classes themselves. PHP mapping keeps
the domain clean but is the least used of the three and has no schema to
validate against.

## Decision

We map entities with external XML files under
`src/Infrastructure/Doctrine/Mapping/`, mirroring the structure of
`src/Domain/`. Domain classes contain no persistence metadata of any kind.

## Consequences

`src/Domain` contains no `use Doctrine\` statement. Domain tests need neither a
kernel nor a database. Embeddables and custom identifier types are configured
entirely from the infrastructure side, so the domain model is unaware that
`Money` becomes two columns.

The mapping and the class it describes now live in separate files and can drift
apart silently. Renaming a property or changing its type is not caught by the
compiler, the IDE or PHPStan — only by `doctrine:schema:validate`, which is
therefore mandatory in CI rather than optional.

XML is verbose and gets no autocompletion, so mapping changes are slower to
write than attributes. A developer joining the project has to look in two
places to understand how an entity is stored.

We accept these costs because they fall on infrastructure work, which is rare,
while the benefit applies to domain work, which is constant.