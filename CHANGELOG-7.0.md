# Changelog - Version 7.0

> **Status: in development.** This document tracks changes landing on the `7.0` branch.
> Nothing here is released yet, and the contents may still change.

## New Features

- **Comments.** `{# ... #}` is removed from the output. It was documented but not implemented: the
  comment was printed as it was written. A comment can span lines, and what is inside it is not
  evaluated.

## Breaking Changes

- **A boolean is printed as `true` or `false`.** `{{ 1 == 2 }}`, `{{ flag }}` and `{{ loop.first }}`
  printed `1` for true and nothing at all for false. Conditions are not affected: `{% if flag %}`
  behaves as before. Inside a concatenation or a string filter a boolean is still `1` or empty.
- **`default` no longer replaces a variable that is `false`.** A `false` variable used to reach the
  filter as an empty string; `{{ flag | default("x") }}` now prints `false`.
- **An array is printed as JSON.** `{{ items }}` and `{{ "a-b" | split("-") }}` print
  `["a","b"]`. They used to raise PHP's "Array to string conversion" warning and print `Array`.
- **`FileSystemLoader` throws `LoaderException`** for a directory or a template that cannot be
  loaded. `LoaderException` now extends `InvalidArgumentException`, which is what was thrown
  before, so a `catch (InvalidArgumentException)` keeps working. Code that caught
  `LoaderException` as a plain `Exception` subclass is not affected either.

- **`FileSystemLoader` only loads from its own directory.** A template name is joined to the
  directory, so `../` in a name used to load a file from somewhere else. The loader now resolves
  the path and refuses a file outside the directory with the same `LoaderException` as a missing
  template. Names in subdirectories keep working. A symbolic link inside the directory that points
  outside it is refused as well.

## Documentation

- The loader pages documented a `getSource()` method that does not exist. They now describe
  `getTemplate()`, the `.jinja` default extension and `StringLoader`.
- Examples that did not run were corrected, and the constructs the engine does not support are
  listed in [Current Limitations](docs/advanced-topics.md#current-limitations): `else`/`elif`
  inside a nested block, `else` on an inner loop, whitespace control on closing tags, the ternary
  operator, `not`, and commas in filter arguments.

## Requirements

- PHP 8.3, 8.4, 8.5 and 8.6 are now supported: `"php": ">=8.3 <8.7"`.
  The previous `<8.6` upper bound excluded PHP 8.6, since `<8.6` is exclusive.

## Toolchain

- PHPUnit updated to `^12.5`.
- Psalm is installed as `psalm/phar` (`^6.16`) instead of `vimeo/psalm`.

  `vimeo/psalm` lists the PHP versions it supports and no published release includes
  8.6, so as a dev dependency it made `composer install` fail on the 8.6 build job
  before any test ran. `psalm/phar` requires only `php ^8.2` and bundles its own
  dependencies, so it installs on every PHP version in the matrix and cannot conflict
  with the project's. Psalm itself still refuses to *run* on 8.6, which is why the
  Psalm job uses 8.5. `composer psalm` runs it.

  PHPUnit 13 is deliberately **not** used. It requires PHP `>=8.4.1`, which would
  break the 8.3 floor.

## Continuous Integration

- The build matrix now includes PHP 8.6.
- The Psalm job now runs on PHP 8.5. Psalm declares
  `~8.1.31 || ~8.2.27 || ~8.3.16 || ~8.4.3 || ~8.5.0` and therefore does not run on
  PHP 8.6.

## Housekeeping

- `phpunit.xml.dist` renamed to `phpunit.xml`.
- Added the `LICENSE` file (MIT); the license was already declared in `composer.json`.
