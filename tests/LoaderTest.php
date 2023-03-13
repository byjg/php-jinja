<?php

namespace Test;

use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\JinjaPhp\Loader\StringLoader;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class LoaderTest extends TestCase
{
    public function testLoader()
    {
        $loader = new StringLoader();
        $template = $loader->getTemplate("test {{ var }}");
        $this->assertEquals('test ok', $template->render(['var' => 'ok']));
    }

    public function testFileLoader()
    {
        $loader = new FileSystemLoader(__DIR__ . '/templates');
        $template = $loader->getTemplate("file.txt");
        $this->assertEquals('Simple template ok', $template->render(['var' => 'ok']));
    }

    public function testFileLoaderWithExtension()
    {
        $loader = new FileSystemLoader(__DIR__ . '/templates');
        $template = $loader->getTemplate("file.txt.jinja");
        $this->assertEquals('Simple template ok', $template->render(['var' => 'ok']));
    }

    public function testFileLoaderDoesntExist()
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The template');
        $loader = new FileSystemLoader(__DIR__ . '/templates');
        $template = $loader->getTemplate("file2.txt");
        $this->assertEquals('Simple template ok', $template->render(['var' => 'ok']));
    }

    public function testFileLoaderFolderDoesntExist() {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The path');
        $loader = new FileSystemLoader(__DIR__ . '/templates2');
        $template = $loader->getTemplate("file.txt");
        $this->assertEquals('Simple template ok', $template->render(['var' => 'ok']));
    }

}