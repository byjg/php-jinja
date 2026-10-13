<?php

namespace Tests;

use PHPUnit\Framework\TestCase;

class TemplateExpressionTest extends TestCase
{
    public function testMathOperations(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 1 + 1 }}");
        $this->assertEquals("2", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 1 - 1 }}");
        $this->assertEquals("0", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 3 * 2 }}");
        $this->assertEquals("6", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 6 / 2 }}");
        $this->assertEquals("3", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 7 % 2 }}");
        $this->assertEquals("1", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 2 ** 3 }}");
        $this->assertEquals("8", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ var1 + 1 }}");
        $this->assertEquals("5", $template->render(['var1' => 4]));

        $template = new \ByJG\JinjaPhp\Template("{{ (2 + 3) ** 2 }}");
        $this->assertEquals("25", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ a + b | join(', ') }}");
        $this->assertEquals("1, 2, 3, 4, 5, 6", $template->render(["a" => [1, 2, 3], "b" => [4, 5, 6]]));
    }

    public function testConcatenation(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'a' ~ 'b' }}");
        $this->assertEquals("ab", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'a' ~ 'b' ~ var1 }}");
        $this->assertEquals("abc", $template->render(['var1' => 'c']));

        $template = new \ByJG\JinjaPhp\Template("{{ 'a' ~ 'b' ~ var1.key1 }}");
        $this->assertEquals("abc", $template->render(['var1' => ['key1' => 'c']]));
    }

    public function testLiteral(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'a' }}");
        $this->assertEquals("a", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ \"a\" }}");
        $this->assertEquals("a", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 1 }}");
        $this->assertEquals("1", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 1.1 }}");
        $this->assertEquals("1.1", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ true }}");
        $this->assertEquals("true", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ false }}");
        $this->assertEquals("false", $template->render());
    }

    public function testIn(): void
    {
        $template = new \ByJG\JinjaPhp\Template("{% if 'a' in ['a', 'b'] %}true{%else%}false{%endif%}");
        $this->assertEquals("true", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if 'c' in ['a', 'b'] %}true{%else%}false{%endif%}");
        $this->assertEquals("false", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if 'test' in 'mytest1' %}true{%else%}false{%endif%}");
        $this->assertEquals("true", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if 'test' in 'none' %}true{%else%}false{%endif%}");
        $this->assertEquals("false", $template->render());

    // TODO: Fix this test
    //     $template = new \ByJG\JinjaPhp\Template("{% if a + b in c %}true{%else%}false{%endif%}");
    //     $this->assertEquals("true", $template->render(["a" => [1], "b" => [2], "c" => [1, 2, 3, 4, 5, 6]]));

    //     $template = new \ByJG\JinjaPhp\Template("{% if a + c in b %}true{%else%}false{%endif%}");
    //     $this->assertEquals("false", $template->render(["a" => [1], "b" => [2], "c" => [1, 2, 3, 4, 5, 6]]));
    }

    /**
     * A boolean used to be cast to a string, which printed "1" for true and nothing at all for
     * false - so a false comparison simply vanished from the output.
     */
    public function testBooleansArePrintedAsWords(): void
    {
        $this->assertEquals("true false", (new \ByJG\JinjaPhp\Template("{{ 1 == 1 }} {{ 1 == 2 }}"))->render());
        $this->assertEquals("true/false", (new \ByJG\JinjaPhp\Template("{{ a }}/{{ b }}"))->render(['a' => true, 'b' => false]));
        $this->assertEquals(
            "true,false;false,true;",
            (new \ByJG\JinjaPhp\Template("{% for i in items %}{{ loop.first }},{{ loop.last }};{% endfor %}"))->render(['items' => [1, 2]])
        );

        // Printing them does not change what they mean in a condition
        $this->assertEquals("no", (new \ByJG\JinjaPhp\Template("{% if b %}yes{% else %}no{% endif %}"))->render(['b' => false]));
    }

    /**
     * An array used to raise PHP's "Array to string conversion" warning and print "Array".
     */
    public function testArraysArePrintedAsJson(): void
    {
        $this->assertEquals('[1,2,3]', (new \ByJG\JinjaPhp\Template("{{ [1, 2, 3] }}"))->render());
        $this->assertEquals('{"a":1,"b":2}', (new \ByJG\JinjaPhp\Template("{{ ['a': 1, 'b': 2] }}"))->render());
        $this->assertEquals('["a","b"]', (new \ByJG\JinjaPhp\Template('{{ "a-b" | split("-") }}'))->render());
        $this->assertEquals(
            'Items: ["maçã","a/b"]',
            (new \ByJG\JinjaPhp\Template("Items: {{ items }}"))->render(['items' => ['maçã', 'a/b']])
        );
    }

    public function testComments(): void
    {
        $this->assertEquals("ab", (new \ByJG\JinjaPhp\Template("a{# hidden #}b"))->render());
        $this->assertEquals("ab", (new \ByJG\JinjaPhp\Template("a{#\n spans\n lines\n#}b"))->render());
        $this->assertEquals("a-b", (new \ByJG\JinjaPhp\Template("a{# one #}-{# two #}b"))->render());

        // Whatever is inside a comment is not template code
        $this->assertEquals("ab", (new \ByJG\JinjaPhp\Template("a{# {{ missing }} {% if x %} #}b"))->render());

        $this->assertEquals(
            "1;2;",
            (new \ByJG\JinjaPhp\Template("{% for i in items %}{# the item #}{{ i }};{% endfor %}"))->render(['items' => [1, 2]])
        );
    }
}
