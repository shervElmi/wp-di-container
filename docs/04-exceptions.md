# Exceptions

WP DI Container provides two custom exception classes for clear, descriptive error handling during dependency resolution. Both live in the `Sherv\Container\Exception` namespace and implement the corresponding PSR-11 interfaces, so you can catch them at the PSR-11 level for container-agnostic code.

## Exception Reference

| Exception | PSR-11 Interface | Extends | When Thrown |
|---|---|---|---|
| `Entry_Not_Found_Exception` | `NotFoundExceptionInterface` | `InvalidArgumentException` | `get()` is called for an identifier with no binding. |
| `Failed_Resolution_Exception` | `ContainerExceptionInterface` | `RuntimeException` | Resolution is attempted but fails for any reason. |

## Entry_Not_Found_Exception

Thrown exclusively by `get()` when the requested identifier has no binding registered in the container.

### Factory Methods

| Method | When It's Thrown |
|---|---|
| `for_entry_id( $id )` | The requested identifier has no binding in the container. |

### When It Occurs

```php
use Sherv\Container\Exception\Entry_Not_Found_Exception;

try {
    $service = $container->get( 'nonexistent' );
} catch ( Entry_Not_Found_Exception $e ) {
    echo $e->getMessage();
    // "No entry found for identifier "nonexistent"."
}
```

> **Note:** `make()` does **not** throw `Entry_Not_Found_Exception`. It always attempts resolution, even for class names that have no explicit binding (zero-configuration resolution). Use `get()` when you require a strict PSR-11 "must be registered" contract.

## Failed_Resolution_Exception

Thrown whenever the container attempts resolution but cannot complete it. The container uses named constructors (static factory methods) internally to produce descriptive messages for each failure scenario.

### Factory Methods

| Method | When It's Thrown |
|---|---|
| `for_circular_dependency( $id )` | Resolving an entry would require resolving itself, directly or transitively. |
| `for_unresolvable_entry( $entry )` | No resolver in the chain can handle the entry. |
| `for_invalid_closure( $entry )` | An entry expected to be a closure is not one. |
| `for_unreflectable_entry( $entry )` | The entry string does not correspond to a known class or interface. |
| `for_uninstantiable_entry( $name )` | The class is an interface or abstract with no concrete binding. |
| `for_unresolvable_primitive( $dep )` | A required scalar constructor parameter has no default and no `$with` value. |

### Circular Dependency

Thrown when the container detects that resolving an entry would require resolving itself, directly or transitively.

```php
class Service_A {

    /**
     * @param Service_B $b The service B dependency.
     */
    public function __construct( Service_B $b ) {
    }
}

class Service_B {

    /**
     * @param Service_A $a The service A dependency (circular).
     */
    public function __construct( Service_A $a ) {
    }
}

$container->make( Service_A::class );
// "Circular dependency detected for "Service_A"."
```

### Unresolvable Entry

Thrown by `Resolver_Chain` when none of the configured resolvers can handle the entry, for example a plain string that is neither a class name nor a bound identifier.

```php
$container->make( 'not_a_class_or_binding' );
// "Cannot resolve entry "not_a_class_or_binding"."
```

### Invalid Closure

Thrown by `Closure_Resolver` when an entry that was expected to be a closure turns out not to be one.

```php
// "The provided entry "foo" is not a valid closure."
```

### Unreflectable Entry

Thrown by `Reflection_Class_Resolver` when the entry is a string but does not correspond to a known class or interface.

```php
$container->make( 'NonExistentClass' );
// "Cannot reflect on the class or interface "NonExistentClass". It may not be valid or does not exist."
```

### Uninstantiable Entry

Thrown when the container tries to instantiate an interface or an abstract class that has no concrete binding registered.

```php
// Attempting to resolve an interface with no binding.
$container->make( Logger_Contract::class );
// "Cannot instantiate entry "Logger_Contract". It may be an interface or an abstract class,
//  probably forgot to bind an implementation."
```

Fix by binding the interface before resolving:

```php
$container->bind( Logger_Contract::class, File_Logger::class );
$logger = $container->make( Logger_Contract::class ); // Works.
```

### Unresolvable Primitive

Thrown when a constructor has a required primitive parameter (a scalar type or untyped parameter) with no default value and no matching entry provided via `$with`.

```php
/**
 * A service that requires a manually-provided API URL.
 */
class My_Service {

    /**
     * Create a new service instance.
     *
     * @param string $api_url The API URL (required, no default).
     */
    public function __construct( string $api_url ) {
    }
}

$container->make( My_Service::class );
// "Unresolvable dependency "$api_url" in class "My_Service"."
```

Fix by passing the value explicitly:

```php
$container->make( My_Service::class, [ 'api_url' => 'https://api.example.com' ] );
```

Or bind it with a closure:

```php
$container->bind(
    My_Service::class,
    fn( $container ) => new My_Service( get_option( 'my_plugin_api_url' ) )
);
```

## Handling Exceptions

### Catch PSR-11 Interfaces

Use PSR-11 interfaces for container-agnostic error handling. This keeps your code portable across different container implementations.

```php
use Psr\Container\NotFoundExceptionInterface;
use Psr\Container\ContainerExceptionInterface;

try {
    $service = $container->get( My_Service::class );
} catch ( NotFoundExceptionInterface $e ) {
    // Entry not registered, safe to provide a fallback or skip.
} catch ( ContainerExceptionInterface $e ) {
    // Resolution failed, log and handle gracefully.
    error_log( $e->getMessage() );
}
```

### Catch Concrete Exceptions

For more specific handling, catch the concrete exception classes directly.

```php
use Sherv\Container\Exception\Entry_Not_Found_Exception;
use Sherv\Container\Exception\Failed_Resolution_Exception;

try {
    $service = $container->make( My_Service::class );
} catch ( Failed_Resolution_Exception $e ) {
    // Inspect the failure message for debugging.
    error_log( 'Container resolution failed: ' . $e->getMessage() );
}
```

## Developer Notes

- **Use `make()` for auto-resolution**, `get()` only when the entry must be explicitly registered.
- **Uninstantiable entry errors** almost always mean a forgotten `bind()` or `singleton()` call for an interface.
- **Unresolvable primitive errors** mean a scalar constructor argument needs to be provided via `$with` or a closure binding.
- **Circular dependency errors** indicate a design issue. Revisit the relationship between the two classes and consider introducing an intermediary or lazy resolution.

## Next Steps

- **[Introduction](./01-introduction.md)**: Overview, features, and quick start.
- **[Architecture](./02-architecture.md)**: Components overview and UML diagram.
- **[Container](./03-container.md)**: Binding, resolving, singletons, extensions, and factory.
