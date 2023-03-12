<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class TemplateForTest extends TestCase
{
    public function testFor()
    {
        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array %}{{ xyz }}{% endfor %}");
        $this->assertEquals("val1val2", $template->render(['array' => ['val1', 'val2']]));

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in [1, 2, 3] %}{{ xyz }}{% endfor %}");
        $this->assertEquals("123", $template->render());

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in var1 | split(',') %}{{ xyz }}{% endfor %}");
        $this->assertEquals("123", $template->render(['var1' => '1,2,3']));

        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array.nested %}{{ xyz }}{% endfor %}");
        $this->assertEquals("val1val2", $template->render(['array' => ['nested' => ['val1', 'val2']]]));

    }
}