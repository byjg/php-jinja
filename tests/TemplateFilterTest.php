<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class TemplateFilterTest extends TestCase
{
    public function testUpper()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'a' | upper }}");
        $this->assertEquals("A", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | upper }}");
        $this->assertEquals("A", $template->render(['var1' => 'a']));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested | upper }}");
        $this->assertEquals("A", $template->render(['var1' => ['nested' => 'a']]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested2 | default('a') | upper }}");
        $this->assertEquals("A", $template->render(['var1' => ['nested' => 'a']]));
    }

    public function testLower()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'A' | lower }}");
        $this->assertEquals("a", $template->render());
    }

    public function testJoinFilter()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ [1, 2, 3] | join }}");
        $this->assertEquals("123", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | join }}");
        $this->assertEquals("123", $template->render(['var1' => [1, 2, 3]]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested | join }}");
        $this->assertEquals("123", $template->render(['var1' => ['nested' => [1, 2, 3]]]));
    }

    public function testJoinFilterWithArgument()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ [1, 2, 3] | join(', ') }}");
        $this->assertEquals("1, 2, 3", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ [1, 2*5, var1 ] | join(', ') }}");
        $this->assertEquals("1, 10, 3", $template->render(['var1' => 3]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | join(', ') }}");
        $this->assertEquals("1, 2, 3", $template->render(['var1' => [1, 2, 3]]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | join(')(') }}");
        $this->assertEquals("1)(2)(3", $template->render(['var1' => [1, 2, 3]]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested | join(', ') }}");
        $this->assertEquals("1, 2, 3", $template->render(['var1' => ['nested' => [1, 2, 3]]]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested | join(delimiter) }}");
        $this->assertEquals("1, 2, 3", $template->render(['var1' => ['nested' => [1, 2, 3]], 'delimiter' => ', ']));
    }

    public function testReplace()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'aba' | replace('a', 'b') }}");
        $this->assertEquals("bbb", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'aba' | replace('b', 'a') }}");
        $this->assertEquals("aaa", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'cec' | replace('a', 'b') }}");
        $this->assertEquals("cec", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'aec' | replace(string, newstring) }}");
        $this->assertEquals("abc", $template->render(['string' => 'e', 'newstring' => 'b']));

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | replace(string, newstring) }}");
        $this->assertEquals("abc", $template->render(['var1' => 'aec', 'string' => 'e', 'newstring' => 'b']));

        $template = new \ByJG\JinjaPhp\Template("{{ ['a', 'b', 'c'] | join | replace('b', 'e') }}");
        $this->assertEquals("aec", $template->render());
    }

    public function testDefault()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ var1 | default('abc') }}");
        $this->assertEquals("abc", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | default }}");
        $this->assertEquals("", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | default(var2) }}");
        $this->assertEquals("abc", $template->render(['var2' => 'abc']));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested | default }}");
        $this->assertEquals("nested", $template->render(['var1' => ['nested' => 'nested']]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested2 | default('abc') }}");
        $this->assertEquals("abc", $template->render(['var1' => ['nested' => 'nested']]));
    }

    public function testLength()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'abc' | length }}");
        $this->assertEquals("3", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ var1 | length }}");
        $this->assertEquals("3", $template->render(['var1' => 'abc']));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested | length }}");
        $this->assertEquals("3", $template->render(['var1' => ['nested' => 'abc']]));

        $template = new \ByJG\JinjaPhp\Template("{{ array | length }}");
        $this->assertEquals("3", $template->render(['array' => [1, 2, 3]]));

        $template = new \ByJG\JinjaPhp\Template("{{ var1.nested2 | default | length }}");
        $this->assertEquals("0", $template->render(['var1' => ['nested' => 'abc']]));
    }

    public function testTrim()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ ' abc ' | trim }}");
        $this->assertEquals("abc", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ ' abc ' | trim(' ') }}");
        $this->assertEquals("abc", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ ' abc ' | trim('a') }}");
        $this->assertEquals(" abc ", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'abc' | trim('a') }}");
        $this->assertEquals("bc", $template->render());
    }

    public function testCapitalize()
    {
        $template = new \ByJG\JinjaPhp\Template("{{ 'abc' | capitalize }}");
        $this->assertEquals("Abc", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'Abc' | capitalize }}");
        $this->assertEquals("Abc", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'ABC' | capitalize }}");
        $this->assertEquals("ABC", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{{ 'abc def' | capitalize }}");
        $this->assertEquals("Abc Def", $template->render());
    }
}