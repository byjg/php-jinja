<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class TemplateIfTest extends TestCase
{
    /**
     * @return array
     */
    public static function ifConditionsProvider(): array
    {
        return [
            'simple true condition' => [
                "{% if true %}true{% endif %}", 
                [], 
                "true"
            ],
            'simple false condition' => [
                "{% if false %}true{% endif %}", 
                [], 
                ""
            ],
            'comparison less than (false)' => [
                "{% if 10 < 4 %}true{% endif %}", 
                [], 
                ""
            ],
            'comparison greater than (true)' => [
                "{% if 10 > 4 %}true{% endif %}", 
                [], 
                "true"
            ],
            'variable equality with parentheses' => [
                "{% if (var1 == 'test') %}true{% endif %}", 
                ['var1' => 'test'], 
                "true"
            ],
            'variable inequality' => [
                "{% if var1 != 'test' %}true{% endif %}", 
                ['var1' => 'test'], 
                ""
            ],
            'variable equality' => [
                "{% if var1 == 'test' %}true{% endif %}", 
                ['var1' => 'test'], 
                "true"
            ],
            'if-else with true condition' => [
                "{% if true %}true{% else %}false{% endif %}", 
                [], 
                "true"
            ],
            'if-else with false condition' => [
                "{% if false %}true{% else %}false{% endif %}", 
                [], 
                "false"
            ],
            'if-else with variable comparison (false)' => [
                "{% if var1 == 'test' %}true{%else%}false{% endif %}", 
                ['var1' => 'notest'], 
                "false"
            ],
            'variable rendering inside if block' => [
                "{% if (var1 == 'abc') %}Show result of {{ var2 }}{% endif %}", 
                ['var1' => 'abc', 'var2' => 123], 
                "Show result of 123"
            ],
            'complex AND condition with variable' => [
                "{% if var1 == 'abc' && var2 == 123 %}Show result of {{ var3 }}{% else %}Show nothing{% endif %}", 
                ['var1' => 'abc', 'var2' => 123, 'var3' => 456], 
                "Show result of 456"
            ],
            'complex negation with AND' => [
                "{% if var1 == 'abc' && !(var2 == 123) %}Show result of {{ var3 }}{% else %}Show nothing{% endif %}", 
                ['var1' => 'abc', 'var2' => 123, 'var3' => 456], 
                "Show nothing"
            ],
            'nested array check' => [
                "{% if var1.type == 'test' %}true{%else%}false{% endif %}", 
                ['var1' => ['type' => 'test']], 
                "true"
            ],
            'nested array check with parentheses' => [
                "{% if var1.type == 'test(1)' %}true{%else%}false{% endif %}",
                ['var1' => ['type' => 'test(1)']],
                "true"
            ],
            'nested array check with parentheses (2)' => [
                "{% if foo == '(some(ffff)(aa))' %}true{%else%}false{% endif %}",
                ['foo' => '(some(ffff)(aa))'],
                "true"
            ],
            'nested array check with square brackets' => [
                "{% if var1.type == 'test[1]' %}true{%else%}false{% endif %}",
                ['var1' => ['type' => 'test[1]']],
                "true"
            ],
            'check var with special words' => [
                "{% if foo == 'rock in the water' %}true{%else%}false{% endif %}",
                ['foo' => 'rock in the water'],
                "true"
            ],
            'check in' => [
                "{% if foo in ['rock', 'classic'] %}true{%else%}false{% endif %}",
                ['foo' => 'rock'],
                "true"
            ],
            'check in false' => [
                "{% if foo in ['rock', 'classic'] %}true{%else%}false{% endif %}",
                ['foo' => 'jazz'],
                "false"
            ],
            'check in in' => [
                "{% if foo in ['rock in the water', 'classic'] %}true{%else%}false{% endif %}",
                ['foo' => 'rock in the water'],
                "true"
            ],
            'check in in false' => [
                "{% if foo in ['rock in the water', 'classic'] %}true{%else%}false{% endif %}",
                ['foo' => 'jazz'],
                "false"
            ],
            'check substring in array element' => [
                "{% if 'test' in var1.type %}true{%else%}false{% endif %}", 
                ['var1' => ['type' => 'test(1)']], 
                "true"
            ],
            'elif condition with first test true' => [
                "{% if true %}true{% elif true %}false{% endif %}", 
                [], 
                "true"
            ],
            'elif condition with first test false' => [
                "{% if false %}true{% elif true %}false{% endif %}",
                [], 
                "false"
            ],
            'elif condition with both tests false' => [
                "{% if false %}true{% elif false %}false{% endif %}",
                [], 
                ""
            ],
            'elif with else condition - all false' => [
                "{% if false %}true{% elif false %}false{% else %}else{% endif %}",
                [], 
                "else"
            ],
            'multiple elif conditions with middle true' => [
                "{% if false %}true{% elif false %}false{% elif true %}elif{% endif %}",
                [], 
                "elif"
            ],
            'multiple elif conditions all false' => [
                "{% if false %}true{% elif false %}false{% elif false %}elif{% endif %}",
                [], 
                ""
            ],
            'multiple elif conditions all false with else' => [
                "{% if false %}true{% elif false %}false{% elif false %}elif{% else %}else{% endif %}",
                [], 
                "else"
            ],
            'logical and operator' => [
                "{% if var1 == 'abc' and var2 == 123 %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 123],
                "true"
            ],
            'logical and operator (false)' => [
                "{% if var1 == 'abc' and var2 == 456 %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 123],
                "false"
            ],
            'logical or operator' => [
                "{% if var1 == 'abc' or var2 == 456 %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 123],
                "true"
            ],
            'logical or operator (both false)' => [
                "{% if var1 == 'xyz' or var2 == 456 %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 123],
                "false"
            ],
            'complex expression with and/or' => [
                "{% if (var1 == 'abc' and var2 == 123) or var3 == 'xyz' %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 123, 'var3' => 'abc'],
                "true"
            ],
            'mix and/or with &&/||' => [
                "{% if var1 == 'abc' and var2 == 123 || var3 == 'xyz' %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 456, 'var3' => 'xyz'],
                "true"
            ],
            'precedence test 1' => [
                "{% if var1 == 'abc' and (var2 == 123 || var3 == 'xyz') %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 456, 'var3' => 'xyz'],
                "true"
            ],
            'precedence test 2' => [
                "{% if var1 == 'abc' and var2 == 123 || var3 == 'abc' %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 456, 'var3' => 'abc'],
                "true"
            ],
            'precedence test 3' => [
                "{% if var1 == 'abc' and (var2 == 123 || var3 == 'efg') %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 456, 'var3' => 'efg'],
                "true"
            ],
            'precedence test 4' => [
                "{% if var1 == 'abc' and var2 == 456 and var3 == 'abc' %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 456, 'var3' => 'abc'],
                "true"
            ],
            'precedence test 5' => [
                "{% if var1 == 'abc' and var2 == 456 or var3 == 'xyz' %}true{% else %}false{% endif %}",
                ['var1' => 'abc', 'var2' => 456, 'var3' => 'abc'],
                "true"
            ],
            'precedence test 6' => [
                "{% if var1 == 'abc' and var2 == 456 or var3 == 'xyz' %}true{% else %}false{% endif %}",
                ['var1' => 'xyz', 'var2' => 456, 'var3' => 'abc'],
                "false"
            ],
        ];
    }

    /**
     * @dataProvider ifConditionsProvider
     */
    public function testIf(string $template, array $variables, string $expected): void
    {
        $template = new \ByJG\JinjaPhp\Template($template);
        $this->assertEquals($expected, $template->render($variables));
    }

    public function testMultipleIf(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{% if true %}true1{% endif %}{% if true %}true2{% endif %}");
        $this->assertEquals("true1true2", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if true %}true1{% endif %}{% if false %}true2{% endif %}");
        $this->assertEquals("true1", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if false %}true1{% endif %}{% if true %}true2{% endif %}");
        $this->assertEquals("true2", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if false %}true1{% endif %}{% if false %}true2{% endif %}");
        $this->assertEquals("", $template->render());
    }

    public function testNestedIf(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{% if true %}{% if true %}true{% endif %}{% endif %}");
        $this->assertEquals("true", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if true %}{% if false %}true{% endif %}{% endif %}");
        $this->assertEquals("", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if false %}{% if true %}true{% endif %}{% endif %}");
        $this->assertEquals("", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if false %}{% if false %}true{% endif %}{% endif %}");
        $this->assertEquals("", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if true %}{% if true %}true1{% endif %}{% endif %}{% if true %}here{% if false %}true2{% endif %}{% endif %}");
        $this->assertEquals("true1here", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if true %}{% if true %}true1{% endif %}{% endif %}{% if true %}here{% if false %}true2{% endif %}{% endif %}{% if true %}{% if true %}true1{% endif %}{% endif %}{% if true %}here{% if false %}true2{% endif %}{% endif %}");
        $this->assertEquals("true1heretrue1here", $template->render());
    }

    public function testIfMultipleLines(): void
    {
        $templateString = <<<EOT
=====
{% if true %}
true
{% endif %}
-----
EOT;

$expected = <<<EOT
=====

true

-----
EOT;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render());
    }

    public function testIfMultipleLinesTrimRightSpace(): void
    {
        $templateString = <<<EOT
=====
{% if true -%}
true
{% endif %}
-----
EOT;

$expected = <<<EOT
=====

true
-----
EOT;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render());
    }

    public function testIfMultipleLinesTrimleftSpace(): void
    {
        $templateString = <<<EOT
=====
{%- if true %}
true
{% endif %}
-----
EOT;

$expected = <<<EOT
=====
true

-----
EOT;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render());
    }

    public function testIfMultipleLinesTrimBothSpace(): void
    {
        $templateString = <<<EOT
=====
{%- if true -%}
true
{% endif %}
-----
EOT;

$expected = <<<EOT
=====
true
-----
EOT;

        $template = new \ByJG\JinjaPhp\Template($templateString);
        $this->assertEquals($expected, $template->render());
    }

    public function testIfElif(): void
    {
        $templateContent = file_get_contents(__DIR__ . '/templates/elif-test.html');

        // Test different age groups
        $ages = [
            5 => "Child user",
            15 => "Teen user",
            30 => "Adult user",
            70 => "Senior user",
        ];

        foreach ($ages as $age => $expected) {
            $template = new \ByJG\JinjaPhp\Template($templateContent);
            $result = $template->render(['age' => $age]);

            $this->assertEquals($expected, trim($result));
        }
    }

}