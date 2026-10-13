<?php

namespace Tests;

use ByJG\JinjaPhp\Loader\FileSystemLoader;
use ByJG\JinjaPhp\Loader\StringLoader;
use ByJG\JinjaPhp\Exception\LoaderException;
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

    /**
     * LoaderException existed and was documented, but nothing threw it. It is an
     * InvalidArgumentException, so code that already catches that keeps working.
     */
    public function testLoaderErrorsAreLoaderExceptions(): void
    {
        try {
            new FileSystemLoader(__DIR__ . '/templates2');
            $this->fail('A directory that does not exist must be rejected');
        } catch (LoaderException $ex) {
            $this->assertInstanceOf(InvalidArgumentException::class, $ex);
            $this->assertStringContainsString('The path', $ex->getMessage());
        }

        $this->expectException(LoaderException::class);
        $this->expectExceptionMessage('The template');
        (new FileSystemLoader(__DIR__ . '/templates'))->getTemplate("file2.txt");
    }

    /**
     * A name is joined to the templates directory, so "../" used to walk out of it and load any
     * file with the loader's extension.
     */
    public function testTemplateOutsideTheDirectoryIsRejected(): void
    {
        $base = sys_get_temp_dir() . '/jinja_' . uniqid();
        mkdir($base . '/templates/emails', 0777, true);
        file_put_contents($base . '/templates/emails/reset.jinja', 'inside {{ var }}');
        file_put_contents($base . '/secret.jinja', 'outside');

        try {
            $loader = new FileSystemLoader($base . '/templates');

            // Subdirectories, and a ".." that stays inside, are fine
            $this->assertEquals('inside ok', $loader->getTemplate('emails/reset')->render(['var' => 'ok']));
            $this->assertEquals('inside ok', $loader->getTemplate('emails/../emails/reset')->render(['var' => 'ok']));

            // A base path written with ".." is fine too
            $viaDots = new FileSystemLoader($base . '/templates/emails/..');
            $this->assertEquals('inside ok', $viaDots->getTemplate('emails/reset')->render(['var' => 'ok']));

            try {
                $loader->getTemplate('../secret');
                $this->fail('A template outside the directory must be rejected');
            } catch (LoaderException $ex) {
                $this->assertStringContainsString('does not exist', $ex->getMessage());
            }

            $this->expectException(LoaderException::class);
            $loader->getTemplate('emails/../../secret');
        } finally {
            unlink($base . '/templates/emails/reset.jinja');
            unlink($base . '/secret.jinja');
            rmdir($base . '/templates/emails');
            rmdir($base . '/templates');
            rmdir($base);
        }
    }

}
