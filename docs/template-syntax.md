---
sidebar_position: 2
---

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
{{ true }}, {{ false }}       <!-- Booleans: printed as true and false -->
{{ [1, 2, 3] }}               <!-- Array: printed as JSON, [1,2,3] -->
{{ ['a': 1, 'b': 2] }}        <!-- Associative array: printed as JSON, {"a":1,"b":2} -->
```

A boolean is printed as the word `true` or `false`, and an array as JSON, when it is the whole
result of the expression. Inside a concatenation or a string filter a boolean is still `1` or
empty: `{{ true ~ "!" }}` prints `1!`.

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
{% elif condition2 %}
    Content rendered if condition2 is true and previous conditions were false
{% elif condition3 %}
    Content rendered if condition3 is true and previous conditions were false
{% else %}
    Content rendered if condition is false
{% endif %}
```

Whitespace control, with a minus sign on the opening tag:

```jinja
{%- if condition -%}
    Content without whitespace before or after
{% endif %}
```

`{%-` removes the whitespace at the beginning of the block's content and `-%}` removes it at the
end. The minus sign is only understood on the opening `if` and `for` tags: on `{% else %}`,
`{% elif %}`, `{% endif %}` or `{% endfor %}` it is an error.

`{% else %}` and `{% elif %}` belong to the outermost block they are written in. See
[the limitations](advanced-topics.md#current-limitations) before nesting an `if` that has them.

### Loops

```jinja
{% for item in items %}
    {{ item }}
{% endfor %}
```

For loops with an else block (executed when the loop array is empty):

```jinja
{% for item in items %}
    {{ item }}
{% else %}
    No items found.
{% endfor %}
```

The `else` clause in a for loop provides a way to handle empty collections. When the collection being iterated is empty (has no items), the content inside the `else` block is rendered instead of the loop body. This is useful for displaying alternative content or messages when there are no items to process.

Examples:

```jinja
{# Display a list of users or a message if no users exist #}
<ul>
    {% for user in users %}
        <li>{{ user.name }}</li>
    {% else %}
        <li>No users found</li>
    {% endfor %}
</ul>

{# Generate table rows or show "empty" message #}
<table>
    <tr><th>Name</th><th>Email</th></tr>
    {% for contact in contacts %}
        <tr>
            <td>{{ contact.name }}</td>
            <td>{{ contact.email }}</td>
        </tr>
    {% else %}
        <tr><td colspan="2">Address book is empty</td></tr>
    {% endfor %}
</table>

{# Conditionally show content when a list exists and has items #}
{% for notification in notifications %}
    <div class="alert">{{ notification.message }}</div>
{% else %}
    {# Nothing is rendered when notifications is empty #}
{% endfor %}
```

Whitespace control can be used with for-else loops as well, on the opening tag:

```jinja
{% for item in items -%}
    {{ item }}
{% else %}
    No items
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

To get the key as well, name two variables: `{% for key, value in items %}`.

### Nested For Loops

For loops can be nested inside each other:

```jinja
{% for category in categories %}
  <h2>{{ category.name }}</h2>
  {% for product in category.products %}
    <div>{{ product.name }}: ${{ product.price }}</div>
  {% endfor %}
{% endfor %}
```

A for-else block can contain a nested loop:

```jinja
{% for category in categories %}
  <h2>{{ category.name }}</h2>
  {% for product in category.products %}
    <div>{{ product.name }}: ${{ product.price }}</div>
  {% endfor %}
{% else %}
  <p>No categories available</p>
{% endfor %}
```

> **Note**: An `else` clause is only supported on the outermost `for`. On an inner loop it is an error.

## Comments

Add comments that are not rendered in the output:

```jinja
{# This is a comment that won't appear in the output #}

{#
   A comment can span several lines, and whatever is inside it
   is not template code: {{ this_is_not_evaluated }}
#}
```

