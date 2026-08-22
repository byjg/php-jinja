# Changelog - Version 6.0

## New Features

### Template Syntax Enhancements
- **Bracket Notation for Variables**: Added support for bracket notation syntax for accessing variables (e.g., `variable['key']`)
- **Logical Operators**: Implemented support for `and` and `or` operators alongside existing `&&` and `||` operators
- **Elif Statement**: Added `elif` support for multi-condition branching in conditional statements

### Loop Improvements
- **Nested For Loops**: Full support for nested `for` loops within templates
- **Else Clause for Loops**: Implemented `else` clause for `for` loops (executed when loop has no iterations)

### Architecture
- **Evaluator System**: Refactored internal architecture to use dedicated Evaluators for better code organization and maintainability
- **Array Evaluator**: Improved array parsing with better handling of invalid array cases

### Development & Quality
- **PHP 8.5 Support**: Added support for PHP 8.5
- **PHPUnit 11.5 Support**: Updated PHPUnit to support versions 10.5+ and 11.5+
- **Psalm Integration**: Added dedicated Psalm workflow with SARIF reporting for improved static analysis
- **Composer Scripts**: Added convenient Composer scripts for `test` and `psalm` commands
- **GitHub Actions**: Migrated CI/CD to GitHub Actions with improved workflow configuration

## Bug Fixes

- Fixed parenthesis handling in expressions
- Fixed `in` operator behavior when used within strings
- Fixed documentation inconsistencies and errors
- Fixed Psalm static analysis issues
- Improved method signatures for better type safety

## Breaking Changes

| Before (5.x) | After (6.x) | Description |
|-------------|------------|-------------|
| PHP >= 7.4 | PHP >= 8.3 < 8.6 | Dropped support for PHP versions below 8.3 |
| `#[\Override]` attribute | `#[Override]` attribute | Changed Override attribute syntax to standard PHP 8.3 format |
| PHPUnit 9.x | PHPUnit 10.5+ or 11.5+ | Upgraded minimum PHPUnit version requirement |
| Psalm 4.x/5.x | Psalm 5.9+ or 6.13+ | Updated Psalm minimum version requirements |
| DataProvider annotations | DataProvider attributes | Migrated from PHPUnit annotations to PHP 8 attributes |

## Path to Upgrade from 5.x to 6.x

### Step 1: Check PHP Version
Ensure your project is running PHP 8.3, 8.4, or 8.5:
```bash
php -v
```

If you're running PHP < 8.3, you'll need to upgrade your PHP version before upgrading to Jinja-PHP 6.0.

### Step 2: Update Composer Dependencies
Update your `composer.json`:
```json
{
  "require": {
    "byjg/jinja-php": "^6.0"
  }
}
```

Then run:
```bash
composer update byjg/jinja-php
```

### Step 3: Update Development Dependencies (if applicable)
If you're using PHPUnit or Psalm in your project, ensure compatibility:
```json
{
  "require-dev": {
    "phpunit/phpunit": "^10.5|^11.5",
    "vimeo/psalm": "^5.9|^6.13"
  }
}
```

### Step 4: Update Override Attributes (if extending library classes)
If you've extended any Jinja-PHP classes and used the `#[\Override]` attribute, update to the standard syntax:

**Before (5.x):**
```php
#[\Override]
public function someMethod() { }
```

**After (6.x):**
```php
#[Override]
public function someMethod() { }
```

### Step 5: Update Tests (if using PHPUnit DataProvider)
If you have tests that extend or interact with Jinja-PHP tests, migrate from annotations to attributes:

**Before (5.x):**
```php
/**
 * @dataProvider myDataProvider
 */
public function testSomething($data) { }
```

**After (6.x):**
```php
#[\PHPUnit\Framework\Attributes\DataProvider('myDataProvider')]
public function testSomething($data) { }
```

### Step 6: Test Your Templates
The template syntax has been enhanced but remains backward compatible. However, it's recommended to:
1. Run your existing test suite
2. Test any templates that use:
   - Complex conditional logic
   - Nested loops
   - Array access patterns

### Step 7: Optional - Leverage New Features
Consider updating your templates to use new features:
- Use `and`/`or` for more readable logical expressions
- Use `elif` for cleaner multi-condition branching
- Use bracket notation for array/object access if preferred
- Add `else` clauses to `for` loops where appropriate

### Compatibility Notes
- All existing 5.x templates should work without modification in 6.x
- The internal evaluator refactoring is transparent to end users
- New features are additive and don't break existing functionality

### Getting Help
If you encounter any issues during the upgrade:
1. Check the [documentation](docs/)
2. Review the [comparison with Python Jinja2](docs/comparison.md)
3. Report issues on [GitHub](https://github.com/byjg/php-jinja/issues)
