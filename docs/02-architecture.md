# Architecture

This page describes the components that make up WP DI Container and how they collaborate.

## UML Diagram

```mermaid
---
title: WP DI Container Architecture
config:
  theme: neutral
---

classDiagram
direction TB

class Container_Contract {
	<<interface>>
	+bind(string id, mixed entry = null, bool shared = false) void
	+singleton(string id, mixed entry = null) void
	+extend(string id, Closure closure) void
	+make(string id, array with = []) mixed
}

class Resolver {
	<<interface>>
	+resolve(mixed entry, array with = []) mixed
	+is_resolvable(mixed entry) bool
}

class Container_Factory {
	-Container container$
	+create() Container$
	+reset() void$
}

class Container {
	#array bindings
	#mixed[] shared_entries
	#array[] extenders
	#bool[] resolved
	#string[] resolving_stack
	+__construct(?Resolver resolver = null)
	+bind(string id, mixed entry = null, bool shared = false) void
	+singleton(string id, mixed entry = null) void
	+extend(string id, Closure closure) void
	+make(string id, array with = []) mixed
	+get(string id) mixed
	+has(string id) bool
	-create_default_resolver() Resolver
	#resolved(string id) bool
	#is_shared_entry(string id) bool
	#get_extenders(string id) array
	+get_bindings() array
	+forget_extenders(string id) void
	+offsetExists(mixed key) bool
	+offsetGet(mixed key) mixed
	+offsetSet(mixed key, mixed value) void
	+offsetUnset(mixed key) void
}

class Resolver_Chain {
	+__construct(Resolver[] resolvers = [])
	+resolve(mixed entry, array with = []) mixed
	+is_resolvable(mixed entry) bool
}

class Reflection_Class_Resolver {
	+__construct(Container container)
	+resolve(mixed entry, array with = []) mixed
	+is_resolvable(mixed entry) bool
	-resolve_dependencies(array dependencies, array with) array
	-resolve_dependency(ReflectionParameter dependency) mixed
	-resolve_class(ReflectionParameter dependency, string class_name) mixed
	-resolve_primitive(ReflectionParameter dependency) mixed
	-resolve_parameter_class_name(ReflectionParameter param) string|null
}

class Closure_Resolver {
	+__construct(Container container)
	+resolve(mixed entry, array with = []) mixed
	+is_resolvable(mixed entry) bool
}

Container_Factory --> Container
Container_Contract <|.. Container
Container ..> Resolver
Resolver <|.. Resolver_Chain
Resolver <|.. Closure_Resolver
Resolver <|.. Reflection_Class_Resolver
Resolver_Chain *-- Resolver
Closure_Resolver ..> Container_Contract
Reflection_Class_Resolver ..> Container_Contract
```

## Components

### Core

| Class | Role |
|---|---|
| `Container` | Central class. Manages bindings, shared entries, extenders, and resolves dependencies. Implements `ContainerInterface` and `ArrayAccess`. |
| `Container_Factory` | Static factory that manages a single shared `Container` instance across your entire plugin or theme. |

### Resolver System

| Class | Role |
|---|---|
| `Resolver` (interface) | Contract for dependency resolution. Defines `resolve()` and `is_resolvable()`. |
| `Resolver_Chain` | Chains resolvers to handle dependency resolution in sequence. Tries each resolver until one succeeds. |
| `Reflection_Class_Resolver` | Resolves class dependencies using PHP's Reflection API. Handles constructor injection, default values, and `self`/`parent` type hints. |
| `Closure_Resolver` | Resolves an entry by invoking closures with the container and optional parameters. |

### Contracts

| Interface | Role |
|---|---|
| `Container` (contract) | Defines the full container API: `bind()`, `singleton()`, `extend()`, `make()`. Extends PSR-11 `ContainerInterface` and `ArrayAccess`. |
| `Resolver` | Defines the resolver contract: `resolve()` and `is_resolvable()`. |

### Exceptions

| Class | Extends | When Thrown |
|---|---|---|
| `Failed_Resolution_Exception` | `RuntimeException` | Circular dependencies, unresolvable entries, uninstantiable classes, unresolvable primitives. |
| `Entry_Not_Found_Exception` | `InvalidArgumentException` | `get()` is called for an identifier with no binding. |

## Resolution Flow

```
$container->make( My_Service::class )
│
├─ Check shared entries                ← Return cached singleton if available
├─ Check circular dependency           ← Throw if already resolving this entry
│
├─ Resolver_Chain::resolve()
│   ├─ Reflection_Class_Resolver       ← If entry is a class/interface string
│   │   ├─ Check instantiable          ← Throw if interface/abstract without binding
│   │   ├─ Resolve constructor params  ← Recursively resolve typed dependencies
│   │   └─ Return new instance
│   │
│   └─ Closure_Resolver                ← If entry is a Closure
│       └─ Invoke closure( $container, $with )
│
├─ Apply extenders                     ← Run registered extend() closures in order
├─ Cache if singleton                  ← Store in shared_entries for next call
└─ Return resolved object
```

## Next Steps

- **[Introduction](./01-introduction.md)**: Overview, features, and quick start.
- **[Container](./03-container.md)**: Binding, resolving, singletons, extensions, and factory.
- **[Exceptions](./04-exceptions.md)**: Error handling reference.
