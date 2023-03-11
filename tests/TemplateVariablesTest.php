<?php

namespace Test;

use PHPUnit\Framework\TestCase;

class TemplateVariablesTest extends TestCase
{
    public function testRender()
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => 'value1']));
    }

    public function testRenderWithUndefined()
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $template->withUndefined(new \ByJG\JinjaPhp\Undefined\DebugUndefined());
        $this->assertEquals("Test var1: {{ NOT_FOUND: var1 }}.", $template->render(['var2' => 'value1']));
    }

    public function testRenderWithUndefinedStrict()
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $template->withUndefined(new \ByJG\JinjaPhp\Undefined\StrictUndefined());
        $this->expectException(\Exception::class);
        $template->render(['var2' => 'value1']);
    }

    public function testRenderWithUndefinedDefault()
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $template->withUndefined(new \ByJG\JinjaPhp\Undefined\DefaultUndefined());
        $this->assertEquals("Test var1: .", $template->render(['var2' => 'value1']));
    }

    public function testRenderArray()
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1.0 }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => ['value1']]));
    }

    // TODO: test with {{ var1[0] }}

    public function testRenderAssociativeArray()
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1.key1 }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => ['key1' => 'value1']]));
    }

    // TODO: test with {{ var1['key1'] }}

}