# PHP-Jinja API Reference

This guide covers the PHP-Jinja API in detail.

## Core Classes

### Template Class

The `Template` class is the central component of PHP-Jinja.

```php
<?php
use ByJG\JinjaPhp\Template;

// Constructor
$template = new Template(string $template);

// Methods
$template->withUndefined(UndefinedInterface $undefined): static;  // Set undefined variable handler
$template->render(array $variables = []): string|array|null;      // Render the template
```

#### Example

```php
$template = new Template('Hello {{ name }}!');
$result = $template->render(['name' => 'World']);
echo $result;  // Outputs: Hello World!
```

### Loader Classes

PHP-Jinja provides classes for loading templates from different sources.

#### FileSystemLoader

```php
<?php
use ByJG\JinjaPhp\Loader\FileSystemLoader;

// Constructor
$loader = new FileSystemLoader(string $basePath);

// Methods
$loader->getSource(string $name): string;  // Load a template file
```

#### Example

```php
$loader = new FileSystemLoader('/path/to/templates');
$templateString = $loader->getSource('my-template.html');
$template = new Template($templateString);
```

### Undefined Variable Handlers

PHP-Jinja offers three implementations of `UndefinedInterface`:

1. **StrictUndefined** (default): Throws an exception for undefined variables
   ```php
   $template->withUndefined(new StrictUndefined());
   ```

2. **DebugUndefined**: Shows a placeholder for undefined variables
   ```php 
   $template->withUndefined(new DebugUndefined());
   // Output example: {{ NOT_FOUND: variable_name }}
   ```

3. **DefaultUndefined**: Uses a default value for undefined variables
   ```php
   $template->withUndefined(new DefaultUndefined('default value'));
   ```

## Interface Reference

### LoaderInterface

```php
<?php
namespace ByJG\JinjaPhp\Loader;

interface LoaderInterface
{
    public function getSource(string $name): string;
}
```

### UndefinedInterface

```php
<?php
namespace ByJG\JinjaPhp\Undefined;

interface UndefinedInterface
{
    public function render(string $varName): string;
}
```

## Exception Classes

PHP-Jinja may throw the following exceptions:

- `TemplateParseException`: When a template cannot be parsed or a variable is not found (with StrictUndefined)
- `LoaderException`: When a template file cannot be loaded

## Feature Reference

### Template Syntax Elements

| Feature | Description | Example |
|---------|-------------|---------|
| Variables | Access variables in templates | `{{ variable }}`, `{{ object.property }}` |
| Expressions | Mathematical, logical operations | `{{ 1 + 2 }}`, `{{ true && false }}` |
| Conditionals | If statements | `{% if condition %}...{% else %}...{% endif %}` |
| Loops | For loops | `{% for item in items %}...{% endfor %}` |
| Filters | Transform variables | `{{ variable \| filter }}` |
| Comments | Non-rendered notes | `{# Comment #}` |
| Whitespace Control | Control whitespace in output | `{%- if condition -%}...{%- endif -%}` |

### Available Filters

| Filter | Description | Example |
|--------|-------------|---------|
| `upper` | Convert to uppercase | `{{ "hello" \| upper }}` |
| `lower` | Convert to lowercase | `{{ "HELLO" \| lower }}` |
| `capitalize` | Capitalize first letter of each word | `{{ "hello world" \| capitalize }}` |
| `trim` | Remove whitespace | `{{ " hello " \| trim }}` |
| `replace` | Replace substring | `{{ "hello" \| replace("h", "j") }}` |
| `length` | Get length | `{{ "hello" \| length }}` |
| `default` | Set default value | `{{ undefined \| default("Guest") }}` |
| `join` | Join array with delimiter | `{{ ["a", "b"] \| join("-") }}` |
| `split` | Split string to array | `{{ "a-b" \| split("-") }}` | 