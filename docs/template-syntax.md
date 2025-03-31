# Template Syntax

PHP-Jinja uses a syntax similar to the Python Jinja2 template engine. This document covers the core syntax elements.

## Delimiters

PHP-Jinja uses three types of delimiters:

- `{{ ... }}`: Output expressions - prints the result of an expression
- `{% ... %}`: Control structures - for loops, if statements, etc.
- `{# ... #}`: Comments - not rendered in the output

## Variables

Access variables using the double curly braces:

```jinja
{{ name }}                  <!-- Simple variable -->
{{ user.name }}             <!-- Object/array property access using dot notation -->
{{ array.0 }}               <!-- Array index access using dot notation -->
{{ array[0] }}              <!-- Array index access using bracket notation -->
{{ user['name'] }}          <!-- Associative array access using bracket notation -->
{{ nested['key'][0].prop }} <!-- Combined notation is also supported -->
```

## Expressions

### Literals

```jinja
{{ 42 }}                      <!-- Integer -->
{{ 3.14 }}                    <!-- Float -->
{{ "hello" }} or {{ 'hello' }} <!-- Strings -->
{{ true }}, {{ false }}       <!-- Booleans -->
{{ [1, 2, 3] }}               <!-- Array -->
{{ ['a': 1, 'b': 2] }}        <!-- Associative array -->
```

### Operators

#### Mathematical

```jinja
{{ 1 + 2 }}    <!-- Addition: 3 -->
{{ 5 - 2 }}    <!-- Subtraction: 3 -->
{{ 2 * 3 }}    <!-- Multiplication: 6 -->
{{ 6 / 2 }}    <!-- Division: 3 -->
{{ 7 % 3 }}    <!-- Modulo: 1 -->
{{ 2 ** 3 }}   <!-- Exponentiation: 8 -->
```

#### String Concatenation

```jinja
{{ "Hello" ~ " " ~ "World" }}  <!-- "Hello World" -->
```

#### Comparison

```jinja
{{ 1 == 1 }}   <!-- Equal: true -->
{{ 1 != 2 }}   <!-- Not equal: true -->
{{ 1 < 2 }}    <!-- Less than: true -->
{{ 2 > 1 }}    <!-- Greater than: true -->
{{ 1 <= 1 }}   <!-- Less than or equal: true -->
{{ 1 >= 1 }}   <!-- Greater than or equal: true -->
```

#### Logical

```jinja
{{ true && false }}  <!-- Logical AND: false -->
{{ true || false }}  <!-- Logical OR: true -->
{{ !true }}          <!-- Logical NOT: false -->
```

#### Membership Test

```jinja
{{ "a" in "abc" }}      <!-- String contains: true -->
{{ 1 in [1, 2, 3] }}    <!-- Array contains: true -->
```

## Control Structures

### Conditionals

```jinja
{% if condition %}
    Content rendered if condition is true
{% else %}
    Content rendered if condition is false
{% endif %}
```

Whitespace control:

```jinja
{%- if condition -%}
    Content without whitespace before or after
{%- endif -%}
```

### Loops

```jinja
{% for item in items %}
    {{ item }}
{% endfor %}
```

Loop variables:

```jinja
{% for item in items %}
    {{ loop.index }}   <!-- 1-based index -->
    {{ loop.index0 }}  <!-- 0-based index -->
    {{ loop.first }}   <!-- true if first iteration -->
    {{ loop.last }}    <!-- true if last iteration -->
    {{ loop.length }}  <!-- total number of items -->
{% endfor %}
```

## Comments

Add comments that are not rendered in the output:

```jinja
{# This is a comment that won't appear in the output #}
``` 