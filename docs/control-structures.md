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
    {%- endif -%}
</div>
```

Without `-` characters, extra whitespace could appear in the output.

### Nested Conditionals

```jinja
{% if user %}
    {% if user.isAdmin %}
        <span class="badge admin">Admin: {{ user.name }}</span>
    {% elif user.isModerator %}
        <span class="badge mod">Moderator: {{ user.name }}</span>
    {% else %}
        <span class="badge user">User: {{ user.name }}</span>
    {% endif %}
{% else %}
    <span class="badge guest">Guest</span>
{% endif %}
```

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
        <tr class="{{ loop.index0 % 2 == 0 ? 'even' : 'odd' }}">
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
- `loop.index`: 1-based counter (starts at 1)
- `loop.index0`: 0-based counter (starts at 0)
- `loop.first`: True for first iteration
- `loop.last`: True for last iteration 
- `loop.length`: Total number of items

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
                    {% elif student.score >= 75 %}
                        <span class="status good">Good</span>
                    {% elif student.score >= 60 %}
                        <span class="status pass">Pass</span>
                    {% else %}
                        <span class="status fail">Fail</span>
                    {% endif %}
                </td>
            </tr>
        {% endfor %}
    </tbody>
</table>
``` 