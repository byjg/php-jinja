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

//     public function testForElse()
//     {
//         $templateString = <<<MSG_EOF
// ===
// {% for item in array %}
// {{ item }}
// {% else %}
// No items
// {% endfor %}
// ---
// MSG_EOF;

//         $expected = <<<MSG_EOF
// ===
// val1
// val2
// ---
// MSG_EOF;

//         $template = new \ByJG\JinjaPhp\Template($templateString);
//         $this->assertEquals($expected, $template->render(['array' => ['val1', 'val2']]));

//         $expected = <<<MSG_EOF
// ===
// No items
// ---
// MSG_EOF;

//         $this->assertEquals($expected, $template->render(['array' => []]));
//     }
}