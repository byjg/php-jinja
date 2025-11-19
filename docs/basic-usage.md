---
sidebar_position: 1
---

# Basic Usage

This guide covers the essential basics of using PHP-Jinja.

## Installation

Install PHP-Jinja via Composer:

```bash
composer require byjg/jinja-php
```

## Creating a Template

You can create a template from a string:

```php
<?php
use ByJG\JinjaPhp\Template;

$templateString = 'Hello {{ name }}!';
$template = new Template($templateString);
```

## Rendering a Template

Once you have a template, you can render it with variables:

```php
$variables = [
    'name' => 'World',
    'user' => [
        'name' => 'John',
        'age' => 30
    ],
    'items' => ['apple', 'banana', 'orange']
];

$result = $template->render($variables);
echo $result;  // Outputs: Hello World!
```

## Complete Example

Here's a complete example that shows the basics:

```php
<?php
require 'vendor/autoload.php';

use ByJG\JinjaPhp\Template;

// Create a template
$templateString = <<<EOT
Hello {{ name }}!

User info:
- Name: {{ user.name }}
- Age: {{ user.age }}

Items:
{% for item in items %}
- {{ item }}
{% endfor %}
EOT;

$template = new Template($templateString);

// Define variables
$variables = [
    'name' => 'World',
    'user' => [
        'name' => 'John',
        'age' => 30
    ],
    'items' => ['apple', 'banana', 'orange']
];

// Render the template
echo $template->render($variables);
```

Output:
```
Hello World!

User info:
- Name: John
- Age: 30

Items:
- apple
- banana
- orange
```

## Next Steps

For more detailed information, check out the following guides:

- [Template Syntax](template-syntax.md) - Learn about variables, expressions and syntax
- [Filters](filters.md) - How to transform output with filters
- [Control Structures](control-structures.md) - Conditionals and loops
- [Undefined Variables](undefined-variables.md) - Handling missing variables
- [Loaders](loaders.md) - Loading templates from files 