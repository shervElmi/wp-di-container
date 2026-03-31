# WP DI Container

A [PSR-11](https://www.php-fig.org/psr/psr-11/) compliant dependency injection container for WordPress plugin development.

[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](https://opensource.org/licenses/MIT)
[![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.2-blue)](https://www.php.net/)

## Requirements

- PHP 8.2+
- Composer
- WordPress (used within a WordPress plugin or theme)

## Installation

```bash
composer require sherv/wp-di-container
```

## Quick Start

```php
use Sherv\Container\Container;

$container = new Container();

// Bind an interface to a concrete implementation.
$container->bind( Logger_Contract::class, File_Logger::class );

// Resolve. File_Logger and all its dependencies are built automatically.
$logger = $container->make( Logger_Contract::class );
```

## Documentation

1. **[Introduction](./docs/01-introduction.md)**: Overview, features, and quick start.
2. **[Architecture](./docs/02-architecture.md)**: Components overview and UML diagram.
3. **[Container](./docs/03-container.md)**: Binding, resolving, singletons, extensions, and factory.
4. **[Exceptions](./docs/04-exceptions.md)**: Error handling reference.

## Development

To get started, clone the repository and install dependencies:

```bash
git clone https://github.com/shervElmi/wp-di-container.git
cd wp-di-container
composer install
```

### Scripts

| Command                  | Description                          |
| ------------------------ | ------------------------------------ |
| `composer test`          | Run the test suite                   |
| `composer test:coverage` | Run tests with code coverage         |
| `composer lint`          | Run PHP CodeSniffer                  |
| `composer format`        | Auto-fix coding standards violations |

## Contributing

Contributions are welcome. Please open an issue or pull request on [GitHub](https://github.com/shervElmi/wp-di-container).

## Security

To report a security vulnerability, please see [SECURITY.md](SECURITY.md).

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for a history of notable changes.

## License

© Sherv Elmi. Licensed under the [MIT License](LICENSE). Distributed without any warranty. See the license for details.
