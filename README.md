# creacoon/phpstan-rules

A small collection of custom PHPStan rules to enforce consistent naming conventions across your PHP codebase.

These rules help you keep parameters, scoped variables, and class-like names aligned with a predictable style, making codebases easier to read, review, and maintain.

## Overview
This repository implements a PHPStan extension that registers three rules via `rules.neon` and Composer `extra.phpstan.includes`:

- EnforceClassNameConvention
  - Ensures class/interface/trait names use PascalCase.
- EnforceParameterConvention
  - Ensures:
    - Closure and arrow-function parameters use snake_case.
    - Named function/method parameters use camelCase.
  - Supports excluding specific namespaces from the rule.
- EnforceScopedVariableConvention
  - Enforces scoped variables use snake_case.

Entry point for PHPStan discovery:
- Composer `extra.phpstan.includes` points to `rules.neon`.
- `rules.neon` registers the rule classes as PHPStan services with the `phpstan.rules.rule` tag.

## Installation
This package is intended to be installed in a PHP project analyzed with PHPStan.

install:

```
composer require --dev creacoon/phpstan-rules
```

## Usage

Include in your project's `phpstan.neon`/`phpstan.neon.dist`:

```neon
includes:
    - vendor/creacoon/phpstan-rules/rules.neon
```

Run PHPStan as usual in your project:

```
vendor/bin/phpstan
```

### Configuring excluded namespaces (EnforceParameterConvention)
`EnforceParameterConvention` and `EnforceScopedVariableConvention` supports excluding namespaces (e.g., DTOs) from the camelCase rule for parameters of named functions/methods.

If you need to override the default excluded namespaces, you can configure the service in your project's PHPStan config by redefining the service and supplying the `excludedNamespaces` constructor argument, for example:

```neon
services:
    -
        factory: Creacoon\PhpStanRules\EnforceParameterConvention([
            "App\\Support\\Dto"
        ])
        tags: [phpstan.rules.rule]
```

## Compatibility
- PHP: ^8.0