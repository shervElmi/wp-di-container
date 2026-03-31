# Introduction

**WP DI Container** is a lightweight, [PSR-11](https://www.php-fig.org/psr/psr-11/) compatible dependency injection container built for the WordPress ecosystem. It provides automatic dependency resolution, singleton and transient bindings, binding extensions, and `ArrayAccess` support, following WordPress coding standards.

## What Is Dependency Injection?

Dependency injection is a design pattern where a class receives its dependencies from the outside rather than creating them itself. This makes your code:

- **Testable**: Swap real dependencies for mocks or stubs in unit tests.
- **Maintainable**: Dependencies are declared explicitly in the constructor, with no hidden coupling.
- **Flexible**: Change an implementation without touching the classes that use it.

Consider this example:

```php
// Without dependency injection, tightly coupled and hard to test.
class Users_Service {

    /**
     * The users provider instance.
     *
     * @var Users_Provider
     */
    private Users_Provider $provider;

    /**
     * Create a new users service instance.
     */
    public function __construct() {
        $this->provider = new Users_Provider(); // Always this exact class.
    }
}

// With dependency injection, decoupled and testable.
class Users_Service {

    /**
     * Create a new users service instance.
     *
     * @param Users_Provider $provider The users provider.
     */
    public function __construct( private Users_Provider $provider ) {
    }
}
```

In the second version, `Users_Provider` is injected from the outside. You can pass a real instance in production and a mock in tests, without changing `Users_Service` at all.

## Features

| Feature | Description |
|---|---|
| **Auto-Resolution** | Resolves concrete classes and their full dependency trees automatically via PHP's Reflection API, with no configuration needed. |
| **Bindings** | Bind interfaces to implementations, closures to factory logic, or identifiers to scalar values. |
| **Singletons** | Mark a binding as shared so the container returns the same instance on every resolution. |
| **Binding Extensions** | Decorate an already-registered binding with additional behaviour without replacing it. |
| **PSR-11 Compatible** | Implements `Psr\Container\ContainerInterface` (`get()` and `has()`). |
| **ArrayAccess** | Read and write bindings using familiar array syntax. |
| **Container Factory** | Manages a single shared container instance across your entire plugin or theme. |
| **Circular Dependency Detection** | Throws a descriptive exception immediately when a circular dependency is detected. |

## When to Use the Container

**Zero-configuration resolution** handles most cases automatically. If a class depends only on other concrete classes, just call `make()` with no prior setup.

You need to interact with the container when:

- **Binding interfaces** to concrete implementations. The container cannot guess which class to use.
- **Binding singletons**, a service that must have exactly one instance (e.g. a cache or a logger).
- **Binding closures** for full control over instantiation, including passing scalar constructor arguments.
- **Binding scalar values** like strings, integers, or arrays that cannot be auto-resolved by reflection.
- **Extending bindings** to decorate a resolved instance with extra behaviour after it is built.

## Getting Started

Install the package via Composer:

```bash
composer require sherv/wp-di-container
```

Create a container, register your services, and resolve them:

```php
use Sherv\Container\Container;

$container = new Container();

// Bind an interface to a concrete implementation.
$container->bind( Logger_Contract::class, File_Logger::class );

// Resolve. File_Logger and all its dependencies are built automatically.
$logger = $container->make( Logger_Contract::class );
```

For a shared container instance across your entire plugin, use `Container_Factory`:

```php
use Sherv\Container\Container_Factory;

// In your plugin bootstrap, register everything once.
$container = Container_Factory::create();
$container->bind( Logger_Contract::class, File_Logger::class );
$container->singleton( Cache_Service::class );

// Anywhere else in your plugin, always the same container.
$logger = Container_Factory::create()->make( Logger_Contract::class );
```

## Documentation

1. **[Introduction](./01-introduction.md)**: Overview, features, and quick start.
2. **[Architecture](./02-architecture.md)**: Components overview and UML diagram.
3. **[Container](./03-container.md)**: Binding, resolving, singletons, extensions, and factory.
4. **[Exceptions](./04-exceptions.md)**: Error handling reference.
