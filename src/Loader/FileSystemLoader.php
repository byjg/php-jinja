<?php

namespace ByJG\JinjaPhp\Loader;

use ByJG\JinjaPhp\Template;

class FileSystemLoader implements LoaderInterface
{
    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $extension;

    /**
     * FileSystemLoader constructor.
     * @param string $path
     */
    public function __construct($path, $extension = '.jinja')
    {
        $this->path = $path;
        $this->extension = $extension;

        if (!is_dir($this->path)) {
            throw new \InvalidArgumentException("The path '{$this->path}' is not a valid directory.");
        }

        if (!is_readable($this->path)) {
            throw new \InvalidArgumentException("The path '{$this->path}' is not readable.");
        }

        if (!file_exists($this->path)) {
            throw new \InvalidArgumentException("The path '{$this->path}' does not exist.");
        }
    }

    /**
     * @param string $template
     * @return Template
     */
    public function getTemplate($template)
    {
        $filename = $this->path . DIRECTORY_SEPARATOR . $template;
        if (substr($filename, -strlen($this->extension)) !== $this->extension) {
            $filename .= $this->extension;
        }

        if (!file_exists($filename)) {
            throw new \InvalidArgumentException("The template '{$filename}' does not exist.");
        }

        return new Template(file_get_contents($filename));
    }
}