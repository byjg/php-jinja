<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class TemplateIfTest extends TestCase
{
    public function testIf()
    {
        $template = new \ByJG\JinjaPhp\Template("{% if true %}true{% endif %}");
        $this->assertEquals("true", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% endif %}");
        $this->assertEquals("", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if 10 < 4 %}true{% endif %}");
        $this->assertEquals("", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if 10 > 4 %}true{% endif %}");
        $this->assertEquals("true", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if (var1 == 'test') %}true{% endif %}");
        $this->assertEquals("true", $template->render(['var1' => 'test']));

        $template = new \ByJG\JinjaPhp\Template("{% if var1 != 'test' %}true{% endif %}");
        $this->assertEquals("", $template->render(['var1' => 'test']));

        $template = new \ByJG\JinjaPhp\Template("{% if var1 == 'test' %}true{% endif %}");
        $this->assertEquals("true", $template->render(['var1' => 'test']));

        $template = new \ByJG\JinjaPhp\Template("{% if true %}true{% else %}false{% endif %}");
        $this->assertEquals("true", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% else %}false{% endif %}");
        $this->assertEquals("false", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% if var1 == 'test' %}true{%else%}false{% endif %}");
        $this->assertEquals("false", $template->render(['var1' => 'notest']));

        $template = new \ByJG\JinjaPhp\Template("{% if (var1 == 'abc') %}Show result of {{ var2 }}{% endif %}");
        $this->assertEquals("Show result of 123", $template->render(['var1' => 'abc', 'var2' => 123]));

        $template = new \ByJG\JinjaPhp\Template("{% if var1 == 'abc' && var2 == 123 %}Show result of {{ var3 }}{% else %}Show nothing{% endif %}");
        $this->assertEquals("Show result of 456", $template->render(['var1' => 'abc', 'var2' => 123, 'var3' => 456]));

        $template = new \ByJG\JinjaPhp\Template("{% if var1 == 'abc' && !(var2 == 123) %}Show result of {{ var3 }}{% else %}Show nothing{% endif %}");
        $this->assertEquals("Show nothing", $template->render(['var1' => 'abc', 'var2' => 123, 'var3' => 456]));

        // $template = new \ByJG\JinjaPhp\Template("{% if true %}true{% elseif true %}false{% endif %}");
        // $this->assertEquals("true", $template->render());

        // $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% elseif true %}false{% endif %}");
        // $this->assertEquals("false", $template->render());

        // $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% elseif false %}false{% endif %}");
        // $this->assertEquals("", $template->render());

        // $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% elseif false %}false{% else %}else{% endif %}");
        // $this->assertEquals("else", $template->render());

        // $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% elseif false %}false{% elseif true %}elseif{% endif %}");
        // $this->assertEquals("elseif", $template->render());

        // $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% elseif false %}false{% elseif false %}elseif{% endif %}");
        // $this->assertEquals("", $template->render());

        // $template = new \ByJG\JinjaPhp\Template("{% if false %}true{% elseif false %}false{% elseif false %}elseif{% else %}else{% endif %}");
        // $this->assertEquals("else", $template->render
    }
}