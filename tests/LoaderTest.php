<?php

namespace Tests;

use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\JinjaPhp\Loader\StringLoader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    public function testLoader(): void
    {
        $loader = new StringLoader();
        $template = $loader->getTemplate("test {{ var }}");
        $this->assertEquals('test ok', $template->render(['var' => 'ok']));
    }

    public function testFileLoader(): void
    {
        $loader = new FileSystemLoader(__DIR__ . '/templates');
        $template = $loader->getTemplate("file.txt");
        $this->assertEquals('Simple template o"k testing \"test\"', $template->render(['var' => 'o"k testing \"test\"']));
    }

    public function testFileLoaderWithExtension(): void
    {
        $loader = new FileSystemLoader(__DIR__ . '/templates');
        $template = $loader->getTemplate("file.txt.jinja");
        $this->assertEquals("Simple template o'k \\'test\\'", $template->render(['var' => "o'k \\'test\\'"]));
    }

    public function testFileLoaderDoesntExist(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The template');
        $loader = new FileSystemLoader(__DIR__ . '/templates');
        $template = $loader->getTemplate("file2.txt");
        $this->assertEquals('Simple template ok', $template->render(['var' => 'ok']));
    }

    public function testFileLoaderFolderDoesntExist(): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The path');
        $loader = new FileSystemLoader(__DIR__ . '/templates2');
        $template = $loader->getTemplate("file.txt");
        $this->assertEquals('Simple template ok', $template->render(['var' => 'ok']));
    }

}