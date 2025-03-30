# Advanced Topics

This document covers advanced usage techniques for PHP-Jinja based strictly on what's available in the code.

## Current Limitations

PHP-Jinja is a lightweight implementation of Jinja2 for PHP applications with the following limitations:

1. **Limited Filter Set**: Only the following filters are available:
   - `upper`, `lower`, `capitalize`
   - `trim`, `replace`, `length`
   - `default`, `join`, `split`

2. **No Custom Filters**: There is no API to add custom filters.

3. **No Template Inheritance**: Features like `extends`, `block`, and `include` are not supported.

## Handling Undefined Variables

PHP-Jinja provides three strategies for handling undefined variables:

```php
<?php
use ByJG\JinjaPhp\Template;
use ByJG\JinjaPhp\Undefined\StrictUndefined;
use ByJG\JinjaPhp\Undefined\DebugUndefined;
use ByJG\JinjaPhp\Undefined\DefaultUndefined;

// Option 1: StrictUndefined (default) - throws an exception for undefined variables
$template1 = new Template('Hello {{ name }}!');
// StrictUndefined is the default, but you can explicitly set it:
$template1->withUndefined(new StrictUndefined());
// This will throw a TemplateParseException if 'name' doesn't exist

// Option 2: DebugUndefined - shows a placeholder without throwing exceptions
$template2 = new Template('Hello {{ name }}!');
$template2->withUndefined(new DebugUndefined());
echo $template2->render([]); // Outputs: Hello {{ NOT_FOUND: name }}!

// Option 3: DefaultUndefined - uses a default value without throwing exceptions
$template3 = new Template('Hello {{ name }}!');
$template3->withUndefined(new DefaultUndefined('Guest'));
echo $template3->render([]); // Outputs: Hello Guest!
```

## Using the Default Filter

You can use the `default` filter to provide fallback values for specific variables:

```php
<?php
use ByJG\JinjaPhp\Template;

// This works even with StrictUndefined (the default)
$template = new Template('Hello {{ name | default("Guest") }}!');
echo $template->render([]); // Outputs: Hello Guest!
```

## Composing Templates

While template inheritance is not supported, you can compose templates by rendering them separately:

```php
<?php
use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\JinjaPhp\Template;

$loader = new FileSystemLoader('/path/to/templates');

// Render separate template parts
$headerTemplate = new Template($loader->getSource('header.html'));
$header = $headerTemplate->render(['title' => 'Page Title']);

$bodyTemplate = new Template($loader->getSource('content.html'));
$body = $bodyTemplate->render(['content' => 'Main content']);

$footerTemplate = new Template($loader->getSource('footer.html'));
$footer = $footerTemplate->render(['year' => date('Y')]);

// Combine the parts
echo $header . $body . $footer;
```

## Reusing Variables

You can create a base set of variables to reuse across templates:

```php
<?php
// Define base variables
$baseVariables = [
    'site_name' => 'My Website',
    'current_year' => date('Y'),
    'user' => ['name' => 'John', 'is_admin' => true]
];

// Create templates
$template1 = new Template($loader->getSource('template1.html'));
$template2 = new Template($loader->getSource('template2.html'));

// Render with combined variables
echo $template1->render($baseVariables + ['page' => 'Home']);
echo $template2->render($baseVariables + ['page' => 'About']);
```

## Security Considerations

### Template Loading

Be careful when loading templates based on user input:

```php
<?php
// INSECURE - could allow directory traversal
$templateName = $_GET['template'];
$template = $loader->getSource($templateName);

// SECURE - use a whitelist approach
$allowedTemplates = ['home', 'about', 'contact'];
if (in_array($_GET['template'], $allowedTemplates)) {
    $template = $loader->getSource($_GET['template'] . '.html');
}
```

### Variable Escaping

PHP-Jinja does not automatically escape variables, so you need to manually escape user-generated content:

```php
<?php
// Sanitize user input before passing to templates
$userComment = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
$variables['comment'] = $userComment;

// Then in template: {{ comment }}
```

## Creating a Custom Undefined Handler

You can create your own undefined handler by implementing the `UndefinedInterface`:

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
echo $template->render([]); // Outputs: Hello [Missing: name]!
``` 