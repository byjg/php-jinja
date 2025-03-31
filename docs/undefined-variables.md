---
sidebar_position: 4
---

# Undefined Variables

PHP-Jinja provides different strategies for handling undefined variables in templates.

## What Are Undefined Variables?

When a template references a variable that doesn't exist in the provided context, it's considered an "undefined variable":

```jinja
{{ some_variable_that_doesnt_exist }}
```

By default, PHP-Jinja will throw an exception when this happens, but you can customize this behavior using different undefined variable handlers.

## Available Handlers

PHP-Jinja offers three built-in strategies for handling undefined variables:

### 1. StrictUndefined (Default)

Throws an exception when an undefined variable is accessed:

```php
<?php
use ByJG\JinjaPhp\Template;
use ByJG\JinjaPhp\Undefined\StrictUndefined;

$template = new Template('Hello {{ name }}!');
// StrictUndefined is the default, but you can explicitly set it:
$template->withUndefined(new StrictUndefined());

// This will throw a TemplateParseException
$template->render([]);  // No 'name' variable provided
```

### 2. DebugUndefined

Shows placeholder text indicating the variable is undefined (without throwing an exception):

```php
<?php
use ByJG\JinjaPhp\Template;
use ByJG\JinjaPhp\Undefined\DebugUndefined;

$template = new Template('Hello {{ name }}!');
$template->withUndefined(new DebugUndefined());

// This will render: Hello {{ NOT_FOUND: name }}!
echo $template->render([]);
```

This is particularly useful during development to identify missing variables.

### 3. DefaultUndefined

Returns a default value for any undefined variables (without throwing an exception):

```php
<?php
use ByJG\JinjaPhp\Template;
use ByJG\JinjaPhp\Undefined\DefaultUndefined;

$template = new Template('Hello {{ name }}!');
$template->withUndefined(new DefaultUndefined('Guest'));

// This will render: Hello Guest!
echo $template->render([]);
```

## The Default Filter

For individual variables, you can also use the `default` filter as an alternative to `DefaultUndefined`:

```jinja
{{ name | default('Guest') }}
```

This only applies to the specific variable, while `DefaultUndefined` affects all undefined variables in the template.

## Creating a Custom Undefined Handler

You can create your own undefined variable handler by implementing the `UndefinedInterface`:

```php
<?php
use ByJG\JinjaPhp\Undefined\UndefinedInterface;

class CustomUndefined implements UndefinedInterface
{
    public function render(string $varName): string
    {
        return "[Missing: $varName]";
    }
}

// Using your custom handler
$template = new Template('Hello {{ name }}!');
$template->withUndefined(new CustomUndefined());

// This will render: Hello [Missing: name]!
echo $template->render([]);
```

## Best Practices

- Use `StrictUndefined` in production to catch errors early and prevent unexpected output
- Use `DebugUndefined` during development to identify missing variables
- Use `DefaultUndefined` when you want to provide fallback values for optional variables
- For individual variables that might be undefined, use the `default` filter 