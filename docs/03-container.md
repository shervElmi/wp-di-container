# Container

The `Container` class is the core of WP DI Container. It manages bindings, resolves dependencies automatically, and supports singletons, closures, extensions, and array access.

## Creating a Container

```php
use Sherv\Container\Container;

$container = new Container();
```

The container ships with a default resolver chain (`Reflection_Class_Resolver` + `Closure_Resolver`). You can provide a custom resolver if needed:

```php
use Sherv\Container\Container;
use Sherv\Container\Contracts\Resolver;

$container = new Container( $custom_resolver );
```

## Zero-Configuration Resolution

If a class depends only on other concrete classes, the container resolves it automatically with no explicit binding needed.

```php
/**
 * Fetches user data from a remote API.
 */
class Users_Provider {

    /**
     * Retrieve users from the remote API.
     *
     * @return array The list of users.
     */
    public function get_users(): array {
        // ...
    }
}

/**
 * Handles user-related requests.
 */
class Users_Controller {

    /**
     * Create a new users controller instance.
     *
     * @param Users_Provider $provider The users data provider.
     */
    public function __construct( private Users_Provider $provider ) {
    }

    /**
     * Get all users.
     *
     * @return array The list of users.
     */
    public function get_users(): array {
        return $this->provider->get_users();
    }
}

// Users_Provider is automatically resolved and injected.
$controller = $container->make( Users_Controller::class );
```

No binding required. The container inspects the constructor, resolves `Users_Provider`, and injects it.

## Binding

### Binding Interfaces to Implementations

When a class depends on an interface, the container cannot guess which implementation to use. Bind the interface to a concrete class:

```php
$container->bind( Logger_Contract::class, File_Logger::class );
```

Now, whenever a class depends on `Logger_Contract`, the container provides a `File_Logger` instance:

```php
/**
 * A service that uses a logger.
 */
class My_Service {

    /**
     * Create a new service instance.
     *
     * @param Logger_Contract $logger The logger instance.
     */
    public function __construct( private Logger_Contract $logger ) {
    }
}

$container->bind( Logger_Contract::class, File_Logger::class );
$service = $container->make( My_Service::class );
// File_Logger is injected automatically.
```

### Binding with a Closure

Use a closure when you need to control how an instance is created, especially when a class requires a scalar parameter that the container cannot resolve automatically.

```php
$container->bind(
    Users_Provider::class,
    fn( $container ) => new Users_Provider(
        $container->make( Http_Client::class ),
        get_option( 'my_plugin_api_url' )
    )
);
```

The closure receives the container as its first argument, giving you full access to resolve other dependencies from within it.

### Binding a Scalar Value

You can bind any non-resolvable value directly, such as a string, integer, boolean, or array.

```php
$container->bind( 'api_url', 'https://api.example.com' );
$container->bind( 'debug',   false );
$container->bind( 'config',  [ 'timeout' => 30, 'retries' => 3 ] );
```

Resolve them the same way as any other entry:

```php
$url    = $container->make( 'api_url' ); // 'https://api.example.com'
$config = $container->make( 'config' );  // [ 'timeout' => 30, 'retries' => 3 ]
```

### Binding Singletons

If you want the container to always return the same instance for a given class, use the `singleton()` method.

```php
$container->singleton( My_Cache::class );

$first  = $container->make( My_Cache::class );
$second = $container->make( My_Cache::class );

// $first === $second (true)
```

Singletons also work with closures and interface bindings:

```php
// Singleton with a closure.
$container->singleton(
    My_Cache::class,
    fn( $container ) => new My_Cache( 'my_plugin_' )
);

// Singleton for an interface.
$container->singleton( Cache_Contract::class, My_Cache::class );
```

> **Note:** Passing `$with` parameters to `make()` on a singleton overrides the cached instance for that call, allowing one-off parameterised resolutions without permanently breaking the singleton behaviour.

### Extending Bindings

Use `extend()` to decorate an existing binding after it has been registered, without replacing the original.

```php
$container->bind( 'config', fn() => [ 'debug' => false ] );

$container->extend(
    'config',
    function ( array $config, $container ) {
        $config['version'] = '1.0.0';
        return $config;
    }
);

$config = $container->make( 'config' );
// [ 'debug' => false, 'version' => '1.0.0' ]
```

Multiple extenders are applied in registration order. To remove all extenders for a binding, call `forget_extenders()`:

```php
$container->forget_extenders( 'config' );
```

