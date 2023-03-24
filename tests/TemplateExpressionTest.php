<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class TemplateExpressionTest extends TestCase
{
    public function testMathOperations()
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

    public function testConcatenation()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'a' ~ 'b' }}");
        $this->assertEquals("ab", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'a' ~ 'b' ~ var1 }}");
        $this->assertEquals("abc", $template->render(['var1' => 'c']));

        $template = new \ByJG\JinjaPhp\Template("{{ 'a' ~ 'b' ~ var1.key1 }}");
        $this->assertEquals("abc", $template->render(['var1' => ['key1' => 'c']]));
    }

    public function testLiteral()
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
        $this->assertEquals("1", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ false }}");
        $this->assertEquals("", $template->render());
    }

    public function testIn()
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

}