# Jinja for PHP

[![Build Status](https://github.com/byjg/php-jinja/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/php-jinja/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/php-jinja/)
[![GitHub license](https://img.shields.io/github/license/byjg/php-jinja.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/php-jinja.svg)](https://github.com/byjg/uri/releases/)

Lightweight PHP implementation of the [Jinja2](https://jinja.palletsprojects.com/) template engine originally developed for Python.

## Overview

This library allows you to seamlessly process Jinja templates in PHP applications. It provides a familiar syntax for those coming from Python while offering a native PHP implementation.

### Key Features

- **Python Compatibility**: Process the same Jinja templates in both Python and PHP
- **Variable Support**: Full support for variables, properties, and nested structures
- **Conditional Logic**: `if`/`else` statements for conditional rendering
- **Loops**: Iterate over arrays and objects with `for` loops
- **Filters**: Transform output with built-in filters like `upper`, `lower`, `default`, etc.
- **Flexible Loaders**: Load templates from strings or the filesystem
- **Undefined Variable Handling**: Different strategies for handling undefined variables
- **Expressions**: Support for mathematical operations, comparisons, and concatenation

## Usage

```php
use ByJG\JinjaPhp\Template;
use ByJG\JinjaPhp\Undefined\DebugUndefined;

$templateString = <<<EOT
Hello {{ name }}
EOT;

$template = new Template($templateString);
$template->withUndefined(new DebugUndefined());  // Default is StrictUndefined

$variables = [
    'name' => 'World'
];
echo $template->render($variables);
```
## Documentation

The detailed documentation is organized as follows:

1. [Basic Usage](docs/basic-usage.md)
2. [Template Syntax](docs/template-syntax.md)
3. [Loaders](docs/loaders.md)
4. [Undefined Variables](docs/undefined-variables.md)
5. [Filters](docs/filters.md)
6. [Control Structures](docs/control-structures.md)
7. [Advanced Topics](docs/advanced-topics.md)
8. [API Reference](docs/api.md)
9. [PHP Jinja vs Python Jinja2 Comparison](docs/comparison.md)

## Installation

```bash
composer require byjg/jinja-php
```

## Dependencies

```mermaid  
flowchart TD  
    byjg/jinja-php   
```

----  
[Open source ByJG](http://opensource.byjg.com)