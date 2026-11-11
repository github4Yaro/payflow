# 0001. Project structure

## Status - Accepted 

## Context

 The service handles orders, invoices and payments. Money arithmetic, 
 status transitions and uniqueness rules have to hold no matter how the data is 
 stored or how a request arrives. Those rules are the long-lived part of the system;
 the framework, the ORM and the surrounding transport are not.

 Symfony's default layout organises code by technical 
 type — `src/Entity`, `src/Repository`, `src/Controller`. A single business 
 concept ends up spread across three folders, and entities tend to collect 
 persistence and serialisation concerns, because that is where the framework 
 expects them to live.

 The service is small and maintained by one developer, 
 so any structure has to stay cheap to navigate.

## Decision

We organise `src/` by layer — `Domain`, `Application`, `Infrastructure`, 
`UI` — and organise `Domain` by business concept (`Order`, `Invoice`, `Shared`) 
rather than by technical type. `Domain` depends on nothing outside itself.

## Consequences

 Domain rules can be tested without a kernel, a database or HTTP, 
which keeps the test suite fast and the invariants explicit. Changing the ORM or a
dding a second entry point touches Infrastructure and UI only.

## The cost is indirection: 
 more files and more wiring for operations that would be a few lines in the default layout.
 Persistence needs explicit mapping rather than annotations on the entity (see 0002). 
 A reader who expects the standard Symfony tree needs a moment to orient, so the structure is documented in the README.