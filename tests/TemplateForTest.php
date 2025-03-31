<?php

namespace Tests;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use PHPUnit\Framework\TestCase;

class TemplateForTest extends TestCase
{
    public function testFor(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array %}{{ xyz }}{% endfor %}");
        $this->assertEquals("val1val2", $template->render(['array' => ['val1', 'val2']]));

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in [1, 2, 3] %}{{ xyz }}{% endfor %}");
        $this->assertEquals("123", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in var1 | split(',') %}{{ xyz }}{% endfor %}");
        $this->assertEquals("123", $template->render(['var1' => '1,2,3']));

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array.nested %}{{ xyz }}{% endfor %}");
        $this->assertEquals("val1val2", $template->render(['array' => ['nested' => ['val1', 'val2']]]));

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array.nested %}{% if xyz == 'val1' %}@{% endif %}{{ xyz }}{% endfor %}");
        $this->assertEquals("@val1val2", $template->render(['array' => ['nested' => ['val1', 'val2']]]));
    }

    public function testForDict(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{% for key, value in array %}{{ key }}:{{ value }} {% endfor %}");
        $this->assertEquals("key1:val1 key2:val2 ", $template->render(['array' => ['key1' => 'val1', 'key2' => 'val2']]));

        $template = new \ByJG\JinjaPhp\Template("{% for key, value in array.nested %}{{ key }}:{{ value }} {% endfor %}");
        $this->assertEquals("key1:val1 key2:val2 ", $template->render(['array' => ['nested' => ['key1' => 'val1', 'key2' => 'val2']]]));

        $template = new \ByJG\JinjaPhp\Template("{% for key, value in ['a': 1, 'b': 2, 'c': 3] %}{{ key }}:{{ value }} {% endfor %}");
        $this->assertEquals("a:1 b:2 c:3 ", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% for key, value in ['a', 'b', 'c'] %}Item {{ loop.index }} of {{ loop.length }}:{{ value }} {% endfor %}");
        $this->assertEquals("Item 1 of 3:a Item 2 of 3:b Item 3 of 3:c ", $template->render());
    }

    public function testNestedFor(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array %}{{ xyz }}{% for item in array2 %}{{ item }}{% endfor %}{% endfor %}");
        $this->assertEquals("val1val3val4val2val3val4", $template->render(['array' => ['val1', 'val2'], 'array2' => ['val3', 'val4']]));

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array %}{% for item in array3 %}{{ item }}{% endfor %}{{ xyz }}{% for item in array2 %}{{ item }}{% endfor %}{% endfor %}");
        $this->assertEquals("val5val6val1val3val4val5val6val2val3val4", $template->render(['array' => ['val1', 'val2'], 'array2' => ['val3', 'val4'], 'array3' => ['val5', 'val6']]));

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array %}{{ xyz }}{% for item in array2 %}{{ item }}{% endfor %}{% endfor %}{% for xyz in array3 %}{{ xyz }}{% for item in array4 %}{{ item }}{% endfor %}{% endfor %}");
        $this->assertEquals("val1val3val4val2val3val4val5val7val8val6val7val8", $template->render(['array' => ['val1', 'val2'], 'array2' => ['val3', 'val4'], 'array3' => ['val5', 'val6'], 'array4' => ['val7', 'val8']]));
    }

    public function testInvalidFor(): void
    {
        $this->expectException(TemplateParseException::class);
        $this->expectExceptionMessage("The number of {% for %}");
        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array %}not closed");
        $template->render(['array' => ['val1', 'val2']]);
    }

    public function testForMultiline(): void
    {
        $templateString = <<<MSG_EOF
===
{% for item in array %}
{{ item }}
{% endfor %}
---
MSG_EOF;

        $expected = <<<MSG_EOF
===

val1

val2

---
MSG_EOF;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render(['array' => ['val1', 'val2']]));
    }

    public function testForMultilineRightSpace(): void
    {
        $templateString = <<<MSG_EOF
===
{% for item in array -%}
{{ item }}
{% endfor %}
---
MSG_EOF;

        $expected = <<<MSG_EOF
===

val1
val2
---
MSG_EOF;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render(['array' => ['val1', 'val2']]));
    }

    public function testForMultilineLeftSpace(): void
    {
        $templateString = <<<MSG_EOF
===
{%- for item in array %}
{{ item }}
{% endfor %}
---
MSG_EOF;

        $expected = <<<MSG_EOF
===
val1
val2

---
MSG_EOF;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render(['array' => ['val1', 'val2']]));
    }

    public function testForMultilineBothSpaces(): void
    {
        $templateString = <<<MSG_EOF
===
{%- for item in array -%}
{{ item }}
{% endfor %}
---
MSG_EOF;

        $expected = <<<MSG_EOF
===
val1val2
---
MSG_EOF;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render(['array' => ['val1', 'val2']]));
    }

    public function testForWithBracketNotation(): void
    {
        // Test for loop with simple bracket notation
        $template = new \ByJG\JinjaPhp\Template("{% for item in items[0] %}{{ item }}{% endfor %}");
        $this->assertEquals("123", $template->render(['items' => [['1', '2', '3'], ['4', '5', '6']]]));
        
        // Test for loop with string key in bracket notation
        $template = new \ByJG\JinjaPhp\Template("{% for item in data['items'] %}{{ item }}{% endfor %}");
        $this->assertEquals("abc", $template->render(['data' => ['items' => ['a', 'b', 'c']]]));
        
        // Test for loop with multiple bracket notation
        $template = new \ByJG\JinjaPhp\Template("{% for user in users['active'][0]['groups'] %}{{ user }}{% endfor %}");
        $this->assertEquals("admineditor", $template->render([
            'users' => [
                'active' => [
                    ['groups' => ['admin', 'editor']], 
                    ['groups' => ['user']]
                ]
            ]
        ]));
        
        // Test for loop with mixed dot and bracket notation
        $template = new \ByJG\JinjaPhp\Template("{% for role in user.roles['primary'] %}{{ role }}{% endfor %}");
        $this->assertEquals("admineditor", $template->render([
            'user' => [
                'roles' => [
                    'primary' => ['admin', 'editor'],
                    'secondary' => ['viewer']
                ]
            ]
        ]));
    }
    
    public function testForDictWithBracketNotation(): void
    {
        // Test for loop with key/value and bracket notation
        $template = new \ByJG\JinjaPhp\Template("{% for key, value in data['users'] %}{{ key }}:{{ value }} {% endfor %}");
        $this->assertEquals("0:John 1:Jane ", $template->render([
            'data' => [
                'users' => ['John', 'Jane']
            ]
        ]));
        
        // Test for loop with key/value and multiple bracket notation
        $template = new \ByJG\JinjaPhp\Template("{% for key, value in config['site']['pages'] %}{{ key }}:{{ value }} {% endfor %}");
        $this->assertEquals("home:Home about:About ", $template->render([
            'config' => [
                'site' => [
                    'pages' => [
                        'home' => 'Home',
                        'about' => 'About'
                    ]
                ]
            ]
        ]));
        
        // Test accessing loop variable with bracket notation
        $template = new \ByJG\JinjaPhp\Template("{% for item in items %}{{ loop['index'] }}:{{ item }} {% endfor %}");
        $this->assertEquals("1:a 2:b 3:c ", $template->render(['items' => ['a', 'b', 'c']]));
    }

    public function testForElse(): void
    {
        $templateString = <<<MSG_EOF
{% for item in array %}
{{ item }}
{% else %}
No items
{% endfor %}
MSG_EOF;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        
        // Test with items in the array
        $result = $template->render(['array' => ['val1', 'val2']]);
        $this->assertStringContainsString('val1', $result);
        $this->assertStringContainsString('val2', $result);
        $this->assertStringNotContainsString('No items', $result);

        // Test with empty array
        $result = $template->render(['array' => []]);
        $this->assertStringContainsString('No items', $result);
        $this->assertStringNotContainsString('val1', $result);
        $this->assertStringNotContainsString('val2', $result);
    }

    public function testForElseWhitespaceControl(): void
    {
        // Start with the most basic for-else test with whitespace control
        $templateString = "{% for item in array %}{{ item }}{% else %}No items{% endfor %}";

        $template = new \ByJG\JinjaPhp\Template($templateString);
        
        // With items in array
        $result = $template->render(['array' => ['val1', 'val2']]);
        $this->assertEquals("val1val2", $result);
        
        // With empty array
        $result = $template->render(['array' => []]);
        $this->assertEquals("No items", $result);
        
        // Test with right whitespace control
        $templateString = "{% for item in array -%}{{ item }}{% else %}No items{% endfor %}";
        
        $template = new \ByJG\JinjaPhp\Template($templateString);
        $result = $template->render(['array' => ['val1', 'val2']]);
        $this->assertEquals("val1val2", $result);
        
        $result = $template->render(['array' => []]);
        $this->assertEquals("No items", $result);
    }

    public function testForElseWithMultipleTemplates(): void
    {
        // Test more complex templates with for-else
        $templates = [
            // 1. Basic template
            [
                'template' => "{% for item in array %}Item: {{ item }}\n{% else %}No items{% endfor %}",
                'with_items' => "Item: val1\nItem: val2\n",
                'without_items' => "No items",
            ],
            
            // 2. Template with newlines
            [
                'template' => "Before\n{% for item in array %}\n{{ item }}\n{% else %}\nEmpty\n{% endfor %}\nAfter",
                'with_items_contains' => ["Before", "val1", "val2", "After"],
                'without_items_contains' => ["Before", "Empty", "After"],
            ],
        ];
        
        foreach ($templates as $index => $test) {
            $template = new \ByJG\JinjaPhp\Template($test['template']);
            
            // Test with items
            $result = $template->render(['array' => ['val1', 'val2']]);
            if (isset($test['with_items'])) {
                $this->assertEquals($test['with_items'], $result, "Template $index failed with items");
            }
            if (isset($test['with_items_contains'])) {
                foreach ($test['with_items_contains'] as $text) {
                    $this->assertStringContainsString($text, $result, "Template $index failed with items (missing '$text')");
                }
            }
            
            // Test without items
            $result = $template->render(['array' => []]);
            if (isset($test['without_items'])) {
                $this->assertEquals($test['without_items'], $result, "Template $index failed without items");
            }
            if (isset($test['without_items_contains'])) {
                foreach ($test['without_items_contains'] as $text) {
                    $this->assertStringContainsString($text, $result, "Template $index failed without items (missing '$text')");
                }
            }
        }
    }

    public function testNestedForElse(): void
    {
        // First, test a working nested for loop without else to see how variables are processed
        $templateString = "{% for item in items %}{% for subitem in item.subitems %}{{ subitem }}{% endfor %}{% endfor %}";

        $template = new \ByJG\JinjaPhp\Template($templateString);
        
        $result = $template->render([
            'items' => [
                ['subitems' => ['a1', 'a2']],
                ['subitems' => ['b1']]
            ]
        ]);
        $this->assertEquals("a1a2b1", $result);

        // Now, test with a simple for-else loop without nesting
        $templateString = "{% for item in items %}{{ item }}{% else %}no items{% endfor %}";
        
        $template = new \ByJG\JinjaPhp\Template($templateString);
        $result = $template->render(['items' => ['a', 'b']]);
        $this->assertEquals("ab", $result);
        
        $result = $template->render(['items' => []]);
        $this->assertEquals("no items", $result);
        
        // Test with nested for loops with else on the outer loop
        $templateString = "{% for item in items %}{% for subitem in item.subitems %}{{ subitem }}{% endfor %}{% else %}no items{% endfor %}";
        
        $template = new \ByJG\JinjaPhp\Template($templateString);
        $result = $template->render([
            'items' => [
                ['subitems' => ['a1', 'a2']],
                ['subitems' => ['b1']]
            ]
        ]);
        $this->assertEquals("a1a2b1", $result);
        
        $result = $template->render(['items' => []]);
        $this->assertEquals("no items", $result);
        
        // Note: This implementation currently doesn't fully support else clauses in inner for loops
        // This feature would require significant changes to the template engine's design
    }
    
    /* Uncomment this test after the basic nested for-else is working */
    public function testComplexNestedForElse(): void 
    {
        // Test with complex nested for-else structure, but only with else on the outer loop
        $templateString = <<<'MSG_EOF'
{% for category in categories %}
  <h2>{{ category.name }}</h2>
  {% for product in category.products %}
    <div>{{ product.name }}: ${{ product.price }}</div>
  {% endfor %}
{% else %}
  <p>No categories available</p>
{% endfor %}
MSG_EOF;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        
        // Case 1: Categories with products
        $result = $template->render([
            'categories' => [
                ['name' => 'Electronics', 'products' => [
                    ['name' => 'Laptop', 'price' => 999],
                    ['name' => 'Phone', 'price' => 699]
                ]],
                ['name' => 'Books', 'products' => [
                    ['name' => 'PHP Guide', 'price' => 29]
                ]]
            ]
        ]);
        $this->assertStringContainsString('<h2>Electronics</h2>', $result);
        $this->assertStringContainsString('<div>Laptop: $999</div>', $result);
        $this->assertStringContainsString('<div>Phone: $699</div>', $result);
        $this->assertStringContainsString('<h2>Books</h2>', $result);
        $this->assertStringContainsString('<div>PHP Guide: $29</div>', $result);
        $this->assertStringNotContainsString('No categories', $result);
        
        // Case 2: No categories
        $result = $template->render(['categories' => []]);
        $this->assertStringContainsString('<p>No categories available</p>', $result);
        $this->assertStringNotContainsString('<h2>', $result);
    }
}