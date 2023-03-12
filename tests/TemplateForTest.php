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

    public function testForDict()
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
}