## Resolving

### The `make()` Method

Resolve a class or binding from the container:

```php
$service = $container->make( My_Service::class );
```

The container resolves all dependencies required by `My_Service` automatically. `make()` also works for unbound class names via zero-configuration resolution.

### Passing Parameters During Resolution

If a class requires parameters that the container cannot resolve automatically, pass them as the second argument. Keys are matched to constructor parameter names.

```php
/**
 * A service that requires a manually-provided API URL.
 */
class My_Service {

    /**
     * Create a new service instance.
     *
     * @param Http_Client $client  The HTTP client (resolved from the container).
     * @param string      $api_url The API URL (must be provided).
     * @param int         $timeout The request timeout in seconds.
     */
    public function __construct(
        private Http_Client $client,
        private string $api_url,
        private int $timeout = 30,
    ) {
    }
}

$service = $container->make( My_Service::class, [ 'api_url' => 'https://api.example.com' ] );
```

### PSR-11: `get()` and `has()`

The container implements PSR-11's `ContainerInterface`, making it interoperable with any framework or library that accepts a PSR-11 container.

```php
if ( $container->has( My_Service::class ) ) {
    $service = $container->get( My_Service::class );
}
```

`get()` requires the entry to be explicitly registered and throws `Entry_Not_Found_Exception` if it is not. `make()` always attempts resolution, even for unbound class names. Use `get()` when you want strict PSR-11 behaviour, and `make()` when you want auto-resolution.

### ArrayAccess

The container supports PHP's array syntax for reading and writing bindings.

```php
// Bind.
$container['api_url'] = 'https://api.example.com';
$container['logger']  = fn() => new File_Logger();

// Resolve.
$url    = $container['api_url'];
$logger = $container['logger'];

// Check.
isset( $container['api_url'] );  // true

// Remove.
unset( $container['api_url'] );
```

## Container Factory

`Container_Factory` provides a single shared container instance across your entire plugin or theme. This is the recommended pattern for plugin bootstrapping.

```php
use Sherv\Container\Container_Factory;

// In your plugin bootstrap, register all bindings once.
$container = Container_Factory::create();
$container->bind( Logger_Contract::class, File_Logger::class );
$container->singleton( My_Cache::class );
$container->bind( 'api_url', get_option( 'my_plugin_api_url' ) );

// Anywhere else in your plugin, always the same container instance.
$logger = Container_Factory::create()->make( Logger_Contract::class );
$cache  = Container_Factory::create()->make( My_Cache::class );
```

`reset()` discards the shared instance. This is primarily useful for resetting state between unit tests.

```php
Container_Factory::reset();
```

## Advanced

### Circular Dependency Detection

The container automatically detects when two classes depend on each other and throws a `Failed_Resolution_Exception` with a clear message.

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
// Failed_Resolution_Exception: "Circular dependency detected for "Service_A"."
```

### Inspecting Bindings

Retrieve all registered bindings, useful for debugging.

```php
$bindings = $container->get_bindings();
```

## Quick Reference

| Operation | Code |
|---|---|
| Bind a concrete class | `$container->bind( My_Class::class )` |
| Bind interface to implementation | `$container->bind( Contract::class, Implementation::class )` |
| Bind with a closure | `$container->bind( My_Class::class, fn( $c ) => new My_Class( $c->make( Dep::class ) ) )` |
| Bind a scalar value | `$container->bind( 'api_url', 'https://example.com' )` |
| Bind a singleton | `$container->singleton( My_Class::class )` |
| Singleton for an interface | `$container->singleton( Contract::class, Implementation::class )` |
| Extend a binding | `$container->extend( My_Class::class, fn( $instance, $c ) => $instance )` |
| Remove extenders | `$container->forget_extenders( My_Class::class )` |
| Resolve a class | `$container->make( My_Class::class )` |
| Resolve with parameters | `$container->make( My_Class::class, [ 'param' => 'value' ] )` |
| PSR-11 check | `$container->has( My_Class::class )` |
| PSR-11 get | `$container->get( My_Class::class )` |
| Shared factory | `Container_Factory::create()` |
| Reset factory | `Container_Factory::reset()` |
| Inspect bindings | `$container->get_bindings()` |

## Next Steps

- **[Introduction](./01-introduction.md)**: Overview, features, and quick start.
- **[Architecture](./02-architecture.md)**: Components overview and UML diagram.
- **[Exceptions](./04-exceptions.md)**: Error handling reference.
