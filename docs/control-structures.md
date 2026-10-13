---
sidebar_position: 6
---

# Control Structures

PHP-Jinja offers conditionals and loops for controlling template flow. This document provides in-depth information and examples.

## Conditionals (If Statements)

### Basic If-Else Usage

```jinja
{% if user.isLoggedIn %}
    <nav class="user-nav">
        Welcome, {{ user.name }}!
        <a href="/logout">Logout</a>
    </nav>
{% else %}
    <nav class="guest-nav">
        <a href="/login">Login</a>
        <a href="/register">Register</a>
    </nav>
{% endif %}
```

### Complex Conditions

You can create complex conditions using various operators:

```jinja
{% if user.age >= 18 && (user.hasSubscription || user.isAdmin) %}
    <!-- Content for adult subscribers or admins -->
{% endif %}
```

### Whitespace Control

Control whitespace around if statements with the minus sign:

```jinja
<div>
    {%- if showHeader -%}
        <header>Compact header with no whitespace around it</header>
    {% endif %}
</div>
```

`{%-` removes the whitespace at the beginning of the content of the block and `-%}` removes it at
the end. Put the minus sign on the opening tag only; on `{% endif %}`, `{% else %}` or `{% elif %}`
it is an error.

### Nested Conditionals

An `if` can be nested in another, as long as the inner one has no `else` or `elif`:

```jinja
{% if user %}
    {% if user.isAdmin %}
        <span class="badge admin">Admin: {{ user.name }}</span>
    {% endif %}
    {% if user.isModerator %}
        <span class="badge mod">Moderator: {{ user.name }}</span>
    {% endif %}
{% else %}
    <span class="badge guest">Guest</span>
{% endif %}
```

:::caution
`{% else %}` and `{% elif %}` are only understood in an `if` that is not inside another block.
In a nested `if`, or in an `if` inside a `for`, an `{% else %}` breaks the template. Write the
alternatives as separate `if` blocks with opposite conditions, as above.
:::

## Loops (For Statements)

### Basic Iteration

```jinja
<ul class="item-list">
    {% for item in items %}
        <li>{{ item }}</li>
    {% endfor %}
</ul>
```

### Accessing the Loop Object

The `loop` variable provides metadata about the current iteration:

```jinja
<table class="data-table">
    {% for user in users %}
        <tr class="{% if loop.even %}even{% endif %}{% if loop.odd %}odd{% endif %}">
            <td>{{ loop.index }}</td>
            <td>{{ user.name }}</td>
            {% if loop.first %}
                <td><span class="badge">New</span></td>
            {% endif %}
            {% if loop.last %}
                <td><span class="badge">Last</span></td>
            {% endif %}
        </tr>
    {% endfor %}
</table>
```

Available loop properties:
- `loop.first`: True for first iteration
- `loop.last`: True for last iteration
- `loop.index`: 1-based counter (starts at 1)
- `loop.index0`: 0-based counter (starts at 0)
- `loop.length`: Total number of items
- `loop.revindex`: Reverse index (starts at loop.length)
- `loop.revindex0`: Reverse index (starts at loop.length - 1)
- `loop.even`: True on the first, third, fifth… iteration (`loop.index0` is even)
- `loop.odd`: True on the second, fourth… iteration

There is no ternary operator (`a ? b : c`); use an `if` block.
 
### Conditional Display with Empty Collections

```jinja
{% if items %}
    <ul>
    {% for item in items %}
        <li>{{ item }}</li>
    {% endfor %}
    </ul>
{% else %}
    <p>No items found.</p>
{% endif %}
```

## Practical Examples

### Menu Generation

```jinja
<nav class="main-nav">
    {% for item in menu %}
        <a href="{{ item.url }}" 
           class="nav-link {% if item.url == currentUrl %}active{% endif %}">
            {{ item.title }}
        </a>
    {% endfor %}
</nav>
```

### Data Table with Conditional Formatting

Inside a loop, each alternative is its own `if`, because `else` is not available there:

```jinja
<table>
    <thead>
        <tr>
            <th>Name</th>
            <th>Score</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        {% for student in students %}
            <tr>
                <td>{{ student.name }}</td>
                <td>{{ student.score }}</td>
                <td>
                    {% if student.score >= 90 %}
                        <span class="status excellent">Excellent</span>
                    {% endif %}
                    {% if student.score >= 60 && student.score < 90 %}
                        <span class="status pass">Pass</span>
                    {% endif %}
                    {% if student.score < 60 %}
                        <span class="status fail">Fail</span>
                    {% endif %}
                </td>
            </tr>
        {% endfor %}
    </tbody>
</table>
``` 