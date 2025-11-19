<?php

namespace Tests;

use ByJG\JinjaPhp\Exception\TemplateParseException;
use PHPUnit\Framework\TestCase;

class TemplateVariablesTest extends TestCase
{
    public function testRender(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => 'value1']));
    }

    public function testRenderWithUndefined(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $template->withUndefined(new \ByJG\JinjaPhp\Undefined\DebugUndefined());
        $this->assertEquals("Test var1: {{ NOT_FOUND: var1 }}.", $template->render(['var2' => 'value1']));
    }

    public function testRenderWithUndefinedStrict(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $template->withUndefined(new \ByJG\JinjaPhp\Undefined\StrictUndefined());
        $this->expectException(\Exception::class);
        $template->render(['var2' => 'value1']);
    }

    public function testRenderWithUndefinedDefault(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1 }}.");
        $template->withUndefined(new \ByJG\JinjaPhp\Undefined\DefaultUndefined());
        $this->assertEquals("Test var1: .", $template->render(['var2' => 'value1']));
    }

    public function testInvalidVar(): void
    {
        $this->expectException(TemplateParseException::class);
        $this->expectExceptionMessage("Variable xyz not defined");
        $template = new \ByJG\JinjaPhp\Template("{% for xyz in array %}{{ xyz }}{% endfor %}{{ xyz }}");
        $template->render(['array' => ['val1', 'val2']]);
    }

    public function testRenderArray(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1.0 }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => ['value1']]));
    }

    // TODO: test with {{ var1[0] }}

    public function testRenderAssociativeArray(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1.key1 }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => ['key1' => 'value1']]));
    }

    public function testRenderArrayWithBrackets(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1[0] }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => ['value1']]));
    }

    public function testRenderAssociativeArrayWithBrackets(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1['key1'] }}.");
        $this->assertEquals("Test var1: value1.", $template->render(['var1' => ['key1' => 'value1', 'key2' => 'value2']]));
    }
    
    public function testRenderArrayWithBracketsAndDotNotation(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1[1].nested }}.");
        $this->assertEquals("Test var1: nestedvalue2.", $template->render(['var1' => [['nested' => 'nestedvalue'], ['nested' => 'nestedvalue2']]]));
    }
    
    public function testRenderArrayWithNestedBrackets(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1['items'][0] }}.");
        $this->assertEquals("Test var1: item1.", $template->render(['var1' => ['items' => ['item1', 'item2']]]));
    }

    public function testRenderArrayWithNestedBracketsAndDotNotation(): void
    {
        $template = new \ByJG\JinjaPhp\Template("Test var1: {{ var1['items'].1 }}.");
        $this->assertEquals("Test var1: item2.", $template->render(['var1' => ['items' => ['item1', 'item2']]]));
    }
}