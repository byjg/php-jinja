# Jinja for PHP

[![Build Status](https://github.com/byjg/jinja_php/actions/workflows/phpunit.yml/badge.svg?branch=master)](https://github.com/byjg/jinja_php/actions/workflows/phpunit.yml)
[![Opensource ByJG](https://img.shields.io/badge/opensource-byjg-success.svg)](http://opensource.byjg.com)
[![GitHub source](https://img.shields.io/badge/Github-source-informational?logo=github)](https://github.com/byjg/jinja_php/)
[![GitHub license](https://img.shields.io/github/license/byjg/jinja_php.svg)](https://opensource.byjg.com/opensource/licensing.html)
[![GitHub release](https://img.shields.io/github/release/byjg/jinja_php.svg)](https://github.com/byjg/jinja_php/releases/)

Jinja for PHP is a PHP implementation of the [Jinja2](http://jinja.pocoo.org/) template engine.

## Introduction

This library is a port of the Jinja2 template engine to PHP. It is a partial implementation of the Jinja2 template engine.

The main goal of this library is allow process templates both in Jinja Python and PHP, however he API is not the same as the original in Python.

## Implemented Features

Currently, the following features are implemented:

### Literals

Most of the literals are supported. e.g.

```jinja
{{ 1 }}
{{ 1.2 }}
{{ "Hello World" }}
{{ true }}
{{ false }}
{{ none }}
{{ [1, 2, 3] }}
{{ ['a': 1, 'b': 2] }}  // It is different from Python
```

TODO: use the python notation for dictionaries.

### Variables

Most of the variables are supported. e.g.

```jinja
{{ myvar }}
{{ myvar.myproperty }}
{{ myvar.myproperty.1 }}
{{ myvar.myproperty.a }}
{{ myvar.myproperty.a.myproperty }}
{{ myvar.myproperty.a.myproperty.1 }}
{{ myvar.myproperty.a.myproperty.1.myproperty }}
```

TODO: The notation with brackets is not yet supported.

### Filters

Some filters are implemented:

```jinja
{{ var | upper }}
{{ var | lower }}
{{ var | default }}
{{ var | default('-') }}
{{ var | replace('a', 'b') }}
{{ var | join }}
{{ var | join(',') }}
{{ var | split }}
{{ var | split(',') }}
{{ var | capitalize }}
{{ var | trim }}
{{ var | trim('-') }}
{{ var | length }}
```

### Math Operations

```jinja
{{ 1 + 2 }}
{{ 1 - 2 }}
{{ 1 * 2 }}
{{ 1 / 2 }}
{{ 1 % 2 }}
{{ 1 ** 2 }}
```

### Concatenation

```jinja
{{ "Hello" ~ "World" }}
{{ var1 ~ var2 }}
```

### Comparison

```jinja
{{ 1 == 2 }}
{{ 1 != 2 }}
{{ 1 < 2 }}
{{ 1 <= 2 }}
{{ 1 > 2 }}
{{ 1 >= 2 }}
```

### Logic Operations

There are some differences between the Python and PHP implementation.
TODO: use `and` and `or` instead of `&&` and `||`

```jinja
{{ 1 && 2 }}
{{ 1 || 2 }}
{{ ! 1 }}
```

### If

TODO: {% elif %} is not implemented yet.

```jinja
{% if var1 == var2 %}
    {{ var1 }} is equal to {{ var2 }}
{% else %}
    1 is not equal to 2 or 3
{% endif %}
```

```jinja
{% if 1 == 2 %}
    1 is equal to 2
{% else %}
    1 is not equal to 2 or 3
{% endif %}
```

### For

TODO: {% else %} is not implemented yet.

```jinja
{% for item in items %}
    {{ item }}
{% endfor %}
```

```jinja
{% for key, value in items %}
    {{ key }}: {{ value }}
{% endfor %}
```

Loop control variable:

- loop.index
- loop.index0
- loop.revindex
- loop.revindex0
- loop.first
- loop.last
- loop.length

```jinja
{% for item in items %}
    {{ loop.index }}: {{ item }}
{% endfor %}
```

## Usage

```php
use ByJG\Jinja\Template;

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

## Installation

```bash
composer require byjg/jinja_php
```
