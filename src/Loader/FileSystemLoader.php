<?php

namespace ByJG\JinjaPhp\Loader;

use ByJG\JinjaPhp\Template;
use InvalidArgumentException;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var string
     */
    protected string $path;

    /**
     * @var string
     */
    protected mixed $extension;

    /**
     * FileSystemLoader constructor.
     * @param string $path
     * @param string $extension
     */
    public function __construct(string $path, string $extension = '.jinja')
    {
        $this->path = $path;
        $this->extension = $extension;

        if (!is_dir($this->path)) {
            throw new InvalidArgumentException("The path '$this->path' is not a valid directory.");
        }

        if (!is_readable($this->path)) {
            throw new InvalidArgumentException("The path '$this->path' is not readable.");
        }

        if (!file_exists($this->path)) {
            throw new InvalidArgumentException("The path '$this->path' does not exist.");
        }
    }

    /**
     * @param string $template
     * @return Template
     */
    public function getTemplate(string $template): Template
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . $template;
        if (!str_ends_with($filename, $this->extension)) {
            $filename .= $this->extension;
        }

        if (!file_exists($filename)) {
            throw new InvalidArgumentException("The template '$filename' does not exist.");
        }

        return new Template(file_get_contents($filename));
    }
}