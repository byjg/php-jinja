# Changelog - Version 7.0

> **Status: in development.** This document tracks changes landing on the `7.0` branch.
> Nothing here is released yet, and the contents may still change.

## Breaking Changes

- None.

## Requirements

- PHP 8.3, 8.4, 8.5 and 8.6 are now supported: `"php": ">=8.3 <8.7"`.
  The previous `<8.6` upper bound excluded PHP 8.6, since `<8.6` is exclusive.

## Toolchain

- PHPUnit updated to `^12.5`.
- Psalm updated to `^6.16`.

  PHPUnit 13 is deliberately **not** used. It requires PHP `>=8.4.1`, which would
  break the 8.3 floor, and it pulls `sebastian/diff ^9.0`, which the newest stable
  Psalm (6.16.1) does not accept — that combination silently resolves Psalm to an
  unreleased `6.x-dev` branch. Pinning PHPUnit to `^12.5` keeps a single stable
  PHPUnit and a single stable Psalm across the whole matrix.

## Continuous Integration

- The build matrix now includes PHP 8.6.
- The Psalm job now runs on PHP 8.5. Psalm 6.16.1 declares
  `~8.1.31 || ~8.2.27 || ~8.3.16 || ~8.4.3 || ~8.5.0` and therefore cannot be
  installed on PHP 8.6.

## Housekeeping

- `phpunit.xml.dist` renamed to `phpunit.xml`.
