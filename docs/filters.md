# Filters

Filters in PHP-Jinja allow you to modify variables before they are rendered in templates.

## Using Filters

Filters are applied to variables using the pipe (|) symbol:

```jinja
{{ variable | filter }}
```

You can chain multiple filters, which are applied from left to right:

```jinja
{{ variable | filter1 | filter2 | filter3 }}
```

Some filters accept arguments:

```jinja
{{ variable | filter(arg1, arg2) }}
```

## Available Filters

PHP-Jinja implements the following filters:

| Filter       | Description                                 | Example                                         | Result              |
|--------------|---------------------------------------------|-------------------------------------------------|---------------------|
| `upper`      | Convert to uppercase                        | `{{ "hello" \| upper }}`                        | `HELLO`             |
| `lower`      | Convert to lowercase                        | `{{ "HELLO" \| lower }}`                        | `hello`             |
| `capitalize` | Capitalize first letter of each word        | `{{ "hello world" \| capitalize }}`             | `Hello World`       |
| `trim`       | Remove whitespace from start/end            | `{{ " hello " \| trim }}`                       | `hello`             |
| `replace`    | Replace occurrences of a substring          | `{{ "hello" \| replace("h", "j") }}`            | `jello`             |
| `length`     | Get string or array length                  | `{{ "hello" \| length }}`                       | `5`                 |
| `default`    | Default value for undefined variables       | `{{ undefined \| default("Guest") }}`           | `Guest`             |
| `join`       | Join array elements with a delimiter        | `{{ ["a", "b"] \| join("-") }}`                 | `a-b`               |
| `split`      | Split a string into an array                | `{{ "a-b" \| split("-") }}`                     | `["a", "b"]`        |

## Examples

### String Manipulation

```jinja
{{ "hello world" | capitalize | replace("world", "everyone") }}
```
Output: `Hello everyone`

### Arrays

```jinja
{{ ["apple", "banana", "orange"] | join(", ") | upper }}
```
Output: `APPLE, BANANA, ORANGE`

### Default Values

The `default` filter is especially useful for handling undefined variables:

```jinja
{{ username | default("Guest") }}
```

This displays the value of `username` if it exists, or "Guest" if it doesn't, without throwing an error.

## Security Note

PHP-Jinja does not automatically escape HTML output. When displaying user-generated content, you should manually escape it to prevent XSS vulnerabilities:

```php
$variables['userContent'] = htmlspecialchars($userInput, ENT_QUOTES, 'UTF-8');
```

Then in your template:
```jinja
{{ userContent }}
``` 