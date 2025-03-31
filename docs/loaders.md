---
sidebar_position: 3
---

# Template Loaders

PHP-Jinja provides flexible ways to load templates from different sources.

## Built-in Loaders

PHP-Jinja comes with two primary methods for loading templates:

### 1. Direct String Usage

The simplest approach is to use template strings directly:

```php
<?php
use ByJG\JinjaPhp\Template;

// Directly use a string as a template
$template = new Template('Hello {{ name }}!');
$result = $template->render(['name' => 'World']);
```

### 2. FileSystemLoader

For loading templates from files:

```php
<?php
use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\JinjaPhp\Template;

// Create a loader with the templates directory
$loader = new FileSystemLoader('/path/to/templates');

// Load a specific template
$templateContent = $loader->getSource('welcome.html');
$template = new Template($templateContent);
$result = $template->render(['name' => 'World']);
```

## Working with Template Files

### Organizing Templates

A typical template directory structure might look like:

```
/templates
  /pages
    home.html
    about.html
  /partials
    header.html
    footer.html
    sidebar.html
  /emails
    welcome.html
    password-reset.html
```

### Loading and Rendering Multiple Templates

Since PHP-Jinja doesn't support template inheritance, you can combine templates manually:

```php
<?php
use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\JinjaPhp\Template;

$loader = new FileSystemLoader('/path/to/templates');

// Load and render partials
$headerTemplate = new Template($loader->getSource('partials/header.html'));
$contentTemplate = new Template($loader->getSource('pages/home.html'));
$footerTemplate = new Template($loader->getSource('partials/footer.html'));

// Define variables
$commonVars = [
    'site_title' => 'My Website',
    'current_year' => date('Y')
];

// Render each part
$header = $headerTemplate->render($commonVars + ['page' => 'Home']);
$content = $contentTemplate->render($commonVars + [
    'welcome_message' => 'Welcome to our site!',
    'featured_items' => ['Item 1', 'Item 2', 'Item 3']
]);
$footer = $footerTemplate->render($commonVars);

// Combine the output
$fullPage = $header . $content . $footer;
echo $fullPage;
```

## Creating a Custom Loader

You can create custom loaders by implementing the `LoaderInterface`:

```php
<?php
use ByJG\JinjaPhp\Loader\LoaderInterface;
use ByJG\JinjaPhp\Template;

class DatabaseLoader implements LoaderInterface
{
    private $db;
    
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    public function getSource(string $name): string
    {
        // Fetch template from database
        $stmt = $this->db->prepare('SELECT content FROM templates WHERE name = ?');
        $stmt->execute([$name]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$result) {
            throw new \Exception("Template '$name' not found in database");
        }
        
        return $result['content'];
    }
}

// Usage
$dbLoader = new DatabaseLoader($pdo);
$templateContent = $dbLoader->getSource('welcome_email');
$template = new Template($templateContent);
```

## Security Considerations

When loading templates from user input or external sources:

1. **Validate template names**: Use a whitelist approach
   ```php
   $allowedTemplates = ['home', 'about', 'contact'];
   if (in_array($requestedTemplate, $allowedTemplates)) {
       $template = $loader->getSource($requestedTemplate . '.html');
   } else {
       throw new \Exception('Invalid template requested');
   }
   ```

2. **Prevent path traversal**: Sanitize file paths
   ```php
   // Don't use this directly!
   $loader->getSource($_GET['template']); // VULNERABLE!
   
   // Instead, validate and sanitize
   $templateName = basename($_GET['template']); // Remove directory paths
   if (preg_match('/^[a-zA-Z0-9_-]+\.html$/', $templateName)) {
       $template = $loader->getSource($templateName);
   }
   ``` 