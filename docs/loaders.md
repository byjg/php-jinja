---
sidebar_position: 3
---

# Template Loaders

A loader gives you a `Template` from a name. PHP-Jinja comes with two, and you can write your own.

## Built-in Loaders

### Using a String Directly

The simplest approach needs no loader at all:

```php
<?php
use ByJG\JinjaPhp\Template;

$template = new Template('Hello {{ name }}!');
$result = $template->render(['name' => 'World']);
```

### FileSystemLoader

Loads templates from the files of a directory:

```php
<?php
use ByJG\JinjaPhp\Loader\FileSystemLoader;

// Create a loader with the templates directory
$loader = new FileSystemLoader('/path/to/templates');

// Loads /path/to/templates/welcome.jinja
$template = $loader->getTemplate('welcome');
$result = $template->render(['name' => 'World']);
```

`getTemplate()` returns a `Template`, ready to render.

The loader adds the extension to the name when the name does not end with it. The extension is
`.jinja` unless you pass another one as the second argument:

```php
<?php
use ByJG\JinjaPhp\Loader\FileSystemLoader;

$loader = new FileSystemLoader('/path/to/templates', '.html');

$template = $loader->getTemplate('welcome');       // welcome.html
$template = $loader->getTemplate('welcome.html');  // the same file
$template = $loader->getTemplate('emails/reset');  // emails/reset.html
```

A directory that does not exist or cannot be read, and a template file that does not exist, throw
a `LoaderException`.

### StringLoader

Implements the same interface for a template you already have as a string. It is useful where code
expects a `LoaderInterface`:

```php
<?php
use ByJG\JinjaPhp\Loader\StringLoader;

$loader = new StringLoader();
$template = $loader->getTemplate('Hello {{ name }}!');
echo $template->render(['name' => 'World']); // Hello World!
```

## Working with Template Files

### Organizing Templates

A typical template directory structure might look like:

```
/templates
  /pages
    home.jinja
    about.jinja
  /partials
    header.jinja
    footer.jinja
    sidebar.jinja
  /emails
    welcome.jinja
    password-reset.jinja
```

### Loading and Rendering Multiple Templates

Since PHP-Jinja doesn't support template inheritance, you can combine templates manually:

```php
<?php
use ByJG\JinjaPhp\Loader\FileSystemLoader;

$loader = new FileSystemLoader('/path/to/templates');

// Define variables
$commonVars = [
    'site_title' => 'My Website',
    'current_year' => date('Y')
];

// Render each part
$header = $loader->getTemplate('partials/header')->render($commonVars + ['page' => 'Home']);
$content = $loader->getTemplate('pages/home')->render($commonVars + [
    'welcome_message' => 'Welcome to our site!',
    'featured_items' => ['Item 1', 'Item 2', 'Item 3']
]);
$footer = $loader->getTemplate('partials/footer')->render($commonVars);

// Combine the output
echo $header . $content . $footer;
```

## Creating a Custom Loader

You can create custom loaders by implementing the `LoaderInterface`:

```php
<?php
use ByJG\JinjaPhp\Exception\LoaderException;
use ByJG\JinjaPhp\Loader\LoaderInterface;
use ByJG\JinjaPhp\Template;

class DatabaseLoader implements LoaderInterface
{
    public function __construct(private PDO $db)
    {
    }

    public function getTemplate(string $template): Template
    {
        // Fetch template from database
        $stmt = $this->db->prepare('SELECT content FROM templates WHERE name = ?');
        $stmt->execute([$template]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$result) {
            throw new LoaderException("Template '$template' not found in database");
        }

        return new Template($result['content']);
    }
}

// Usage
$dbLoader = new DatabaseLoader($pdo);
echo $dbLoader->getTemplate('welcome_email')->render(['name' => 'World']);
```

## Security Considerations

When loading templates from user input or external sources:

1. **Validate template names**: Use a whitelist approach
   ```php
   $allowedTemplates = ['home', 'about', 'contact'];
   if (in_array($requestedTemplate, $allowedTemplates)) {
       $template = $loader->getTemplate($requestedTemplate);
   } else {
       throw new \Exception('Invalid template requested');
   }
   ```

2. **Path traversal**: `FileSystemLoader` only loads files that are inside its directory. A name
   with `../` that leaves it is refused with a `LoaderException`, the same as a template that does
   not exist. Any file inside the directory can still be requested, so the whitelist above remains
   the right way to decide which templates a visitor may see. A custom loader has to do its own
   check.
