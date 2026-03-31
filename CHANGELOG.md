# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.0] - 2026-03-31

### Added

- `Container` class implementing PSR-11 `ContainerInterface` and `ArrayAccess`.
- `Container_Factory` for managing a shared container instance via `create()` and `reset()`.
- `Reflection_Class_Resolver` for runtime dependency resolution via PHP Reflection API.
- `Closure_Resolver` for closure-based bindings.
- `Resolver_Chain` for chaining multiple resolvers.
- Automatic dependency resolution via PHP Reflection API.
- Singleton and transient binding support.
- Binding extension support via closures.
- Circular dependency detection.
- Brain Monkey integration for testing WordPress functions without WordPress loaded.
- GitHub Actions workflows: Lint, Tests, CodeQL Analysis, Workflow Lint, Stale Monitor, Dependency Review, PR Labeling Automation, Enforce PR Labels, and Packagist.
- Dependabot configuration for GitHub Actions and Composer dependency updates.
- Husky pre-commit hook with lint-staged for PHPCS on staged PHP files.

[Unreleased]: https://github.com/shervElmi/wp-di-container/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/shervElmi/wp-di-container/releases/tag/v1.0.0
